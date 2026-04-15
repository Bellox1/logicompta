<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMPTAFIQ - Configuration de l'Espace</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { 
            --primary-color: #0062cc; 
            --primary-hover: #0056b3; 
            --dark-blue: #1a1c2e;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8f9fc; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
            color: #2d3748;
        }
        .setup-container {
            max-width: 1000px;
            margin: auto;
            padding: 40px 20px;
        }
        .setup-header { 
            text-align: center; 
            margin-bottom: 60px;
        }
        .setup-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.03);
            border: 1px solid #edf2f7;
            padding: 45px 30px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .setup-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .setup-card:hover { 
            transform: translateY(-12px); 
            border-color: rgba(0, 98, 204, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08); 
        }
        .setup-card:hover::after {
            transform: scaleX(1);
        }
        .icon-box {
            width: 85px;
            height: 85px;
            border-radius: 22px;
            background: rgba(0, 98, 204, 0.06);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            transition: all 0.3s;
            font-size: 32px;
        }
        .setup-card:hover .icon-box { 
            background: var(--primary-color); 
            color: #fff; 
            transform: rotate(-5deg) scale(1.1);
        }
        .btn-skip { 
            color: #a0aec0; 
            font-weight: 800; 
            font-size: 11px; 
            letter-spacing: 1.5px; 
            text-transform: uppercase; 
            text-decoration: none !important; 
            transition: 0.3s; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-skip:hover { color: var(--dark-blue); }
        h3 { font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 1.4rem; }
    </style>
</head>
<body>
    <div class="setup-container flex-grow-1 d-flex flex-column justify-content-center">
        <div class="setup-header animate__animated animate__fadeInDown">
            <img src="{{ asset('storage/images/logo.png') }}" class="mb-4" style="max-height: 80px; width: auto; object-fit: contain;">
            <h1 class="font-weight-bold display-4 mb-3" style="font-family: 'Manrope'; color: var(--dark-blue); letter-spacing: -1px;">Prêt à commencer ?</h1>
            <p class="text-muted font-weight-bold h5 mx-auto" style="max-width: 600px; opacity: 0.7;">Identifiez votre environnement de travail pour finaliser l'initialisation de votre espace COMPTAFIQ.</p>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-5 mb-4">
                <div class="setup-card" onclick="showStep('join')">
                    <div class="icon-box">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-3">Rejoindre une équipe</h3>
                    <p class="text-muted small px-3">On vous a transmis un code d'invitation ? Entrez-le pour commencer à collaborer immédiatement sur un dossier existant.</p>
                </div>
            </div>
            <div class="col-md-5 mb-4">
                <div class="setup-card" onclick="showStep('create')">
                    <div class="icon-box">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="mb-3">Nouvelle Entreprise</h3>
                    <p class="text-muted small px-3">Vous souhaitez créer un nouvel espace de gestion pour votre propre entité ou client ? Configurez votre structure ici.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-2">
            <form action="{{ route('entreprise.setup.post') }}" method="POST" id="skip-form">
                @csrf
                <input type="hidden" name="action" value="skip">
                <a href="javascript:void(0)" onclick="confirmSkip()" class="btn-skip">
                    Passer cette étape
                    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                </a>
            </form>
        </div>
    </div>

    {{-- Hidden Forms --}}
    <form id="join-form-main" action="{{ route('entreprise.setup.post') }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="join">
        <input type="hidden" name="company_code" id="swal-company-code">
    </form>
    <form id="create-form-main" action="{{ route('entreprise.setup.post') }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="company_name" id="swal-company-name">
    </form>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script>
        function showStep(type) {
            if (type === 'join') {
                Swal.fire({
                    title: 'Code d\'invitation',
                    text: 'Veuillez saisir le code unique fourni par votre administrateur.',
                    input: 'text',
                    inputPlaceholder: 'EX: COMPTA-XXXX',
                    confirmButtonText: 'REJOINDRE L\'ÉQUIPE',
                    confirmButtonColor: '#0062cc',
                    showCancelButton: true,
                    cancelButtonText: 'RETOUR',
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-24',
                        confirmButton: 'rounded-pill px-4',
                        cancelButton: 'rounded-pill px-4'
                    },
                    didOpen: () => {
                        const input = Swal.getInput();
                        input.style.textTransform = 'uppercase';
                        input.style.textAlign = 'center';
                        input.style.fontWeight = '800';
                        input.style.padding = '15px';
                        input.style.borderRadius = '12px';
                    }
                }).then((r) => {
                    if (r.isConfirmed && r.value) {
                        document.getElementById('swal-company-code').value = r.value;
                        document.getElementById('join-form-main').submit();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Création d\'entreprise',
                    text: 'Quel est le nom de votre structure ou de votre dossier comptable ?',
                    input: 'text',
                    inputPlaceholder: 'Ex: Ma Société Africa SARL',
                    confirmButtonText: 'CRÉER L\'ESPACE',
                    confirmButtonColor: '#0062cc',
                    showCancelButton: true,
                    cancelButtonText: 'RETOUR',
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-24',
                        confirmButton: 'rounded-pill px-4',
                        cancelButton: 'rounded-pill px-4'
                    },
                    didOpen: () => {
                        const input = Swal.getInput();
                        input.style.padding = '15px';
                        input.style.borderRadius = '12px';
                    }
                }).then((r) => {
                    if (r.isConfirmed && r.value) {
                        document.getElementById('swal-company-name').value = r.value;
                        document.getElementById('create-form-main').submit();
                    }
                });
            }
        }

        function confirmSkip() {
            Swal.fire({
                title: 'Configuration requise',
                text: 'Sans entreprise configurée, vous accéderez uniquement au mode consultation. Souhaitez-vous continuer ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'CONTINUER QUAND MÊME',
                confirmButtonColor: '#0062cc',
                cancelButtonText: 'CONFIGURER MAINTENANT',
                heightAuto: false,
                customClass: {
                    popup: 'rounded-24',
                    confirmButton: 'rounded-pill px-4',
                    cancelButton: 'rounded-pill px-4'
                }
            }).then((r) => { if (r.isConfirmed) document.getElementById('skip-form').submit(); });
        }
    </script>
</body>
</html>
