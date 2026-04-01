<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pièce Comptable {{ str_replace('PC-', '', $entry->numero_piece) }} -
        {{ $user->entreprise->name ?? 'Logicompta' }}</title>
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
                margin: 0.5cm;
                size: portrait;
            }
            
            table {
                min-width: 100% !important;
                width: 100% !important;
            }
        }

        body {
            font-family: sans-serif;
            background: white;
            -webkit-print-color-adjust: exact;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10px;
            table-layout: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            word-wrap: break-word;
        }

        th {
            background-color: #f3f4f6 !important;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-row {
            background-color: #f9fafb !important;
            font-weight: 900;
        }
    </style>
</head>

<body class="bg-white p-2 md:p-8">
    <div class="no-print mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-200">
        <p class="text-sm text-gray-600 font-medium italic text-center md:text-left">Aperçu avant impression. Sur téléphone, utilisez l'option "Enregistrer en PDF".</p>
        <div class="flex justify-center gap-4">
            <button onclick="window.close()"
                class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 border border-gray-300 rounded-xl hover:bg-white transition-all">Fermer</button>
            <button onclick="window.print()"
                class="px-8 py-2.5 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-primary-light transition-all flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Imprimer
            </button>
        </div>
    </div>

    <div class="w-full max-w-full overflow-hidden">
        <div class="mb-8 border-b-2 border-gray-900 pb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
            <div>
                <span class="inline-block px-2 py-0.5 bg-primary text-white text-[9px] font-black rounded uppercase tracking-widest mb-2">Pièce Comptable</span>
                <h1 class="text-3xl font-black uppercase tracking-tighter text-gray-900">N° {{ str_replace('PC-', '', $entry->numero_piece) }}</h1>
                <p class="text-base font-bold text-gray-500 italic uppercase mt-1">{{ $user->entreprise->name ?? 'MA SOCIETE' }}</p>
            </div>
            <div class="md:text-right">
                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Date</div>
                <div class="text-xl font-black text-primary">{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                <span class="block text-[9px] uppercase font-bold text-gray-400 mb-1 tracking-widest text-primary">Libellé</span>
                <p class="text-sm font-semibold text-gray-800 italic">"{{ $entry->libelle }}"</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 flex flex-col justify-center space-y-1">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-400 font-bold uppercase text-[9px]">Journal</span>
                    <span class="font-black text-gray-900 uppercase">{{ $entry->journal->name }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-400 font-bold uppercase text-[9px]">Statut</span>
                    <span class="font-black text-green-600 uppercase">Validée</span>
                </div>
            </div>
        </div>

        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr>
                        <th style="width: 15%;">Compte</th>
                        <th style="width: 55%;">Intitulé / Libellé</th>
                        <th style="width: 15%; text-align: right;">Débit</th>
                        <th style="width: 15%; text-align: right;">Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                        <tr>
                            <td class="font-bold text-primary text-center">{{ $line->sousCompte->numero_sous_compte }}</td>
                            <td>
                                <div class="font-bold text-gray-900 uppercase text-[10px]">{{ $line->sousCompte->libelle }}</div>
                                @if($line->libelle && $line->libelle != $entry->libelle)
                                    <div class="text-[9px] text-gray-500 italic">{{ $line->libelle }}</div>
                                @endif
                            </td>
                            <td class="text-right font-bold text-gray-800">
                                {{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}</td>
                            <td class="text-right font-bold text-gray-800">
                                {{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="text-right uppercase text-[9px] py-3 opacity-60">Totaux de l'écriture</td>
                        <td class="text-right text-sm text-primary font-black">
                            {{ number_format($entry->lines->sum('debit'), 2, ',', ' ') }}
                        </td>
                        <td class="text-right text-sm text-primary font-black">
                            {{ number_format($entry->lines->sum('credit'), 2, ',', ' ') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-10 pt-6 border-t border-gray-100 flex justify-between items-center italic">
            <div class="text-[8px] text-gray-400 uppercase tracking-widest">
                Généré par Logicompta le {{ now()->format('d/m/Y') }}
            </div>
            <div class="text-[9px] text-gray-300 font-bold uppercase p-3 border border-gray-100 rounded-lg">
                Cachet & Signature
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
