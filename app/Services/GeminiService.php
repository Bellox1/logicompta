<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    // On définit une liste de modèles pour le fallback automatique
    protected $models = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-1.5-flash-latest'
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
    }

    /**
     * Traite le texte OCR pour générer une écriture comptable JSON avec résilience
     */
    public function processOCR($ocrText)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Clé API Gemini non configurée.");
        }

        $truncatedText = mb_substr($ocrText, 0, 3000);
        $prompt = $this->getAccountingPrompt($truncatedText);

        $maxAttempts = 3;
        
        // Boucle sur les modèles disponibles pour le fallback
        foreach ($this->models as $modelName) {
            $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent";
            $url = $baseUrl . '?key=' . $this->apiKey;
            
            $attempt = 0;
            while ($attempt < $maxAttempts) {
                try {
                    $response = Http::timeout(30)->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'response_mime_type' => 'application/json',
                        ]
                    ]);

                    // ✅ SUCCESS
                    if ($response->successful()) {
                        $data = $response->json();
                        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                        $content = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));

                        Log::info("Gemini Success with model {$modelName}: " . $content);

                        $decoded = json_decode($content, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            Log::error('JSON Error: ' . json_last_error_msg());
                            throw new \Exception("Format IA invalide.");
                        }

                        return $decoded;
                    }

                    $status = $response->status();

                    // 🔁 RETRY LOGIC (Surcharge ou Quota temporaire)
                    if (in_array($status, [503, 429, 500])) {
                        Log::warning("Gemini retry ($status) for $modelName - attempt $attempt");
                        sleep(pow(2, $attempt)); // Backoff exponentiel (1s, 2s, 4s)
                        $attempt++;
                        continue; 
                    }

                    // ❌ AUTRE ERREUR (404, 400 etc.) -> On change de modèle
                    Log::error("Gemini Error $status for $modelName: " . $response->body());
                    break; // Sort de la boucle de retry pour essayer le modèle suivant

                } catch (\Exception $e) {
                    Log::error("Gemini Exception for $modelName: " . $e->getMessage());
                    break; // On change de modèle
                }
            }
        }

        throw new \Exception("Gemini indisponible ou quota épuisé après plusieurs tentatives sur tous les modèles.");
    }

    protected function getAccountingPrompt($ocrText)
    {
        return <<<PROMPT
Tu es un expert comptable.

À partir du texte OCR ci-dessous, tu dois générer une écriture comptable structurée.

RÈGLES OBLIGATOIRES :
- Retourne UNIQUEMENT du JSON valide
- Une facture = une écriture
- Chaque écriture contient plusieurs lignes
- Chaque ligne doit avoir : ligne, compte, sous_compte, debit, credit
- Débit = Crédit (équilibré)
- Si information manquante, utilise null ou 0
- Ne jamais inventer de données
- Utilise uniquement les comptes suivants :
  57 = caisse
  52 = banque
  605 = achats carburant / charges
  701 = ventes
  31 = marchandises
  22 = immobilisations

STRUCTURE ATTENDUE :

{
  "date": "YYYY-MM-DD",
  "reference": "",
  "journal": "ACHAT|VENTE|CAISSE|BANQUE",
  "type": "achat|vente|autre",
  "libelle": "",
  "total": 0,
  "lignes": [
    {
      "ligne": 1,
      "compte": "605",
      "sous_compte": "605001",
      "debit": 100,
      "credit": 0,
      "libelle": "Achat carburant"
    }
  ]
}

TEXTE OCR :
<<<
{$ocrText}
>>>
PROMPT;
    }
}
