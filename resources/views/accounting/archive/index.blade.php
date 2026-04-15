@extends('layouts.accounting')

@section('title', 'Archives Comptables')

@section('styles')
    <style>
        .archive-hero {
            background: linear-gradient(135deg, #005b82 0%, #004a6b 100%);
            border-radius: 24px;
            padding: 40px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 91, 130, 0.15);
        }

        .archive-hero::after {
            content: 'inventory_2';
            font-family: 'Material Symbols Outlined';
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 200px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .archive-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .archive-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: #0062cc;
        }

        .archive-icon {
            width: 56px;
            height: 56px;
            background: #f8fafc;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0062cc;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .archive-card:hover .archive-icon {
            background: #0062cc;
            color: white;
            transform: scale(1.1);
        }

        .year-display {
            font-family: 'Manrope';
            font-weight: 800;
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .btn-archive {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .archive-card:hover .btn-archive {
            background: #0062cc;
            color: white;
        }

        .status-badge {
            background: #ecfdf5;
            color: #059669;
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endsection

@section('content')
<div class="archive-hero">
    <div class="position-relative" style="z-index: 2;">
        <h1 class="font-weight-bold mb-2" style="font-family: 'Manrope'; font-size: 32px;">Coffre-fort Numérique</h1>
        <p class="opacity-75 mb-0 font-weight-bold">Accédez en toute sécurité à vos archives comptables scellées par exercice.</p>
    </div>
</div>

<div class="row">
    @forelse($archivedYears as $data)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="archive-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="archive-icon">
                        <span class="material-symbols-outlined" style="font-size: 28px;">folder_zip</span>
                    </div>
                    <span class="status-badge">Exercice Clos</span>
                </div>
                
                <h2 class="year-display">{{ $data->year }}</h2>
                <div class="d-flex align-items-center text-muted small font-weight-bold mb-4">
                    <span class="material-symbols-outlined mr-1" style="font-size: 16px;">database</span>
                    {{ number_format($data->total, 0, ',', ' ') }} Écritures Scellées
                </div>

                <div class="flex-grow-1">
                    <p class="text-secondary small font-weight-bold italic mb-4">
                        Ce coffre contient le journal, la balance, le bilan et le compte de résultat de l'exercice {{ $data->year }}.
                    </p>
                </div>

                <a href="{{ route('accounting.archive.show', $data->year) }}" class="btn-archive text-decoration-none">
                    Ouvrir les archives <span class="material-symbols-outlined ml-2" style="font-size: 18px;">arrow_forward</span>
                </a>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 bg-white border-0 shadow-sm" style="border-radius: 32px;">
            <div class="mb-4">
                <span class="material-symbols-outlined" style="font-size: 80px; opacity: 0.05; color: #0062cc;">inventory_2</span>
            </div>
            <h4 class="font-weight-bold text-dark mb-2">Aucune archive disponible</h4>
            <p class="text-muted small px-5 font-weight-bold">Le système génère automatiquement des archives pour chaque année d'activité clôturée.</p>
        </div>
    @endforelse
</div>
@endsection
