@extends('layouts.accounting')

@section('title', 'Centre d\'Aide Comptable')

@section('content')
<div>
    <div class="mb-10 text-center">
        <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white mb-4 uppercase tracking-tighter">Guide de Gestion Logicompta</h1>
        <p class="text-base md:text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto italic">
            Maîtrisez les outils de votre comptabilité et les principes du système SYSCOHADA.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Carte 1: Plan Comptable -->
        <div class="bg-card-bg border border-border p-8 shadow-sm transition-all hover:shadow-md group">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="book-open" class="w-6 h-6"></i>
                </div>
                <h2 class="text-xl font-black uppercase tracking-widest text-gray-800 dark:text-white">1. Plan Comptable</h2>
            </div>
            <div class="space-y-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 1</b> <span>Ressources durables (Capitaux, Emprunts). Passif.</span></div>
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 2</b> <span>Actif immobilisé (Matériel, Logiciels, etc.).</span></div>
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 3</b> <span>Stocks et en-cours (Marchandises, Produits).</span></div>
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 4</b> <span>Tiers (Clients, Fournisseurs, État).</span></div>
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 5</b> <span>Comptes de trésorerie (Banques, Caisses).</span></div>
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 6</b> <span>Charges (Dépenses liées à l'activité).</span></div>
                <div class="flex gap-3"><b class="text-primary font-black shrink-0 w-16">Classe 7</b> <span>Produits (Recettes et Revenus).</span></div>
            </div>
        </div>

        <!-- Carte 2: États de Synthèse -->
        <div class="bg-card-bg border border-border p-8 shadow-sm transition-all hover:shadow-md group">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <h2 class="text-xl font-black uppercase tracking-widest text-gray-800 dark:text-white">2. États de Synthèse</h2>
            </div>
            <div class="space-y-4 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                <p>• <strong class="text-gray-800 dark:text-white">Le Journal :</strong> Enregistrement quotidien et chronologique.</p>
                <p>• <strong class="text-gray-800 dark:text-white">Le Grand Livre :</strong> Détail exhaustif compte par compte.</p>
                <p>• <strong class="text-gray-800 dark:text-white">La Balance :</strong> Vérification de l'équilibre Débit/Crédit.</p>
                <p>• <strong class="text-gray-800 dark:text-white">Le Bilan :</strong> Patrimoine net de l'entreprise (Actif = Passif).</p>
                <p>• <strong class="text-gray-800 dark:text-white">Résultat :</strong> Performance sur l'exercice (Produits - Charges).</p>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Règles de Saisie -->
    <div class="bg-primary text-white p-10 shadow-xl relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 translate-x-1/4 translate-y-1/4">
            <i data-lucide="shield-check" class="w-64 h-64"></i>
        </div>
        <h2 class="text-2xl font-black uppercase tracking-[0.3em] mb-8 flex items-center gap-4">
            <i data-lucide="shield-check" class="w-8 h-8"></i>
            Règles de Rigueur
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-sm">
            <div class="flex items-start gap-4">
                <div class="mt-1 w-2 h-2 bg-accent shrink-0"></div>
                <p><b class="uppercase block text-xs opacity-70 mb-1">Équilibre</b> L'enregistrement est impossible si le total débit ne correspond pas au crédit.</p>
            </div>
            <div class="flex items-start gap-4">
                <div class="mt-1 w-2 h-2 bg-accent shrink-0"></div>
                <p><b class="uppercase block text-xs opacity-70 mb-1">Période</b> Saisie flexible sur le mois en cours avec 5 jours de battement sur le mois passé.</p>
            </div>
            <div class="flex items-start gap-4">
                <div class="mt-1 w-2 h-2 bg-accent shrink-0"></div>
                <p><b class="uppercase block text-xs opacity-70 mb-1">Intégrité</b> Les numéros de pièces sont séquentiels et immuables une fois validés.</p>
            </div>
            <div class="flex items-start gap-4">
                <div class="mt-1 w-2 h-2 bg-accent shrink-0"></div>
                <p><b class="uppercase block text-xs opacity-70 mb-1">Exports</b> Chaque écran est optimisé pour être exporté en format papier ou tableur.</p>
            </div>
        </div>
    </div>

    <div class="mt-12 text-center">
        <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.5em]">Logicompta -Entreprise</p>
    </div>
</div>
@endsection
