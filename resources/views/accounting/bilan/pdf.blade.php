<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan Financier - {{ $user->entreprise->name ?? 'Logicompta' }}</title>
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
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: white !important; }
            @page { margin: 1cm; }
        }
        body { font-family: 'Inter', sans-serif; font-size: 10px; }
        table { border-collapse: collapse; width: 100%; border: 2px solid #005b82; }
        th, td { border: 1px solid #94a3b8; padding: 6px; }
        th { background: #f8fafc; text-transform: uppercase; }
        .total-row { background: #eee; font-weight: bold; font-family: 'Inter', sans-serif; }
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

    <div class="mb-8 border-b-2 border-primary pb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tighter text-primary">BILAN COMPTABLE GÉNÉRAL</h1>
            <p class="text-sm font-bold text-slate-500 italic uppercase">{{ $user->entreprise->name ?? 'MA SOCIETE' }}</p>
        </div>
        <div class="text-right text-xs font-medium text-slate-400">
            Édité le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <div class="flex flex-col xl:flex-row gap-6 mt-4">
        <!-- ACTIF -->
        <div class="flex-1 overflow-x-auto shadow-sm rounded-xl border border-slate-200">
            <table class="w-full min-w-[500px]">
            <thead>
                <tr><th colspan="2" class="text-lg py-3 bg-primary text-white text-left font-black tracking-widest">ACTIF (IMMOBILISATIONS & DISPONIBILITÉS)</th></tr>
                <tr><th>POSTES</th><th style="width: 100px; text-align: right;">MONTANT NET</th></tr>
            </thead>
            <tbody>
                @foreach($actif as $item)
                    <tr><td>{{ $item['libelle'] }}</td><td class="text-right font-bold">{{ number_format($item['solde'], 2, ',', ' ') }}</td></tr>
                @endforeach
                @for($i = 0; $i < max(0, $passif->count() - $actif->count()); $i++)
                    <tr><td class="py-3">&nbsp;</td><td>&nbsp;</td></tr>
                @endfor
            </tbody>
            <tfoot>
                <tr class="total-row"><td class="text-right uppercase">TOTAL GÉNÉRAL DE L'ACTIF</td><td class="text-right text-lg">{{ number_format($actif->sum('solde'), 2, ',', ' ') }}</td></tr>
            </tfoot>
        </div>
        
        <!-- PASSIF -->
        <div class="flex-1 overflow-x-auto shadow-sm rounded-xl border border-slate-200 mt-6 xl:mt-0">
            <table class="w-full min-w-[500px]">
            <thead>
                <tr><th colspan="2" class="text-lg py-3 bg-primary text-white text-left font-black tracking-widest">PASSIF (CAPITAUX PROPRES & DETTES)</th></tr>
                <tr><th>POSTES</th><th style="width: 100px; text-align: right;">VALEURS</th></tr>
            </thead>
            <tbody>
                @foreach($passif as $item)
                    <tr>
                        <td class="{{ isset($item['is_resultat']) ? 'italic text-primary font-black' : '' }}">{{ $item['libelle'] }}</td>
                        <td class="text-right font-bold {{ isset($item['is_resultat']) ? ($item['solde'] >= 0 ? 'text-green-700' : 'text-red-700') : '' }}">
                            {{ number_format($item['solde'], 2, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
                @for($i = 0; $i < max(0, $actif->count() - $passif->count()); $i++)
                    <tr><td class="py-3">&nbsp;</td><td>&nbsp;</td></tr>
                @endfor
            </tbody>
            <tfoot>
                <tr class="total-row"><td class="text-right uppercase">TOTAL GÉNÉRAL DU PASSIF</td><td class="text-right text-lg">{{ number_format($passif->sum('solde'), 2, ',', ' ') }}</td></tr>
            </tfoot>
        </table>
    </div>
    </div>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    @php $diff = abs($actif->sum('solde') - $passif->sum('solde')); @endphp
    @if($diff > 0.01)
        <div class="mt-6 text-center text-red-600 font-bold border-4 border-red-600 p-4 rounded-xl uppercase tracking-widest no-print">
            DÉSÉQUILIBRE CONSTATÉ : {{ number_format($diff, 2, ',', ' ') }} F - VÉRIFIEZ VOS ÉCRITURES !
        </div>
    @endif
</body>
</html>
