@extends('layouts.accounting')

@section('title', 'Journal des écritures')

@section('styles')
<style>
    .journal-table {
        width: 100%;
        border-collapse: collapse;
    }
    .journal-table th, .journal-table td {
        padding: 0.8rem 1rem;
        border-bottom: 1px solid var(--border);
    }
    .journal-table th {
        background: var(--primary);
        color: white;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .journal-table th.amount { text-align: right; }
    
    .entry-group-header {
        background: rgba(0, 0, 0, 0.05);
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--primary);
    }
    
    .amount { text-align: right; font-weight: 500; white-space: nowrap; }
    .footer-pagination { margin-top: 2rem; }
    
    .piece-info {
        display: flex;
        gap: 1.5rem;
        align-items: center;
    }
    .piece-info span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Journal des écritures</h1>
        <p class="text-sm text-gray-500">Historique complet des opérations comptables</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="exportJournalToCSV()" class="inline-flex items-center justify-center px-5 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-all shadow-sm">
            <i data-lucide="download" class="w-5 h-5 mr-2"></i>
            Excel
        </button>
        <a href="{{ route('accounting.journal.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold rounded-xl hover:bg-primary-light transition-all shadow-sm">
            <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i>
            Nouvelle écriture
        </a>
    </div>
</div>

<div class="bg-card-bg border border-border rounded-2xl shadow-sm mb-8">
    <div class="table-responsive">
        <table class="w-full text-left border-collapse min-w-[1000px] sticky-thead">
            <thead>
                <tr class="bg-primary text-white">
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-center border-r border-white/10" style="width: 100px;">DATE</th>
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-center border-r border-white/10" style="width: 100px;">Num PC</th>
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10" style="width: 120px;">N° DE COMPTE</th>
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10">INTITULE</th>
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest border-r border-white/10">LIBELLES</th>
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-right border-r border-white/10" style="width: 120px;">DEBIT</th>
                    <th class="px-4 py-4 font-bold text-[11px] uppercase tracking-widest text-right" style="width: 120px;">CREDIT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 italic">
                @forelse($entries as $entry)
                    @foreach($entry->lines as $index => $line)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            @if($index === 0)
                                <td rowspan="{{ $entry->lines->count() }}" class="px-4 py-4 text-sm text-gray-900 font-bold text-center border-r border-gray-100 align-middle not-italic">
                                    {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                </td>
                                <td rowspan="{{ $entry->lines->count() }}" class="px-4 py-4 text-sm text-primary font-black text-center border-r border-gray-100 align-middle not-italic">
                                    {{ str_replace('PC-', '', $entry->numero_piece) }}
                                </td>
                            @endif
                            
                            <td class="px-4 py-4 text-sm font-bold text-gray-800 border-r border-gray-100 not-italic">
                                {{ $line->account->code_compte }}
                            </td>
                            <td class="px-4 py-4 text-xs font-semibold text-gray-600 border-r border-gray-100 not-italic">
                                {{ $line->account->libelle }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 border-r border-gray-100">
                                {{ $line->libelle ?: $entry->libelle }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-gray-900 border-r border-gray-100 bg-gray-50/20 not-italic whitespace-nowrap">
                                {{ number_format($line->debit, 2, ',', ' ') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-gray-900 bg-gray-50/20 not-italic whitespace-nowrap">
                                {{ number_format($line->credit, 2, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center not-italic">
                            <i data-lucide="search-x" class="mx-auto w-12 h-12 text-gray-300 mb-4 opacity-50"></i>
                            <p class="text-gray-500 font-medium">Aucune écriture trouvée dans le journal.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8 flex justify-center">
    {{ $entries->links() }}
</div>
@section('scripts')
<script>
function exportJournalToCSV() {
    const sep = ';';
    const q = (v) => '"' + String(v).replace(/"/g, '""') + '"';
    let rows = [];
    
    // Headers matching the view exactly
    rows.push(['DATE', 'Num PC', 'N° DE COMPTE', 'INTITULE', 'LIBELLES', 'DEBIT', 'CREDIT'].join(sep));
    
    let currentDate = '';
    let currentPC = '';
    
    document.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.querySelector('td[colspan="7"]')) return; // skip empty state
        
        let rowData = [];
        const cells = tr.querySelectorAll('td');
        
        if (cells.length === 7) {
            // This is the first line of an entry (has date and PC)
            currentDate = cells[0].innerText.trim();
            currentPC = cells[1].innerText.trim();
            
            rowData.push(q(currentDate));
            rowData.push(q(currentPC));
            rowData.push(q(cells[2].innerText.trim()));
            rowData.push(q(cells[3].innerText.trim()));
            rowData.push(q(cells[4].innerText.trim()));
            rowData.push(q(cells[5].innerText.trim()));
            rowData.push(q(cells[6].innerText.trim()));
        } else {
            // Subsequent lines of the same entry (no date/PC in HTML due to rowspan)
            rowData.push(q(currentDate));
            rowData.push(q(currentPC));
            rowData.push(q(cells[0].innerText.trim()));
            rowData.push(q(cells[1].innerText.trim()));
            rowData.push(q(cells[2].innerText.trim()));
            rowData.push(q(cells[3].innerText.trim()));
            rowData.push(q(cells[4].innerText.trim()));
        }
        rows.push(rowData.join(sep));
    });
    
    const csvContent = '\uFEFF' + rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'Journal_Comptable_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
@endsection
