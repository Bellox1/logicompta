📊 COMPTAFRIQ ERP

ComptAfriq est un progiciel de gestion intégré (ERP) conçu pour répondre aux besoins comptables, financiers et administratifs des entreprises africaines.

Le système est basé sur les normes SYSCOHADA et vise à offrir aux :

Entreprises
Cabinets comptables
Institutions financières
Systèmes financiers décentralisés

une solution moderne, flexible et adaptée aux réalités africaines.

🎯 Objectif du projet

Le logiciel ComptAfriq a été conçu pour combler le manque de solutions comptables adaptées au contexte africain.

Il permet aux organisations de :

- Structurer leur gestion comptable
- Automatiser les processus financiers
- Produire des états financiers conformes aux normes OHADA
- Améliorer la prise de décision grâce aux données financières.

🧩 Modules du système

Le progiciel ComptAfriq est composé de plusieurs modules interconnectés :

1️⃣ Comptabilité générale
2️⃣ Gestion de la paie
3️⃣ Suivi des immobilisations
4️⃣ Suivi budgétaire
5️⃣ Comptabilité analytique
6️⃣ Gestion commerciale

Chaque module peut fonctionner indépendamment ou être intégré dans un système complet.

⚙️ Technologies utilisées

- **Framework** : Laravel (PHP)
- **Base de données** : SQLite / MySQL
- **Interface** : Blade / CSS (Modernized UI)
- **Outils** : Git & GitHub, SweetAlert2 (Modales)

📂 ARCHITECTURE DU MODULE COMPTABILITÉ

Le code a été scindé pour une meilleure lisibilité et performance :

- **`JournalController`** : Gère la saisie des écritures, l'index du journal et l'importation CSV massive.
- **`LedgerController`** : Dédié à l'analyse et l'exportation du **Grand Livre** par compte.
- **`TrialBalanceController`** : Gère l'équilibre de la **Balance** et ses calculs automatiques.
- **`FinancialStatementController`** : Génère le **Bilan** (Actif/Passif) et le **Compte de Résultat** (Pertes/Profits).
- **`SupportController`** : Gère la page d'**Aide** et le référentiel interactif du **Plan Comptable** SYSCOHADA.
- **`EntrepriseController`** : Gère la configuration initiale ("Démarrer la gestion") et la liaison avec les entités.
En production: 
sudo apt install tesseract-ocr tesseract-ocr-fra
composer require thiagoalessio/tesseract_ocr


scan facture
Utilisateur clique sur icône/texte  ──┐
Utilisateur glisse une image         ──┼──→  handleOcrUpload(file)
                                        │
                    ┌───────────────────┘
                    │
            FormData { file, _token, service }
                    │
            service = "tesseract" ou "mindee"
            (choisi via le toggle dans la dropzone)
                    │
            POST /accounting/journal/ocr-import
                    │
            ┌───────┴────────┐
            │                │
      Tesseract (local)    Mindee (cloud)
      ImageMagick preproc  api.mindee.net
      → parse regex        → structuré
            │                │
            └───────┬────────┘
                    │
      { date, amount, libelle, service, ... }
                    │
      Remplit auto le formulaire
      Toast: "Extrait via Tesseract 🖥️" ou "Mindee ☁️"
