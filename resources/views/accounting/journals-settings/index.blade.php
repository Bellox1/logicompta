@extends('layouts.accounting')

@section('title', 'Gestion des Journaux')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Gestion des Journaux</h1>
        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">Configurez vos différents journaux comptables</p>
    </div>
    <div class="flex items-center gap-4">
        <a href="{{ route('accounting.journal.create') }}" class="px-6 py-3 bg-white border border-border text-slate-700 font-bold rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm uppercase tracking-widest text-xs">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour Saisie
        </a>
        <a href="{{ route('accounting.journals-settings.create') }}" class="px-6 py-3 bg-primary text-white font-bold rounded-2xl hover:bg-primary-light transition-all flex items-center gap-2 shadow-lg uppercase tracking-widest text-xs">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Nouveau Journal
        </a>
    </div>
</div>

<div class="bg-card-bg border border-border rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-border">
            <tr>
                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-400">Nom du Journal</th>
                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-400">Description</th>
                <th class="px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-400 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($journals as $journal)
            <tr class="hover:bg-slate-50/50 transition-all">
                <td class="px-8 py-6 font-bold text-slate-900 border-r border-slate-100">{{ $journal->name }}</td>
                <td class="px-8 py-6 text-sm text-slate-500">{{ $journal->description ?: 'Aucune description' }}</td>
                <td class="px-8 py-6">
                    <div class="flex justify-center items-center gap-4">
                        <a href="{{ route('accounting.journals-settings.edit', $journal->id) }}" class="p-2 text-slate-400 hover:text-primary transition-colors hover:bg-slate-100 rounded-xl" title="Modifier">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </a>
                        <form id="delete-form-{{ $journal->id }}" action="{{ route('accounting.journals-settings.destroy', $journal->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                onclick="Swal.fire({
                                    title: 'Supprimer ce journal ?',
                                    text: 'Cette action est irréversible.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#005b82',
                                    cancelButtonColor: '#94a3b8',
                                    confirmButtonText: 'Oui, supprimer',
                                    cancelButtonText: 'Annuler'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        document.getElementById('delete-form-{{ $journal->id }}').submit();
                                    }
                                })"
                                class="p-2 text-slate-400 hover:text-red-500 transition-colors hover:bg-slate-100 rounded-xl" title="Supprimer">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-8 py-20 text-center">
                    <i data-lucide="book-x" class="mx-auto w-12 h-12 text-slate-200 mb-4 opacity-50"></i>
                    <p class="text-slate-400 font-medium">Aucun journal configuré.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
