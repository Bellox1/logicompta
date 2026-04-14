@extends('layouts.accounting')

@section('title', 'Modifier le Journal')

@section('content')
@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="mb-8 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-400 flex items-center gap-3 animate-fade-up relative overflow-hidden">
            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="flex-1 font-bold">{{ $error }}</span>
            <button onclick="this.parentElement.remove()"
                class="p-1 hover:bg-black/5 rounded-lg transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endforeach
@endif

<div class="max-w-[800px] mx-auto">
    <div class="mb-10 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('accounting.journals-settings.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
                </a>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Modifier le Journal</h1>
            </div>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Modification de l'entité : {{ $journal->name }}</p>
        </div>
    </div>

    <div class="bg-card-bg border border-border rounded-none shadow-sm overflow-hidden p-8">
        <form action="{{ route('accounting.journals-settings.update', $journal->id) }}" method="POST" class="space-y-10">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-900 mb-2 tracking-[0.2em] px-1">
                        Nom du journal
                    </label>
                    <input type="text" name="name" value="{{ old('name', $journal->name) }}" placeholder="Ex: Journal de Caisse" required
                        class="w-full bg-white border border-border px-6 py-4 text-sm font-bold text-slate-700 outline-none focus:border-primary transition-all rounded-xl shadow-sm">
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-900 mb-2 tracking-[0.2em] px-1">
                        Description (Optionnel)
                    </label>
                    <textarea name="description" placeholder="Ex: Opérations en espèces uniquement..." rows="4"
                        class="w-full bg-white border border-border px-6 py-4 text-sm font-bold text-slate-700 outline-none focus:border-primary transition-all rounded-xl shadow-sm resize-y">{{ old('description', $journal->description) }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex flex-col md:flex-row justify-end gap-3">
                <a href="{{ route('accounting.journals-settings.index') }}" class="w-full md:w-auto px-5 py-2.5 text-center bg-slate-100 text-slate-500 font-black text-xs rounded-xl uppercase tracking-[0.2em] hover:bg-slate-200 transition-all">
                    Annuler
                </a>
                <button type="submit" class="w-full md:w-auto px-5 py-2.5 bg-primary text-white font-black text-xs rounded-xl uppercase tracking-[0.2em] hover:bg-primary-light transition-all shadow-lg text-center">
                    Mettre à jour le Journal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
