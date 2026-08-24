# 📋 ISSUE: Perombakan Wilayah Radar Geospatial (Tulungagung, Blitar, Trenggalek) & Re-Theming UI Radar Tulungagung

## 📌 Ringkasan Masalah & Tujuan (Overview)
Proyek **Geometric Agent Radar** saat ini menggunakan data dummy umum (skala nasional / Jakarta) dan tema dark-cyber neon. Diperlukan perombakan menyeluruh agar aplikasi berfokus penuh pada **3 wilayah Mataraman Jawa Timur**, yaitu:
1. **Kabupaten Tulungagung** (Warna Identitas: **Biru**)
2. **Kabupaten & Kota Blitar** (Warna Identitas: **Merah**)
3. **Kabupaten Trenggalek** (Warna Identitas: **Kuning**)

Aplikasi harus memiliki kemampuan navigasi geografis bertingkat (*hierarchical drill-down* dari Wilayah ➔ Kecamatan ➔ Desa), visualisasi kantor utama Radar (*HQ*), titik agen lapangan dengan modal detail, sistem isolasi area agen, serta pembaruan antarmuka (UI) bernuansa **Biru-Putih khas Radar Tulungagung (Jawa Pos Group)**.

---

## 🎨 1. Aturan Warna & Identitas Visual (Color Coding Rules)

Setiap entitas visual (polygon batas, marker icon kecamatan, desa, kantor HQ, dan agen) wajib mematuhi skema warna berikut:

| Wilayah / Kota | Warna Utama | Kode Hex Primer | Kode Hex Aksen / Border | Kode Hex Transparan (Polygon) |
| :--- | :--- | :--- | :--- | :--- |
| **Tulungagung** | **Biru (Blue)** | `#004B87` *(Radar Blue)* | `#0073E6` / `#0D6EFD` | `rgba(0, 75, 135, 0.25)` |
| **Blitar** | **Merah (Red)** | `#D90429` *(Crimson Red)* | `#EF233C` / `#DC3545` | `rgba(217, 4, 41, 0.25)` |
| **Trenggalek** | **Kuning (Yellow/Gold)** | `#E5A900` *(Amber Gold)* | `#FFC107` / `#F39C12` | `rgba(229, 169, 0, 0.25)` |

### Tema Antarmuka (UI Theme - Radar Tulungagung)
- **Warna Dasar Navbar & Header:** Biru Radar (`#004B87` & `#003366`)
- **Latar Belakang Aplikasi:** Bersih dan modern (*Clean Light / Semi-Dark Navy Slate* `#F4F7FC` atau `#0A192F`)
- **Kartu & Panel:** Putih Bersih (`#FFFFFF`) dengan border halus (`#E2E8F0` / shadow lembut)
- **Tipografi:** Sans-serif modern (Inter / Poppins) dipadukan dengan JetBrains Mono untuk koordinat & status.

---

## 🗺️ 2. Fitur Geografis & Logika Peta (Map Hierarchy & Interactions)

### A. Tampilan Awal (Initial Load)
- **Viewport Awal:** Peta langsung terpusat (*centered & bounded*) pada kawasan Mataraman Pesisir Selatan Jawa Timur:
  - Koordinat Tengah: `Latitude: -8.1000`, `Longitude: 111.9500`
  - Tingkat Zoom: Level `10`
- **Batas Wilayah Kabupaten/Kota:** Menampilkan poligon batas 3 wilayah dengan garis tepi tebal sesuai warna masing-masing.

### B. Titik Kantor Utama Radar (3 Titik Besar / HQ Markers)
Menampilkan 3 icon besar dengan efek glowing / pulse khusus sebagai landmark kantor radar:
1. **Kantor Radar Tulungagung:**
   - Lokasi: Jl. I Gusti Ngurah Rai No. 34 / Jl. Jayeng Kusuma, Tulungagung
   - Koordinat: `[-8.0645, 111.9025]`
   - Warna: Biru (`#004B87`)
2. **Kantor Radar Blitar:**
   - Lokasi: Jl. Mastrip / Jl. Kenanga, Kota Blitar
   - Koordinat: `[-8.0983, 112.1681]`
   - Warna: Merah (`#D90429`)
