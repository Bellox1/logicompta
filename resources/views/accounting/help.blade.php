@extends('layouts.accounting')

@section('title', 'Centre d\'Aide Comptable')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="mb-12 border-b border-gray-200 dark:border-white/10 pb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Guide d'utilisation Logicompta</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed italic">
            Ce document regroupe toutes les informations essentielles concernant le fonctionnement de l'application et les principes comptables SYSCOHADA appliqués.
        </p>
    </div>

    <div class="space-y-12 text-gray-800 dark:text-gray-300 leading-relaxed">
        
        <!-- SECTION 1 -->
        <section>
            <h2 class="text-2xl font-bold text-primary dark:text-primary-light uppercase tracking-wide mb-6">1. Le Plan Comptable (Les Classes)</h2>
            <div class="space-y-4">
                <p><strong class="text-gray-900 dark:text-white">Classe 1 - Comptes de ressources durables :</strong> Comprend les capitaux propres et les emprunts à long terme. Ils figurent au passif du bilan.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 2 - Comptes d'actif immobilisé :</strong> Regroupe les biens destinés à rester durablement dans l'entreprise (terrains, bâtiments, matériel, logiciels).</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 3 - Comptes de stocks :</strong> Matières premières, produits finis ou marchandises destinés à la vente ou à la production.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 4 - Comptes de tiers :</strong> Relations avec les clients (créances) et les fournisseurs (dettes), ainsi que l'État et le personnel.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 5 - Comptes de trésorerie :</strong> Disponibilités en banque, en caisse et les virements internes.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 6 - Comptes de charges :</strong> Toutes les dépenses liées à l'activité (achats, loyers, électricité, salaires). Ils servent à calculer le résultat.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 7 - Comptes de produits :</strong> Toutes les recettes (ventes, prestations de services). Ils servent à calculer le résultat.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 8 - Comptes spéciaux :</strong> Opérations exceptionnelles ou de fin d'exercice.</p>
                <p><strong class="text-gray-900 dark:text-white">Classe 9 - Comptabilité des engagements :</strong> Utilisée pour le suivi analytique ou les engagements hors bilan.</p>
            </div>
        </section>

        <!-- SECTION 2 -->
        <section>
            <h2 class="text-2xl font-bold text-primary dark:text-primary-light uppercase tracking-wide mb-6">2. Les États de Synthèse</h2>
            <div class="space-y-4">
                <p><strong class="text-gray-900 dark:text-white">Le Journal :</strong> C'est ici que sont saisies toutes les opérations au jour le jour. Chaque ligne doit obligatoirement appartenir à une pièce justificative.</p>
                <p><strong class="text-gray-900 dark:text-white">Le Grand Livre :</strong> Il classe les écritures du journal par compte spécifique. C'est l'outil indispensable pour analyser le détail d'un compte précis.</p>
                <p><strong class="text-gray-900 dark:text-white">La Balance :</strong> Elle récapitule les totaux de tous les comptes. Elle permet de s'assurer que le principe de la partie double est respecté (Total Débit = Total Crédit).</p>
                <p><strong class="text-gray-900 dark:text-white">Le Bilan :</strong> Un état qui montre la situation patrimoniale à un instant T. L'Actif (ce que l'entreprise possède) doit toujours être égal au Passif (ce que l'entreprise doit).</p>
                <p><strong class="text-gray-900 dark:text-white">Le Compte de Résultat :</strong> Un état qui mesure la performance sur une période. On y soustrait le total des comptes de classe 6 du total des comptes de classe 7 pour obtenir le bénéfice ou la perte.</p>
            </div>
        </section>

        <!-- SECTION 3 -->
        <section>
            <h2 class="text-2xl font-bold text-primary dark:text-primary-light uppercase tracking-wide mb-6">3. Règles de Saisie et Utilisation</h2>
            <div class="space-y-4 shadow-sm p-6 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/10">
                <p>• <strong class="text-gray-900 dark:text-white">Équilibre :</strong> Le logiciel refuse d'enregistrer une écriture si le total débit n'est pas strictement égal au total crédit.</p>
                <p>• <strong class="text-gray-900 dark:text-white">Période :</strong> La saisie est autorisée pour le mois en cours, avec une tolérance de 5 jours de retard sur le mois précédent.</p>
                <p>• <strong class="text-gray-900 dark:text-white">Numérotation :</strong> Le numéro de pièce (PC) est généré automatiquement de manière séquentielle pour garantir l'intégrité de la comptabilité.</p>
                <p>• <strong class="text-gray-900 dark:text-white">Menu :</strong> Vous pouvez replier le menu latéral à tout moment en cliquant sur la flèche pour libérer de l'espace sur les grands tableaux (Balance, Grand Livre).</p>
                <p>• <strong class="text-gray-900 dark:text-white">Impression :</strong> Chaque page dispose d'un bouton d'export qui génère une version PDF simplifiée et optimisée pour l'impression papier.</p>
            </div>
        </section>

    </div>

    <div class="mt-16 pt-8 border-t border-gray-100 dark:border-white/10 text-center">
        <p class="text-sm text-gray-400 uppercase tracking-[0.2em] font-bold">Logicompta - Documentation Interne v1.0</p>
    </div>
</div>
@endsection
