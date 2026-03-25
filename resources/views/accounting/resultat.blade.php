@extends('layouts.accounting')

@section('title', 'Compte de Résultat')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 no-print">
    <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Compte de Résultat</h1>
        <p class="text-sm text-gray-500 font-bold tracking-[0.2em] uppercase opacity-70">Performance de l'exercice au {{ date('d/m/Y') }}</p>
    </div>
    <div class="flex flex-wrap gap-4 no-print">
        <div class="relative group">
            <button id="resultat-actions-dropdown-btn" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-800 text-white font-bold shadow text-xs gap-3">
                <i data-lucide="download" class="w-4 h-4"></i>
                OPTIONS D'EXPORT
                <i data-lucide="chevron-down" class="w-3 h-3"></i>
            </button>
            <div id="resultat-actions-dropdown-menu" class="absolute right-0 mt-2 w-64 bg-white border border-border shadow-xl z-[2000] hidden">
                <a href="{{ route('accounting.resultat.pdf') }}" target="_blank" class="flex items-center gap-3 px-4 py-3 text-[11px] font-black text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
                    TÉLÉCHARGER PDF COMPLET
                </a>
                <button onclick="exportResultatToExcel('charges', 'Compte_Resultat_Charges')" class="flex items-center gap-3 px-4 py-3 text-[11px] font-black text-gray-700 hover:bg-gray-50 border-b border-gray-100 w-full text-left">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                    EXPORTER CHARGES (CSV)
                </button>
                <button onclick="exportResultatToExcel('produits', 'Compte_Resultat_Produits')" class="flex items-center gap-3 px-4 py-3 text-[11px] font-black text-gray-700 hover:bg-gray-50 w-full text-left">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                    EXPORTER PRODUITS (CSV)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filtre par période -->
