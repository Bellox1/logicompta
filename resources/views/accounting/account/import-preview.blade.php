@extends('layouts.accounting')

@section('title', 'Aperçu de l\'import')

@section('content')
<div class="px-6 sm:px-12 py-10 w-full max-w-[1600px] mx-auto min-h-screen flex flex-col">
    <!-- HEADER -->
    <div class="mb-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-8 animate-in fade-in slide-in-from-top duration-700">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 uppercase tracking-tight">Aperçu des données</h1>
            <div class="flex items-center gap-3 mt-4">
                <span class="h-[2px] w-12 bg-primary"></span>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Vérifiez les comptes avant validation</p>
            </div>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('accounting.account.import') }}" class="group flex items-center gap-3 px-6 py-3 bg-white border border-gray-200 text-xs font-bold uppercase text-gray-500 hover:text-black hover:border-black transition-all tracking-widest italic">
                <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                Changer de fichier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <!-- TABLEAU APERÇU -->
        <div class="lg:col-span-9 bg-card-bg border border-border shadow-sm overflow-hidden animate-in fade-in slide-in-from-left duration-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-border text-[10px] uppercase font-bold text-gray-400 tracking-widest">
                            <th class="px-6 py-5 w-16">Ligne</th>
                            <th class="px-6 py-5">Numéro</th>
                            <th class="px-6 py-5">Libellé</th>
                            <th class="px-6 py-5">Compte Parent</th>
                            <th class="px-6 py-5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($previewData as $row)
                            @php $isError = str_starts_with($row['status'], 'error_'); @endphp
                            <tr class="transition-colors {{ $isError ? 'bg-red-50/50 dark:bg-red-900/10 hover:bg-red-100/50' : 'hover:bg-gray-50/50 dark:hover:bg-white/5' }}">
                                <td class="px-6 py-4 text-gray-400 font-mono">{{ $row['line'] }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white tracking-widest">{{ $row['numero'] }}</td>
                                <td class="px-6 py-4 font-medium text-gray-600 dark:text-gray-300">{{ $row['libelle'] }}</td>
                                <td class="px-6 py-4">
                                    @if($row['parent'])
                                        <span class="px-2 py-1 bg-primary/5 text-primary font-bold rounded-sm border border-primary/10">
                                            {{ $row['parent'] }}
                                        </span>
                                    @else
                                        <span class="text-red-500 italic opacity-50 text-[10px]">Non détecté</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @switch($row['status'])
                                        @case('new')
                                            <span class="flex items-center gap-2 text-green-600 font-bold uppercase text-[9px] tracking-widest">
                                                <i data-lucide="plus-circle" class="w-3 h-3"></i> Nouveau
                                            </span>
                                            @break
                                        @case('update')
                                            <span class="flex items-center gap-2 text-blue-600 font-bold uppercase text-[9px] tracking-widest">
                                                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Existant (MàJ)
                                            </span>
                                            @break
                                        @case('error_main')
                                            <span class="flex items-center gap-2 text-red-500 font-bold uppercase text-[9px] tracking-widest bg-red-50 px-2 py-1 rounded">
                                                <i data-lucide="alert-octagon" class="w-3 h-3"></i> Compte Principal
                                            </span>
                                            @break
                                        @case('error_no_parent')
                                            <span class="flex items-center gap-2 text-red-500 font-bold uppercase text-[9px] tracking-widest bg-red-50 px-2 py-1 rounded">
                                                <i data-lucide="alert-octagon" class="w-3 h-3"></i> Parent Inconnu
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

        <!-- ACTIONS -->
        <div class="lg:col-span-3 flex flex-col gap-6 animate-in fade-in slide-in-from-right duration-700">
            <div class="bg-primary/5 border border-primary/20 p-8 flex flex-col gap-8 sticky top-32">
                <h3 class="text-xs font-black uppercase text-primary tracking-[0.2em] border-b border-primary/10 pb-4">Résumé</h3>
                
                <div class="space-y-4">
                    @php
                        $newCount = collect($previewData)->where('status', 'new')->count();
                        $updateCount = collect($previewData)->where('status', 'update')->count();
                        $errorCount = collect($previewData)->whereIn('status', ['error_main', 'error_no_parent'])->count();
                    @endphp

                    <div class="flex justify-between items-center text-[11px] font-bold">
                        <span class="text-gray-400 uppercase tracking-widest">À créer</span>
                        <span class="text-green-600 bg-green-50 px-2 py-1">{{ $newCount }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] font-bold">
                        <span class="text-gray-400 uppercase tracking-widest">À mettre à jour</span>
                        <span class="text-blue-600 bg-blue-50 px-2 py-1">{{ $updateCount }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] font-bold">
                        <span class="text-gray-400 uppercase tracking-widest">Invalides</span>
                        <span class="text-red-600 bg-red-50 px-2 py-1">{{ $errorCount }}</span>
                    </div>
                </div>

                <div class="pt-6 border-t border-primary/10">
                    <form action="{{ route('accounting.account.import.process') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-4 bg-primary text-white font-black text-[11px] uppercase tracking-[0.2em] hover:bg-black transition-all flex items-center justify-center gap-3 shadow-xl">
                            Confirmer l'import
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    </form>
                    
                    @if($errorCount > 0)
                        <p class="mt-4 text-[10px] text-red-500 italic font-bold leading-relaxed text-center">
                            {{ $errorCount }} ligne(s) invalide(s) seront ignorées lors de l'importation.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
