@extends('layouts.accounting')

@section('title', "Archives $year")

@section('content')
<div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
        <a href="{{ route('accounting.archive.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-primary mb-2 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            RETOUR AUX ARCHIVES
        </a>
        <h1 class="text-3xl md:text-4xl font-black text-gray-800 tracking-tight">ARCHIVE EXERCICE {{ $year }}</h1>
        <p class="text-sm text-gray-500 font-medium tracking-tight">Consultez l'historique scellé de l'année {{ $year }} ({{ $totalEntries }} écritures).</p>
    </div>
    <div class="flex items-center gap-2 text-[10px] font-black uppercase text-amber-600 bg-amber-50 border border-amber-200 px-4 py-2 rounded-full tracking-widest shadow-sm">
        <i data-lucide="lock" class="w-3 h-3"></i> Données Scellées
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Rapports de Journal -->
    <div class="bg-white border border-border overflow-hidden group hover:border-primary transition-all shadow-sm">
        <div class="px-8 py-8 h-full flex flex-col">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center group-hover:scale-110 transition-all shadow-md">
                    <i data-lucide="book-open" class="w-7 h-7"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800 leading-none">JOURNAL ARCHIVÉ</h2>
                    <p class="text-xs text-gray-400 uppercase font-black tracking-widest mt-1">Écritures comptables {{ $year }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-8 flex-grow">
                Accédez à l'intégralité des écritures du journal enregistrées durant l'année {{ $year }}. Toutes les écritures sont marquées comme archivées.
            </p>
            <a href="{{ $links['journal'] }}" 
                class="flex items-center justify-between px-6 py-4 bg-primary text-white font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all">
                <span>Consulter le journal</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- Trial Balance -->
    <div class="bg-white border border-border overflow-hidden group hover:border-primary transition-all shadow-sm">
        <div class="px-8 py-8 h-full flex flex-col">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center group-hover:scale-110 transition-all shadow-md">
                    <i data-lucide="scale" class="w-7 h-7"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800 leading-none">BALANCE GÉNÉRALE</h2>
                    <p class="text-xs text-gray-400 uppercase font-black tracking-widest mt-1">Exercice {{ $year }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-8 flex-grow">
                Visualisez la balance de clôture de l'exercice {{ $year }}. Tous les comptes avec mouvements sont récapitulés.
            </p>
            <a href="{{ $links['balance'] }}" 
                class="flex items-center justify-between px-6 py-4 bg-primary text-white font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all">
                <span>Consulter la balance</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- Bilan -->
    <div class="bg-white border border-border overflow-hidden group hover:border-primary transition-all shadow-sm">
        <div class="px-8 py-8 h-full flex flex-col">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center group-hover:scale-110 transition-all shadow-md">
                    <i data-lucide="landmark" class="w-7 h-7"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800 leading-none">BILAN ARCHIVÉ</h2>
                    <p class="text-xs text-gray-400 uppercase font-black tracking-widest mt-1">Situation au 31/12/{{ $year }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-8 flex-grow">
                États financiers de clôture de l'année {{ $year }}. Actif, Passif et Capitaux propres tels qu'archivés.
            </p>
            <a href="{{ $links['bilan'] }}" 
                class="flex items-center justify-between px-6 py-4 bg-primary text-white font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all">
                <span>Consulter le bilan</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- Resultat -->
    <div class="bg-white border border-border overflow-hidden group hover:border-primary transition-all shadow-sm">
        <div class="px-8 py-8 h-full flex flex-col">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center group-hover:scale-110 transition-all shadow-md">
                    <i data-lucide="bar-chart-3" class="w-7 h-7"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800 leading-none">COMPTE DE RÉSULTAT</h2>
                    <p class="text-xs text-gray-400 uppercase font-black tracking-widest mt-1">Exercice {{ $year }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-8 flex-grow">
                Détail des charges et produits pour l'exercice {{ $year }} et calcul du résultat net final.
            </p>
            <a href="{{ $links['resultat'] }}" 
                class="flex items-center justify-between px-6 py-4 bg-primary text-white font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all">
                <span>Consulter le résultat</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</div>

@endsection
