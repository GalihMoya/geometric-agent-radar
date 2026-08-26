@extends('layouts.admin')

@section('title', 'Tambah Agen Baru')

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
            <i class="bi bi-person-plus-fill text-primary"></i> Pendaftaran Agen Spasial Baru
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
                    <i class="bi bi-card-heading text-primary"></i> Profil & Wilayah Penugasan
                </h5>

                <div class="row g-3">
                    <!-- Kode Agen -->
                    <div class="col-12 col-sm-6">
                        <label for="code" class="form-label font-heading fw-semibold small text-secondary">
                            Kode Agen <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light font-mono text-muted"><i class="bi bi-qr-code"></i></span>
                            <input type="text" 
                                   class="form-control font-mono @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code', 'TA-' . rand(100, 999)) }}" 
                                   placeholder="Contoh: TA-006" 
                                   required>
                        </div>
                        <small class="text-muted font-sans" style="font-size: 0.72rem;">Harus unik. Contoh: TA-006, BL-006, TG-006</small>
                        @error('code')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="col-12 col-sm-6">
                        <label for="name" class="form-label font-heading fw-semibold small text-secondary">
                            Nama Lengkap Agen <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Nama lengkap..." 
                               required>
                        @error('name')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Wilayah Kota/Kabupaten -->
                    <div class="col-12 col-sm-6">
                        <label for="city" class="form-label font-heading fw-semibold small text-secondary">
                            Kabupaten / Kota Wilayah <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('city') is-invalid @enderror" id="city" name="city" required>
                            <option value="" disabled {{ old('city') ? '' : 'selected' }}>Pilih Wilayah...</option>
                            <option value="tulungagung" {{ old('city', 'tulungagung') == 'tulungagung' ? 'selected' : '' }}>Kab. Tulungagung (Biru)</option>
                            <option value="blitar" {{ old('city') == 'blitar' ? 'selected' : '' }}>Kab. & Kota Blitar (Merah)</option>
                            <option value="trenggalek" {{ old('city') == 'trenggalek' ? 'selected' : '' }}>Kab. Trenggalek (Kuning)</option>
                        </select>
                        @error('city')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipe / Divisi -->
                    <div class="col-12 col-sm-6">
                        <label for="type" class="form-label font-heading fw-semibold small text-secondary">
                            Divisi / Tipe Agen <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="Field Reporter" {{ old('type') == 'Field Reporter' ? 'selected' : '' }}>Field Reporter (Jurnalis Lapangan)</option>
                            <option value="Intelijen Spasial" {{ old('type') == 'Intelijen Spasial' ? 'selected' : '' }}>Intelijen Spasial</option>
                            <option value="Investigasi Khusus" {{ old('type') == 'Investigasi Khusus' ? 'selected' : '' }}>Investigasi Khusus</option>
                            <option value="Redaksi & Liputan" {{ old('type') == 'Redaksi & Liputan' ? 'selected' : '' }}>Redaksi & Liputan</option>
                            <option value="Tim Reaksi Cepat" {{ old('type') == 'Tim Reaksi Cepat' ? 'selected' : '' }}>Tim Reaksi Cepat</option>
                        </select>
                        @error('type')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kecamatan -->
                    <div class="col-12 col-sm-6">
                        <label for="district" class="form-label font-heading fw-semibold small text-secondary">
                            Kecamatan Penugasan
                        </label>
                        <input type="text" 
                               class="form-control @error('district') is-invalid @enderror" 
                               id="district" 
                               name="district" 
                               value="{{ old('district') }}" 
                               placeholder="Contoh: Boyolangu, Kepanjenkidul, dll">
                        @error('district')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Desa / Kelurahan -->
                    <div class="col-12 col-sm-6">
                        <label for="village" class="form-label font-heading fw-semibold small text-secondary">
                            Desa / Kelurahan
                        </label>
                        <input type="text" 
                               class="form-control @error('village') is-invalid @enderror" 
                               id="village" 
                               name="village" 
                               value="{{ old('village') }}" 
                               placeholder="Contoh: Moyoketen, Bendo, dll">
                        @error('village')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status Operasional -->
                    <div class="col-12 col-sm-6">
                        <label for="status" class="form-label font-heading fw-semibold small text-secondary">
                            Status Operasional <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                            <option value="patrol" {{ old('status') == 'patrol' ? 'selected' : '' }}>Patrol (Sedang Patroli)</option>
                            <option value="alert" {{ old('status') == 'alert' ? 'selected' : '' }}>Alert (Status Siaga)</option>
                            <option value="standby" {{ old('status') == 'standby' ? 'selected' : '' }}>Standby (Cadangan)</option>
                        </select>
                        @error('status')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="col-12 col-sm-6">
                        <label for="phone" class="form-label font-heading fw-semibold small text-secondary">
                            Nomor WhatsApp / HP
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                            <input type="text" 
                                   class="form-control font-mono @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', '+62 812-') }}" 
                                   placeholder="+62 812-xxxx-xxxx">
                        </div>
                        @error('phone')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kekuatan Sinyal -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="signal_strength" class="form-label font-heading fw-semibold small text-secondary mb-0">
                                Kekuatan Sinyal GPS / Transmisi: <span id="signal-display" class="fw-bold font-mono text-primary">{{ old('signal_strength', 95) }}%</span>
                            </label>
                        </div>
                        <input type="range" 
                               class="form-range" 
                               min="10" 
                               max="100" 
                               step="5" 
                               id="signal_strength" 
                               name="signal_strength" 
                               value="{{ old('signal_strength', 95) }}"
                               oninput="document.getElementById('signal-display').innerText = this.value + '%'">
                        @error('signal_strength')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi / Catatan Lapangan -->
                    <div class="col-12">
                        <label for="description" class="form-label font-heading fw-semibold small text-secondary">
                            Deskripsi Penugasan & Catatan Lapangan
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Tuliskan catatan khusus atau fokus peliputan agen ini...">{{ old('description') }}</textarea>
                        @error('description')
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
                        <i class="bi bi-geo-alt-fill text-danger"></i> Titik Koordinat Geospatial
                    </h5>
                    <div class="badge bg-primary bg-opacity-10 text-primary font-mono small">
                        2-Way Sync Aktif
                    </div>
                </div>

                <!-- Petunjuk Interaktif -->
                <div class="alert alert-info border-0 py-2 px-3 mb-3 d-flex align-items-start gap-2 small" style="background-color: #EBF4FC; color: #004B87;">
                    <i class="bi bi-info-circle-fill fs-6 mt-1 flex-shrink-0"></i>
                    <div>
                        <strong>Cara Menentukan Lokasi Agen:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li><strong>Klik di Peta:</strong> Klik di mana saja pada peta untuk menaruh/memindahkan pin marker.</li>
                            <li><strong>Geser Marker:</strong> Drag & drop pin marker untuk posisi presisi.</li>
                            <li><strong>Ketik Manual:</strong> Masukkan angka di kotak <em>Latitude</em> & <em>Longitude</em> di bawah, posisi pin akan otomatis menyesuaikan.</li>
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

                <!-- Peta Kontainer -->
                <div class="position-relative flex-grow-1 mb-3" style="min-height: 380px;">
                    <div id="admin-map" style="height: 100%; width: 100%; min-height: 380px; border-radius: 12px; border: 1px solid #E2E8F0; z-index: 1;"></div>
                </div>

                <!-- Input Koordinat (2-Way Binding) -->
                <div class="row g-2 p-3 bg-light rounded-3 border mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="latitude" class="form-label font-heading fw-semibold small text-secondary mb-1">
                            <i class="bi bi-compass me-1"></i> Latitude (Lintang) <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control font-mono fw-bold text-primary @error('latitude') is-invalid @enderror" 
                               id="latitude" 
                               name="latitude" 
                               value="{{ old('latitude', '-8.066000') }}" 
                               placeholder="-8.066000" 
                               required>
                        @error('latitude')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="longitude" class="form-label font-heading fw-semibold small text-secondary mb-1">
                            <i class="bi bi-compass me-1"></i> Longitude (Bujur) <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control font-mono fw-bold text-primary @error('longitude') is-invalid @enderror" 
                               id="longitude" 
                               name="longitude" 
                               value="{{ old('longitude', '111.900000') }}" 
                               placeholder="111.900000" 
                               required>
                        @error('longitude')
                            <div class="text-danger small font-mono mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Submit & Aksi -->
                <div class="d-flex align-items-center justify-content-end gap-2 mt-auto">
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-light font-heading">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary font-heading fw-bold px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-check-circle-fill"></i> Simpan Data Agen
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Definisi Titik Pusat Presisi Kota Mataraman
    const CITY_CENTERS = {
        tulungagung: [-8.066, 111.900],
        blitar: [-8.100, 112.160],
        trenggalek: [-8.050, 111.710]
    };

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const citySelect = document.getElementById('city');

    // Inisialisasi Koordinat Awal
    let initialLat = parseFloat(latInput.value) || -8.066000;
    let initialLng = parseFloat(lngInput.value) || 111.900000;

    // 1. Inisialisasi Peta Leaflet
    const map = L.map('admin-map', {
        center: [initialLat, initialLng],
        zoom: 11,
        zoomControl: true
    });

    // Tile Layer OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // 2. Buat Marker Draggable
    const marker = L.marker([initialLat, initialLng], {
        draggable: true,
        autoPan: true
    }).addTo(map);

    marker.bindPopup("<div class='font-sans small text-center'><strong>Lokasi Agen Baru</strong><br><span class='text-muted'>Geser untuk memindahkan</span></div>").openPopup();

    // Fungsi Update Input Koordinat dari Koordinat Peta (Map-to-Form)
    function updateInputs(lat, lng) {
        latInput.value = parseFloat(lat).toFixed(6);
        lngInput.value = parseFloat(lng).toFixed(6);
    }

    // Event A: Klik Pada Peta (Map Click -> Form Update)
    map.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        marker.setLatLng([lat, lng]);
        updateInputs(lat, lng);
    });

    // Event B: Drag Selesai pada Marker (Marker Dragend -> Form Update)
    marker.on('dragend', function (e) {
        const pos = marker.getLatLng();
        updateInputs(pos.lat, pos.lng);
    });

    // Event C: Form Input Manual (Form -> Map Update)
    function syncFormToMap() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            marker.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
        }
    }

    latInput.addEventListener('input', syncFormToMap);
    lngInput.addEventListener('input', syncFormToMap);

    // Event D: Perubahan Pilihan Dropdown Kota (City Select -> Pan Map)
    citySelect.addEventListener('change', function () {
        const selectedCity = this.value;
        if (selectedCity && CITY_CENTERS[selectedCity]) {
            const center = CITY_CENTERS[selectedCity];
            map.flyTo(center, 12, { duration: 1.0 });
            marker.setLatLng(center);
            updateInputs(center[0], center[1]);
        }
    });

    // Fungsi Helper Preset Button
    window.focusPresetCity = function (cityName) {
        if (CITY_CENTERS[cityName]) {
            const center = CITY_CENTERS[cityName];
            citySelect.value = cityName;
            map.flyTo(center, 12, { duration: 1.0 });
            marker.setLatLng(center);
            updateInputs(center[0], center[1]);
        }
    };

    // Pastikan ukuran peta disesuaikan setelah render DOM
    setTimeout(function() {
        map.invalidateSize();
    }, 250);
});
</script>
@endpush
