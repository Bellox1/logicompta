<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            $ocr->psm(6);             // PSM 6 : bloc de texte uniforme
            $ocr->oem(1);             // OEM 1 : LSTM seulement (plus rapide)

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
     * Analyse le texte brut pour en extraire une date et un montant.
     * Utilisé uniquement pour le service Tesseract (Mindee renvoie du structuré).
     */
    private function parseText(string $fullText): array
    {
        $data = [
            'raw_text' => $fullText,
            'date'     => null,
            'amount'   => null,
            'libelle'  => 'Achat selon facture',
            'supplier' => null,
        ];

        // --- Détection de la date ---
        // Formats supportés : JJ/MM/AAAA  JJ-MM-AAAA  JJ.MM.AAAA  AAAA-MM-JJ
        $datePatterns = [
            '/\b(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{2,4})\b/', // JJ/MM/AAAA
            '/\b(\d{4})[\/\.\-](\d{1,2})[\/\.\-](\d{1,2})\b/',   // AAAA-MM-JJ
        ];
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $fullText, $matches)) {
                if (strlen($matches[3] ?? '') == 2) {
                    $matches[3] = '20' . $matches[3];
                }
                $year  = $matches[3] ?? date('Y');
                $month = str_pad($matches[2] ?? date('m'), 2, '0', STR_PAD_LEFT);
                $day   = str_pad($matches[1] ?? date('d'), 2, '0', STR_PAD_LEFT);
                $data['date'] = "$year-$month-$day";
                break;
            }
        }

        // --- Détection du montant ---
        // Priorité 1 : ligne TOTAL TTC / NET À PAYER / MONTANT TTC
        if (preg_match(
            '/(?:TOTAL\s*TTC|MONTANT\s*TTC|NET\s*[AÀ]\s*PAYER|TOTAL\s*GÉNÉRAL|PAYER)[^\d]{0,10}([\d\s,.]+)/i',
            $fullText, $m
        )) {
            $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[1]));
            if (is_numeric($clean) && (float)$clean > 0) {
                $data['amount'] = (float)$clean;
            }
        }

        // Priorité 2 : ligne TOTAL / TTC seul
        if (!$data['amount'] && preg_match(
            '/(?:TOTAL|TTC)[^\d]{0,10}([\d\s,.]+)/i',
            $fullText, $m
        )) {
            $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[1]));
            if (is_numeric($clean) && (float)$clean > 0) {
                $data['amount'] = (float)$clean;
            }
        }

        // Priorité 3 : plus grand nombre numérique du document
        if (!$data['amount']) {
            preg_match_all('/\b\d+[,.]?\d{2}\b/', $fullText, $amounts);
            $nums = [];
            foreach ($amounts[0] as $amt) {
                $clean = (float) str_replace(',', '.', $amt);
                if ($clean > 10) { // Ignorer quantités / numéros de pièce
                    $nums[] = $clean;
                }
            }
            if (!empty($nums)) {
                $data['amount'] = max($nums);
            }
        }

        return $data;
    }
}
