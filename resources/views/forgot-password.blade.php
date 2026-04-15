<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMPTAFIQ - Récupération</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --primary-color: #0062cc; 
            --primary-hover: #0056b3; 
            --dark-blue: #161e2e;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8f9fc; 
            height: 100vh; 
            overflow: hidden; 
            margin: 0;
        }
        .auth-split-wrapper { display: flex; height: 100vh; }
        .auth-banner-side { 
            background: linear-gradient(135deg, #001a3a 0%, var(--primary-color) 100%);
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px;
            color: white;
            position: relative;
        }
        .auth-form-side { 
            width: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 60px; 
            background: #fff; 
        }
        .form-container { width: 100%; max-width: 400px; }
        
        .form-control-premium {
            height: 52px;
            border-radius: 14px;
            border: 1px solid #edf2f7;
            padding-left: 45px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            background: #fdfdfd;
        }
        .form-control-premium:focus { 
            border-color: var(--primary-color); 
            box-shadow: 0 0 0 4px rgba(0, 98, 204, 0.08); 
        }
        .icon-field { 
            position: absolute; 
            left: 18px; 
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0; 
            font-size: 16px; 
            z-index: 5;
        }
        
        .btn-recover-premium {
            height: 52px;
            border-radius: 14px;
            background: var(--primary-color);
            border: none;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 13px;
            box-shadow: 0 10px 20px rgba(0, 98, 204, 0.15);
            transition: all 0.3s;
        }
        .btn-recover-premium:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 15px 30px rgba(0, 98, 204, 0.25); 
            background: var(--primary-hover);
        }
        
        .btn-back {
            color: var(--primary-color);
            font-weight: 700;
            text-decoration: none !important;
            font-size: 14px;
            margin-bottom: 40px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 991px) { 
            .auth-banner-side { display: none; } 
            .auth-form-side { width: 100%; padding: 40px 20px; } 
        }
    </style>
</head>
<body>
    <div class="auth-split-wrapper">
        <div class="auth-banner-side">
            <div class="text-center" style="max-width: 450px;">
                <img src="{{ asset('storage/images/logo.png') }}" class="mb-5" style="max-height: 80px; filter: brightness(0) invert(1);">
                <h1 class="font-weight-bold display-4 mb-4" style="font-family: 'Manrope'; letter-spacing: -1.5px;">Pas de panique.</h1>
                <p class="h5 opacity-75 font-weight-light mb-5">Nous allons vous aider à retrouver l'accès à votre espace COMPTAFIQ en toute sécurité.</p>
                <div class="bg-white p-4 text-dark text-left shadow-lg" style="border-radius: 20px; border: 1px solid rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; flex-shrink: 0; background-color: var(--primary-color) !important;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <p class="font-weight-bold mb-0">Lien de secours</p>
                            <p class="small text-muted mb-0">Un email sécurisé vous sera envoyé sous 60 secondes.</p>
                        </div>
                    </div>
                </div>
            </div>
            <i class="fas fa-lock-open position-absolute" style="bottom: -50px; right: -50px; font-size: 300px; opacity: 0.03;"></i>
        </div>

        <div class="auth-form-side">
            <div class="form-container">
                <a href="{{ route('login') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                </a>
                
                <div class="mb-5">
                    <h2 class="font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Récupération d'accès</h2>
                    <p class="text-muted font-weight-bold small">Entrez l'adresse email associée à votre compte pour recevoir vos instructions.</p>
                </div>

                <form action="{{ route('forgot-password.post') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Votre Adresse Email</label>
                        <div class="position-relative">
                            <i class="fas fa-envelope icon-field"></i>
                            <input type="email" name="email" class="form-control form-control-premium" placeholder="Ex: jean@monentreprise.com" required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-recover-premium btn-block mt-4 mb-3">Envoyer le lien de secours</button>
                    
                    <div class="text-center mt-5 pt-3 border-top">
                        <p class="text-muted small font-weight-bold">Besoin d'assistance ? <a href="#" class="text-primary font-weight-bolder text-decoration-none">Contacter le support</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