3. **Kantor Radar Trenggalek:**
   - Lokasi: Jl. Panglima Sudirman / Jl. Brigjen Soetran, Trenggalek
   - Koordinat: `[-8.0506, 111.7145]`
   - Warna: Kuning (`#E5A900`)

### C. Level Kecamatan (Districts Drill-Down)
- Menampilkan batas poligon seluruh kecamatan di Tulungagung, Blitar, dan Trenggalek.
- Menampilkan marker/label icon kecamatan dengan warna wilayahnya (Biru = Tulungagung, Merah = Blitar, Kuning = Trenggalek).
- **Interaksi Klik Kecamatan:**
  - Peta otomatis *Fly-To* dan *Zoom-In* ke kecamatan tersebut (Zoom level `12` - `13`).
  - Memunculkan titik desa-desa / kelurahan yang ada di dalam kecamatan tersebut.

### D. Level Desa (Villages Drill-Down)
- Menampilkan titik desa/kelurahan dengan icon sesuai warna wilayah.
- **Interaksi Klik Desa:**
  - Peta otomatis *Fly-To* dan *Zoom-In* ke area desa (Zoom level `14` - `15`).
  - Menyorot (*highlight*) area/poligon dari desa tersebut sesuai warna kotanya.

### E. Titik Agen (Agent Points) & Modal Detail
- Titik-titik kecil yang merepresentasikan lokasi agen operasional:
  - Agen Tulungagung: Icon titik kecil **Biru**
  - Agen Blitar: Icon titik kecil **Merah**
  - Agen Trenggalek: Icon titik kecil **Kuning**
- **Interaksi Klik Agen:** Membuka **Modal Detail Agen** (Bootstrap Modal) yang berisi:
  - Foto/Avatar Profil Agen
  - Kode & Nama Agen (Bernama khas Indonesia)
  - Kota / Kecamatan / Desa Penugasan
  - Status (Active / Patrol / Alert / Standby)
  - Kekuatan Sinyal (*Signal Strength %*)
  - Spesialisasi / Tipe Agen & Deskripsi Tugas
  - Koordinat Latitude & Longitude
- **Aturan Isolasi Area Agen (*Agent Boundary Isolation*):**
  - Saat memilih/memfilter area tertentu (contoh: Wilayah Tulungagung), peta **HANYA** menampilkan agen Tulungagung. Agen dari Blitar dan Trenggalek disembunyikan (*hidden*).
  - Berlaku sama jika memilih Blitar atau Trenggalek.

---

## 💾 3. Perubahan Skema Database & Data Seeder

### A. Modifikasi Tabel `agents`
Update migrasi `database/migrations/2026_08_24_142803_create_agents_table.php` atau buat migrasi baru:
- `id` (bigIncrements)
- `code` (string, unique, contoh: `AGT-TA-01`, `AGT-BL-01`, `AGT-TG-01`)
- `name` (string, nama agen bernuansa Indonesia)
- `city` (enum: `tulungagung`, `blitar`, `trenggalek`)
- `district` (string, nama kecamatan)
- `village` (string, nama desa/kelurahan)
- `type` (string: `Tactical Lead`, `Field Reporter`, `Scout Drone`, `Investigative Recon`, `Traffic Patrol`)
- `status` (enum: `active`, `patrol`, `alert`, `standby`)
- `latitude` (decimal 10, 7)
- `longitude` (decimal 10, 7)
- `signal_strength` (integer, 1 - 100)
- `phone` (string, nomor kontak dummy format Indonesia, contoh: `0812-3456-7890`)
- `description` (text, deskripsi tugas lokal)
- `timestamps`

### B. Seeder Baru (`AgentSeeder.php`)
- Hapus semua data agen lama (Jakarta).
- Buat minimal **15 - 25 agen baru** yang terdistribusi merata di:
  - **Tulungagung:** Boyolangu, Kedungwaru, Kauman, Ngunut, Campurdarat, Sendang, Besuki, Gondang, dll.
  - **Blitar:** Sananwetan, Kepanjenkidul, Sukorejo, Kanigoro, Wlingi, Srengat, Garum, Sutojayan, dll.
  - **Trenggalek:** Trenggalek Kota, Watulimo, Durenan, Karangan, Panggul, Gandusari, Pogalan, dll.
