<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Livre - {{ $user->entreprise->name ?? 'Logicompta' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: { primary: '#005b82', 'primary-light': '#0055aa' } } }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: white !important; }
            @page { margin: 1cm; }
            .page-break { page-break-after: always; }
        }
        body { font-family: 'Inter', sans-serif; }
        table { border-collapse: collapse; width: 100%; font-size: 9px; }
        th, td { border: 1px solid #e2e8f0; padding: 4px; }
        th { background-color: #f8fafc; text-transform: uppercase; font-weight: bold; }
    </style>
</head>
<body class="bg-white p-4 md:p-8">
    <div class="no-print mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-200">
        <p class="text-sm text-slate-600 font-medium italic">Vérifiez l'aperçu avant d'enregistrer en PDF.</p>
        <div class="flex flex-wrap gap-4">
            <button onclick="window.close()" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-900 border border-slate-300 rounded-xl hover:bg-white transition-all">Fermer</button>
            <button onclick="window.print()" class="px-8 py-2.5 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-primary-light transition-all flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Imprimer / Enregistrer PDF
            </button>
        </div>
    </div>

    <div class="mb-8 border-b-2 border-slate-900 pb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">GRAND LIVRE COMPTABLE</h1>
            <p class="text-sm font-bold text-slate-500 italic uppercase">{{ $user->entreprise->name ?? 'MA SOCIETE' }}</p>
        </div>
        <div class="text-right text-xs font-medium text-slate-400">
            Édité le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    @foreach($data as $account)
        <div class="mb-10 {{ !$loop->last ? 'page-break' : '' }}">
            <div class="bg-slate-50 border-x border-t border-slate-300 p-3 flex flex-col md:flex-row md:items-center justify-between gap-2">
                <span class="text-sm font-black uppercase text-primary">{{ $account->code_compte }} - {{ $account->libelle }}</span>
                <span class="text-[10px] font-bold italic text-slate-400">Compte de classe {{ substr($account->code_compte, 0, 1) }}</span>
            </div>
            <div class="overflow-x-auto shadow-sm rounded-b-xl border border-slate-300">
                <table class="w-full min-w-[800px]">
                <thead>
                    <tr>
                        <th style="width: 70px;">Date</th>
                        <th style="width: 70px;">N° Pièce</th>
                        <th>Libellé des opérations</th>
                        <th style="width: 90px; text-align: right;">Débit</th>
                        <th style="width: 90px; text-align: right;">Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningSolde = 0; @endphp
                    @foreach($account->entryLines as $line)
                        @php $runningSolde += ($line->debit - $line->credit); @endphp
                        <tr>
                            <td class="text-center">{{ \Carbon\Carbon::parse($line->entry->date)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ str_replace('PC-', '', $line->entry->numero_piece) }}</td>
                            <td class="italic">{{ $line->libelle }}</td>
                            <td class="text-right font-bold">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}</td>
                            <td class="text-right font-bold">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold">
                        <td colspan="3" class="text-right uppercase">Totaux mouvements</td>
                        <td class="text-right">{{ number_format($account->entryLines->sum('debit'), 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($account->entryLines->sum('credit'), 2, ',', ' ') }}</td>
                    </tr>
                    <tr class="font-black bg-white">
                        <td colspan="3" class="text-right uppercase">Solde Net {{ $runningSolde >= 0 ? 'débité' : 'crédité' }}</td>
                        <td colspan="2" class="text-right">
                           <span class="{{ $runningSolde >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ number_format(abs($runningSolde), 2, ',', ' ') }}
                           </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    @endforeach
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
