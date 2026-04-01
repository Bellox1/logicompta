@extends('layouts.accounting')

@section('title', 'Archives Comptables')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800">Archives des Exercices</h1>
    <p class="text-slate-500">Consultez l'historique de vos journaux, bilans et balances archivés par année.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($archivedYears as $data)
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                <div>
                    <h2 class="text-xl font-black tracking-tight leading-none uppercase">EXERCICE {{ $data->year }}</h2>
                    <span class="text-[10px] font-bold text-white/60 tracking-widest mt-1 inline-block">{{ $data->total }} ÉCRITURES SCELLÉES</span>
                </div>
                <span class="bg-white/20 px-2 py-1 rounded text-[10px] font-bold">ARCHIVÉ</span>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-500 mb-6 font-medium">
                    Toutes les données de l'année {{ $data->year }} ont été scellées. Vous pouvez consulter les rapports et exports correspondants.
                </p>
                <a href="{{ route('accounting.archive.show', $data->year) }}" 
                   class="flex items-center justify-center gap-2 w-full py-3 bg-slate-50 hover:bg-primary hover:text-white text-slate-800 font-bold rounded-lg transition-all text-sm border border-slate-100 hover:border-primary">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    OUVRIR LES ARCHIVES
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-slate-50 border border-slate-100 rounded-none p-16 text-center shadow-inner">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-full shadow-sm mb-6 border border-slate-100">
                <i data-lucide="archive" class="w-10 h-10 text-primary opacity-40"></i>
            </div>
            <h2 class="text-xl font-black text-slate-800 mb-2 uppercase tracking-tight">Aucune archive disponible</h2>
            <p class="text-slate-400 max-w-sm mx-auto text-sm font-medium">
                L'archivage automatique n'a pas encore eu lieu ou aucune écriture n'a été marquée pour les années précédentes.
            </p>
        </div>
    @endforelse
</div>
@endsection
