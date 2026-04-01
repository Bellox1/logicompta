@extends('layouts.accounting')

@section('title', 'Aperçu de l\'import Journal')

@section('content')
<div class="px-6 sm:px-12 py-10 w-full max-w-[1600px] mx-auto min-h-screen flex flex-col">
    <!-- HEADER -->
    <div class="mb-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Validation de l'import</h1>
            <p class="text-sm text-slate-500 mt-1 uppercase font-bold tracking-widest tracking-tighter">Modifiez les erreurs directement dans le tableau</p>
        </div>
        <a href="{{ route('accounting.journal.import') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all text-xs flex items-center gap-2 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Changer de fichier
        </a>
    </div>

    <form action="{{ route('accounting.journal.import.process') }}" method="POST" class="flex flex-col gap-10">
        @csrf
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest border-b border-slate-100">
                        <th class="px-6 py-4 w-12 text-center">#</th>
                        <th class="px-6 py-4 w-32">Pièce</th>
                        <th class="px-6 py-4 w-32 text-center">Date</th>
                        <th class="px-6 py-4 w-48 text-center">Compte</th>
                        <th class="px-6 py-4">Libellé</th>
                        <th class="px-6 py-4 w-32 text-right">Débit</th>
                        <th class="px-6 py-4 w-32 text-right">Crédit</th>
                        <th class="px-6 py-4 w-24 text-center">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($previewData as $index => $row)
                        @php $isError = $row['status'] !== 'ok'; @endphp
                        <tr class="transition-colors {{ $isError ? 'bg-red-50' : 'hover:bg-slate-50/50' }}">
                            <td class="px-6 py-4 text-center">
                                <span class="text-[10px] font-bold text-slate-300">{{ $row['line'] }}</span>
                                <input type="hidden" name="rows[{{ $index }}][line]" value="{{ $row['line'] }}">
                            </td>
                            <td class="px-6 py-3">
                                <input type="text" name="rows[{{ $index }}][piece]" value="{{ $row['piece'] }}" 
                                    class="w-full bg-transparent border-none focus:ring-2 focus:ring-primary rounded px-2 py-1 font-black text-slate-800 text-xs uppercase tracking-widest">
                            </td>
                            <td class="px-6 py-3">
                                <input type="date" name="rows[{{ $index }}][date]" value="{{ $row['date'] }}"
                                    class="w-full bg-transparent border-none focus:ring-2 focus:ring-primary rounded px-1 py-1 text-slate-500 text-[11px] font-bold">
                            </td>
                            <td class="px-6 py-3">
                                <div class="relative group">
                                    <input type="text" name="rows[{{ $index }}][account]" value="{{ $row['account'] }}"
                                        class="w-full bg-transparent border-none focus:ring-2 focus:ring-primary rounded px-2 py-1 font-bold text-slate-700 text-xs tracking-widest {{ $isError ? 'text-red-600 bg-red-100/50' : '' }}">
                                    @if($isError)
                                        <div class="absolute -top-8 left-0 hidden group-hover:block bg-red-600 text-white text-[9px] font-bold px-2 py-1 rounded shadow-lg z-50 whitespace-nowrap">
                                            @if($row['status'] == 'error_main') Compte général interdit @else Compte introuvable @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <input type="text" name="rows[{{ $index }}][label]" value="{{ $row['label'] }}"
                                    class="w-full bg-transparent border-none focus:ring-2 focus:ring-primary rounded px-2 py-1 text-slate-600 font-semibold text-xs">
                            </td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.01" name="rows[{{ $index }}][debit]" value="{{ $row['debit'] }}"
                                    class="w-full text-right bg-transparent border-none focus:ring-2 focus:ring-primary rounded px-2 py-1 font-mono text-xs text-slate-700">
                            </td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.01" name="rows[{{ $index }}][credit]" value="{{ $row['credit'] }}"
                                    class="w-full text-right bg-transparent border-none focus:ring-2 focus:ring-primary rounded px-2 py-1 font-mono text-xs text-slate-700">
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($row['status'] == 'ok')
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500 mx-auto"></i>
                                @else
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 animate-pulse mx-auto border-2 border-red-500 rounded-full"></i>
                                @endif
                                <input type="hidden" name="rows[{{ $index }}][journal]" value="{{ $row['journal'] }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- STICKY ACTIONS -->
        <div class="fixed bottom-10 right-10 flex flex-col gap-4 shadow-2xl animate-fade-up z-[100]">
            <div class="bg-slate-900 text-white p-6 rounded-2xl flex flex-col gap-4 min-w-[300px]">
                <div class="flex justify-between items-center border-b border-slate-700 pb-4">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Débit</span>
                    <span id="global-debit" class="text-sm font-bold text-green-400">0,00 FCFA</span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-700 pb-4">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Crédit</span>
                    <span id="global-credit" class="text-sm font-bold text-rose-400">0,00 FCFA</span>
                </div>
                <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-xl hover:opacity-90 transition-all text-sm flex items-center justify-center gap-3">
                    Confirmer l'importation
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;
        document.querySelectorAll('input[name$="[debit]"]').forEach(input => {
            totalDebit += parseFloat(input.value || 0);
        });
        document.querySelectorAll('input[name$="[credit]"]').forEach(input => {
            totalCredit += parseFloat(input.value || 0);
        });
        
        document.getElementById('global-debit').innerText = totalDebit.toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' FCFA';
        document.getElementById('global-credit').innerText = totalCredit.toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' FCFA';
    }

    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    calculateTotals();
</script>
@endsection
