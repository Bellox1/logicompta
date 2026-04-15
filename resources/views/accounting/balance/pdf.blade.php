<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Balance Générale - {{ $user->entreprise->name ?? 'COMPTAFIQ' }}</title>
    <!-- Font: Arial -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Arial', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0062cc',
                        'primary-light': '#005599'
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

        body {
            font-family: Arial, sans-serif;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 8px;
            border: 1px solid #0062cc;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 4px;
        }

        th {
            background: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-row {
            font-weight: bold;
            background: #f9fafb;
            font-style: italic;
        }

        .grand-total {
            font-weight: 900;
            background: #eeeeee;
            color: #0062cc;
            text-transform: uppercase;
        }
    </style>
</head>

<body class="bg-white p-4 md:p-8">
    <div
        class="no-print mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-200">
        <p class="text-sm text-slate-600 font-medium italic">Vérifiez l'aperçu avant d'enregistrer en PDF.</p>
        <div class="flex flex-wrap gap-4">
            <button onclick="window.close()"
                class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-900 border border-slate-300 rounded-xl hover:bg-white transition-all">Fermer</button>
            <button onclick="window.print()"
                class="px-8 py-2.5 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-primary-light transition-all flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Imprimer / Enregistrer PDF
            </button>
        </div>
    </div>

    <div class="mb-8 border-b-2 border-slate-900 pb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">BALANCE GÉNÉRALE DES COMPTES</h1>
            <p class="text-sm font-bold text-slate-500 italic uppercase">{{ $user->entreprise->name ?? 'MA SOCIETE' }}
            </p>
        </div>
        <div class="text-right text-xs font-medium text-slate-400">
            Édité le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <div class="overflow-x-auto shadow-sm rounded-xl border border-slate-200">
        <table class="w-full min-w-[900px]">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 60px;">COMPTE</th>
                    <th rowspan="2">INTITULÉ</th>
                    <th colspan="2">MOUVEMENTS PÉRIODE</th>
                    <th colspan="2">SOLDES FIN PÉRIODE</th>
                </tr>
                <tr>
                    <th style="width: 80px;">DÉBIT</th>
                    <th style="width: 80px;">CRÉDIT</th>
                    <th style="width: 80px;">DÉBIT</th>
                    <th style="width: 80px;">CRÉDIT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($balanceData as $classCode => $class)
                    @foreach ($class['groups'] as $prefix => $group)
                        @foreach ($group['accounts'] as $acc)
                            <tr>
                                <td class="text-center font-bold">{{ $acc['code'] }}</td>
                                <td class="uppercase">{{ $acc['libelle'] }}</td>
                                <td class="text-right">{{ number_format($acc['mouv_debit'], 2, ',', ' ') }}</td>
                                <td class="text-right">{{ number_format($acc['mouv_credit'], 2, ',', ' ') }}</td>
                                <td class="text-right font-bold">
                                    {{ $acc['fin_debit'] > 0 ? number_format($acc['fin_debit'], 2, ',', ' ') : '-' }}
                                </td>
                                <td class="text-right font-bold">
                                    {{ $acc['fin_credit'] > 0 ? number_format($acc['fin_credit'], 2, ',', ' ') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2" class="text-right">Total Groupe {{ $prefix }}</td>
                            <td class="text-right">
                                {{ number_format($group['group_totals']['mouv_debit'], 2, ',', ' ') }}</td>
                            <td class="text-right">
                                {{ number_format($group['group_totals']['mouv_credit'], 2, ',', ' ') }}</td>
                            <td class="text-right">
                                {{ number_format($group['group_totals']['fin_debit'], 2, ',', ' ') }}</td>
                            <td class="text-right">
                                {{ number_format($group['group_totals']['fin_credit'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    <tr class="grand-total italic opacity-80 border-t-2 border-slate-400">
                        <td colspan="2" class="text-right">{{ $class['label'] }}</td>
                        <td class="text-right">{{ number_format($class['class_totals']['mouv_debit'], 2, ',', ' ') }}
                        </td>
                        <td class="text-right">{{ number_format($class['class_totals']['mouv_credit'], 2, ',', ' ') }}
                        </td>
                        <td class="text-right">{{ number_format($class['class_totals']['fin_debit'], 2, ',', ' ') }}
                        </td>
                        <td class="text-right">{{ number_format($class['class_totals']['fin_credit'], 2, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total bg-slate-200">
                    <td colspan="2" class="text-right text-sm">TOTAUX GÉNÉRAUX</td>
                    <td class="text-right text-sm whitespace-nowrap">
                        {{ number_format($grandTotal['mouv_debit'], 2, ',', ' ') }}</td>
                    <td class="text-right text-sm whitespace-nowrap">
                        {{ number_format($grandTotal['mouv_credit'], 2, ',', ' ') }}</td>
                    <td class="text-right text-sm whitespace-nowrap">
                        {{ number_format($grandTotal['fin_debit'], 2, ',', ' ') }}</td>
                    <td class="text-right text-sm whitespace-nowrap">
                        {{ number_format($grandTotal['fin_credit'], 2, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
