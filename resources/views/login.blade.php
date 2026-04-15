<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Connexion | COMPTAFIQ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #0062cc;
            --primary-hover: #0056b3;
            --dark-color: #1a1c2e;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .auth-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
            min-height: 600px;
            border: 1px solid #edf2f7;
        }
        .auth-sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d57 100%);
            color: white;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .auth-sidebar::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        .auth-content {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-control-premium {
            height: 52px;
            border-radius: 12px;
            padding-left: 45px;
            background-color: #fdfdfd;
            border: 1px solid #edf2f7;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 14px;
        }
        .form-control-premium:focus {
            background-color: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 98, 204, 0.08);
        }
        .input-group-prepend i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #a0aec0;
            font-size: 16px;
        }
        .btn-primary-custom {
            background-color: var(--primary-color);
            color: white;
            font-weight: 800;
            border-radius: 14px;
            padding: 14px 25px;
            border: none;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 13px;
            box-shadow: 0 10px 20px rgba(0, 98, 204, 0.2);
        }
        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 98, 204, 0.3);
            color: white;
        }
        @media (max-width: 991px) {
            .auth-sidebar { display: none; }
            .auth-card { max-width: 500px; border-radius: 0; min-height: auto; }
            .auth-content { padding: 3rem 2rem; }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="auth-card mx-auto">
            <div class="col-lg-5 auth-sidebar">
                <div class="position-relative" style="z-index: 2;">
                    <div class="mb-5">
                        <i class="fas fa-chart-pie" style="font-size: 50px; opacity: 0.9;"></i>
                    </div>
                    <h1 class="font-weight-bold mb-4 display-4 text-white" style="font-family: 'Manrope'; letter-spacing: -2px;">COMPTAFIQ</h1>
                    <p class="h5 font-weight-light" style="opacity: 0.8; line-height: 1.6;">L'excellence comptable à portée de clic. Performance, Sécurité et Simplicité.</p>
                    <div class="mt-5">
                        <div class="d-flex align-items-center mb-4">
                            <i class="fas fa-check-circle mr-3" style="color: #4fd1c5;"></i>
                            <span class="font-weight-bold">Conformité SYSCOHADA</span>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <i class="fas fa-bolt mr-3" style="color: #4fd1c5;"></i>
                            <span class="font-weight-bold">Importations Intelligentes</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt mr-3" style="color: #4fd1c5;"></i>
                            <span class="font-weight-bold">Protection Haute Sécurité</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 auth-content bg-white">
                <div class="mb-5 text-center text-lg-left">
                    <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" style="max-height: 80px; width: auto; object-fit: contain;">
                </div>

                <div class="mb-4 text-center text-lg-left">
                    <h2 class="font-weight-bold mb-1" style="font-family: 'Manrope'; color: var(--dark-color);">Bon retour.</h2>
                    <p class="text-muted font-weight-bold small">Accédez à votre console de gestion financière.</p>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger border-0 shadow-sm px-4 py-3 mb-4 rounded-xl d-flex align-items-center" style="background-color: #fff5f5; color: #c53030;">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="small font-weight-bold">{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="small text-uppercase font-weight-bold text-muted tracking-widest mb-2" style="font-size: 10px;">Adresse Email</label>
                        <div class="position-relative">
                            <div class="input-group-prepend">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-premium" placeholder="utilisateur@exemple.com" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between">
                            <label class="small text-uppercase font-weight-bold text-muted tracking-widest mb-2" style="font-size: 10px;">Mot de passe</label>
                            <a href="{{ route('forgot-password') }}" class="small font-weight-bold text-primary text-decoration-none">Oublié ?</a>
                        </div>
                        <div class="position-relative">
                            <div class="input-group-prepend">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" name="password" id="password" class="form-control form-control-premium" placeholder="••••••••" required>
                            <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #a0aec0;" onclick="togglePassword()">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom btn-block py-3 mt-4">
                        Se connecter <i class="fas fa-chevron-right ml-2" style="font-size: 11px;"></i>
                    </button>
                </form>

                <div class="mt-5 text-center pt-3 border-top">
                    <p class="small text-muted font-weight-bold">
                        Nouveau sur COMPTAFIQ ?
                        <a href="{{ route('signup') }}" class="text-primary font-weight-bolder ml-1 text-decoration-none">Créer un compte maintenant</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
