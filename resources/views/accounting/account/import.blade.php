@extends('layouts.accounting')

@section('title', 'Importer des sous-comptes')

@section('content')
<div class="px-6 sm:px-12 py-10 w-full max-w-[1600px] mx-auto min-h-screen flex flex-col">
    <!-- HEADER -->
    <div class="mb-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Import de Sous-comptes</h1>
            <p class="text-sm text-slate-500 mt-1 uppercase font-bold tracking-widest">Chargement massif via CSV</p>
        </div>
        <a href="{{ route('accounting.account.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all text-xs flex items-center gap-2">
            <i data-lucide="x" class="w-4 h-4"></i>
            Quitter l'import
        </a>
    </div>



    <form id="import-form" action="{{ route('accounting.account.import.preview') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-grow gap-12 sm:gap-16">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-16 items-start">
            <!-- COLONNE GAUCHE -->
            <div class="lg:col-span-8 flex flex-col gap-16 animate-in fade-in slide-in-from-left duration-700 delay-100">
                
                <!-- ZONE DE DEPOT -->
                <div class="relative group h-[300px]">
                    <input type="file" name="file" id="file" required accept=".csv,.txt"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50"
                        onchange="updateFileName(this)">
                    
                    <div id="drop-zone" class="w-full h-full border-2 border-dashed border-slate-200 rounded-2xl p-10 flex flex-col items-center justify-center text-center group-hover:bg-slate-50/50 group-hover:border-primary transition-all duration-300 bg-white relative overflow-hidden shadow-sm">
                        <div id="icon-container" class="mb-6">
                            <i data-lucide="file-up" class="w-16 h-16 text-slate-200 transition-all"></i>
                        </div>

                        <div class="space-y-4">
                            <h3 id="file-name" class="text-lg font-bold text-slate-800">Sélectionnez votre fichier CSV</h3>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">ou déposez-le directement ici</p>
                        </div>
                    </div>
                </div>
                <!-- ZONE D'APERÇU INSTANTANNÉ (JS) -->
                <div id="js-preview-container" class="hidden">
                    <div class="mb-6 flex items-center justify-between border-b border-slate-200 pb-4">
                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-primary">Aperçu du fichier</h3>
                        <span id="file-row-count" class="text-[10px] font-bold text-slate-400 uppercase"></span>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest border-b border-slate-100">
                                <tr id="js-preview-header"></tr>
                            </thead>
                            <tbody id="js-preview-body" class="divide-y divide-slate-50 text-[13px]"></tbody>
                        </table>
                    </div>
                </div>

                <!-- ILLUSTRATION -->
                <div id="format-illustration" class="w-full">
                    <div class="mb-8 flex items-center justify-between border-b border-slate-200 pb-4">
                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-800 leading-none">Structure attendue</h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Format : CSV (point-virgule)</span>
                    </div>
                    
                    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="text-slate-400 uppercase text-[10px] font-bold tracking-widest text-left border-b border-slate-100 bg-slate-50/50">
                                    <th class="px-6 py-4">NUMERO</th>
                                    <th class="px-6 py-4">LIBELLE</th>
                                </tr>
                            </thead>
                            <tbody class="text-[13px]">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-primary">411101</td>
                                    <td class="px-6 py-4 font-semibold text-slate-700">Client Dupont SAS</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- INFO BOX -->
                <div class="mt-8 flex items-start gap-4 p-6 bg-slate-50 rounded-xl border border-slate-200">
                    <i data-lucide="info" class="w-5 h-5 text-primary shrink-0"></i>
                    <p class="text-[13px] text-slate-600 leading-relaxed font-medium">
                        Le parent est détecté automatiquement. Le numéro de sous-compte doit être unique et commencer par le numéro du compte parent correspondant.
                    </p>
                </div>
            </div>

            <!-- COLONNE DROITE -->
            <div class="lg:col-span-4 flex flex-col gap-10">
                <div class="bg-white border border-slate-200 rounded-xl p-8 sticky top-8 shadow-sm flex flex-col gap-6">
                    <div>
                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-4">Validation</h3>
                        <p class="text-[13px] text-slate-500 leading-relaxed mt-4">
                            Après avoir cliqué, vous pourrez vérifier chaque ligne avant la validation finale dans la base de données.
                        </p>
                    </div>
                    
                    <button type="submit" 
                        class="w-full py-4 bg-primary text-white font-bold rounded-xl hover:opacity-95 transition-all text-sm flex items-center justify-center gap-3 shadow-md">
                        Continuer vers l'aperçu
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
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
                    const headerHtml = header.map(h => `<th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 whitespace-nowrap">${h.trim().replace(/^"(.*)"$/, '$1')}</th>`).join('');
                    document.getElementById('js-preview-header').innerHTML = headerHtml;
                    
                    // Render body
                    let bodyHtml = "";
                    rows.forEach(row => {
                        const cols = row.split(delimiter);
                        bodyHtml += `<tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">${cols.map(c => `<td class="px-6 py-4 text-slate-700 font-semibold text-xs whitespace-nowrap">${c.trim().replace(/^"(.*)"$/, '$1')}</td>`).join('')}</tr>`;
                    });
                    document.getElementById('js-preview-body').innerHTML = bodyHtml;
                    document.getElementById('file-row-count').innerText = `${lines.length - 1} lignes détectées`;
                    
                    jsPreviewContainer.classList.remove('hidden');
                    formatIllustration.classList.add('hidden');
                    dropZone.classList.add('border-primary', 'bg-white/80');
                    dropZone.classList.remove('bg-slate-50/50');
                }
            };
            reader.readAsText(input.files[0]);
            
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
</script>
@endsection
