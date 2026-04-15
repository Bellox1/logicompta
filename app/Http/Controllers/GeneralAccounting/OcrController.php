<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\AiService;
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * OcrController — Extraction de texte sur factures
 *
 * Deux services disponibles, choix via le paramètre POST `service` :
 *
 *  ┌─────────────────┬──────────────────────────────────────────────────────┐
 *  │ service=tesseract│ Tesseract OCR local (gratuit, hors-ligne, RGPD safe) │
 *  │ service=mindee   │ Mindee Cloud API (précis, structuré, données tiers)  │
 *  └─────────────────┴──────────────────────────────────────────────────────┘
 *
 * Le service par défaut est défini dans .env → OCR_SERVICE_DEFAULT
 *
 * Prérequis Tesseract (serveur local) :
 *   sudo apt install tesseract-ocr tesseract-ocr-fra imagemagick
 *   composer require thiagoalessio/tesseract_ocr
 *
 * Clés Mindee (.env) :
 *   MINDEE_API_KEY=<votre_clé>
 */
class OcrController extends Controller
{
    /**
     * Point d'entrée unique — route POST /journal/ocr-import
     *
     * Paramètres :
     *  - file    (required) : image jpg/png/gif/pdf, max 10 Mo
     *  - service (optional) : 'tesseract' | 'mindee'
     *
     * Réponse JSON :
     * {
     *   "raw_text"  : "...",        // Texte brut extrait
     *   "date"      : "2024-01-15", // Date détectée (nullable)
     *   "amount"    : 1250.00,      // Montant TTC/TOTAL (nullable)
     *   "libelle"   : "...",        // Libellé suggéré
     *   "service"   : "tesseract",  // Service utilisé
     *   "supplier"  : "...",        // Fournisseur (Mindee uniquement)
     * }
     */
    public function ocrImport(Request $request)
    {
        $request->validate([
            'file'    => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp,pdf|max:10240',
            'service' => 'nullable|string|in:tesseract,mindee',
        ]);

        // Choisir le service : paramètre POST > variable .env > 'tesseract' par défaut
        $service = $request->input('service', env('OCR_SERVICE_DEFAULT', 'tesseract'));

        if ($service === 'mindee') {
            return $this->runMindee($request);
        }

        return $this->runTesseract($request);
    }

    /* =========================================================================
     * SERVICE 1 : TESSERACT (local, gratuit)
     * ========================================================================= */

    /**
     * Extraction OCR via Tesseract installé sur le serveur.
     *
     * Optimisations pour la vitesse :
     *  1. Prétraitement ImageMagick : conversion en niveaux de gris + augmentation
     *     contraste + résolution 300 dpi → Tesseract lit beaucoup plus vite
     *  2. PSM 6 (bloc de texte uniforme) : adapté aux factures bien structurées
     *  3. Timeout 30 s : évite les blocages infinis
     *  4. OEM 1 (LSTM only) : moteur neuronal seul, plus rapide que le mode mixte
     */
    private function runTesseract(Request $request): \Illuminate\Http\JsonResponse
    {
        $file    = $request->file('file');
        $srcPath = $file->getRealPath();

        // --- Prétraitement avec ImageMagick (si disponible) ---
        // Convertit l'image en N&B amélioré à 300 dpi pour accélérer Tesseract
        $processedPath = sys_get_temp_dir() . '/ocr_preprocessed_' . uniqid() . '.png';
        $convertAvailable = !empty(shell_exec('which convert 2>/dev/null'));

        if ($convertAvailable) {
            // -colorspace Gray  : niveaux de gris (Tesseract préfère ça)
            // -contrast-stretch 0 : améliore la lisibilité du texte
            // -density 300        : résolution cible 300 dpi (optimal pour Tesseract)
            // -threshold 50%      : binarisation pour texte net
            $cmd = sprintf(
                'convert %s -colorspace Gray -contrast-stretch 0 -density 300 -threshold 50%% %s 2>&1',
                escapeshellarg($srcPath),
                escapeshellarg($processedPath)
            );
            shell_exec($cmd);
            $imagePath = file_exists($processedPath) ? $processedPath : $srcPath;
        } else {
            $imagePath = $srcPath;
        }

        try {
            $ocr = new TesseractOCR($imagePath);
            $ocr->lang('fra', 'eng'); // Français + Anglais
            $ocr->psm(3);             // PSM 3 : Entièrement automatique (mieux pour les colonnes/tableaux)
            $ocr->oem(1);             // OEM 1 : LSTM seulement (plus rapide/moderne)

            $fullText = $ocr->run();

        } catch (\Exception $e) {
            // Nettoyer le fichier temporaire
            if ($convertAvailable && file_exists($processedPath)) {
                @unlink($processedPath);
            }
            return response()->json([
                'error' => 'Tesseract error : ' . $e->getMessage()
                         . ' — Vérifiez : sudo apt install tesseract-ocr tesseract-ocr-fra',
            ], 500);
        }

        // Nettoyage fichier temporaire
        if ($convertAvailable && file_exists($processedPath)) {
            @unlink($processedPath);
        }

        if (empty(trim($fullText))) {
            return response()->json([
                'error' => 'Aucun texte détecté. Vérifiez la qualité de l\'image (300 dpi recommandé).',
            ], 404);
        }

        $parsed = $this->parseText($fullText);

        return response()->json(array_merge($parsed, [
            'service' => 'tesseract',
        ]));
    }

