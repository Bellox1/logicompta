@extends('layouts.accounting')

@section('title', 'Gestion des Journaux')

@section('styles')
    <style>
        .journals-table thead th {
            background: #f8fafc;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 20px 25px;
            border-bottom: 2px solid #f1f5f9;
        }

        .journals-table tbody td {
            padding: 20px 25px;
            vertical-align: middle;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .premium-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }

        .journal-badge {
            background: rgba(0, 91, 130, 0.05);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 12px;
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Gestion des Journaux</h1>
            <p class="text-muted small font-weight-bold uppercase tracking-wider">Configurez vos différents types de journaux comptables</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="{{ route('accounting.journal.create') }}" class="btn btn-white btn-sm px-4 py-2 font-weight-bold border rounded-lg shadow-sm mr-2">
                <span class="material-symbols-outlined align-middle mr-1" style="font-size: 18px;">arrow_back</span> RETOUR SAISIE
            </a>
            <a href="{{ route('accounting.journals-settings.create') }}" class="btn btn-primary btn-sm px-4 py-2 font-weight-bold rounded-lg shadow-sm">
                <span class="material-symbols-outlined align-middle mr-1" style="font-size: 18px;">add_circle</span> NOUVEAU JOURNAL
            </a>
        </div>
    </div>

    <div class="premium-card">
        <div class="table-responsive">
            <table class="table journals-table mb-0">
                <thead>
                    <tr>
                        <th>NOM DU JOURNAL</th>
                        <th>DESCRIPTION</th>
                        <th class="text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        <tr>
                            <td style="width: 300px;">
                                <div class="journal-badge">{{ $journal->name }}</div>
                            </td>
                            <td class="text-muted small">
                                {{ $journal->description ?: 'Aucune description fournie pour ce journal.' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('accounting.journals-settings.edit', $journal->id) }}" class="btn btn-link text-primary p-2">
                                        <span class="material-symbols-outlined" style="font-size: 22px;">edit_document</span>
                                    </a>
                                    <button type="button" 
                                        onclick="Swal.fire({
                                            title: 'Supprimer ce journal ?',
                                            text: 'Cette action effacera définitivement le journal.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#005b82',
                                            confirmButtonText: 'OUI, SUPPRIMER',
                                            cancelButtonText: 'ANNULER'
                                        }).then((result) => {
                                            if (result.isConfirmed) document.getElementById('delete-form-{{ $journal->id }}').submit();
                                        })"
                                        class="btn btn-link text-danger p-2">
                                        <span class="material-symbols-outlined" style="font-size: 22px;">delete</span>
                                    </button>
                                    <form id="delete-form-{{ $journal->id }}" action="{{ route('accounting.journals-settings.destroy', $journal->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <span class="material-symbols-outlined text-muted opacity-25 mb-3" style="font-size: 60px;">book_5</span>
                                <p class="small font-weight-bold text-muted">Aucun journal trouvé dans la base</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
