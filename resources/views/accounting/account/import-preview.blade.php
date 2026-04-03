@extends('layouts.accounting')

@section('title', 'Aperçu de l\'import')

@section('content')
<div class="px-6 sm:px-12 py-10 w-full max-w-[1600px] mx-auto min-h-screen flex flex-col">
    <!-- HEADER -->
    <div class="mb-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Aperçu de l'import</h1>
            <p class="text-sm text-slate-500 mt-1 uppercase font-bold tracking-widest">Vérifiez vos données avant validation</p>
        </div>
        <a href="{{ route('accounting.account.import') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all text-xs flex items-center gap-2 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Changer de fichier
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <!-- TABLEAU APERÇU -->
        <div class="lg:col-span-9">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-bold tracking-widest border-b border-slate-100">
                                <th class="px-6 py-4 w-16">#</th>
                                <th class="px-6 py-4">Numéro</th>
                                <th class="px-6 py-4">Libellé</th>
                                <th class="px-6 py-4">Compte Parent</th>
                                <th class="px-6 py-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($previewData as $row)
                                @php $isError = str_starts_with($row['status'], 'error_'); @endphp
                                <tr class="transition-colors @if($isError) bg-red-50 border-l-4 border-red-400 @else hover:bg-slate-50/50 @endif">
                                    <td class="px-6 py-4 text-slate-400 font-mono text-[10px]">{{ $row['line'] }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-800 tracking-widest">{{ $row['numero'] }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-600 text-sm">{{ $row['libelle'] }}</td>
                                    <td class="px-6 py-4">
                                        @if($row['parent'])
                                            <span class="px-3 py-1 bg-primary/5 text-primary text-[10px] font-bold rounded-lg border border-primary/10">
                                                {{ $row['parent'] }}
                                            </span>
                                        @else
                                            <span class="text-red-500 text-[10px] font-bold uppercase tracking-tighter">Non détecté</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @switch($row['status'])
                                            @case('new')
                                                <span class="flex items-center gap-2 text-green-600 font-bold uppercase text-[9px] tracking-widest">
                                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div> Nouveau
                                                </span>
                                                @break
                                            @case('update')
                                                <span class="flex items-center gap-2 text-primary font-bold uppercase text-[9px] tracking-widest">
                                                    <div class="w-1.5 h-1.5 bg-primary rounded-full"></div> Existant
                                                </span>
                                                @break
                                            @case('error_main')
                                                <span class="flex items-center gap-2 text-red-600 font-bold uppercase text-[9px] tracking-widest bg-red-100 px-2 py-1 rounded-lg">
                                                    <i data-lucide="alert-circle" class="w-3 h-3"></i> Compte Principal
                                                </span>
                                                @break
                                            @case('error_no_parent')
                                                <span class="flex items-center gap-2 text-red-600 font-bold uppercase text-[9px] tracking-widest bg-red-100 px-2 py-1 rounded-lg">
                                                    <i data-lucide="alert-circle" class="w-3 h-3"></i> Parent Inconnu
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="lg:col-span-3 flex flex-col gap-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-8 sticky top-8 shadow-sm flex flex-col gap-6">
                <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-4">Résumé de l'import</h3>
                
                <div class="space-y-4">
                    @php
                        $newCount = collect($previewData)->where('status', 'new')->count();
                        $updateCount = collect($previewData)->where('status', 'update')->count();
                        $errorCount = collect($previewData)->whereIn('status', ['error_main', 'error_no_parent'])->count();
                    @endphp

                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">À créer</span>
                        <span class="text-green-600 bg-green-50 px-3 py-1 rounded-lg font-bold text-xs">{{ $newCount }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">À mettre à jour</span>
                        <span class="text-primary bg-primary/5 px-3 py-1 rounded-lg font-bold text-xs">{{ $updateCount }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Invalides</span>
                        <span class="text-red-600 bg-red-50 px-3 py-1 rounded-lg font-bold text-xs">{{ $errorCount }}</span>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <form action="{{ route('accounting.account.import.process') }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="w-full py-4 bg-primary text-white font-bold rounded-xl hover:opacity-95 transition-all text-sm flex items-center justify-center gap-3 shadow-md">
                            Confirmer l'import
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    </form>
                    
                    @if($errorCount > 0)
                        <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                            <p class="text-[11px] text-red-600 font-semibold leading-relaxed text-center">
                                <i data-lucide="alert-circle" class="w-3 h-3 inline-block mr-1"></i>
                                {{ $errorCount }} ligne(s) invalide(s) seront ignorées.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
