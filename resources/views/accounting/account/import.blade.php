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



    <form id="import-form" action="{{ route('accounting.account.import.preview') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-grow gap-12 sm:gap-16">
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
                <!-- ZONE D'APERÇU INSTANTANNÉ (JS) -->
                <div id="js-preview-container" class="hidden animate-in fade-in zoom-in duration-500">
                    <div class="mb-6 flex items-center justify-between border-b-2 border-primary pb-4">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-primary italic leading-none">Aperçu instantané du fichier choisi</h3>
                        <span id="file-row-count" class="text-[10px] font-bold text-gray-500 uppercase"></span>
                    </div>
                    <div class="w-full overflow-x-auto bg-white border border-gray-100 p-1">
                        <table class="w-full text-[10px] font-mono">
                            <thead class="bg-gray-50 text-gray-400 uppercase text-left border-b border-gray-100">
                                <tr id="js-preview-header"></tr>
                            </thead>
                            <tbody id="js-preview-body" class="text-gray-900 font-bold divide-y divide-gray-50"></tbody>
                        </table>
                    </div>
                </div>

                <!-- ILLUSTRATION -->
                <div id="format-illustration" class="w-full">
                    <div class="mb-10 flex items-center justify-between border-b-2 border-gray-900 pb-6">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-900 italic leading-none">Format attendu</h3>
                        <span class="text-[9px] font-bold text-gray-400 uppercase opacity-50 underline">Séparateur (;) ou (,)</span>
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
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- INFO BOX -->
                <div class="mt-4 flex items-start gap-5 py-6 px-10 border-l-[4px] border-black bg-gray-50 italic">
                    <i data-lucide="info" class="w-6 h-6 text-black opacity-30 shrink-0"></i>
                    <p class="text-[11px] font-bold text-gray-600 uppercase tracking-wider leading-relaxed">
                        Le parent est détecté automatiquement. Le numéro de sous-compte doit être unique et commencer par le numéro du compte parent correspondant.
                    </p>
                </div>
            </div>

            <!-- COLONNE DROITE -->
            <div class="lg:col-span-4 flex flex-col gap-10">
                <div class="bg-white border-2 border-gray-100 rounded-none p-10 flex flex-col h-full sticky top-32 shadow-xl">
                    <h3 class="text-xs font-black uppercase text-gray-900 mb-10 pb-4 border-b-2 border-gray-900 italic tracking-widest">Étape suivante</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mb-8 leading-relaxed">
                        Après avoir cliqué, vous pourrez vérifier chaque ligne avant la validation finale.
                    </p>
                    
                    <div class="mt-auto">
                        <button type="submit" class="w-full px-8 py-5 bg-primary text-white font-black rounded-none shadow-2xl hover:bg-black hover:scale-[1.02] active:scale-100 transition-all uppercase tracking-[0.2em] text-xs flex items-center justify-center gap-4">
                            Continuer vers l'aperçu
                            <i data-lucide="arrow-right" class="w-4 h-4 text-white"></i>
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
        const jsPreviewContainer = document.getElementById('js-preview-container');
        const formatIllustration = document.getElementById('format-illustration');
        
        if (input.files.length > 0) {
            const name = input.files[0].name.toUpperCase();
            fileNameDisplay.innerHTML = `<span class="text-primary font-black animate-pulse">${name}</span>`;
            dropZone.classList.add('border-primary', 'bg-white');
            
            // Generate JS Preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split(/\r?\n/).filter(line => line.trim() !== "");
                if (lines.length > 0) {
                    // Try to detect delimiter
                    const firstLine = lines[0];
                    const delimiter = firstLine.split(';').length >= firstLine.split(',').length ? ';' : ',';
                    
                    const header = lines[0].split(delimiter);
                    const rows = lines.slice(1, 6); // Max 5 preview rows
                    
                    // Render header
                    const headerHtml = header.map(h => `<th class="px-3 py-3 border-b border-gray-100">${h}</th>`).join('');
                    document.getElementById('js-preview-header').innerHTML = headerHtml;
                    
                    // Render body
                    let bodyHtml = "";
                    rows.forEach(row => {
                        const cols = row.split(delimiter);
                        bodyHtml += `<tr>${cols.map(c => `<td class="px-3 py-3">${c}</td>`).join('')}</tr>`;
                    });
                    document.getElementById('js-preview-body').innerHTML = bodyHtml;
                    document.getElementById('file-row-count').innerText = `${lines.length - 1} lignes détectées`;
                    
                    jsPreviewContainer.classList.remove('hidden');
                    formatIllustration.classList.add('hidden');
                }
            };
            reader.readAsText(input.files[0]);
            
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
</script>
@endsection
