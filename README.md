# 📡 Geometric Agent Radar - Jawa Pos Radar Tulungagung
### Tactical Geospatial Surveillance System (Biro Mataraman: Tulungagung • Blitar • Trenggalek)

![Radar Tulungagung](https://img.shields.io/badge/Radar-Tulungagung%20(Jawa%20Pos)-004B87?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel)
![Leaflet.js](https://img.shields.io/badge/Leaflet.js-1.9.4-199900?style=for-the-badge&logo=leaflet)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap)

---

## 📌 Ringkasan Proyek

**Geometric Agent Radar** adalah platform pemantauan intelijen geografis (*geospatial surveillance radar*) berbasis web yang dirancang khusus untuk operasional **Jawa Pos Radar Tulungagung** di 3 wilayah Mataraman Jawa Timur:
1. 🔵 **Kabupaten Tulungagung** (Warna Identitas: **Biru** `#004B87`)
2. 🔴 **Kabupaten & Kota Blitar** (Warna Identitas: **Merah** `#D90429`)
3. 🟡 **Kabupaten Trenggalek** (Warna Identitas: **Kuning** `#E5A900`)

---

## 🗺️ Fitur Utama

- 📍 **Hierarchical Geospatial Drill-Down**:
  - **Level Wilayah (Kabupaten/Kota)**: Poligon batas administratif dan visualisasi landmark kantor utama Radar (*HQ Beacons*).
  - **Level Kecamatan (Districts)**: Batas poligon dan pin label interaktif (27 kecamatan).
  - **Level Desa (Villages)**: Titik desa/kelurahan dengan polygon highlight saat diklik.
- 🏢 **3 Kantor Utama Radar (Landmark HQ)**:
  - **Radar Tulungagung HQ**: Jl. I Gusti Ngurah Rai No. 34 / Jayeng Kusuma, Bago (`[-8.059625, 111.9071825]`)
  - **Radar Blitar HQ**: Jl. Mastrip No. 12 / Kenanga, Kepanjenkidul (`[-8.0931251, 112.1789795]`)
  - **Radar Trenggalek HQ**: Jl. Brigjen Soetran No. 05, Trenggalek (`[-8.0669383, 111.70837]`)
- 👥 **27 Agen Lapangan Terdistribusi**:
  - Penamaan otentik Indonesia dengan status operasional (*Active, Patrol, Alert, Standby*).
  - Spesialisasi peran (*Tactical Lead, Field Reporter, Scout Drone, Investigative Recon, Traffic Patrol*).
- 🗂️ **Sistem Isolasi Area Agen**:
  - Filter interaktif berdasarkan wilayah yang mengisolasi marker agen kota lain.
- 📱 **Modal Detail Agen Responsif**:
  - Profil agen, indikator kekuatan sinyal real-time, koordinat GPS presisi, kontak telepon langsung, dan deskripsi tugas.
- 🎨 **Tema Visual Khas Radar Tulungagung**:
  - Desain antarmuka modern *Blue-White Clean Slate* dengan tipografi Inter, Plus Jakarta Sans, dan JetBrains Mono.

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Kebutuhan Sistem
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL / MariaDB (XAMPP)

### 2. Langkah Setup
```bash
# 1. Clone repository & masuk ke direktori
git clone https://github.com/GalihMoya/geometric-agent-radar.git
cd "Geometric Agent Radar"

# 2. Install dependencies PHP & Node
composer install
npm install

# 3. Konfigurasi Environment (.env)
cp .env.example .env
php artisan key:generate

# 4. Migrasi Database & Seeding Agen Mataraman
php artisan migrate:fresh --seed

# 5. Build Asset Frontend (Vite)
npm run build

# 6. Jalankan Server Lokal
php artisan serve
```

Akses aplikasi pada browser di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Automated Tests

Aplikasi dilengkapi dengan rangkaian automated test menggunakan PHPUnit:
```bash
php artisan test
```

---

## 📂 Struktur Data Geografis (GeoJSON)
File spasial tersimpan di folder `public/data/geojson/`:
- `mataraman_regions.json` - Batas kabupaten Tulungagung, Blitar, Trenggalek
- `districts.json` - Poligon dan titik pusat 27 kecamatan
- `villages.json` - Titik desa/kelurahan sampel untuk drill-down
- `radar_hq.json` - Koordinat dan metadata 3 kantor pusat Radar
