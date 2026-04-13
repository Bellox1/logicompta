<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès au profil - Comptafriq</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #1A202C 0%, #2D3748 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .content h2 {
            color: #1A202C;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .content p {
            line-height: 1.6;
            margin-bottom: 25px;
            color: #4a5568;
        }
        
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        
        .reset-button:hover {
            background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        
        .security-info {
            background-color: #f7fafc;
            border-left: 4px solid #3B82F6;
            padding: 20px;
            margin: 30px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .security-info h3 {
            color: #2D3748;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        
        .security-info ul {
            margin: 0;
            padding-left: 20px;
            color: #4a5568;
        }
        
        .security-info li {
            margin-bottom: 8px;
        }
        
        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            margin: 0;
            color: #718096;
            font-size: 14px;
        }
        
        .footer a {
            color: #3B82F6;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .token-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
            color: #856404;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Comptafriq</h1>
            <p>Système Comptable Africain</p>
        </div>
        
        <div class="content">
            <h2>🔑 Réinitialisation de votre mot de passe</h2>
            
            <p>Bonjour,</p>
            
            <p>Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Comptafriq associé à l'email : <strong>{{ $userEmail }}</strong></p>
            
            <p><strong>🚀 Définissez votre nouveau mot de passe !</strong> Cliquez sur le bouton ci-dessous pour accéder à la page de réinitialisation :</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">🔓 Réinitialiser mon mot de passe</a>
            </div>
            
            <p>Ou copiez-collez ce lien dans votre navigateur :</p>
            
            <div class="token-info">
                {{ $resetUrl }}
            </div>
            
            <div class="security-info">
                <h3>🔒 Informations importantes</h3>
                <ul>
                    <li><strong>Ce lien vous permet de définir un nouveau mot de passe</strong></li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                    <li>Ne partagez jamais ce lien avec d'autres personnes</li>
                   
                </ul>
            </div>
            
            <p>Si vous avez des questions ou besoin d'aide, n'hésitez pas à contacter notre support.</p>
            
            <p>Cordialement,<br>L'équipe Comptafriq</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Comptafriq - Système Comptable Africain</p>
            <p>
                <a href="http://localhost:8000">Site web</a> | 
                <a href="mailto:support@comptafriq.com">Support</a>
            </p>
        </div>
    </div>
</body>
</html>
