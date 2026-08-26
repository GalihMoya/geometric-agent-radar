@extends('layouts.admin')

@section('title', 'Manajemen Data Agen Spasial')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold font-heading text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i> Data Agen Spasial Mataraman
        </h3>
        <p class="text-muted mb-0 font-sans">
            Kelola profil, wilayah penugasan, dan koordinat geospatial agen biro Tulungagung, Blitar, dan Trenggalek.
        </p>
    </div>
    <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.agents.create') }}" class="btn btn-primary font-heading fw-bold d-inline-flex align-items-center gap-2 shadow-sm py-2 px-3">
            <i class="bi bi-plus-circle-fill fs-6"></i>
            <span>Tambah Agen Baru</span>
        </a>
    </div>
</div>

<!-- Quick Stats Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-primary border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted font-heading small fw-semibold">TOTAL SELURUH AGEN</div>
                    <div class="fs-3 fw-bold text-dark font-mono mt-1">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                <span class="text-primary fw-semibold">{{ $stats['tulungagung'] }}</span> TA • 
                <span class="text-danger fw-semibold">{{ $stats['blitar'] }}</span> BL • 
                <span class="text-warning fw-semibold">{{ $stats['trenggalek'] }}</span> TG
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-success border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted font-heading small fw-semibold">AGEN STATUS AKTIF</div>
                    <div class="fs-3 fw-bold text-success font-mono mt-1">{{ $stats['active'] }}</div>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-broadcast fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                Transmisi sinyal aktif & live
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-indigo border-4" style="border-left-color: #6366F1 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted font-heading small fw-semibold">SEDANG PATROLI</div>
                    <div class="fs-3 fw-bold text-indigo font-mono mt-1" style="color: #6366F1;">{{ $stats['patrol'] }}</div>
                </div>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background-color: rgba(99, 102, 241, 0.1); color: #6366F1; width: 44px; height: 44px;">
                    <i class="bi bi-shield-shaded fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                Mobile / patroli lapangan
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-danger border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted font-heading small fw-semibold">ALERT / STANDBY</div>
                    <div class="fs-3 fw-bold text-danger font-mono mt-1">{{ $stats['alert'] + $stats['standby'] }}</div>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                <span class="text-danger fw-semibold">{{ $stats['alert'] }}</span> Alert • 
                <span class="text-warning fw-semibold">{{ $stats['standby'] }}</span> Standby
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    <!-- Filter & Search Header -->
    <div class="card-header bg-white border-bottom p-3">
        <form method="GET" action="{{ route('admin.agents.index') }}" class="row g-2 align-items-center">
            <!-- Search Query -->
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama, kode, kecamatan, desa..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- City Filter -->
            <div class="col-6 col-md-2">
                <select name="city" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="all" {{ request('city') == 'all' || !request('city') ? 'selected' : '' }}>Semua Wilayah</option>
                    <option value="tulungagung" {{ request('city') == 'tulungagung' ? 'selected' : '' }}>Tulungagung</option>
                    <option value="blitar" {{ request('city') == 'blitar' ? 'selected' : '' }}>Blitar</option>
                    <option value="trenggalek" {{ request('city') == 'trenggalek' ? 'selected' : '' }}>Trenggalek</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                    <option value="patrol" {{ request('status') == 'patrol' ? 'selected' : '' }}>Patrol (Patroli)</option>
                    <option value="alert" {{ request('status') == 'alert' ? 'selected' : '' }}>Alert (Siaga)</option>
                    <option value="standby" {{ request('status') == 'standby' ? 'selected' : '' }}>Standby</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div class="col-6 col-md-2">
                <select name="type" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>Semua Tipe Divisi</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Filter / Submit -->
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 font-heading">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'city', 'status', 'type']))
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Responsive Container -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted font-heading small">
                <tr>
                    <th scope="col" class="ps-4">AGEN & KODE</th>
                    <th scope="col">WILAYAH (KOTA/KEC/DESA)</th>
                    <th scope="col">TIPE DIVISI</th>
                    <th scope="col">KOORDINAT (LAT, LNG)</th>
                    <th scope="col">STATUS & SINYAL</th>
                    <th scope="col">KONTAK</th>
                    <th scope="col" class="text-end pe-4">AKSI</th>
                </tr>
            </thead>
            <tbody class="font-sans">
                @forelse($agents as $agent)
                    <tr>
                        <!-- Agent Info -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="agent-table-avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                     style="width: 38px; height: 38px; background: {{ $agent->city == 'tulungagung' ? 'var(--color-tulungagung)' : ($agent->city == 'blitar' ? 'var(--color-blitar)' : 'var(--color-trenggalek)') }};">
                                    {{ strtoupper(substr($agent->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark font-heading">{{ $agent->name }}</div>
                                    <span class="badge bg-light text-dark font-mono border" style="font-size: 0.72rem;">{{ $agent->code }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Region -->
                        <td>
                            @if($agent->city == 'tulungagung')
                                <span class="badge" style="background-color: var(--color-tulungagung-bg); color: var(--color-tulungagung); border: 1px solid var(--color-tulungagung-border);">
                                    <i class="bi bi-geo-alt-fill"></i> Tulungagung
                                </span>
                            @elseif($agent->city == 'blitar')
                                <span class="badge" style="background-color: var(--color-blitar-bg); color: var(--color-blitar); border: 1px solid var(--color-blitar-border);">
                                    <i class="bi bi-geo-alt-fill"></i> Blitar
                                </span>
                            @else
                                <span class="badge" style="background-color: var(--color-trenggalek-bg); color: #B45309; border: 1px solid var(--color-trenggalek-border);">
                                    <i class="bi bi-geo-alt-fill"></i> Trenggalek
                                </span>
                            @endif
                            <div class="small text-muted mt-1">
                                {{ $agent->district ?? '-' }}{{ $agent->village ? ' • ' . $agent->village : '' }}
                            </div>
                        </td>

                        <!-- Type -->
                        <td>
                            <span class="text-secondary small fw-medium">{{ $agent->type }}</span>
                        </td>

                        <!-- Coordinates -->
                        <td>
                            <div class="font-mono small text-dark fw-semibold">
                                {{ number_format($agent->latitude, 5) }}, {{ number_format($agent->longitude, 5) }}
                            </div>
                            <small class="text-muted font-sans" style="font-size: 0.72rem;">WGS84 GPS</small>
                        </td>

                        <!-- Status & Signal -->
                        <td>
                            @if($agent->status == 'active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 d-inline-flex align-items-center gap-1">
                                    <span class="spinner-grow spinner-grow-sm" style="width: 6px; height: 6px;"></span> AKTIF
                                </span>
                            @elseif($agent->status == 'patrol')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-shield-shaded"></i> PATROLI
                                </span>
                            @elseif($agent->status == 'alert')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-exclamation-triangle-fill"></i> ALERT
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-pause-circle"></i> STANDBY
                                </span>
                            @endif

                            <!-- Signal Bar -->
                            <div class="d-flex align-items-center gap-2 mt-1" style="width: 100px;">
                                <div class="progress flex-grow-1" style="height: 5px;">
                                    <div class="progress-bar {{ $agent->signal_strength > 70 ? 'bg-success' : ($agent->signal_strength > 40 ? 'bg-warning' : 'bg-danger') }}" 
                                         style="width: {{ $agent->signal_strength }}%"></div>
                                </div>
                                <span class="font-mono text-muted" style="font-size: 0.68rem;">{{ $agent->signal_strength }}%</span>
                            </div>
                        </td>

                        <!-- Phone -->
                        <td>
                            @if($agent->phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $agent->phone) }}" target="_blank" class="text-decoration-none text-muted small font-mono d-inline-flex align-items-center gap-1 hover-primary">
                                    <i class="bi bi-telephone text-success"></i> {{ $agent->phone }}
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-outline-primary" title="Edit Data & Koordinat">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $agent->id }}" title="Hapus Agen">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>

                            <!-- Modal Konfirmasi Hapus -->
                            <div class="modal fade text-start" id="deleteModal{{ $agent->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $agent->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title font-heading fw-bold text-danger d-flex align-items-center gap-2" id="deleteModalLabel{{ $agent->id }}">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Hapus Agen
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-3 font-sans">
                                            <p class="mb-2">Apakah Anda yakin ingin menghapus data agen berikut secara permanen?</p>
                                            <div class="p-3 bg-light rounded-3 border">
                                                <div class="fw-bold font-heading text-dark">{{ $agent->name }}</div>
                                                <div class="font-mono text-muted small">Kode: {{ $agent->code }} | Wilayah: {{ ucfirst($agent->city) }}</div>
                                            </div>
                                            <p class="text-danger small mt-2 mb-0">
                                                <i class="bi bi-info-circle"></i> Tindakan ini tidak dapat dibatalkan dan titik agen akan hilang dari Radar Spasial.
                                            </p>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger font-heading fw-bold">
                                                    <i class="bi bi-trash3-fill"></i> Hapus Permanen
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold font-heading">Tidak Ada Data Agen</h6>
                                <p class="small mb-3">Tidak ditemukan data agen yang sesuai dengan kata kunci pencarian atau filter yang dipilih.</p>
                                <a href="{{ route('admin.agents.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Tambah Agen Baru
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Card Footer & Pagination -->
    @if($agents->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
            <small class="text-muted font-sans">
                Menampilkan {{ $agents->firstItem() ?? 0 }} - {{ $agents->lastItem() ?? 0 }} dari {{ $agents->total() }} agen
            </small>
            <div>
                {{ $agents->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection
