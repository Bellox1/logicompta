@extends('layouts.accounting')

@section('title', 'Modifier le Journal')

@section('content')
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

    @if ($errors->any())
        <div class="mb-8 p-6 bg-red-50 border-l-4 border-red-500 rounded-r-xl animate-fade-in shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-red-500 text-white p-1 rounded-full"><i data-lucide="alert-circle" class="w-4 h-4"></i></div>
                <h3 class="text-sm font-black text-red-900 uppercase">Certaines informations sont manquantes</h3>
            </div>
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-xs text-red-600 font-bold italic">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-card-bg border border-border rounded-none shadow-sm overflow-hidden p-8">
        <form action="{{ route('accounting.journals-settings.update', $journal->id) }}" method="POST" class="space-y-10">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-[0.2em] px-1">
                        Nom du journal
                    </label>
                    <input type="text" name="name" value="{{ old('name', $journal->name) }}" placeholder="Ex: Journal de Caisse" required
                        class="w-full bg-white border border-border px-6 py-4 text-sm font-bold outline-none focus:border-primary transition-all rounded-xl shadow-sm">
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-[0.2em] px-1">
                        Description (Optionnel)
                    </label>
                    <textarea name="description" placeholder="Ex: Opérations en espèces uniquement..." rows="4"
                        class="w-full bg-white border border-border px-6 py-4 text-sm font-bold outline-none focus:border-primary transition-all rounded-xl shadow-sm resize-y">{{ old('description', $journal->description) }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end gap-4">
                <a href="{{ route('accounting.journals-settings.index') }}" class="px-8 py-4 bg-slate-100 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Annuler
                </a>
                <button type="submit" class="px-10 py-4 bg-primary text-white font-black text-xs uppercase tracking-[0.4em] hover:bg-primary-light transition-all shadow-xl">
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