- **Daftar Nama Indonesia:** Contoh: *Bambang Pamungkas, Agus Setiawan, Siti Rahmawati, Hendra Wijaya, Eko Prasetyo, Dian Lestari, Budi Santoso, Rina Kurniawati, Tri Wibowo, Bayu Danendra, Suryo Wicaksono, Ratna Sari Dewi, Gatot Sasongko, Arif Hidayat, Maya Anggraini*.

---

## 🛠️ 4. Tahapan Pengerjaan Step-by-Step (Work Breakdown Structure)

Dokumentasi tahapan ini disusun secara runut agar mudah dieksekusi oleh junior developer maupun asisten AI:

```
┌─────────────────────────────────────────────────────────────┐
│ TAHAP 1: Persiapan Database, Model & Seeder Baru           │
├─────────────────────────────────────────────────────────────┤
│ TAHAP 2: Penyediaan & Struktur Data GeoJSON (Peta Wilayah)  │
├─────────────────────────────────────────────────────────────┤
│ TAHAP 3: Backend Controller & API Endpoint                  │
├─────────────────────────────────────────────────────────────┤
│ TAHAP 4: Redesign UI & Styling Tema Biru-Putih Radar        │
├─────────────────────────────────────────────────────────────┤
│ TAHAP 5: Implementasi Map Engine Leaflet.js (Hierarki & HQ) │
├─────────────────────────────────────────────────────────────┤
│ TAHAP 6: Integrasi Modal Detail Agen & Isolasi Area         │
├─────────────────────────────────────────────────────────────┤
│ TAHAP 7: Pengujian, Validasi & QA Checklist                 │
└─────────────────────────────────────────────────────────────┘
```

---

### 🔹 TAHAP 1: Persiapan Database, Model & Seeder Baru
1. Perbarui file model `app/Models/Agent.php`:
   - Tambahkan `$fillable` untuk field baru (`city`, `district`, `village`, `phone`, dll.).
   - Tambahkan helper scope: `scopeByCity($query, $city)`.
2. Perbarui migrasi database untuk tabel `agents` agar memiliki field `city`, `district`, `village`, dan `phone`.
3. Tulis ulang `database/seeders/AgentSeeder.php`:
   - Gunakan koordinat asli di wilayah Tulungagung, Blitar, dan Trenggalek.
   - Tetapkan nama-nama agen bernuansa lokal Indonesia.
4. Jalankan perintah migrasi ulang dan seeding:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

### 🔹 TAHAP 2: Penyediaan & Struktur Data Geografis (GeoJSON)
1. Buat direktori data spasial di `public/data/geojson/` atau `resources/geojson/`:
   - `regions.json` / `mataraman_boundaries.geojson` (Batas Kabupaten Tulungagung, Blitar, Trenggalek).
   - `districts_tulungagung.json`, `districts_blitar.json`, `districts_trenggalek.json` (Poligon & koordinat tengah kecamatan).
   - `villages.json` (Data titik koordinat dan batas desa/kelurahan).
   - `radar_hq.json` (Data 3 titik kantor utama Radar Tulungagung, Blitar, Trenggalek).
2. Sediakan fallback coordinate array di JavaScript jika file GeoJSON eksternal gagal dimuat, sehingga peta tetap berjalan tanpa error.

---

### 🔹 TAHAP 3: Backend Controller & Routing
1. Perbarui `app/Http/Controllers/AgentController.php`:
   - `index()`: Mengembalikan view `radar`.
   - `getAgents(Request $request)`:
     - Filter berdasarkan `city` (`all`, `tulungagung`, `blitar`, `trenggalek`).
     - Filter berdasarkan `status`, `type`, dan `search`.
     - Kembalikan statistik dinamis: total agen per kota, agen aktif, patroli, alert, standby, dan rata-rata sinyal.
   - `getHqLocations()`: Mengembalikan JSON daftar 3 kantor utama Radar.
