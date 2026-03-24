<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pièce Comptable {{ str_replace('PC-', '', $entry->numero_piece) }} - {{ $user->entreprise->name ?? 'Logicompta' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#003366',
                        'primary-light': '#0055aa',
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
            }

            @page {
                margin: 1cm;
            }
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
        }

        th,
        td {
            border: 1px dotted #ccc;
            padding: 8px 12px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-row {
            background-color: #f9fafb;
            font-weight: 900;
        }
    </style>
</head>

<body class="bg-white p-4 md:p-8">
    <div class="no-print mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
        <p class="text-sm text-gray-600 font-medium italic">Aperçu de la pièce comptable avant impression.</p>
        <div class="flex flex-wrap gap-4">
            <button onclick="window.close()"
                class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 border border-gray-300 rounded-xl hover:bg-white transition-all">Fermer</button>
            <button onclick="window.print()"
                class="px-8 py-2.5 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-primary-light transition-all flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Imprimer en PDF
            </button>
        </div>
    </div>

    <div class="mb-10 border-b-2 border-gray-900 pb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div>
            <span class="inline-block px-3 py-1 bg-primary text-white text-[10px] font-black rounded-lg uppercase tracking-widest mb-3">Pièce Comptable Officielle</span>
            <h1 class="text-4xl font-black uppercase tracking-tighter text-gray-900">N° {{ str_replace('PC-', '', $entry->numero_piece) }}</h1>
            <p class="text-lg font-bold text-gray-500 italic uppercase mt-1">{{ $user->entreprise->name ?? 'MA SOCIETE' }}</p>
        </div>
        <div class="text-right space-y-1">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date de l'opération</div>
            <div class="text-xl font-black text-primary">{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
            <span class="block text-[10px] uppercase font-bold text-primary mb-2 tracking-widest">Libellé de l'opération</span>
            <p class="text-base font-semibold text-gray-800 italic leading-relaxed">"{{ $entry->libelle }}"</p>
        </div>
        <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 flex flex-col justify-center">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-widest">Journal</span>
                <span class="text-sm font-black text-gray-900 uppercase">[{{ $entry->journal->code }}] {{ $entry->journal->name }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-widest">Statut</span>
                <span class="text-[10px] font-black text-green-600 uppercase bg-green-100 px-2 py-1 rounded">Équilibrée & Validée</span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto shadow-sm rounded-2xl border border-gray-100">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr>
                    <th style="width: 120px;">CODE COMPTE</th>
                    <th>INTITULÉ / LIBELLÉ</th>
                    <th style="width: 150px; text-align: right;">DÉBIT</th>
                    <th style="width: 150px; text-align: right;">CRÉDIT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entry->lines as $line)
                    <tr>
                        <td class="font-bold text-primary text-center tracking-widest">{{ $line->account->code_compte }}</td>
                        <td>
                            <div class="font-bold text-gray-900 uppercase text-xs">{{ $line->account->libelle }}</div>
                            @if($line->libelle && $line->libelle != $entry->libelle)
                                <div class="text-[10px] text-gray-500 italic mt-0.5">{{ $line->libelle }}</div>
                            @endif
                        </td>
                        <td class="text-right font-black text-gray-800">
                            {{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}</td>
                        <td class="text-right font-black text-gray-800">
                            {{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-right uppercase tracking-widest text-xs opacity-60">Totaux Pièce Comptable</td>
                    <td class="text-right text-base text-primary">{{ number_format($entry->lines->sum('debit'), 2, ',', ' ') }}</td>
                    <td class="text-right text-base text-primary">{{ number_format($entry->lines->sum('credit'), 2, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-12 pt-8 border-t border-gray-100 flex justify-between items-center italic">
        <div class="text-[9px] text-gray-400 uppercase tracking-widest">
            Généré par Logicompta le {{ now()->format('d/m/Y à H:i') }}
        </div>
        <div class="text-[10px] text-gray-300 font-bold uppercase p-4 border border-gray-100 rounded-xl">
            Cachet & Signature
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
