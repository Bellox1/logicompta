<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Comptafriq</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <i class="fas fa-user-circle mr-3"></i>Profil Utilisateur
            </h1>
            <p class="text-white/80">Gérez vos informations personnelles</p>
        </div>

        <!-- Profile Card -->
        <div class="profile-card rounded-2xl p-8">
            <!-- Alert for temporary access -->
            <div id="tempAccessAlert" class="hidden mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>🔑 Accès temporaire activé</strong> - Vous avez accès via mot de passe oublié. 
                            <a href="#" onclick="showPasswordModal()" class="underline font-medium">Pensez à définir un nouveau mot de passe</a>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Profile Picture Section -->
                <div class="text-center">
                    <div class="relative inline-block">
                        <div class="w-32 h-32 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                            <i class="fas fa-user"></i>
                        </div>
                        <button class="absolute bottom-0 right-0 bg-purple-600 text-white rounded-full p-2 hover:bg-purple-700 transition">
                            <i class="fas fa-camera text-sm"></i>
                        </button>
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-gray-800" id="profileName">Chargement...</h3>
                    <p class="text-gray-600" id="profileEmail">Chargement...</p>
                    <span class="inline-block mt-2 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium" id="profileRole">
                        Chargement...
                    </span>
                </div>

                <!-- Profile Information -->
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Informations du profil</h2>
                        <button onclick="toggleEditMode()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-edit mr-2"></i>Modifier
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="viewMode" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="text-sm text-gray-600 font-medium">Nom complet</label>
                                <p class="text-lg font-semibold text-gray-800" id="displayName">Chargement...</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="text-sm text-gray-600 font-medium">Email</label>
                                <p class="text-lg font-semibold text-gray-800" id="displayEmail">Chargement...</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="text-sm text-gray-600 font-medium">Rôle</label>
                                <p class="text-lg font-semibold text-gray-800" id="displayRole">Chargement...</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="text-sm text-gray-600 font-medium">Date d'inscription</label>
                                <p class="text-lg font-semibold text-gray-800" id="displayDate">Chargement...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode (Hidden by default) -->
                    <div id="editMode" class="hidden space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                                <input type="text" id="editName" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="editEmail" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <button onclick="saveProfile()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i>Enregistrer
                            </button>
                            <button onclick="cancelEdit()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions rapides</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <button onclick="showPasswordModal()" class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-key mr-2"></i>Changer le mot de passe
                    </button>
                    <button onclick="showDeleteModal()" class="bg-red-600 text-white p-4 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>Supprimer le compte
                    </button>
                    <button onclick="logout()" class="bg-gray-600 text-white p-4 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </button>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-6">
            <a href="{{ url('/') }}" class="text-white hover:text-white/80 transition">
                <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
            </a>
        </div>
    </div>

    <!-- Success Message -->
    <div id="successMessage" class="hidden fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successText">Opération réussie</span>
    </div>

    <script>
        let currentUser = null;

        // Load user profile
        async function loadProfile() {
            try {
                // Check if we have direct access via token
                const urlParams = new URLSearchParams(window.location.search);
                const token = urlParams.get('token');
                const email = urlParams.get('email');
                
                if (token && email) {
                    // Direct access via email token
                    await loadProfileViaToken(token, email);
                    return;
                }
                
                // Normal authentication check - utiliser session web
                const response = await fetch('{{ url("/api/user") }}', {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    currentUser = data.user;
                    displayProfile();
                } else {
                    throw new Error('Erreur lors du chargement du profil');
                }
            } catch (error) {
                console.error('Erreur:', error);
                // showMessage('Erreur lors du chargement du profil', 'error'); // Désactivé
            }
        }

        // Load profile via email token
        async function loadProfileViaToken(token, email) {
            try {
                const response = await fetch('{{ url("/api/user-by-token") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ token, email })
                });

                if (response.ok) {
                    const data = await response.json();
                    currentUser = data.user;
                    displayProfile();
                    
                    // Show temporary access alert
                    document.getElementById('tempAccessAlert').classList.remove('hidden');
                    // showMessage('🔑 Accès temporaire activé - Pensez à définir un nouveau mot de passe', 'success'); // Désactivé
                } else {
                    throw new Error('Token invalide ou expiré');
                }
            } catch (error) {
                console.error('Erreur token:', error);
                // showMessage('Token invalide. Veuillez vous connecter normalement.', 'error'); // Désactivé
                setTimeout(() => {
                    window.location.href = '{{ url("/login") }}';
                }, 3000);
            }
        }

        function displayProfile() {
            if (!currentUser) return;

            document.getElementById('profileName').textContent = currentUser.name;
            document.getElementById('profileEmail').textContent = currentUser.email;
            document.getElementById('profileRole').textContent = currentUser.role;
            document.getElementById('displayName').textContent = currentUser.name;
            document.getElementById('displayEmail').textContent = currentUser.email;
            document.getElementById('displayRole').textContent = currentUser.role.charAt(0).toUpperCase() + currentUser.role.slice(1);
            document.getElementById('displayDate').textContent = new Date().toLocaleDateString('fr-FR');
        }

        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            
            if (editMode.classList.contains('hidden')) {
                // Switch to edit mode
                viewMode.classList.add('hidden');
                editMode.classList.remove('hidden');
                document.getElementById('editName').value = currentUser.name;
                document.getElementById('editEmail').value = currentUser.email;
            } else {
                // Switch to view mode
                editMode.classList.add('hidden');
                viewMode.classList.remove('hidden');
            }
        }

        function cancelEdit() {
            toggleEditMode();
        }

        async function saveProfile() {
            try {
                // Check if we're in direct access mode
                const urlParams = new URLSearchParams(window.location.search);
                const token = urlParams.get('token');
                const email = urlParams.get('email');
                
                let response;
                
                if (token && email) {
                    // Update via token access
                    const name = document.getElementById('editName').value;
                    const userEmail = document.getElementById('editEmail').value;
                    
                    response = await fetch('{{ url("/api/update-profile-by-token") }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ token, email, name, new_email: userEmail })
                    });
                } else {
                    // Normal authenticated update - utiliser session web
                    const name = document.getElementById('editName').value;
                    const userEmail = document.getElementById('editEmail').value;

                    response = await fetch('{{ url("/api/user") }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        body: JSON.stringify({ name, email: userEmail })
                    });
                }

                if (response.ok) {
                    const data = await response.json();
                    currentUser = data.user;
                    displayProfile();
                    toggleEditMode();
                    // showMessage('Profil mis à jour avec succès', 'success'); // Désactivé
                } else {
                    throw new Error('Erreur lors de la mise à jour');
                }
            } catch (error) {
                console.error('Erreur:', error);
                // showMessage('Erreur lors de la mise à jour du profil', 'error'); // Désactivé
            }
        }

        function showPasswordModal() {
            // showMessage('Fonctionnalité de changement de mot de passe bientôt disponible', 'info'); // Désactivé
        }

        function showDeleteModal() {
            if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irreversible.')) {
                // showMessage('Fonctionnalité de suppression bientôt disponible', 'info'); // Désactivé
            }
        }

        function showMessage(message, type = 'success') {
            const messageEl = document.getElementById('successMessage');
            const textEl = document.getElementById('successText');
            
            textEl.textContent = message;
            
            if (type === 'error') {
                messageEl.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg';
            } else if (type === 'info') {
                messageEl.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg';
            } else {
                messageEl.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
            }
            
            messageEl.classList.remove('hidden');
            
            setTimeout(() => {
                messageEl.classList.add('hidden');
            }, 3000);
        }

        // Load profile on page load
        document.addEventListener('DOMContentLoaded', loadProfile);
    </script>
</body>
</html>
