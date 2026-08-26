@extends('layouts.admin')

@section('title', 'Tambah Agen Koran Baru')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 font-heading small">
                <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}" class="text-decoration-none">Data Agen</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Agen Baru</li>
            </ol>
        </nav>
        <h3 class="fw-bold font-heading text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-shop-window text-primary"></i> Pendaftaran Mitra & Agen Koran Baru
        </h3>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary font-heading">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.agents.store') }}" id="form-agent">
    @csrf

    <div class="row g-4">
        <!-- Kolom Kiri: Form Informasi Agen -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h5 class="fw-bold font-heading text-dark border-bottom pb-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-card-heading text-primary"></i> Profil Kios & Kepemilikan
                </h5>

                <div class="row g-3">
                    <!-- Nama Kios / Agen -->
                    <div class="col-12 col-sm-6">
                        <label for="nama_agen" class="form-label font-heading fw-semibold small text-secondary">
                            Nama Kios / Agen <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-shop"></i></span>
                            <input type="text" 
                                   class="form-control @error('nama_agen') is-invalid @enderror" 
                                   id="nama_agen" 
                                   name="nama_agen" 
                                   value="{{ old('nama_agen') }}" 
                                   placeholder="Contoh: Kios Koran Barokah" 
                                   required>
                        </div>
                        @error('nama_agen')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Pemilik -->
                    <div class="col-12 col-sm-6">
                        <label for="nama_pemilik" class="form-label font-heading fw-semibold small text-secondary">
                            Nama Pemilik <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   class="form-control @error('nama_pemilik') is-invalid @enderror" 
                                   id="nama_pemilik" 
                                   name="nama_pemilik" 
                                   value="{{ old('nama_pemilik') }}" 
                                   placeholder="Nama pemilik kios..." 
                                   required>
                        </div>
                        @error('nama_pemilik')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cabang Radar -->
                    <div class="col-12 col-sm-6">
                        <label for="cabang_id" class="form-label font-heading fw-semibold small text-secondary">
                            Kantor Cabang Radar <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('cabang_id') is-invalid @enderror" id="cabang_id" name="cabang_id" required>
                            <option value="" disabled {{ old('cabang_id') ? '' : 'selected' }}>Pilih Cabang...</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}" {{ old('cabang_id', 1) == $cabang->id ? 'selected' : '' }}>
                                    {{ $cabang->nama_cabang }} ({{ ucfirst($cabang->kode_cabang) }})
                                </option>
                            @endforeach
                        </select>
                        @error('cabang_id')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipe Mitra -->
                    <div class="col-12 col-sm-6">
                        <label for="tipe_agen" class="form-label font-heading fw-semibold small text-secondary">
                            Tipe Mitra <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('tipe_agen') is-invalid @enderror" id="tipe_agen" name="tipe_agen" required>
                            <option value="Kios Eceran" {{ old('tipe_agen') == 'Kios Eceran' ? 'selected' : '' }}>Kios Eceran</option>
                            <option value="Sub-Agen Loper" {{ old('tipe_agen') == 'Sub-Agen Loper' ? 'selected' : '' }}>Sub-Agen Loper</option>
                            <option value="Lapak Koran" {{ old('tipe_agen') == 'Lapak Koran' ? 'selected' : '' }}>Lapak Koran</option>
                            <option value="Toko Buku & Majalah" {{ old('tipe_agen') == 'Toko Buku & Majalah' ? 'selected' : '' }}>Toko Buku & Majalah</option>
                            <option value="Agen Utama" {{ old('tipe_agen') == 'Agen Utama' ? 'selected' : '' }}>Agen Utama</option>
                        </select>
                        @error('tipe_agen')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status Agen -->
                    <div class="col-12 col-sm-6">
                        <label for="status" class="form-label font-heading fw-semibold small text-secondary">
                            Status Agen <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif (Berlangganan)</option>
                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif (Jeda/Libur)</option>
                        </select>
                        @error('status')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div class="col-12 col-sm-6">
                        <label for="nomor_whatsapp" class="form-label font-heading fw-semibold small text-secondary">
                            Nomor WhatsApp / HP
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" 
                                   class="form-control font-mono @error('nomor_whatsapp') is-invalid @enderror" 
                                   id="nomor_whatsapp" 
                                   name="nomor_whatsapp" 
                                   value="{{ old('nomor_whatsapp') }}" 
                                   placeholder="Contoh: 0812-3456-7890">
                        </div>
                        @error('nomor_whatsapp')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Alamat Lengkap -->
                    <div class="col-12">
                        <label for="alamat_lengkap" class="form-label font-heading fw-semibold small text-secondary">
                            Alamat Lengkap Kios / Lokasi
                        </label>
                        <textarea class="form-control @error('alamat_lengkap') is-invalid @enderror" 
                                  id="alamat_lengkap" 
                                  name="alamat_lengkap" 
                                  rows="3" 
                                  placeholder="Tuliskan alamat lengkap lokasi kios koran / titik penjualan...">{{ old('alamat_lengkap') }}</textarea>
                        @error('alamat_lengkap')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Peta Interaktif & Koordinat -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                    <h5 class="fw-bold font-heading text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Titik Lokasi Kios (Geospatial)
                    </h5>
                    <div class="badge bg-primary bg-opacity-10 text-primary font-mono small">
                        Pilih Titik di Peta
                    </div>
                </div>

                <!-- Petunjuk Interaktif -->
                <div class="alert alert-info border-0 py-2 px-3 mb-3 d-flex align-items-start gap-2 small" style="background-color: #EBF4FC; color: #004B87;">
                    <i class="bi bi-info-circle-fill fs-6 mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>Petunjuk Penentuan Lokasi Kios:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li><strong>Klik di Peta:</strong> Titik marker akan otomatis berpindah ke lokasi yang Anda klik.</li>
                            <li><strong>Geser Marker:</strong> Seret pin biru ke titik presisi kios koran.</li>
                        </ul>
                    </div>
                </div>

                <!-- Preset Cepat Kota -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="text-muted font-heading small fw-semibold">Fokus Wilayah:</span>
                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 font-mono small" onclick="focusPresetCity('tulungagung')">
                        Tulungagung
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger py-1 px-2 font-mono small" onclick="focusPresetCity('blitar')">
                        Blitar
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning py-1 px-2 font-mono small text-dark" onclick="focusPresetCity('trenggalek')">
                        Trenggalek
                    </button>
                </div>

                <!-- Map Container Leaflet -->
                <div id="admin-map" class="rounded-3 border overflow-hidden flex-grow-1" style="min-height: 280px; width: 100%;"></div>

                <!-- Input Koordinat Manual -->
                <div class="row g-2 mt-3 pt-3 border-top">
                    <div class="col-6">
                        <label for="latitude" class="form-label font-heading fw-semibold small text-secondary">
                            Latitude <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light font-mono">LAT</span>
                            <input type="number" 
                                   step="any" 
                                   class="form-control font-mono @error('latitude') is-invalid @enderror" 
                                   id="latitude" 
                                   name="latitude" 
                                   value="{{ old('latitude', -8.0655) }}" 
                                   required>
                        </div>
                        @error('latitude')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="longitude" class="form-label font-heading fw-semibold small text-secondary">
                            Longitude <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light font-mono">LNG</span>
                            <input type="number" 
                                   step="any" 
                                   class="form-control font-mono @error('longitude') is-invalid @enderror" 
                                   id="longitude" 
                                   name="longitude" 
                                   value="{{ old('longitude', 111.9015) }}" 
                                   required>
                        </div>
                        @error('longitude')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        <!-- Tombol Aksi Submit -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row justify-content-between align-items-center">
                <a href="{{ route('admin.agents.index') }}" class="btn btn-light font-heading">
                    <i class="bi bi-x-circle"></i> Batalkan
                </a>
                <button type="submit" class="btn btn-primary px-4 py-2 font-heading fw-bold d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-check-circle-fill"></i> Simpan Data Agen Koran
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let adminMap = null;
    let marker = null;

    const cityCenters = {
        tulungagung: [-8.0645, 111.9025],
        blitar: [-8.0983, 112.1681],
        trenggalek: [-8.0506, 111.7145],
    };

    document.addEventListener('DOMContentLoaded', () => {
        const initialLat = parseFloat(document.getElementById('latitude').value) || -8.0655;
        const initialLng = parseFloat(document.getElementById('longitude').value) || 111.9015;

        // Init Map
        adminMap = L.map('admin-map').setView([initialLat, initialLng], 13);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            attribution: '&copy; CARTO'
        }).addTo(adminMap);

        // Marker
        marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(adminMap);

        // Marker drag listener
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateCoordsInputs(position.lat, position.lng);
        });

        // Map click listener
        adminMap.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateCoordsInputs(e.latlng.lat, e.latlng.lng);
        });

        // Coords inputs manual change
        document.getElementById('latitude').addEventListener('input', updateMarkerFromInputs);
        document.getElementById('longitude').addEventListener('input', updateMarkerFromInputs);

        // Auto zoom when choosing cabang
        document.getElementById('cabang_id').addEventListener('change', function () {
            const sel = this.options[this.selectedIndex].text.toLowerCase();
            if (sel.includes('blitar')) focusPresetCity('blitar');
            else if (sel.includes('trenggalek')) focusPresetCity('trenggalek');
            else focusPresetCity('tulungagung');
        });
    });

    function updateCoordsInputs(lat, lng) {
        document.getElementById('latitude').value = parseFloat(lat).toFixed(6);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(6);
    }

    function updateMarkerFromInputs() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            adminMap.panTo([lat, lng]);
        }
    }

    function focusPresetCity(cityKey) {
        if (cityCenters[cityKey] && adminMap) {
            const target = cityCenters[cityKey];
            adminMap.flyTo(target, 13, { duration: 1.2 });
            marker.setLatLng(target);
            updateCoordsInputs(target[0], target[1]);
        }
    }
</script>
@endpush
@endsection