2. Perbarui `routes/web.php`:
   ```php
   Route::get('/', [AgentController::class, 'index'])->name('radar.index');
   Route::get('/api/agents', [AgentController::class, 'getAgents'])->name('api.agents');
   Route::get('/api/hq', [AgentController::class, 'getHqLocations'])->name('api.hq');
   ```

---

### 🔹 TAHAP 4: Redesign UI & Styling Tema Biru-Putih Radar Tulungagung
1. Modifikasi `resources/css/app.css`:
   - Ganti variabel warna utama dengan palet Radar Tulungagung:
     ```css
     :root {
         --radar-blue: #004B87;
         --radar-blue-dark: #003366;
         --radar-blue-light: #0073E6;
         --radar-bg: #F4F7FC;
         --radar-card-bg: #FFFFFF;
         --radar-text: #1E293B;
         --radar-text-muted: #64748B;
         
         /* City Accent Colors */
         --color-tulungagung: #0073E6;
         --color-blitar: #D90429;
         --color-trenggalek: #E5A900;
     }
     ```
   - Sesuaikan tampilan Navbar, Card Statistik, Sidebar Manifest, dan Legend Peta agar berpenampilan elegan, modern, dan bersih.
   - Buat class CSS untuk marker HQ yang berdenyut (*pulsing animation*):
     - `.hq-marker-tulungagung` (Biru besar)
     - `.hq-marker-blitar` (Merah besar)
     - `.hq-marker-trenggalek` (Kuning besar)
   - Buat class CSS untuk marker agen kecil:
     - `.agent-marker-tulungagung` (Biru kecil)
     - `.agent-marker-blitar` (Merah kecil)
     - `.agent-marker-trenggalek` (Kuning kecil)
2. Perbarui `resources/views/radar.blade.php`:
   - Perbarui header branding: *"JAWA POS RADAR TULUNGAGUNG - GEOMETRIC AGENT RADAR"*.
   - Tambahkan dropdown/tab filter kota: **Semua Wilayah | Tulungagung (Biru) | Blitar (Merah) | Trenggalek (Kuning)**.
   - Buat komponen **Bootstrap Modal (`#agentDetailModal`)** untuk menampilkan profil agen saat titik agen diklik.

---

### 🔹 TAHAP 5: Implementasi Map Engine Leaflet.js (Hierarki & Interaksi)
Perbarui `resources/js/app.js` dengan arsitektur layer sebagai berikut:
1. **Inisialisasi Peta (`initMap`)**:
   - Pusatkan di Tulungagung-Blitar-Trenggalek `[-8.1000, 111.9500]`, zoom `10`.
   - Gunakan tile layer modern yang bersih (OpenStreetMap / CartoDB Positron / Voyager).
2. **Layer Management (`LayerGroups`)**:
   - `hqLayerGroup`: Menyimpan 3 marker besar kantor Radar.
   - `regionBoundaryLayer`: Menyimpan poligon batas 3 kabupaten.
   - `districtLayerGroup`: Menyimpan poligon dan marker icon kecamatan.
   - `villageLayerGroup`: Menyimpan poligon dan marker desa (hanya muncul saat zoom tinggi atau kecamatan diklik).
   - `agentClusterGroup`: Menyimpan titik-titik agen kecil dengan warna masing-masing.
3. **Logika Hierarki Drill-Down**:
   - **Klik Poligon / Icon Wilayah:** Zoom ke level kabupaten terkait.
   - **Klik Poligon / Icon Kecamatan:** Peta melakukan `flyTo` ke kecamatan (zoom `12-13`), aktifkan layer desa dalam kecamatan tersebut.
   - **Klik Poligon / Icon Desa:** Peta melakukan `flyTo` ke desa (zoom `14-15`), beri efek highlight poligon area desa tersebut.
4. **Logika Zoom Event Listener**:
   - Zoom `< 11`: Tampilkan level Kabupaten & Titik Kantor HQ.
   - Zoom `11 - 13`: Tampilkan Batas & Icon Kecamatan.
   - Zoom `> 13`: Tampilkan Batas & Icon Desa.

---

