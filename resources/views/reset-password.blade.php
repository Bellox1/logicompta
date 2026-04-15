<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMPTAFIQ - Nouveau mot de passe</title>
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
        
        .btn-reset-premium {
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
        .btn-reset-premium:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 15px 30px rgba(0, 98, 204, 0.25); 
            background: var(--primary-hover);
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
                <h1 class="font-weight-bold display-4 mb-4" style="font-family: 'Manrope'; letter-spacing: -1.5px;">Renouveau.</h1>
                <p class="h5 opacity-75 font-weight-light mb-5">Choisissez un mot de passe robuste pour garantir l'intégrité de vos dossiers financiers sur COMPTAFIQ.</p>
                <div class="d-inline-flex align-items-center bg-white border border-light px-4 py-2 rounded-pill shadow-sm" style="color: #2d3748;">
                    <i class="fas fa-shield-halved text-success mr-2"></i>
                    <span class="small font-weight-bold">Protection des données certifiée</span>
                </div>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="form-container">
                <div class="mb-5">
                    <h2 class="font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Définir un nouveau MDP</h2>
                    <p class="text-muted font-weight-bold small">Finalisez votre récupération en toute sécurité.</p>
                </div>

                <form action="{{ route('reset-password.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Nouveau Mot de passe</label>
                        <div class="position-relative">
                            <i class="fas fa-lock-open icon-field"></i>
                            <input type="password" name="password" class="form-control form-control-premium" placeholder="••••••••" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Confirmer le mot de passe</label>
                        <div class="position-relative">
                            <i class="fas fa-circle-check icon-field"></i>
                            <input type="password" name="password_confirmation" class="form-control form-control-premium" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-reset-premium btn-block mt-4">Mettre à jour le mot de passe</button>
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>
