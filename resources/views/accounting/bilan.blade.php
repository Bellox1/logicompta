@extends('layouts.accounting')

@section('title', 'Bilan')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Bilan Patrimonial</h1>
        <p class="text-sm text-gray-500 font-medium tracking-wide">État de santé financière au {{ date('d/m/Y') }}</p>
    </div>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('accounting.bilan.pdf') }}" target="_blank" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-700 transition-all shadow-sm">
            <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
            Exporter PDF
        </a>
        <button onclick="exportBilanComplete()" class="flex-1 lg:flex-none inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-bold rounded-2xl hover:bg-green-700 transition-all shadow-sm">
            <i data-lucide="file-spreadsheet" class="w-5 h-5 mr-3"></i>
            Exporter Bilan Complet
        </button>
    </div>
</div>

<div class="flex flex-col xl:flex-row gap-8 mb-12">
    <!-- ACTIF -->
    <div class="flex-1 min-w-0 bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 flex items-center justify-between text-white border-b border-green-800">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 p-2.5 rounded-xl shadow-inner">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold uppercase tracking-widest leading-none mb-1">Actif</h2>
                    <p class="text-xs font-bold text-green-100 uppercase tracking-tighter opacity-80">Emplois de l'entreprise</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold tracking-widest leading-none opacity-60">Total Actif</div>
                <div class="text-2xl font-bold italic whitespace-nowrap">{{ number_format($actif->sum('solde'), 2, ',', ' ') }} F</div>
            </div>
        </div>
        
        <div class="table-responsive flex-1" id="bilan-actif">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="bg-gray-50 border-b border-gray-100 italic">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-gray-400">Rubrique</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-widest text-gray-400">Montant Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($actif as $item)
                        <tr class="hover:bg-green-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="block text-sm font-bold text-gray-800">{{ $item['libelle'] }}</span>
                                <span class="text-[10px] uppercase font-medium text-gray-400">Ressource durable</span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">{{ number_format($item['solde'], 2, ',', ' ') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-16 text-center text-gray-400 italic font-medium">
                                <i data-lucide="info" class="w-10 h-10 mx-auto mb-3 opacity-20"></i>
                                Aucun compte d'actif mouvementé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-green-50 border-t border-green-100 flex justify-between items-center">
            <span class="text-xs font-bold uppercase tracking-widest text-green-800">Total Bilan (Actif)</span>
            <span class="text-2xl font-bold text-green-700 italic underline decoration-green-200 decoration-4 underline-offset-4 whitespace-nowrap">{{ number_format($actif->sum('solde'), 2, ',', ' ') }} F</span>
        </div>
    </div>

    <!-- PASSIF -->
    <div class="flex-1 min-w-0 bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-primary to-primary-light p-6 flex items-center justify-between text-white border-b border-blue-900">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 p-2.5 rounded-xl shadow-inner">
                    <i data-lucide="trending-down" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold uppercase tracking-widest leading-none mb-1">Passif</h2>
                    <p class="text-xs font-bold text-blue-100 uppercase tracking-tighter opacity-80">Ressources de l'entreprise</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold tracking-widest leading-none opacity-60">Total Passif</div>
                <div class="text-2xl font-bold italic whitespace-nowrap">{{ number_format($passif->sum('solde'), 2, ',', ' ') }} F</div>
            </div>
        </div>
        
        <div class="table-responsive flex-1" id="bilan-passif">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="bg-gray-50 border-b border-gray-100 italic">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-gray-400">Rubrique</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-widest text-gray-400">Montant Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($passif as $item)
                        <tr class="hover:bg-primary/5 transition-colors">
                            <td class="px-6 py-4">
                                <span class="block text-sm font-bold text-gray-800">{{ $item['libelle'] }}</span>
                                <span class="text-[10px] uppercase font-medium text-gray-400">Dettes / Capitaux</span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">{{ number_format($item['solde'], 2, ',', ' ') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-16 text-center text-gray-400 italic font-medium">
                                <i data-lucide="info" class="w-10 h-10 mx-auto mb-3 opacity-20"></i>
                                Aucun compte de passif mouvementé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-blue-50 border-t border-blue-100 flex justify-between items-center">
            <span class="text-xs font-bold uppercase tracking-widest text-primary">Total Bilan (Passif)</span>
            <span class="text-2xl font-bold text-primary italic underline decoration-blue-200 decoration-4 underline-offset-4 whitespace-nowrap">{{ number_format($passif->sum('solde'), 2, ',', ' ') }} F</span>
        </div>
    </div>
</div>

@php
    $difference = $actif->sum('solde') - $passif->sum('solde');
@endphp

@if(abs($difference) > 0.001)
<div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 flex flex-col md:flex-row items-center gap-6 text-red-700 animate-pulse">
    <div class="bg-red-100 p-4 rounded-full">
        <i data-lucide="octagon-alert" class="w-8 h-8"></i>
    </div>
    <div class="flex-1 text-center md:text-left">
        <h4 class="text-lg font-bold uppercase tracking-wider leading-none mb-1">Déséquilibre détecté !</h4>
        <p class="text-sm font-medium opacity-80 leading-relaxed italic">
            Une différence de <strong class="whitespace-nowrap">{{ number_format(abs($difference), 2, ',', ' ') }} F</strong> a été identifiée entre l'Actif et le Passif. Vérifiez vos saisies dans le journal.
        </p>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function exportBilanComplete() {
    const sep = ';';
    const q = (v) => '"' + String(v).replace(/"/g, '""').trim() + '"';
    let csv = [];
    
    // Header unifié pour un tableau plat et propre
    csv.push([q('TYPE'), q('RUBRIQUE'), q('MONTANT NET')].join(sep));
    
    // --- ACTIF ---
    const actifTable = document.getElementById('bilan-actif').querySelector('table');
    actifTable.querySelectorAll('tbody tr').forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length < 2) return;
        csv.push([q('ACTIF'), q(cols[0].innerText.trim()), q(cols[1].innerText.trim())].join(sep));
    });
    csv.push([q('SOUS-TOTAL'), q('TOTAL ACTIF'), q('{{ number_format($actif->sum('solde'), 2, ',', ' ') }}')].join(sep));
    
    csv.push(['', '', ''].join(sep)); // Ligne de séparation
    
    // --- PASSIF ---
    const passifTable = document.getElementById('bilan-passif').querySelector('table');
    passifTable.querySelectorAll('tbody tr').forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length < 2) return;
        csv.push([q('PASSIF'), q(cols[0].innerText.trim()), q(cols[1].innerText.trim())].join(sep));
    });
    csv.push([q('SOUS-TOTAL'), q('TOTAL PASSIF'), q('{{ number_format($passif->sum('solde'), 2, ',', ' ') }}')].join(sep));

    const csvContent = '\uFEFF' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'Bilan_Patrimonial_Complet_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
