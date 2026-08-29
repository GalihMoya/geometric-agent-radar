@extends('layouts.admin')

@section('title', 'Manajemen Data Agen Koran & Mitra')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold font-heading text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-shop text-primary"></i> Data Agen & Kios Koran Radar
        </h3>
        <p class="text-muted mb-0 font-sans">
            Kelola data mitra kios, sub-agen loper, dan lapak koran di wilayah Tulungagung, Blitar, dan Trenggalek.
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
                    <i class="bi bi-shop fs-5"></i>
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
                    <div class="text-muted font-heading small fw-semibold">AGEN AKTIF</div>
                    <div class="fs-3 fw-bold text-success font-mono mt-1">{{ $stats['aktif'] }}</div>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                Mitra aktif berlangganan koran
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-secondary border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted font-heading small fw-semibold">AGEN NONAKTIF</div>
                    <div class="fs-3 fw-bold text-secondary font-mono mt-1">{{ $stats['nonaktif'] }}</div>
                </div>
                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-pause-circle-fill fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                Sedang libur / jeda langganan
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted font-heading small fw-semibold">KANTOR CABANG</div>
                    <div class="fs-3 fw-bold text-warning font-mono mt-1">3 Biro</div>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-building fs-5"></i>
                </div>
            </div>
            <div class="text-muted small mt-2 font-sans">
                Tulungagung, Blitar, Trenggalek
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
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama kios, pemilik, alamat, WA..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Cabang / City Filter -->
            <div class="col-6 col-md-2">
                <select name="city" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="all" {{ request('city') == 'all' || !request('city') ? 'selected' : '' }}>Semua Cabang</option>
                    <option value="tulungagung" {{ request('city') == 'tulungagung' ? 'selected' : '' }}>Cabang Tulungagung</option>
                    <option value="blitar" {{ request('city') == 'blitar' ? 'selected' : '' }}>Cabang Blitar</option>
                    <option value="trenggalek" {{ request('city') == 'trenggalek' ? 'selected' : '' }}>Cabang Trenggalek</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div class="col-6 col-md-2">
                <select name="tipe_agen" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="all" {{ request('tipe_agen') == 'all' || !request('tipe_agen') ? 'selected' : '' }}>Semua Tipe Mitra</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('tipe_agen') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Filter / Submit -->
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 font-heading">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'city', 'status', 'tipe_agen']))
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
                    <th scope="col" class="ps-4">Nama Kios & Pemilik</th>
                    <th scope="col">Wilayah (Kota/Kec/Desa)</th>
                    <th scope="col">Tipe Mitra</th>
                    <th scope="col">Koordinat (Lat, Lng)</th>
                    <th scope="col">Status agen</th>
                    <th scope="col">Kontak</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="font-sans">
                @forelse($agents as $agent)
                    @php
                        $kodeCabang = $agent->cabang ? $agent->cabang->kode_cabang : 'tulungagung';
                        $namaCabang = $agent->cabang ? $agent->cabang->nama_cabang : 'Cabang Tulungagung';
                    @endphp
                    <tr>
                        <!-- Nama Kios & Pemilik -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="agent-table-avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0"
                                     style="width: 38px; height: 38px; background: {{ $kodeCabang == 'tulungagung' ? 'var(--color-tulungagung)' : ($kodeCabang == 'blitar' ? 'var(--color-blitar)' : 'var(--color-trenggalek)') }};">
                                    {{ strtoupper(substr($agent->nama_agen, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark font-heading">{{ $agent->nama_agen }}</div>
                                    <div class="text-muted small font-sans">
                                        <i class="bi bi-person-fill text-secondary"></i> Pemilik: <span class="fw-medium text-dark">{{ $agent->nama_pemilik }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Wilayah (Kota/Kec/Desa) & Alamat -->
                        <td>
                            @if($kodeCabang == 'tulungagung')
                                <span class="badge" style="background-color: var(--color-tulungagung-bg); color: var(--color-tulungagung); border: 1px solid var(--color-tulungagung-border);">
                                    <i class="bi bi-geo-alt-fill"></i> Tulungagung
                                </span>
                            @elseif($kodeCabang == 'blitar')
                                <span class="badge" style="background-color: var(--color-blitar-bg); color: var(--color-blitar); border: 1px solid var(--color-blitar-border);">
                                    <i class="bi bi-geo-alt-fill"></i> Blitar
                                </span>
                            @else
                                <span class="badge" style="background-color: var(--color-trenggalek-bg); color: #B45309; border: 1px solid var(--color-trenggalek-border);">
                                    <i class="bi bi-geo-alt-fill"></i> Trenggalek
                                </span>
                            @endif
                            <div class="small text-muted mt-1 text-truncate" style="max-width: 220px;" title="{{ $agent->alamat_lengkap }}">
                                {{ $agent->alamat_lengkap ?? '-' }}
                            </div>
                        </td>

                        <!-- Tipe Mitra -->
                        <td>
                            <span class="badge bg-light text-dark border font-heading">
                                <i class="bi bi-tag-fill text-primary me-1"></i>{{ $agent->tipe_agen }}
                            </span>
                        </td>

                        <!-- Koordinat (Lat, Lng) -->
                        <td>
                            <div class="font-mono small text-dark fw-semibold">
                                {{ number_format($agent->latitude, 5) }}, {{ number_format($agent->longitude, 5) }}
                            </div>
                            <small class="text-muted font-sans" style="font-size: 0.72rem;">WGS84 GPS</small>
                        </td>

                        <!-- Status agen -->
                        <td>
                            @if($agent->status == 'aktif')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 d-inline-flex align-items-center gap-1">
                                    <span class="spinner-grow spinner-grow-sm" style="width: 6px; height: 6px;"></span> AKTIF
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-pause-circle"></i> NONAKTIF
                                </span>
                            @endif
                        </td>

                        <!-- Kontak -->
                        <td>
                            @if($agent->nomor_whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $agent->nomor_whatsapp) }}" target="_blank" class="text-decoration-none text-muted small font-mono d-inline-flex align-items-center gap-1 hover-primary">
                                    <i class="bi bi-whatsapp text-success"></i> {{ $agent->nomor_whatsapp }}
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-outline-primary" title="Edit Data & Lokasi Kios">
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
                                                <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Hapus Agen Koran
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-3 font-sans">
                                            <p class="mb-2">Apakah Anda yakin ingin menghapus data agen koran berikut secara permanen?</p>
                                            <div class="p-3 bg-light rounded-3 border">
                                                <div class="fw-bold font-heading text-dark">{{ $agent->nama_agen }}</div>
                                                <div class="text-muted small">Pemilik: {{ $agent->nama_pemilik }} | {{ $namaCabang }}</div>
                                                <div class="text-muted small font-mono mt-1">{{ $agent->alamat_lengkap }}</div>
                                            </div>
                                            <p class="text-danger small mt-2 mb-0">
                                                <i class="bi bi-info-circle"></i> Titik kios ini akan dihapus dan tidak lagi muncul di Peta Radar.
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
                                <i class="bi bi-shop-window fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold font-heading">Tidak Ada Data Agen Koran</h6>
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
