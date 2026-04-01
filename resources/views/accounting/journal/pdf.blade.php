<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Journal Comptable - {{ $user->entreprise->name ?? 'Logicompta' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: {
                        primary: '#005b82',
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

        body {
            font-family: 'Inter', sans-serif;
            background: white;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 4px;
        }

        th {
            background-color: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
        }

        .entry-border {
            border-bottom: 2px solid #555 !important;
        }
    </style>
</head>

<body class="p-8">
    <div class="no-print mb-6 flex justify-between items-center bg-slate-50 p-6 rounded-2xl border border-slate-200">
        <p class="text-sm text-slate-600 font-medium italic">Vérifiez l'aperçu avant d'enregistrer en PDF.</p>
        <div class="flex gap-4">
            <button onclick="window.close()"
                class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-900 border border-slate-300 rounded-xl hover:bg-white transition-all">Fermer</button>
            <button onclick="window.print()"
                class="px-8 py-2.5 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-primary-light transition-all flex items-center justify-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Imprimer / Enregistrer PDF
            </button>
        </div>
    </div>

    <div class="mb-8 border-b-2 border-slate-900 pb-6 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">JOURNAL COMPTABLE GÉNÉRAL</h1>
            <p class="text-sm font-bold text-slate-500 italic uppercase">{{ $user->entreprise->name ?? 'MA SOCIETE' }}</p>
        </div>
        <div class="text-right text-xs font-medium text-slate-400">
            Édité le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <table class="w-full shadow-sm rounded-xl overflow-hidden border border-slate-200">
        <thead>
            <tr>
                <th style="width: 80px;">DATE</th>
                <th style="width: 80px;">N° Pièce</th>
                <th style="width: 90px;">COMPTE</th>
                <th>INTITULÉ / LIBELLÉ</th>
                <th style="width: 100px; text-align: right;">DÉBIT</th>
                <th style="width: 100px; text-align: right;">CRÉDIT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                @foreach ($entry->lines as $index => $line)
                    <tr class="{{ $loop->last ? 'entry-border' : '' }}">
                        @if ($index === 0)
                            <td rowspan="{{ $entry->lines->count() }}" class="text-center font-bold">
                                {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                            </td>
                            <td rowspan="{{ $entry->lines->count() }}" class="text-center font-black text-slate-900">
                                {{ str_replace('PC-', '', $entry->numero_piece) }}
                            </td>
                        @endif
                        <td class="font-bold text-slate-700">{{ $line->sousCompte->numero_sous_compte }}</td>
                        <td>
                            <div class="font-bold text-slate-900">{{ $line->sousCompte->libelle }}</div>
                            <div class="text-[10px] text-slate-500 italic">{{ $line->libelle ?: $entry->libelle }}</div>
                        </td>
                        <td class="text-right font-bold">
                            {{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '-' }}</td>
                        <td class="text-right font-bold">
                            {{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10 italic">Aucune donnée à afficher.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-10 text-center text-[10px] text-slate-400 uppercase tracking-widest italic no-print opacity-50">
        Document généré par Logicompta - Système de gestion comptable
    </div>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>