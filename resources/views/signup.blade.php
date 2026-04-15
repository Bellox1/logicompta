<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMPTAFIQ - Inscription</title>
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
            background: linear-gradient(135deg, var(--primary-color) 0%, #003e80 100%);
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
            padding: 40px 60px; 
            overflow-y: auto; 
            background: #fff; 
        }
        .form-container { width: 100%; max-width: 440px; }
        
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
            background: #fff;
        }
        .icon-field { 
            position: absolute; 
            left: 18px; 
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0; 
            font-size: 16px; 
            transition: 0.3s; 
            z-index: 5;
        }
        
        .btn-register-premium {
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
        .btn-register-premium:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 15px 30px rgba(0, 98, 204, 0.25); 
            background: var(--primary-hover); 
        }
        
        .strength-meter-box { margin-top: 10px; height: 5px; background: #edf2f7; border-radius: 10px; overflow: hidden; }
        .strength-bar { height: 100%; width: 0; transition: all 0.4s ease; }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            background: rgba(255,255,255,0.08);
            padding: 15px 20px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .feature-item i { font-size: 18px; color: #4fd1c5; margin-right: 15px; }
        
        @media (max-width: 1100px) { .auth-banner-side { padding: 40px; } }
        @media (max-width: 991px) { 
            .auth-banner-side { display: none; } 
            .auth-form-side { width: 100%; padding: 40px 20px; } 
            body { overflow: auto; }
        }
    </style>
</head>
<body>
    <div class="auth-split-wrapper">
        <!-- Banner Side -->
        <div class="auth-banner-side">
            <div class="text-center" style="max-width: 500px;">
                <img src="{{ asset('storage/images/logo.png') }}" class="mb-5" style="max-height: 90px; filter: brightness(0) invert(1);">
                <h1 class="font-weight-bold display-4 mb-4" style="font-family: 'Manrope'; letter-spacing: -1.5px;">Rejoignez l'élite.</h1>
                <p class="h5 opacity-75 font-weight-light mb-5">Prenez le contrôle total de votre comptabilité avec l'infrastructure la plus avancée du marché.</p>
                
                <div class="text-left mt-5">
                    <div class="feature-item">
                        <i class="fas fa-microchip"></i>
                        <span class="font-weight-bold">Intelligence Artificielle OCR & Auto-classification</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span class="font-weight-bold">États financiers temps réel & Pilotage analytique</span>
                    </div>
                </div>
            </div>
            <i class="fas fa-user-plus position-absolute" style="bottom: -50px; right: -50px; font-size: 350px; opacity: 0.03;"></i>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="form-container">
                <div class="mb-5">
                    <h2 class="font-weight-bold text-dark mb-1" style="font-family: 'Manrope'; letter-spacing: -0.5px;">Création de votre espace.</h2>
                    <p class="text-muted font-weight-bold small">C'est gratuit et ça ne prend que quelques secondes.</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger border-0 small font-weight-bold py-3 mb-4 rounded-xl shadow-sm" style="background: #fff5f5; color: #c53030;">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('signup.post') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Votre Nom Complet</label>
                        <div class="position-relative">
                            <i class="fas fa-user-circle icon-field"></i>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-premium" placeholder="Ex: Jean Kouamé" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Adresse Email Professionnelle</label>
                        <div class="position-relative">
                            <i class="fas fa-envelope icon-field"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-premium" placeholder="nom@entreprise.com" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Mot de passe</label>
                        <div class="position-relative">
                            <i class="fas fa-lock icon-field"></i>
                            <input type="password" name="password" id="password" class="form-control form-control-premium" placeholder="8+ caractères" required>
                        </div>
                        <div class="strength-meter-box">
                            <div id="strength-bar" class="strength-bar bg-danger"></div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase tracking-widest" style="font-size: 10px;">Confirmation du mot de passe</label>
                        <div class="position-relative">
                            <i class="fas fa-circle-check icon-field"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-premium" placeholder="Confirmer le mot de passe" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register-premium btn-block mt-4 mb-4">CRÉER MON COMPTE</button>
                    
                    <p class="text-center small text-muted font-weight-medium">En vous inscrivant, vous acceptez nos <a href="#" class="text-primary font-weight-bold text-decoration-none">Conditions d'Utilisation</a></p>
                    
                    <div class="text-center mt-5 border-top pt-4">
                        <p class="text-muted small font-weight-bold">Déjà utilisateur ? <a href="{{ route('login') }}" class="text-primary font-weight-bolder text-decoration-none">Se connecter ici</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('password').addEventListener('input', function(e) {
            let val = e.target.value;
            let bar = document.getElementById('strength-bar');
            let score = 0;
            if (val.length >= 8) score += 25;
            if (/[A-Z]/.test(val)) score += 25;
            if (/[0-9]/.test(val)) score += 25;
            if (/[^A-Za-z0-9]/.test(val)) score += 25;
            
            bar.style.width = score + '%';
            if (score <= 25) { bar.className = 'strength-bar bg-danger'; }
            else if (score <= 50) { bar.className = 'strength-bar bg-warning'; }
            else if (score <= 75) { bar.className = 'strength-bar bg-info'; }
            else { bar.className = 'strength-bar bg-success'; }
        });
    </script>
</body>
</html>
