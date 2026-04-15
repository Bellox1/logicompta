@extends('layouts.accounting')

@section('title', 'Modifier le Journal')

@section('styles')
    <style>
        .premium-card {
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            padding: 40px;
        }

        .form-label-premium {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 10px;
            display: block;
        }

        .form-control-premium {
            height: 55px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            padding: 0 20px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control-premium:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 91, 130, 0.05);
        }

        .textarea-premium {
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .textarea-premium:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 91, 130, 0.05);
        }
    </style>
@endsection

@section('content')
    <div class="max-w-800 mx-auto" style="max-width: 800px;">
        <div class="mb-5 d-flex align-items-center">
            <a href="{{ route('accounting.journals-settings.index') }}" class="btn btn-light rounded-circle p-2 mr-3 border shadow-sm d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                <span class="material-symbols-outlined text-dark">arrow_back</span>
            </a>
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Modifier le Journal</h1>
                <p class="text-muted small font-weight-bold uppercase tracking-wider">Mise à jour de : {{ $journal->name }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-xl py-3 px-4 mb-4">
                <ul class="mb-0 small font-weight-bold">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="premium-card">
            <form action="{{ route('accounting.journals-settings.update', $journal->id) }}" method="POST">
                @csrf
                <div class="form-group mb-4">
                    <label class="form-label-premium">Nom du journal</label>
                    <input type="text" name="name" value="{{ old('name', $journal->name) }}" class="form-control form-control-premium" required>
                </div>

                <div class="form-group mb-5">
                    <label class="form-label-premium">Description (Optionnel)</label>
                    <textarea name="description" placeholder="Ex: Opérations en espèces uniquement..." rows="4" class="form-control textarea-premium">{{ old('description', $journal->description) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-3 border-top pt-4">
                    <a href="{{ route('accounting.journals-settings.index') }}" class="btn btn-light px-4 py-2 font-weight-bold text-uppercase rounded-lg mr-2" style="font-size: 11px; letter-spacing: 1px;">Annuler</a>
                    <button type="submit" class="btn btn-primary px-5 py-3 font-weight-bold text-uppercase rounded-lg shadow-lg" style="font-size: 11px; letter-spacing: 1px;">
                        Mettre à jour le Journal
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