### 🔹 TAHAP 6: Integrasi Modal Detail Agen & Isolasi Area
1. **Event Listener Klik Marker Agen:**
   - Ketika marker agen diklik, ambil data agen (ID/Object).
   - Isi elemen-elemen di dalam `#agentDetailModal`:
     - Avatar / Inisial Agen
     - Nama & Kode Agen
     - Badge Wilayah (Tulungagung / Blitar / Trenggalek) dengan warna yang sesuai
     - Kecamatan & Desa Penugasan
     - Status & Signal Strength Bar
     - Nomor Telepon & Deskripsi Tugas
   - Buka modal menggunakan Bootstrap API: `new bootstrap.Modal(document.getElementById('agentDetailModal')).show();`
2. **Sistem Isolasi Area Agen:**
   - Buat fungsi filter `filterAgentsByArea(selectedCity)`:
     - Jika `selectedCity === 'tulungagung'`, bersihkan layer agen dan hanya render agen yang memiliki `city === 'tulungagung'`. Agen Blitar & Trenggalek tidak dirender.
     - Begitu pula untuk `blitar` dan `trenggalek`.
     - Jika `all`, tampilkan seluruh agen dari ketiga kota.

---

### 🔹 TAHAP 7: Pengujian, Validasi & QA Checklist
Lakukan verifikasi terhadap semua item berikut:
- [ ] Peta terbuka langsung terfokus di Tulungagung, Blitar, Trenggalek (tidak lagi menampilkan Jakarta).
- [ ] Terdapat 3 titik besar Kantor Radar (Tulungagung = Biru, Blitar = Merah, Trenggalek = Kuning).
- [ ] Poligon batas kabupaten dan kecamatan tampil dengan warna yang sesuai.
- [ ] Klik kecamatan melakukan zoom dan memunculkan desa-desa di dalamnya.
- [ ] Klik desa melakukan zoom dan menampilkan batas area desa tersebut.
- [ ] Titik agen berwarna Biru (Tulungagung), Merah (Blitar), dan Kuning (Trenggalek).
- [ ] Klik titik agen memunculkan Modal Detail Agen yang responsif.
- [ ] Filter wilayah mengisolasi titik agen kota lain (agen kota lain tidak muncul).
- [ ] UI bernuansa dominan Biru dan Putih khas Radar Tulungagung (`#004B87`).
- [ ] Semua nama agen lama telah dihapus dan diganti dengan nama-nama khas Indonesia.
- [ ] `npm run build` atau `npm run dev` berjalan tanpa error.

---

## 📂 5. Rujukan Struktur File yang Akan Dimodifikasi / Dibuat

```
Geometric Agent Radar/
├── app/
│   ├── Http/Controllers/
│   │   └── AgentController.php         <-- [MODIFY] Tambah filter city, stats per wilayah, endpoint HQ
│   └── Models/
│       └── Agent.php                   <-- [MODIFY] Update fillable & scope kota
├── database/
│   ├── migrations/
│   │   └── 2026_08_24_142803_create_agents_table.php <-- [MODIFY] Tambah kolom city, district, village, phone
│   └── seeders/
│       └── AgentSeeder.php             <-- [MODIFY] Hapus data lama, isi agen Tulungagung, Blitar, Trenggalek
├── public/
│   └── data/
│       └── geojson/                    <-- [NEW] Data koordinat & batas wilayah, kecamatan, desa, HQ
│           ├── mataraman_regions.json
│           ├── districts.json
│           └── radar_hq.json
├── resources/
│   ├── css/
│   │   └── app.css                     <-- [MODIFY] Tema Biru-Putih Radar Tulungagung & marker styles
│   ├── js/
│   │   └── app.js                      <-- [MODIFY] Logic Leaflet 3 level zoom, marker HQ, modal agen, filter isolasi
│   └── views/
│       └── radar.blade.php             <-- [MODIFY] UI header Radar Tulungagung, filter bar, modal popup
├── issue.md                            <-- [NEW] File panduan implementasi lengkap ini
└── routes/
    └── web.php                         <-- [MODIFY] Route API pendukung
```

---
*Dokumen ini siap digunakan sebagai acuan kerja implementasi oleh developer maupun model AI.*
