@extends('layouts.accounting')

@section('title', 'Traçabilité & Archives Critique')

@section('content')
<div class="animate-fade-up">
    {{-- Header --}}
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-text-main uppercase tracking-tight italic">Boîte Noire & Traçabilité</h1>
            <p class="text-sm text-text-secondary mt-1 font-bold italic opacity-70">Historique tamper-evident des actions critiques</p>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="flex items-center gap-4">
            <form action="{{ route('accounting.traceabilite.clear_all') }}" method="POST" onsubmit="return confirm('VOULEZ-VOUS VRAIMENT VIDER TOUT L\'HISTORIQUE ? Cette action est irréversible.')">
                @csrf @method('DELETE')
                <button type="submit" class="px-6 py-3 bg-rose-500/10 text-rose-600 hover:bg-rose-500 hover:text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all shadow-sm border border-rose-500/20">
                    Vider tout l'historique
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Search Bar --}}
    <div class="mb-8 bg-card-bg border border-border p-4 rounded-3xl shadow-sm">
        <form action="{{ route('accounting.traceabilite.index') }}" method="GET" class="flex items-center gap-4">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par action, type de donnée ou ID..." 
                    class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3 pl-12 pr-4 text-sm font-bold focus:border-primary outline-none transition-all">
            </div>
            <button type="submit" class="px-8 py-3 bg-primary text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:opacity-90 transition-all shadow-lg shadow-primary/20">
                Filtrer
            </button>
            @if($search)
                <a href="{{ route('accounting.traceabilite.index') }}" class="p-3 text-slate-400 hover:text-rose-500 transition-colors">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Table Content --}}
    <div class="bg-card-bg border border-border rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 text-[9px] uppercase text-slate-400 font-black tracking-widest border-b border-border">
                    <tr>
                        <th class="px-6 py-5">Horodatage</th>
                        <th class="px-6 py-5">Opérateur</th>
                        <th class="px-6 py-5 text-center">Nature</th>
                        <th class="px-6 py-5">Entité</th>
                        <th class="px-6 py-5">Détails</th>
                        <th class="px-6 py-5 text-right">Contrôles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-[12px]">
                    @if(count($logs) > 0)
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/30 transition-colors group">
                                <td class="px-6 py-5 font-mono text-slate-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y') }}
                                    <span class="block text-[10px] opacity-50">{{ $log->created_at->format('H:i:s') }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center font-black text-[10px]">
                                            {{ substr($log->user?->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="font-black text-text-main italic">{{ $log->user?->name ?? 'Système' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($log->action === 'DELETE')
                                        <span class="px-3 py-1 bg-rose-500 text-white rounded-lg text-[9px] font-black uppercase tracking-tighter">Suppression</span>
                                    @elseif($log->action === 'RESTORE')
                                        <span class="px-3 py-1 bg-emerald-500 text-white rounded-lg text-[9px] font-black uppercase tracking-tighter">Restauration</span>
                                    @else
                                        <span class="px-3 py-1 bg-slate-800 text-white rounded-lg text-[9px] font-black uppercase tracking-tighter">{{ $log->action }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="font-bold text-slate-600 italic uppercase tracking-tight">{{ class_basename($log->model_type) }}</span>
                                    <span class="text-[10px] text-slate-300 ml-1">#{{ $log->model_id }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <button onclick='showDetails(@json($log->details))' class="p-2 text-primary hover:bg-primary/5 rounded-xl transition-all">
                                        <i data-lucide="database" class="w-4 h-4"></i>
                                    </button>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($log->action === 'DELETE')
                                            <form action="{{ route('accounting.traceabilite.restore', $log->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" title="Restaurer">
                                                    <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(Auth::user()->role === 'admin')
                                            <form action="{{ route('accounting.traceabilite.force_delete', $log->id) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce log ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all opacity-0 group-hover:opacity-100" title="Supprimer le log">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="bg-slate-50 w-24 h-24 rounded-[2rem] flex items-center justify-center mx-auto mb-6 rotate-12">
                                    <i data-lucide="shield" class="w-12 h-12 text-slate-200"></i>
                                </div>
                                <h3 class="text-xl font-black text-slate-300 uppercase tracking-[0.2em]">Sain et Sauf</h3>
                                <p class="text-xs text-slate-400 mt-2 font-bold italic italic uppercase">Aucune anomalie ou action critique détectée.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-8 py-6 border-t border-border bg-slate-50/20">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Detail Modal --}}
<div id="detailModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[9999] hidden items-center justify-center p-4">
    <div class="bg-card-bg rounded-[2.5rem] shadow-2xl max-w-3xl w-full relative overflow-hidden animate-in fade-in zoom-in duration-300 border border-border">
        <div class="p-10 border-b border-border flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-2xl font-black text-text-main uppercase tracking-widest italic">Data Snapshot</h3>
                <p class="text-[10px] text-text-secondary font-bold uppercase tracking-widest mt-1 opacity-50 italic">Contenu de l'entité au moment de l'action</p>
            </div>
            <button onclick="closeModal()" class="w-12 h-12 flex items-center justify-center bg-white border border-border rounded-2xl text-text-secondary hover:text-rose-500 transition-all shadow-sm">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-10 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <pre id="jsonContent" class="text-[12px] font-mono text-emerald-950 bg-emerald-50/50 p-8 rounded-3xl border border-emerald-100 whitespace-pre-wrap break-all shadow-inner"></pre>
        </div>
        <div class="p-10 border-t border-border flex justify-end bg-slate-50/30">
            <button onclick="closeModal()" class="px-10 py-4 bg-slate-900 text-white font-black rounded-2xl uppercase text-[10px] tracking-widest hover:bg-black transition-all shadow-xl shadow-slate-900/20">Fermer la console</button>
        </div>
    </div>
</div>

<script>
    function showDetails(data) {
        document.getElementById('jsonContent').textContent = JSON.stringify(data, null, 4);
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModal').classList.remove('flex');
    }
</script>
@endsection
