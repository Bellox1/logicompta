<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptafriq - Connexion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1A202C;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .main-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 450px;
            background: #1A202C;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            position: relative;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        /* Logo en haut */
        .logo-section {
            text-align: center;
            margin-bottom: 0.25rem;
        }
        
        .logo {
            width: 280px;
            margin: 0 auto;
            display: block;
            filter: drop-shadow(0 10px 30px rgba(70, 130, 180, 0.4));
        }
        
        /* Titre de connexion */
        .welcome-text h2 {
            font-size: 2rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
            color: white;
        }
        
        .welcome-text p {
            color: #93C5FD;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .form-container {
            width: 100%;
            max-width: 350px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #93C5FD;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #374151;
            border-radius: 10px;
            font-size: 1rem;
            background: #1A202C;
            color: white;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3B82F6;
            background: #1A202C;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group input::placeholder {
            color: #9CA3AF;
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            width: 100%;
            padding: 0.75rem;
            background: transparent;
            color: #93C5FD;
            border: 2px solid #93C5FD;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #3B82F6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        }
        
        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #374151;
        }
        
        .divider span {
            background: #1A202C;
            color: #93C5FD;
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid #374151;
            border-radius: 20px;
            display: inline-block;
            margin: 0.5rem 0;
        }
        
        .message {
            padding: 0.75rem;
            margin: 1rem 0;
            border-radius: 8px;
            display: none;
            font-size: 0.9rem;
        }
        
        .message.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22C55E;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .main-container {
                max-width: 400px;
                padding: 2rem 1.5rem;
            }
            
            .logo {
                width: 200px;
            }
            
            .welcome-text h2 {
                font-size: 1.5rem;
            }
            
            .divider {
                margin: 1.5rem 0;
            }
            
            .divider span {
                padding: 0.4rem 1.2rem;
                font-size: 0.8rem;
            }
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    
    <!-- Container principal unique -->
    <div class="main-container">
        <!-- Logo en haut -->
        <div class="logo-section">
            <img src="{{ asset('images/ChatGPT Image 11 mars 2026, 10_41_49.png') }}" alt="Comptafriq Logo" class="logo">
            <div class="welcome-text">
                <h2>Connectez-vous</h2>
                <p>Accédez à votre espace Comptafriq</p>
            </div>
        </div>
        
        <!-- Formulaire -->
        <div class="form-container">
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary">Se connecter</button>
            </form>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <button class="btn-secondary" onclick="window.location.href='{{ url('/forgot-password') }}'">Mot de passe oublié ?</button>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
        const API_BASE = '{{ url('/api') }}';
        let currentToken = null;

        // Fonctions d'affichage
        function showMessage(message, type = 'success') {
            const messageEl = document.getElementById('message');
            messageEl.textContent = message;
            messageEl.className = `message ${type}`;
            messageEl.style.display = 'block';
            
            setTimeout(() => {
                hideMessage();
            }, 5000);
        }

        function hideMessage() {
            document.getElementById('message').style.display = 'none';
        }

        // Connexion
        async function login(event) {
            event.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                showMessage('Veuillez remplir tous les champs', 'error');
                return;
            }

            const btn = event.target.querySelector('button[type="submit"]');
            btn.textContent = 'Connexion...';
            btn.disabled = true;

            try {
                const response = await fetch(`${API_BASE}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success) {
                    // Pas de stockage localStorage - session gérée côté serveur
                    
                    // Redirection immédiate vers le dashboard
                    window.location.href = '{{ url('/dashbord') }}';
                } else {
                    // showMessage(data.message || 'Erreur de connexion', 'error'); // Désactivé
                }
            } catch (error) {
                // showMessage('Erreur de connexion au serveur', 'error'); // Désactivé
                console.error('Erreur:', error);
            } finally {
                btn.textContent = 'Se connecter';
                btn.disabled = false;
            }
        }

        // Initialisation
        window.onload = function() {
            // Pas d'initialisation spéciale nécessaire
        };
        
        // Event listener
        document.getElementById('loginForm').addEventListener('submit', login);
    </script>
</body>
</html>
