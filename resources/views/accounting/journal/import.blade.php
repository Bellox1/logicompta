@extends('layouts.accounting')

@section('title', 'Importer des écritures')

@section('content')
<div class="px-6 sm:px-12 py-10 w-full max-w-[1600px] mx-auto min-h-screen flex flex-col">
    <!-- HEADER -->
    <div class="mb-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-8 animate-in fade-in slide-in-from-top duration-700">
        <div>
            <h1 class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tighter uppercase leading-none">IMPORT JOURNAL</h1>
            <div class="flex items-center gap-3 mt-4">
                <span class="h-[2px] w-12 bg-gray-900"></span>
                <p class="text-[11px] text-gray-400 font-bold tracking-[0.4em] uppercase">Chargement massif via CSV (;)</p>
            </div>
        </div>
        <a href="{{ route('accounting.journal.index') }}" class="group flex items-center gap-4 px-8 py-4 bg-white border border-gray-100 rounded-none text-xs font-black uppercase text-gray-400 hover:text-black hover:border-black transition-all tracking-[0.2em] italic">
            <i data-lucide="x" class="w-4 h-4 group-hover:rotate-90 transition-transform"></i>
            Fermer l'import
        </a>
    </div>

    <!-- ALERTES : SIMPLE ET SANS FOND NOIR -->
    <!-- ALERTES : SIMPLE ET SANS FOND NOIR -->
    @if (session('error'))
        <div id="error-alert" class="mb-12 p-8 bg-red-50 border-l-4 border-red-600 text-red-900 flex flex-col gap-8 shadow-sm animate-in fade-in zoom-in duration-500 relative">
            <div class="flex items-start justify-between min-w-0">
                <div class="flex items-start gap-4 italic font-bold">
                    <i data-lucide="alert-circle" class="w-6 h-6 shrink-0 opacity-50 mt-1"></i>
                    <p class="text-sm leading-relaxed">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="shrink-0 p-2 hover:bg-white rounded-none transition-all ml-4">
                    <i data-lucide="x" class="w-6 h-6 text-red-300 hover:text-red-900"></i>
                </button>
            </div>

            @if(session('needs_reindex'))
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 pt-8 border-t border-red-100">
                    <form action="{{ route('accounting.journal.import.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="force_reindex" value="1">
                        <button type="submit" class="px-8 py-4 bg-red-600 text-white font-black text-[11px] uppercase tracking-widest hover:bg-black transition-all rounded-none shadow-xl">
                            RÉINDEXER ET IMPORTER MAINTENANT
                        </button>
                    </form>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed max-w-xs">
                        Cliquez sur ce bouton pour générer de nouveaux numéros et finaliser l'import sans recharger votre fichier.
                    </p>
                </div>
            @endif
        </div>
    @endif

    <form id="import-form" action="{{ route('accounting.journal.import.process') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-grow gap-12 sm:gap-16">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-16 items-start">
            <!-- COLONNE GAUCHE (8 COL) -->
            <div class="lg:col-span-8 flex flex-col gap-16 animate-in fade-in slide-in-from-left duration-700 delay-100">
                
                <!-- ZONE DE DEPOT (SANS ARRONDIS) -->
                <div class="relative group h-[350px] sm:h-[400px]">
                    <input type="file" name="file" id="file" required accept=".csv,.txt"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50"
                        onchange="updateFileName(this)">
                    
                    <div id="drop-zone" class="w-full h-full border-2 border-dashed border-gray-200 rounded-none p-10 flex flex-col items-center justify-center text-center group-hover:bg-white group-hover:border-black transition-all duration-500 bg-gray-50/50 relative overflow-hidden">
                        <div id="icon-container" class="mb-8 relative transition-transform duration-500 group-hover:-translate-y-4">
                            <i data-lucide="upload-cloud" class="w-16 h-16 text-gray-200 group-hover:text-black transition-colors"></i>
                        </div>

                        <div class="space-y-4">
                            <h3 id="file-name" class="text-xl sm:text-2xl font-black text-gray-900 uppercase tracking-tighter">Déposez votre CSV</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] opacity-60">ou cliquez pour choisir un fichier</p>
                        </div>
                    </div>
                </div>

                <!-- ILLUSTRATION (HORS CARDE, SANS ARRONDIS) -->
                <div class="w-full">
                    <div class="mb-10 flex items-center justify-between border-b-2 border-gray-900 pb-6">
                        <h3 class="text-xs font-black uppercase tracking-[0.4em] text-gray-900 italic leading-none">Illustration du format CSV</h3>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest opacity-50 underline">Séparateur (;)</span>
                    </div>
                    
                    <div class="w-full">
                        <table class="w-full text-[10px] sm:text-xs font-mono border-collapse border-b border-gray-100">
                            <thead>
                                <tr class="text-gray-400 uppercase text-left border-b-2 border-gray-900">
                                    <th class="py-5 pr-4 font-black">DATE</th>
                                    <th class="py-5 pr-4 font-black">Réf</th>
                                    <th class="py-5 pr-4 font-black">N° COMPTE</th>
                                    <th class="py-5 pr-4 font-black hidden sm:table-cell">LIBELLÉ</th>
                                    <th class="py-5 text-right font-black">DÉBIT</th>
                                    <th class="py-5 text-right font-black text-red-600">CRÉDIT</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-900 font-bold divide-y divide-gray-100">
                                <tr class="group">
                                    <td class="py-6 pr-4">24/03/2026</td>
                                    <td class="py-6 pr-4 font-black tracking-tighter">PC001</td>
                                    <td class="py-6 pr-4">707</td>
                                    <td class="py-6 pr-4 italic font-medium hidden sm:table-cell text-gray-400">Vente de marchandises</td>
                                    <td class="py-6 text-right">-</td>
                                    <td class="py-6 text-right text-red-600">1 500,00</td>
                                </tr>
                                <tr class="bg-gray-50/10">
                                    <td class="py-6 pr-4 opacity-30 italic">...</td>
                                    <td class="py-6 pr-4 font-black tracking-tighter opacity-10">PC001</td>
                                    <td class="py-6 pr-4 opacity-40">512</td>
                                    <td class="py-6 pr-4 italic font-medium hidden sm:table-cell opacity-20">Encaissement client</td>
                                    <td class="py-6 text-right font-black">1 500,00</td>
                                    <td class="py-6 text-right text-red-600">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- INFO BOX : SANS BLEU, SANS ARRONDIS -->
                    <div class="mt-10 flex items-start gap-5 py-6 px-10 border-l-[4px] border-black bg-gray-50 italic">
                        <i data-lucide="info" class="w-6 h-6 text-black opacity-30 shrink-0"></i>
                        <p class="text-[11px] font-bold text-gray-600 uppercase tracking-widest leading-relaxed">
                            Même référence = Même pièce. Equilibre obligatoire (Débit = Crédit). Les colonnes doivent correspondre à l'ordre exact.
                        </p>
                    </div>
                </div>
            </div>

            <!-- COLONNE DROITE (4 COL) -->
            <div class="lg:col-span-4 flex flex-col gap-10">
                <div class="bg-white border-2 border-gray-100 rounded-none p-10 flex flex-col h-full sticky top-32">
                    <h3 class="text-xs font-black uppercase tracking-[0.4em] text-gray-900 mb-10 pb-4 border-b-2 border-gray-900 italic">Colonnes</h3>
                    <div class="space-y-4 flex-grow">
                        @php 
                        $cols = ['DATE', 'Num PC (Réf)', 'JOURNAL', 'N° Compte', 'Libellé Compte', 'Libellé Op.', 'DÉBIT', 'CRÉDIT']; 
                        @endphp
                        @foreach($cols as $index => $col)
                        <div class="flex items-center justify-between p-4 border-b border-gray-50 group hover:border-black transition-all duration-300">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest group-hover:text-black">{{ $col }}</span>
                            <i data-lucide="chevron-right" class="w-3 h-3 text-gray-200 group-hover:text-black opacity-0 group-hover:opacity-100 transition-all"></i>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-16">
                        <button type="submit" class="w-full px-8 py-4 bg-primary text-white font-black rounded-none shadow-lg hover:bg-black hover:-translate-y-1 active:translate-y-0 transition-all uppercase tracking-[0.4em] text-xs flex items-center justify-center gap-4">
                            VALIDER L'IMPORTATION
                            <i data-lucide="zap" class="w-4 h-4 text-yellow-500"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function updateFileName(input) {
        const fileNameDisplay = document.getElementById('file-name');
        const dropZone = document.getElementById('drop-zone');
        
        if (input.files.length > 0) {
            const name = input.files[0].name.toUpperCase();
            fileNameDisplay.innerHTML = `<span class="text-primary font-black">${name}</span>`;
            dropZone.classList.add('border-black', 'bg-white');
            lucide.createIcons();
        }
    }
</script>
@endsection
