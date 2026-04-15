<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Tente OpenAI, et si ça échoue (quota, erreur), bascule sur Gemini
     */
    public function processOCR($ocrText)
    {
        // 1. Essai avec OpenAI
        try {
            Log::info("Tentative OCR avec OpenAI...");
            $openai = new OpenAiService();
            return $openai->processOCR($ocrText);
        } catch (\Exception $e) {
            Log::warning("Échec OpenAI: " . $e->getMessage() . ". Tentative de repli sur Gemini...");
            
            // 2. Basculement sur Gemini
            try {
                $gemini = new GeminiService();
                return $gemini->processOCR($ocrText);
            } catch (\Exception $ge) {
                Log::error("Échec définitif des services IA: " . $ge->getMessage());
                throw new \Exception("Désolé, les services IA (OpenAI et Gemini) sont actuellement indisponibles ou ont épuisé leurs quotas.");
            }
        }
    }
}
