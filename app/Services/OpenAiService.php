<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected $apiKey;
    protected $model = 'gpt-4o-mini'; // Modèle par défaut équilibré prix/perf

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');
    }

    /**
     * Traite le texte OCR pour générer une écriture comptable JSON via OpenAI
     */
    public function processOCR($ocrText)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Clé API OpenAI non configurée.");
        }

        $truncatedText = mb_substr($ocrText, 0, 4000); // GPT supporte généralement plus de contexte
        $prompt = $this->getAccountingPrompt($truncatedText);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un expert comptable spécialisé dans la saisie d\'écritures à partir de factures OCR.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                Log::info("OpenAI Success: " . $content);

                $decoded = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Error: ' . json_last_error_msg());
                    throw new \Exception("Format IA invalide (OpenAI).");
                }

                return $decoded;
            }

            Log::error("OpenAI Error " . $response->status() . ": " . $response->body());
            throw new \Exception("Erreur lors de la communication avec OpenAI (" . $response->status() . ").");

        } catch (\Exception $e) {
            Log::error("OpenAI Exception: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getAccountingPrompt($ocrText)
    {
        return <<<PROMPT
À partir du texte OCR ci-dessous, génère une écriture comptable structurée au format JSON.

RÈGLES OBLIGATOIRES :
- Une facture = une écriture
- Chaque écriture contient plusieurs lignes
- Chaque ligne doit avoir : ligne, compte, sous_compte, debit, credit
- Débit = Crédit (équilibré)
- Si information manquante, utilise null ou 0
- Ne jamais inventer de données
- Utilise uniquement les racines de comptes suivantes si possible :
  57 = caisse
  52 = banque
  605 = achats carburant / charges
  701 = ventes
  31 = marchandises
  22 = immobilisations

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
      "debit": montant,
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