<div class="mb-10 no-print">
    <form action="{{ request()->url() }}" method="GET" class="grid grid-cols-1 md:flex md:flex-row md:items-end gap-3 md:gap-5 bg-card-bg p-4 md:p-8 border border-border shadow-sm overflow-hidden max-w-full">
        <div class="w-full md:flex-1 md:min-w-[200px]">
            <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest px-1 flex items-center gap-2">
                <i data-lucide="calendar" class="w-3 h-3"></i> Période du
            </label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="JJ/MM/AAAA"
                   class="w-full bg-bg border border-border px-4 py-3 text-sm font-bold outline-none focus:border-primary transition-all rounded-lg box-border max-w-full">
        </div>
        <div class="w-full md:flex-1 md:min-w-[200px]">
            <label class="block text-[10px] uppercase font-bold text-gray-400 mb-2 tracking-widest px-1 flex items-center gap-2">
                <i data-lucide="calendar" class="w-3 h-3"></i> Au
            </label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="JJ/MM/AAAA"
                   class="w-full bg-bg border border-border px-4 py-3 text-sm font-bold outline-none focus:border-primary transition-all rounded-lg box-border max-w-full">
        </div>
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
            <button type="submit" class="w-full md:px-10 py-3 bg-primary text-white text-[11px] font-black uppercase tracking-[0.2em] hover:bg-primary-light transition-all shadow-lg flex items-center justify-center gap-3">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualiser
            </button>
            @if(request()->hasAny(['start_date', 'end_date']))
                <a href="{{ request()->url() }}" class="w-full md:px-8 py-3 bg-gray-100 text-gray-500 text-[11px] font-black uppercase tracking-[0.2em] hover:bg-gray-200 transition-all flex items-center justify-center gap-2 border border-gray-200">
                    <i data-lucide="x" class="w-4 h-4"></i> Effacer
                </a>
            @endif
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
    <!-- SECTION CHARGES (CLASSE 6) -->
    <div class="bg-card-bg border border-border rounded-none shadow-sm overflow-hidden flex flex-col">
        <div class="bg-primary text-white px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-[0.2em]">Charges</h2>
            <div class="bg-white/10 px-3 py-1 rounded-lg text-[10px] uppercase font-bold">Nature des dépenses</div>
        </div>
        
        <div class="table-responsive flex-1" id="resultat-charges">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                        <th class="px-4 py-3 border-r border-gray-200">Compte</th>
                        <th class="px-4 py-3 border-r border-gray-200">Intitulé</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($charges['groups'] as $prefix => $group)
                        @foreach($group['accounts'] as $acc)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-gray-900 border-r border-gray-200">{{ $acc['code'] }}</td>
                                <td class="px-4 py-3 text-gray-600 border-r border-gray-200 uppercase">{{ $acc['libelle'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <!-- Sous Total Groupe -->
                        <tr class="bg-gray-50/50 border-b border-gray-100 italic font-bold text-gray-500">
                            <td colspan="2" class="px-4 py-2 border-r border-gray-200">Sous Total {{ $group['prefix'] }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-primary text-white flex justify-between items-center mt-auto border-t-4 border-white/10">
            <span class="text-xs font-black uppercase tracking-widest leading-none">Total des Charges (VI)</span>
            <span class="text-2xl font-black italic whitespace-nowrap">{{ number_format($totalCharges, 2, ',', ' ') }} F</span>
        </div>
    </div>

    <!-- SECTION PRODUITS (CLASSE 7) -->
    <div class="bg-card-bg border border-border rounded-none shadow-sm overflow-hidden flex flex-col">
        <div class="bg-primary text-white px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-[0.2em]">Produits</h2>
            <div class="bg-white/10 px-3 py-1 rounded-lg text-[10px] uppercase font-bold">Nature des revenus</div>
        </div>
        
        <div class="table-responsive flex-1" id="resultat-produits">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-black text-gray-400 border-b border-gray-100">
                        <th class="px-4 py-3 border-r border-gray-200">Compte</th>
                        <th class="px-4 py-3 border-r border-gray-200">Intitulé</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($produits['groups'] as $prefix => $group)
                        @foreach($group['accounts'] as $acc)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-gray-900 border-r border-gray-200">{{ $acc['code'] }}</td>
                                <td class="px-4 py-3 text-gray-600 border-r border-gray-200 uppercase">{{ $acc['libelle'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <!-- Sous Total Groupe -->
                        <tr class="bg-gray-50/50 border-b border-gray-100 italic font-bold text-gray-500">
                            <td colspan="2" class="px-4 py-2 border-r border-gray-200">Sous Total {{ $group['prefix'] }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-primary text-white flex justify-between items-center mt-auto border-t-4 border-white/10">
            <span class="text-xs font-black uppercase tracking-widest leading-none">Total des Produits (VII)</span>
            <span class="text-2xl font-black italic whitespace-nowrap">{{ number_format($totalProduits, 2, ',', ' ') }} F</span>
        </div>
    </div>
</div>

<!-- RESULTAT FINAL -->
<div class="relative bg-card-bg border-4 border-primary rounded-[2.5rem] p-12 shadow-2xl overflow-hidden text-center">
    <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none">
        <i data-lucide="{{ $profit >= 0 ? 'award' : 'alert-octagon' }}" class="w-64 h-64 text-primary"></i>
    </div>

    <div class="relative z-10">
        <h3 class="text-sm font-black uppercase tracking-[0.5em] mb-6 text-primary">RÉSULTAT NET DE L'EXERCICE</h3>
        <div class="text-4xl md:text-8xl font-black tracking-tighter italic mb-6 {{ $profit >= 0 ? 'text-green-700' : 'text-red-700' }} whitespace-nowrap overflow-hidden text-ellipsis">
            {{ number_format(abs($profit), 2, ',', ' ') }} <span class="text-sm md:text-2xl font-normal not-italic opacity-40">FCFA</span>
        </div>
        
        <div class="mt-8 flex justify-center">
            @if($profit >= 0)
                <div class="inline-flex items-center px-8 py-3 bg-green-100 text-green-700 rounded-2xl font-black text-xs uppercase tracking-widest border-2 border-green-200">
                    <i data-lucide="smile" class="w-5 h-5 mr-3"></i>
                    BÉNÉFICE RÉALISÉ
                </div>
            @else
                <div class="inline-flex items-center px-8 py-3 bg-red-100 text-red-700 rounded-2xl font-black text-xs uppercase tracking-widest border-2 border-red-200">
                    <i data-lucide="frown" class="w-5 h-5 mr-3"></i>
                    DÉFICIT CONSTATÉ
                </div>
            @endif
        </div>
        <p class="mt-8 text-gray-500 italic font-bold text-sm tracking-wide max-w-lg mx-auto uppercase opacity-70">
            {{ $profit >= 0 ? 'La performance renforce les capitaux propres de l\'entité.' : 'Les charges excèdent les produits générés sur la période.' }}
        </p>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .grid { display: block !important; }
        .bg-card-bg { border: 1px solid #eee !important; margin-bottom: 2rem; }
        .bg-primary { background-color: #003366 !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endsection

@section('scripts')
{{-- Données JSON pour exports propres --}}
<script>
const chargesJson = @json($charges);
const produitsJson = @json($produits);

function exportResultatToExcel(dataset, filename) {
    const sep = ';';
    const q = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
    const fmt = (n) => parseFloat(n).toFixed(2).replace('.', ',');

    let rows = [];
    // En-tête
    rows.push([q('COMPTE'), q('INTITULÉ'), q('MONTANT')].join(sep));

    const data = dataset === 'charges' ? chargesJson : produitsJson;

    for (const [prefix, group] of Object.entries(data.groups)) {
        // Comptes du groupe
        for (const acc of group.accounts) {
            rows.push([q(acc.code), q(acc.libelle), q(fmt(acc.montant))].join(sep));
        }
        // Sous-total du groupe
        rows.push([q('Sous Total ' + group.prefix), q(''), q(fmt(group.total))].join(sep));
        rows.push(['', '', ''].join(sep)); // ligne vide
    }

    // Total général
    const total = dataset === 'charges' ? chargesJson.total : produitsJson.total;
    rows.push([q('TOTAL'), q(''), q(fmt(total))].join(sep));

    const csvContent = '\uFEFF' + rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', filename + '_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection

