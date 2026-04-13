@extends('layouts.accounting')

@section('title', 'Compte de Résultat')

@section('content')
@if(request('show_archived') == '1' && request('start_date'))
    <div class="mb-6 bg-primary/10 border-l-4 border-primary p-4 flex items-center justify-between shadow-sm animate-fade-in no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary text-white flex items-center justify-center rounded-xl">
                <i data-lucide="archive" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-text-main uppercase leading-none">Archives de l'exercice {{ date('Y', strtotime(request('start_date'))) }}</h3>
                <p class="text-xs text-text-secondary font-black uppercase tracking-widest mt-1">Données scellées et définitives</p>
            </div>
        </div>
        <a href="{{ route('accounting.archive.index') }}" class="text-[10px] font-black uppercase text-primary bg-white border border-primary px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-all">
            Retour au Hub
        </a>
    </div>
@endif

<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 no-print">
    <div>
        <h1 class="text-3xl font-black text-text-main uppercase tracking-tight">Compte de Résultat</h1>
        <p class="text-sm text-text-secondary mt-1 font-bold tracking-widest uppercase">Performance de l'exercice au {{ date('d/m/Y') }}</p>
    </div>
    <div class="flex flex-wrap gap-4 no-print">
        <div class="relative group">
            <button id="resultat-actions-dropdown-btn" class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-800 text-white font-bold shadow text-xs gap-3">
                <i data-lucide="download" class="w-4 h-4"></i>
                OPTIONS D'EXPORT
                <i data-lucide="chevron-down" class="w-3 h-3"></i>
            </button>
            <div id="resultat-actions-dropdown-menu" class="absolute right-0 mt-2 w-64 bg-card-bg border border-border shadow-xl z-[2000] hidden rounded-xl overflow-hidden">
                <a href="{{ route('accounting.resultat.pdf') }}" target="_blank" class="flex items-center gap-3 px-4 py-3 text-[11px] font-black text-text-main hover:bg-white/50 border-b border-border">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
                    TÉLÉCHARGER PDF COMPLET
                </a>
                <button onclick="exportResultatToExcel('charges', 'Compte_Resultat_Charges')" class="flex items-center gap-3 px-4 py-3 text-[11px] font-black text-text-main hover:bg-white/50 border-b border-border w-full text-left">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                    EXPORTER CHARGES (CSV)
                </button>
                <button onclick="exportResultatToExcel('produits', 'Compte_Resultat_Produits')" class="flex items-center gap-3 px-4 py-3 text-[11px] font-black text-text-main hover:bg-white/50 w-full text-left">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-green-600"></i>
                    EXPORTER PRODUITS (CSV)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filtre par période -->
