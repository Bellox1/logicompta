<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Compte de Résultat - {{ $user->entreprise->name ?? 'COMPTAFIQ' }}</title>
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
                        primary: '#0062cc'
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
            font-size: 9px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 6px;
        }

        th {
            background: #f8fafc;
            text-align: left;
            text-transform: uppercase;
            font-weight: bold;
        }

        .group-total {
            font-weight: bold;
            background: #fafafa;
            font-style: italic;
            color: #555;
        }

        .section-header {
            background: #0062cc;
            color: white !important;
            -webkit-print-color-adjust: exact;
            padding: 8px 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
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
            <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">COMPTE DE RÉSULTAT</h1>
            <p class="text-sm font-bold text-slate-500 italic uppercase">{{ $user->entreprise->name ?? 'MA SOCIETE' }}
            </p>
        </div>
        <div class="text-right text-xs font-medium text-slate-400">
            Édité le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <div class="flex flex-col xl:flex-row gap-6 mt-4">
        <!-- CHARGES -->
        <div class="flex-1 overflow-x-auto shadow-sm rounded-xl border border-slate-100 p-0 overflow-hidden">
            <div class="section-header">CHARGE (EXPLOITATION & FINANCIÈRES)</div>
            <table class="w-full mb-0 min-w-[500px]">
                <thead>
                    <tr>
                        <th>COMPTE</th>
                        <th>INTITULÉ</th>
                        <th style="width: 80px; text-align: right;">MONTANT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($charges['groups'] as $group)
                        @foreach ($group['accounts'] as $acc)
                            <tr>
                                <td class="font-bold">{{ $acc['code'] }}</td>
                                <td>{{ $acc['libelle'] }}</td>
                                <td class="text-right font-bold">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <tr class="group-total">
                            <td colspan="2">Sous-total {{ $group['prefix'] }}</td>
                            <td class="text-right">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-400">
                        <td colspan="2" class="text-right uppercase">TOTAL DES CHARGES</td>
                        <td class="text-right text-sm">{{ number_format($charges['total'], 2, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- PRODUITS -->
        <div
            class="flex-1 overflow-x-auto shadow-sm rounded-xl border border-slate-100 p-0 overflow-hidden mt-6 xl:mt-0">
            <div class="section-header">PRODUIT (VENTES & REVENUS)</div>
            <table class="w-full mb-0 min-w-[500px]">
                <thead>
                    <tr>
                        <th>COMPTE</th>
                        <th>INTITULÉ</th>
                        <th style="width: 80px; text-align: right;">MONTANT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($produits['groups'] as $group)
                        @foreach ($group['accounts'] as $acc)
                            <tr>
                                <td class="font-bold">{{ $acc['code'] }}</td>
                                <td>{{ $acc['libelle'] }}</td>
                                <td class="text-right font-bold">{{ number_format($acc['montant'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        <tr class="group-total">
                            <td colspan="2">Sous-total {{ $group['prefix'] }}</td>
                            <td class="text-right">{{ number_format($group['total'], 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-400">
                        <td colspan="2" class="text-right uppercase">TOTAL DES PRODUITS</td>
                        <td class="text-right text-sm">{{ number_format($produits['total'], 2, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-8 border-4 border-primary p-10 text-center rounded-[2rem]">
        <h2 class="text-xs font-black uppercase tracking-widest text-primary mb-4">RÉSULTAT NET DE L'EXERCICE</h2>
        <div class="text-5xl font-black {{ $profit >= 0 ? 'text-green-700' : 'text-red-700' }}">
            {{ number_format(abs($profit), 2, ',', ' ') }} F
        </div>
        <div
            class="mt-4 inline-block px-6 py-2 border-2 {{ $profit >= 0 ? 'bg-green-100 border-green-200 text-green-700' : 'bg-red-100 border-red-200 text-red-700' }} rounded-xl font-bold uppercase text-[10px] tracking-widest">
            {{ $profit >= 0 ? 'BÉNÉFICE RÉALISÉ' : 'DÉFICIT CONSTATÉ' }}
        </div>
    </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
