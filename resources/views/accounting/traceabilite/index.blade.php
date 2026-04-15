@extends('layouts.accounting')

@section('title', 'Traçabilité & Archives Critique')

@section('styles')
    <style>
        .trace-table thead th {
            background: #f8fafc;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 20px 25px;
            border-bottom: 2px solid #f1f5f9;
        }

        .trace-table tbody td {
            padding: 18px 25px;
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

        .search-input-premium {
            height: 50px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding-left: 45px;
            font-size: 14px;
            font-weight: 600;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .action-badge {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 12px;
            border-radius: 8px;
        }

        .modal-premium .modal-content {
            border-radius: 30px;
            border: none;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        }

        .json-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 25px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 11px;
            color: #334155;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
@endsection

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1" style="font-family: 'Manrope';">Boîte Noire & Traçabilité</h1>
            <p class="text-muted small font-weight-bold uppercase tracking-wider">Historique sécurisé des actions critiques sur la plateforme</p>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="mt-3 mt-md-0">
            <form action="{{ route('accounting.traceabilite.clear_all') }}" method="POST" onsubmit="return confirm('VOULEZ-VOUS VRAIMENT VIDER TOUT L\'HISTORIQUE ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm px-4 py-2 font-weight-bold rounded-lg text-uppercase" style="letter-spacing: 1px; font-size: 10px;">
                    <i class="fas fa-trash-sweep mr-1" style="font-size: 14px;"></i> Vider l'historique
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Search Bar -->
    <div class="premium-card p-4 mb-4">
        <form action="{{ route('accounting.traceabilite.index') }}" method="GET" class="row align-items-center">
            <div class="col-md-9 mb-3 mb-md-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0" style="border-radius: 12px 0 0 12px; border: 1px solid #e2e8f0;">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" 
                        style="height: 50px; border-radius: 0 12px 12px 0; border: 1px solid #e2e8f0; font-weight: 600; font-size: 14px;" 
                        placeholder="Rechercher par action, type de donnée ou ID...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold text-uppercase rounded-lg mr-2" style="font-size: 11px;">Filtrer</button>
                @if($search)
                    <a href="{{ route('accounting.traceabilite.index') }}" class="btn btn-light border p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 50px; border-radius: 12px;">
                        <i class="fas fa-times text-muted"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="premium-card">
        <div class="table-responsive">
            <table class="table trace-table mb-0">
                <thead>
                    <tr>
                        <th>HORODATAGE</th>
                        <th>OPÉRATEUR</th>
                        <th class="text-center">NATURE</th>
                        <th>ENTITÉ</th>
                        <th class="text-center">DATA</th>
                        <th class="text-right">CONTRÔLES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small font-weight-bold text-muted font-mono" style="width: 150px;">
                                <div class="text-dark">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 10px;">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center font-weight-bold text-primary mr-2" style="width: 30px; height: 30px; font-size: 11px;">
                                        {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="small font-weight-bold text-dark italic">{{ $log->user?->name ?? 'Système' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($log->action === 'DELETE')
                                    <span class="action-badge bg-danger text-white">Suppression</span>
                                @elseif($log->action === 'RESTORE')
                                    <span class="action-badge bg-success text-white">Restauration</span>
                                @else
                                    <span class="action-badge bg-dark text-white">{{ $log->action }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="small font-weight-bold text-muted text-uppercase">{{ class_basename($log->model_type) }}</div>
                                <div class="small text-muted font-weight-light">Ref #{{ $log->model_id }}</div>
                            </td>
                            <td class="text-center">
                                <button onclick='showDetails(@json($log->details))' class="btn btn-link text-primary p-0">
                                    <i class="fas fa-database" style="font-size: 18px;"></i>
                                </button>
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end">
                                    @if($log->action === 'DELETE')
                                        <form action="{{ route('accounting.traceabilite.restore', $log->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-success p-1 mr-2" title="Restaurer">
                                                <i class="fas fa-undo-alt" style="font-size: 18px;"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(Auth::user()->role === 'admin')
                                        <form action="{{ route('accounting.traceabilite.force_delete', $log->id) }}" method="POST" onsubmit="return confirm('Log définitif ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-link text-muted hover-danger p-1" title="Supprimer le log">
                                                <i class="fas fa-trash-alt" style="font-size: 18px;"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-shield-alt text-muted opacity-25 mb-3" style="font-size: 50px;"></i>
                                <p class="small font-weight-bold text-muted">Aucune action critique détectée dans l'historique</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-light border-top">
            {{ $logs->appends(['search' => $search])->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade modal-premium" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title font-weight-bold text-dark" style="font-family: 'Manrope';">Data Snapshot</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div id="jsonContent" class="json-box"></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-dark px-4 py-2 font-weight-bold text-uppercase rounded-lg" style="font-size: 10px;" data-dismiss="modal">Fermer la console</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        function showDetails(data) {
            document.getElementById('jsonContent').textContent = JSON.stringify(data, null, 4);
            $('#detailModal').modal('show');
        }
    </script>
@endsection
