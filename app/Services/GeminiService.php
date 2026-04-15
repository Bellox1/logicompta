<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $models = [
        'gemini-2.0-flash',
        'gemini-1.5-flash-latest'
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
    }

    public function processOCR($ocrText)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Clé API Gemini non configurée.");
        }

        $truncatedText = mb_substr($ocrText, 0, 3000);
        $prompt = $this->getAccountingPrompt($truncatedText);

        foreach ($this->models as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $this->apiKey;
            
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

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $content = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
                    
                    Log::info("Gemini Fallback Success with model {$modelName}");

                    return json_decode($content, true);
                }
            } catch (\Exception $e) {
                Log::error("Gemini Fallback Error for $modelName: " . $e->getMessage());
            }
        }

        throw new \Exception("Gemini également indisponible ou quota épuisé.");
    }

    protected function getAccountingPrompt($ocrText)
    {
        return <<<PROMPT
Tu es un expert comptable. À partir de l'OCR ci-dessous, génère une écriture comptable structurée au format JSON.

RÈGLES OBLIGATOIRES :
- Une facture = une écriture
- colonnes : ligne, compte, sous_compte, debit, credit
- Débit = Crédit (équilibré)
- Retourne UNIQUEMENT du JSON valide

STRUCTURE JSON ATTENDUE :
{
  "date": "YYYY-MM-DD",
  "reference": "NOM_FOURNISSEUR_NUMERO",
  "journal": "ACHAT|VENTE|CAISSE|BANQUE",
  "type": "achat|vente|autre",
  "libelle": "Achat/Vente [Détail]",
  "total": 0,
  "lignes": [
    {
      "ligne": 1,
      "compte": "racine",
      "sous_compte": "compte_complet",
      "debit": 100,
      "credit": 0,
      "libelle": "description"
    }
  ]
}

TEXTE OCR :
{$ocrText}
PROMPT;
    }
}
