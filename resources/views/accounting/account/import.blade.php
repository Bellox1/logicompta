@extends('layouts.accounting')

@section('title', 'Importer des sous-comptes')

@section('content')
<div class="px-6 sm:px-12 py-10 w-full max-w-[1600px] mx-auto min-h-screen flex flex-col">
    <!-- HEADER -->
    <div class="mb-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-8 animate-in fade-in slide-in-from-top duration-700">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 uppercase">Import Sous-comptes</h1>
            <div class="flex items-center gap-3 mt-4">
                <span class="h-[2px] w-12 bg-gray-900"></span>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Chargement massif via CSV</p>
            </div>
        </div>
        <a href="{{ route('accounting.account.index') }}" class="group flex items-center gap-4 px-8 py-4 bg-white border border-gray-100 rounded-none text-xs font-bold uppercase text-gray-500 hover:text-black hover:border-black transition-all tracking-widest italic">
            <i data-lucide="x" class="w-4 h-4 group-hover:rotate-90 transition-transform"></i>
            Quitter l'import
        </a>
    </div>



    <form id="import-form" action="{{ route('accounting.account.import.process') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-grow gap-12 sm:gap-16">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-16 items-start">
            <!-- COLONNE GAUCHE -->
            <div class="lg:col-span-8 flex flex-col gap-16 animate-in fade-in slide-in-from-left duration-700 delay-100">
                
                <!-- ZONE DE DEPOT -->
                <div class="relative group h-[350px] sm:h-[400px]">
                    <input type="file" name="file" id="file" required accept=".csv,.txt"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50"
                        onchange="updateFileName(this)">
                    
                    <div id="drop-zone" class="w-full h-full border-2 border-dashed border-gray-200 rounded-none p-10 flex flex-col items-center justify-center text-center group-hover:bg-white group-hover:border-black transition-all duration-500 bg-gray-50/50 relative overflow-hidden">
                        <div id="icon-container" class="mb-8 relative transition-transform duration-500 group-hover:-translate-y-4">
                            <i data-lucide="upload-cloud" class="w-16 h-16 text-gray-200 group-hover:text-black transition-colors"></i>
                        </div>

                        <div class="space-y-4">
                            <h3 id="file-name" class="text-xl sm:text-2xl font-bold text-gray-900 uppercase">Déposez votre CSV</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest opacity-60">ou cliquez pour choisir un fichier</p>
                        </div>
                    </div>
                </div>

                <!-- ILLUSTRATION -->
                <div class="w-full">
                    <div class="mb-10 flex items-center justify-between border-b-2 border-gray-900 pb-6">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-900 italic leading-none">Aperçu du format CSV</h3>
                        <span class="text-[9px] font-bold text-gray-400 uppercase opacity-50 underline">Séparateur (;)</span>
                    </div>
                    
                    <div class="w-full overflow-auto">
                        <table class="w-full text-[10px] sm:text-xs font-mono border-collapse border-b border-gray-100">
                            <thead>
                                <tr class="text-gray-400 uppercase text-left border-b-2 border-gray-900">
                                    <th class="py-5 pr-4 font-bold">NUMERO</th>
                                    <th class="py-5 pr-4 font-bold">LIBELLE</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-900 font-bold divide-y divide-gray-100">
                                <tr class="group">
                                    <td class="py-6 pr-4 font-bold">411101</td>
                                    <td class="py-6 pr-4 italic font-medium text-gray-500">Client Dupont SAS</td>
                                </tr>
                                <tr class="group">
                                    <td class="py-6 pr-4 font-bold">512001</td>
                                    <td class="py-6 pr-4 italic font-medium text-gray-500">Banque BOA - Cpte Courant</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- INFO BOX -->
                    <div class="mt-10 flex items-start gap-5 py-6 px-10 border-l-[4px] border-black bg-gray-50 italic">
                        <i data-lucide="info" class="w-6 h-6 text-black opacity-30 shrink-0"></i>
                        <p class="text-[11px] font-bold text-gray-600 uppercase tracking-wider leading-relaxed">
                            Le parent est détecté automatiquement. Le numéro de sous-compte doit être unique et commencer par le numéro du compte parent correspondant.
                        </p>
                    </div>
                </div>
            </div>

            <!-- COLONNE DROITE -->
            <div class="lg:col-span-4 flex flex-col gap-10">
                <div class="bg-white border-2 border-gray-100 rounded-none p-10 flex flex-col h-full sticky top-32">
                    <h3 class="text-xs font-bold uppercase text-gray-900 mb-10 pb-4 border-b-2 border-gray-900 italic">Colonnes attendues</h3>
                    <div class="space-y-4 flex-grow">
                        <div class="flex items-center justify-between p-4 border-b border-gray-50 group hover:border-black transition-all duration-300">
                            <span class="text-[11px] font-bold text-primary uppercase tracking-widest">NUMERO</span>
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500"></i>
                        </div>
                        <div class="flex items-center justify-between p-4 border-b border-gray-50 group hover:border-black transition-all duration-300">
                            <span class="text-[11px] font-bold text-primary uppercase tracking-widest">LIBELLE</span>
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500"></i>
                        </div>
                    </div>
                    
                    <div class="mt-16">
                        <button type="submit" class="w-full px-8 py-4 bg-primary text-white font-bold rounded-none shadow-lg hover:bg-black hover:scale-[1.02] active:scale-100 transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-4">
                            Lancer l'importation
                            <i data-lucide="upload" class="w-4 h-4 text-white"></i>
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