<div class="mb-10 no-print">
    <form action="{{ request()->url() }}" method="GET" class="mb-10 grid grid-cols-1 md:flex md:flex-row md:items-end gap-5 bg-card-bg border border-border p-6 rounded-3xl shadow-sm no-print">
        <div class="w-full md:flex-1">
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wider px-1">Période du</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                   class="w-full bg-bg border border-border px-4 py-3 text-sm font-bold outline-none focus:border-primary transition-all rounded-xl dark:text-white">
        </div>
        <div class="w-full md:flex-1">
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-2 uppercase tracking-wider px-1">Au</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                   class="w-full bg-bg border border-border px-4 py-3 text-sm font-bold outline-none focus:border-primary transition-all rounded-xl dark:text-white">
        </div>
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
            <button type="submit" class="w-full md:px-10 py-3 bg-primary text-white text-[11px] font-black uppercase tracking-[0.2em] hover:bg-primary-light transition-all shadow-lg flex items-center justify-center gap-3">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Actualiser
            </button>
            @if(request()->hasAny(['start_date', 'end_date']))
                <a href="{{ request()->url() }}" class="w-full md:px-8 py-3 bg-white text-text-secondary text-[11px] font-black uppercase tracking-[0.2em] hover:bg-slate-50 transition-all flex items-center justify-center gap-2 border border-border shadow-sm rounded-xl">
                    <i data-lucide="x" class="w-4 h-4"></i> Effacer
                </a>
            @endif
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
    <!-- SECTION CHARGES (CLASSE 6) -->
    <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
        <div class="bg-primary text-white px-6 py-6 flex items-center justify-between">
            <h2 class="text-lg font-black uppercase tracking-[0.3em]">Charges</h2>
            <div class="bg-white/10 px-4 py-1.5 rounded-xl text-[10px] uppercase font-black">Nature des dépenses</div>
        </div>
        
        <div class="table-responsive flex-1" id="resultat-charges">
            <table class="w-full text-left border-collapse min-w-[600px] sticky-thead">
                <thead>
                    <tr class="bg-white/50 text-[10px] uppercase font-black text-text-secondary border-b border-border">
                        <th class="px-4 py-4 border-r border-border">Compte</th>
                        <th class="px-4 py-4 border-r border-border">Intitulé</th>
                        <th class="px-4 py-4 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($charges['groups'] as $prefix => $group)
                        @foreach($group['accounts'] as $acc)
                            <tr class="border-b border-border hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4 font-mono font-black text-text-main border-r border-border">{{ $acc['code'] }}</td>
                                <td class="px-4 py-4 text-text-secondary border-r border-border font-black uppercase">{{ $acc['libelle'] }}</td>
                                <td class="px-4 py-4 text-right font-black text-text-main whitespace-nowrap">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <!-- Sous Total Groupe -->
                        <tr class="bg-slate-50/50 border-b border-slate-100 font-bold text-slate-500">
                            <td colspan="2" class="px-4 py-2 border-r border-slate-200">Sous Total {{ $group['prefix'] }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center text-text-secondary/40">
                                    <i data-lucide="file-warning" class="w-12 h-12 mb-4 opacity-20"></i>
                                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Aucun mouvement de charge enregistré</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-primary text-white flex justify-between items-center mt-auto border-t-4 border-white/20">
            <span class="text-xs font-black uppercase tracking-[0.2em]">Total des Charges (VI)</span>
            <span class="text-2xl font-black whitespace-nowrap">{{ number_format($totalCharges, 2, ',', ' ') }} F</span>
        </div>
    </div>

    <!-- SECTION PRODUITS (CLASSE 7) -->
    <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
        <div class="bg-primary text-white px-6 py-6 flex items-center justify-between">
            <h2 class="text-lg font-black uppercase tracking-[0.3em]">Produits</h2>
            <div class="bg-white/10 px-4 py-1.5 rounded-xl text-[10px] uppercase font-black">Nature des revenus</div>
        </div>
        
        <div class="table-responsive flex-1" id="resultat-produits">
            <table class="w-full text-left border-collapse min-w-[600px] sticky-thead">
                <thead>
                    <tr class="bg-white/50 text-[10px] uppercase font-black text-text-secondary border-b border-border">
                        <th class="px-4 py-4 border-r border-border">Compte</th>
                        <th class="px-4 py-4 border-r border-border">Intitulé</th>
                        <th class="px-4 py-4 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($produits['groups'] as $prefix => $group)
                        @foreach($group['accounts'] as $acc)
                            <tr class="border-b border-border hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4 font-mono font-black text-text-main border-r border-border">{{ $acc['code'] }}</td>
                                <td class="px-4 py-4 text-text-secondary border-r border-border font-black uppercase">{{ $acc['libelle'] }}</td>
                                <td class="px-4 py-4 text-right font-black text-text-main whitespace-nowrap">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <!-- Sous Total Groupe -->
                        <tr class="bg-slate-50/50 border-b border-slate-100 font-bold text-slate-500">
                            <td colspan="2" class="px-4 py-2 border-r border-slate-200">Sous Total {{ $group['prefix'] }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center text-text-secondary/40">
                                    <i data-lucide="file-warning" class="w-12 h-12 mb-4 opacity-20"></i>
                                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Aucun mouvement de produit enregistré</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-primary text-white flex justify-between items-center mt-auto border-t-4 border-white/20">
            <span class="text-xs font-black uppercase tracking-[0.2em]">Total des Produits (VII)</span>
            <span class="text-2xl font-black whitespace-nowrap">{{ number_format($totalProduits, 2, ',', ' ') }} F</span>
        </div>
    </div>
</div>

<!-- RESULTAT FINAL -->
<div class="relative bg-card-bg border-4 border-primary rounded-[3rem] p-16 shadow-2xl overflow-hidden text-center">
    <div class="absolute -right-10 -bottom-10 opacity-5 pointer-events-none">
        <i data-lucide="{{ $profit >= 0 ? 'award' : 'alert-octagon' }}" class="w-64 h-64 text-primary"></i>
    </div>
    
    <div class="relative z-10">
        <h3 class="text-sm font-black uppercase tracking-[0.5em] mb-8 text-primary">RÉSULTAT NET DE L'EXERCICE</h3>
        <div class="text-5xl md:text-8xl font-black tracking-tighter mb-8 {{ $profit >= 0 ? 'text-green-700' : 'text-red-700' }} whitespace-nowrap overflow-hidden text-ellipsis shadow-sm">
            {{ number_format(abs($profit), 2, ',', ' ') }} <span class="text-sm md:text-2xl font-normal opacity-40">FCFA</span>
        </div>
        
        <div class="mt-8 flex justify-center">
            @if($profit >= 0)
                <div class="inline-flex items-center px-10 py-4 bg-green-100 text-green-700 rounded-3xl font-black text-xs uppercase tracking-widest border-2 border-green-200">
                    <i data-lucide="smile" class="w-6 h-6 mr-3"></i>
                    BÉNÉFICE RÉALISÉ
                </div>
            @else
                <div class="inline-flex items-center px-10 py-4 bg-red-100 text-red-700 rounded-3xl font-black text-xs uppercase tracking-widest border-2 border-red-200">
                    <i data-lucide="frown" class="w-6 h-6 mr-3"></i>
                    DÉFICIT CONSTATÉ
                </div>
            @endif
        </div>
        <p class="mt-10 text-text-secondary font-black text-xs tracking-[0.2em] max-w-lg mx-auto uppercase opacity-60">
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
        .bg-primary { background-color: #005b82 !important; color: white !important; -webkit-print-color-adjust: exact; }
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