    /* =========================================================================
     * SERVICE 2 : MINDEE (cloud, structuré)
     * =========================================================================
     *
     * Mindee Invoice API v4 — extrait automatiquement :
     *  - Date de facture, date d'échéance
     *  - Montant HT, TVA, TTC
     *  - Nom du fournisseur, numéro de facture
     *  - Lignes de détail
     *
     * Endpoint : POST https://api.mindee.net/v1/products/mindee/invoices/v4/predict
     * Auth     : Token {MINDEE_API_KEY}
     * Champ    : document (multipart/form-data)
     */
    private function runMindee(Request $request): \Illuminate\Http\JsonResponse
    {
        $apiKey = env('MINDEE_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Clé API Mindee non configurée. Ajoutez MINDEE_API_KEY dans le fichier .env',
            ], 400);
        }

        $file = $request->file('file');

        try {
            // Appel API Mindee avec le fichier en multipart
            // connectTimeout : temps max pour établir la connexion DNS/TCP
            // timeout        : temps max total pour recevoir la réponse complète
            $response = Http::withToken($apiKey)
                ->connectTimeout(15)   // 15s max pour établir la connexion
                ->timeout(60)          // 60s max pour recevoir la réponse (analyse IA)
                ->attach(
                    'document',        // Nom du champ attendu par Mindee
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post('https://api.mindee.net/v1/products/mindee/invoices/v4/predict');

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Erreur Mindee API (' . $response->status() . ') : ' . $response->body(),
                ], 500);
            }

            $result    = $response->json();
            $prediction = $result['document']['inference']['prediction'] ?? [];

            // --- Extraction des champs structurés ---
            $date     = $prediction['date']['value']          ?? null;
            $totalTTC = $prediction['total_amount']['value']  ?? null;
            $totalHT  = $prediction['total_net']['value']     ?? null;
            $supplier = $prediction['supplier_name']['value'] ?? null;
            $invoiceN = $prediction['invoice_number']['value'] ?? null;

            // Texte brut reconstitué depuis les blocs de texte de l'OCR
            $rawText  = '';
            foreach ($result['document']['inference']['pages'] ?? [] as $page) {
                foreach ($page['prediction']['line_items'] ?? [] as $line) {
                    $rawText .= ($line['description'] ?? '') . "\n";
                }
            }
            if (empty($rawText)) {
                $rawText = "Facture traitée par Mindee (texte structuré)";
            }

            // Libellé intelligent en fonction du fournisseur
            $libelle = $supplier
                ? "Achat — $supplier" . ($invoiceN ? " / Facture N° $invoiceN" : '')
                : 'Achat selon facture';

            return response()->json([
                'raw_text' => $rawText,
                'date'     => $date,
                'amount'   => $totalTTC ?? $totalHT,
                'libelle'  => $libelle,
                'supplier' => $supplier,
                'service'  => 'mindee',
                // Données supplémentaires de Mindee
                'mindee'   => [
                    'total_ht'       => $totalHT,
                    'total_ttc'      => $totalTTC,
                    'invoice_number' => $invoiceN,
                    'supplier'       => $supplier,
                ],
            ]);

        } catch (\Exception $e) {
            $msg = $e->getMessage();

            // Erreur cURL 28 = timeout réseau (serveur ne peut pas joindre api.mindee.net)
            if (str_contains($msg, 'cURL error 28') || str_contains($msg, 'timed out')) {
                return response()->json([
                    'error' => 'Mindee injoignable : le serveur n\'arrive pas à contacter api.mindee.net. '
                             . 'Vérifiez la connexion internet du serveur, ou utilisez le service Local (Tesseract).',
                ], 503);
            }

            return response()->json([
                'error' => 'Erreur technique Mindee : ' . $msg,
            ], 500);
        }
    }

    /* =========================================================================
     * UTILITAIRE : Parsing date & montant (utilisé par Tesseract)
     * ========================================================================= */

    /**
     * Analyse le texte brut et extrait un maximum de champs de la facture.
     * Utilisé uniquement pour Tesseract (Mindee renvoie du structuré).
     *
     * Champs tentés :
     *  - date, date_echeance
     *  - montant_ht, tva_taux, tva_montant, montant_ttc (= amount)
     *  - numero_facture, numero_avoir
     *  - fournisseur (premier nom détecté)
     *  - siret, numero_tva
     *  - telephone, email
     *  - mode_paiement
     *  - lignes (tableau des lignes article détectées)
     */
    private function parseText(string $fullText): array
    {
        $data = [
            'raw_text'        => $fullText,
            // --- Montants ---
            'amount'          => null,   // Montant TTC (principal, utilisé par le formulaire)
            'montant_ttc'     => null,
            'montant_ht'      => null,
            'tva_taux'        => null,
            'tva_montant'     => null,
            // --- Dates ---
            'date'            => null,
            'date_echeance'   => null,
            // --- Identification ---
            'libelle'         => 'Achat selon facture',
            'fournisseur'     => null,
            'numero_facture'  => null,
            'numero_avoir'    => null,
            'siret'           => null,
            'numero_tva'      => null,
            // --- Coordonnées ---
            'telephone'       => null,
            'email'           => null,
            'mode_paiement'   => null,
            // --- Lignes détail ---
            'lignes'          => [],
            // --- Méta ---
            'service'         => 'tesseract',
        ];

        // ================================================================
        // 1. MONTANT TTC (priorité 1 : libellé explicite)
        // ================================================================
        $ttcPatterns = [
            '/(?:TOTAL\s*TTC|MONTANT\s*TTC|NET\s*[AÀ]\s*PAYER|TTC)[^\d]{0,15}([\d\s,.]+)/i',
            '/(?:TOTAL\s*GÉNÉRAL|TOTAL\s*FACTURE|TOTAL)[^\d]{0,10}([\d\s,.]+)/i',
        ];
        foreach ($ttcPatterns as $p) {
            if (preg_match($p, $fullText, $m)) {
                $clean = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[1]));
                if ($clean > 0) { $data['montant_ttc'] = $clean; $data['amount'] = $clean; break; }
            }
        }

        // ================================================================
        // 2. MONTANT HT
        // ================================================================
        if (preg_match('/(?:TOTAL\s*HT|MONTANT\s*HT|HT)[^\d]{0,15}([\d\s,.]+)/i', $fullText, $m)) {
            $clean = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[1]));
            if ($clean > 0) $data['montant_ht'] = $clean;
        }

        // ================================================================
        // 3. TVA taux et montant
        // ================================================================
        if (preg_match('/TVA\s*[:\-]?\s*([\d,\.]+)\s*%/i', $fullText, $m)) {
            $data['tva_taux'] = (float) str_replace(',', '.', $m[1]);
        }
        if (preg_match('/(?:MONTANT\s*TVA|TVA)[^\d]{0,15}([\d\s,.]+)/i', $fullText, $m)) {
            $clean = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[1]));
            if ($clean > 0 && $clean < 1000000) $data['tva_montant'] = $clean;
        }

        // Fallback montant : plus grand nombre du texte
        if (!$data['amount']) {
            preg_match_all('/\b\d+[,.]?\d{2}\b/', $fullText, $amounts);
            $nums = array_filter(array_map(fn($a) => (float) str_replace(',', '.', $a), $amounts[0]), fn($n) => $n > 10);
            if (!empty($nums)) { $data['amount'] = max($nums); $data['montant_ttc'] = max($nums); }
        }

        // ================================================================
        // 4. DATE DE FACTURE
        // ================================================================
        $datePatterns = [
            '/(?:date|le|émis|du)[^\d]{0,10}(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{2,4})/i',
            '/\b(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{2,4})\b/',
            '/\b(\d{4})[\/\.\-](\d{1,2})[\/\.\-](\d{1,2})\b/',
        ];
        foreach ($datePatterns as $p) {
            if (preg_match($p, $fullText, $m) && count($m) >= 4) {
                $y = strlen($m[3]) == 2 ? '20'.$m[3] : $m[3];
                $data['date'] = $y.'-'.str_pad($m[2],2,'0',STR_PAD_LEFT).'-'.str_pad($m[1],2,'0',STR_PAD_LEFT);
                break;
            }
        }

        // ================================================================
        // 5. DATE D'ÉCHÉANCE
        // ================================================================
        if (preg_match('/(?:échéan|due date|payer avant|régler avant)[^\d]{0,15}(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{2,4})/i', $fullText, $m)) {
            $y = strlen($m[3]) == 2 ? '20'.$m[3] : $m[3];
            $data['date_echeance'] = $y.'-'.str_pad($m[2],2,'0',STR_PAD_LEFT).'-'.str_pad($m[1],2,'0',STR_PAD_LEFT);
        }

        // ================================================================
        // 6. NUMÉRO DE FACTURE
        // ================================================================
        if (preg_match('/(?:facture\s*n[°o]?|invoice\s*n[°o]?|n[°o]\s*facture|réf[.\s:]*)\s*[:\-]?\s*([A-Z0-9\-\/]+)/i', $fullText, $m)) {
            $data['numero_facture'] = trim($m[1]);
        }

        // ================================================================
        // 7. NUMÉRO D'AVOIR
        // ================================================================
        if (preg_match('/(?:avoir\s*n[°o]?|note\s*de\s*crédit)\s*[:\-]?\s*([A-Z0-9\-\/]+)/i', $fullText, $m)) {
            $data['numero_avoir'] = trim($m[1]);
        }

        // ================================================================
        // 8. FOURNISSEUR (première ligne non vide du document)
        // ================================================================
        $lines = array_filter(array_map('trim', explode("\n", $fullText)));
        $firstLine = reset($lines);
        if ($firstLine && strlen($firstLine) > 2 && strlen($firstLine) < 80) {
            $data['fournisseur'] = $firstLine;
            $data['libelle'] = "Achat — $firstLine";
        }

        // ================================================================
        // 9. SIRET / SIREN
        // ================================================================
        if (preg_match('/(?:SIRET|SIREN)\s*[:\-]?\s*(\d[\d\s]{12,17})/i', $fullText, $m)) {
            $data['siret'] = preg_replace('/\s/', '', $m[1]);
        }

        // ================================================================
        // 10. NUMÉRO DE TVA INTRACOMMUNAUTAIRE
        // ================================================================
        if (preg_match('/(?:N[°o]?\s*TVA|TVA\s*intra)[^\w]{0,5}([A-Z]{2}[\d]{9,12})/i', $fullText, $m)) {
            $data['numero_tva'] = trim($m[1]);
        }

        // ================================================================
        // 11. TÉLÉPHONE
        // ================================================================
        if (preg_match('/(?:tel[.\s:]*|tél[.\s:]*|phone[.\s:]*|contact[.\s:]*)?(\+?[\d\s\.\-]{10,17})(?:\s|$)/i', $fullText, $m)) {
            $candidate = preg_replace('/\s/', '', $m[1]);
            if (strlen($candidate) >= 10) $data['telephone'] = $candidate;
        }

        // ================================================================
        // 12. EMAIL
        // ================================================================
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $fullText, $m)) {
            $data['email'] = $m[0];
        }

        // ================================================================
        // 13. MODE DE PAIEMENT
        // ================================================================
        if (preg_match('/(?:mode\s*(?:de\s*)?paiement|règlement|payment)[^\n]{0,5}([^\n]+)/i', $fullText, $m)) {
            $data['mode_paiement'] = trim($m[1]);
        } elseif (preg_match('/\b(virement|chèque|espèces|carte|CB|prélèvement|mobile money|wave|orange money|momo)\b/i', $fullText, $m)) {
            $data['mode_paiement'] = ucfirst(strtolower($m[1]));
        }

        // ================================================================
        // 14. LIGNES DE DÉTAIL (description + montant sur la même ligne)
        // ================================================================
        $lignes = [];
        foreach ($lines as $line) {
            // Ligne avec texte + nombre (potentiellement une ligne d'article)
            if (preg_match('/^(.{3,40}?)\s+([\d\s,.]+)\s*$/', $line, $m)) {
                $montant = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[2]));
                if ($montant > 0 && $montant < 10000000) {
                    $lignes[] = ['description' => trim($m[1]), 'montant' => $montant];
                }
            }
        }
        $data['lignes'] = array_slice($lignes, 0, 15); // Max 15 lignes

        return $data;
    }

    /**
     * Traitement IA du texte brut (OpenAI avec Fallback Gemini)
     */
    public function processWithAI(Request $request)
    {
        $request->validate([
            'raw_text' => 'required|string',
        ]);

        $ai = new AiService();
        
        try {
            $result = $ai->processOCR($request->raw_text);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
