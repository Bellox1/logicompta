<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthFromToken
{
    public function handle(Request $request, Closure $next)
    {
        // Essayer de récupérer l'utilisateur depuis le token
        $user = $this->getUserFromToken($request);
        
        if ($user) {
            // Partager l'utilisateur via le service container
            app()->instance('current_user', $user);
            
            // Partager dans la session pour les contrôleurs
            session(['comptafriq_user_id' => $user->id]);
            
            // Partager dans les vues
            view()->share('currentUser', $user);
        }
        
        return $next($request);
    }
    
    private function getUserFromToken(Request $request)
    {
        try {
            // Essayer depuis le cookie
            if ($token = $request->cookie('comptafriq_token')) {
                $model = PersonalAccessToken::findToken($token);
                if ($model) {
                    return $model->tokenable;
                }
            }
            
            // Essayer depuis le header Authorization
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                $model = PersonalAccessToken::findToken($token);
                if ($model) {
                    return $model->tokenable;
                }
            }
            
            // Essayer depuis le header X-Auth-Token
            if ($token = $request->header('X-Auth-Token')) {
                $model = PersonalAccessToken::findToken($token);
                if ($model) {
                    return $model->tokenable;
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner null
        }
        
        return null;
    }
}
