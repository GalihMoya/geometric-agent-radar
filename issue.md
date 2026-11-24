# 📌 Rencana Implementasi & Panduan Fitur Proximity: Deteksi Lokasi Pemakai & Pencarian Agen Terdekat

**Dokumen:** `issue.md`  
**Fitur:** Titik Lokasi Pemakai (GPS & Manual), Pengukuran Jarak ke Agen/Cabang, dan Pencarian Agen Terdekat  
**Aplikasi:** Jawa Pos Radar Tulungagung - Geometric Agent Radar (Tulungagung • Blitar • Trenggalek)  
**Target Pembaca:** Junior Programmer / Budget AI Model / QA Tester  
**Branch:** `main` (Perubahan diuji secara lokal, **jangan langsung push ke remote repository sebelum diverifikasi**)

---

## 🎯 1. Ringkasan & Tujuan Fitur

Dokumen ini disusun sebagai panduan teknis dan tahapan perencanaan (*implementation plan*) yang terperinci agar seorang **Junior Programmer** maupun **model AI** dapat memahami, mereplikasi, menguji, dan melakukan *debugging* terhadap 3 fitur utama:

1. **Fitur Titik Lokasi Pemakai (User & Non-User):**
   - Mendeteksi posisi pemakai aplikasi (baik pengunjung umum/tamu maupun user/admin login) menggunakan **HTML5 Geolocation API**.
   - Menyediakan fitur cadangan (*fallback*) berupa **Tentukan Titik di Peta** (*interactive map click & draggable marker*) jika GPS tidak aktif, izin ditolak, atau diakses melalui jaringan lokal HTTP.
   - Menyimpan posisi sementara di `sessionStorage` agar tidak hilang saat halaman di-*refresh*.
2. **Fitur Pengukuran Jarak Terdekat ke Agen Koran maupun Kantor Cabang:**
   - Menghitung jarak garis lurus bola bumi (*Great-Circle Distance*) secara presisi menggunakan **Rumus Haversine** antara titik pemakai dengan setiap kios agen koran atau kantor cabang (HQ).
   - Menampilkan *badge* jarak interaktif (contoh: `450 m`, `2.4 km`) pada kartu agen di *sidebar* dan di dalam modal detail.
   - Menggambar garis rute visual (*dashed polyline*) di peta antara posisi pemakai dan kios/cabang yang dipilih.
   - Tombol satu-klik menuju navigasi **Google Maps Direction** dengan rute asal (*origin*) koordinat pemakai dan tujuan (*destination*) koordinat kios/cabang.
3. **Fitur Pencarian & Filter Agen Terdekat Berdasarkan Lokasi Pemakai:**
   - Filter radius interaktif dari posisi pemakai (`Semua Jarak`, `< 1 KM`, `< 3 KM`, `< 5 KM`, `< 10 KM`, `< 20 KM`, `< 35 KM`).
   - Fitur urutkan (*sorting*) agen dari yang paling dekat ke paling jauh.
   - Tombol aksi cepat: **"TEMUKAN KIOS TERDEKAT"** yang secara otomatis mencari agen aktif terdekat, memusatkan peta dengan animasi halus (*smooth flyTo*), menghubungkan garis rute, dan langsung membuka modal informasinya.

---

## 🏗️ 2. Arsitektur & Konsep Teknis

### A. Rumus Jarak Haversine (Great-Circle Distance)
Jarak dihitung langsung di sisi peramban (*client-side*) menggunakan radius rata-rata bumi $R = 6.371.000\text{ meter}$:
$$\Delta\varphi = (\text{lat}_2 - \text{lat}_1) \times \frac{\pi}{180}$$
$$\Delta\lambda = (\text{lon}_2 - \text{lon}_1) \times \frac{\pi}{180}$$
$$a = \sin^2\left(\frac{\Delta\varphi}{2}\right) + \cos(\varphi_1) \cdot \cos(\varphi_2) \cdot \sin^2\left(\frac{\Delta\lambda}{2}\right)$$
$$c = 2 \cdot \text{atan2}\left(\sqrt{a}, \sqrt{1-a}\right)$$
$$d = R \cdot c\quad (\text{meter})$$

**Implementasi JavaScript:**
```javascript
function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
    if (lat1 === null || lon1 === null || lat2 === null || lon2 === null) return null;
    const nLat1 = Number(lat1), nLon1 = Number(lon1);
    const nLat2 = Number(lat2), nLon2 = Number(lon2);
    if (isNaN(nLat1) || isNaN(nLon1) || isNaN(nLat2) || isNaN(nLon2)) return null;

    const R = 6371e3; // Radius bumi dalam meter
    const phi1 = (nLat1 * Math.PI) / 180;
    const phi2 = (nLat2 * Math.PI) / 180;
    const deltaPhi = ((nLat2 - nLat1) * Math.PI) / 180;
    const deltaLambda = ((nLon2 - nLon1) * Math.PI) / 180;

    const a =
        Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
        Math.cos(phi1) * Math.cos(phi2) *
        Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return Math.round(R * c);
}
```

### B. Format Tampilan Jarak
- Jarak $< 1000\text{ meter}$: Tampilkan dalam satuan meter (misal: `350 m`, `820 m`).
- Jarak $\ge 1000\text{ meter}$: Tampilkan dalam satuan kilometer dengan 1 desimal (misal: `1.4 km`, `12.8 km`).

### C. Alur Status Lokasi (*State Lifecycle*)
```
[Pengguna Membuka Web]
         │
         ├─► Cek sessionStorage ('radar_user_loc') ──► [Ada] ──► Render Marker Pemakai & Hitung Jarak
         │
         └─► [Belum Ada] ──► Tampilkan Kotak Prompt "PENCARIAN TERDEKAT" di Sidebar
                                 │
             ┌───────────────────┴───────────────────┐
             ▼                                       ▼
    [Klik Deteksi GPS]                     [Klik Tentukan di Peta]
             │                                       │
     Browser Minta Izin                 Aktifkan Map Pick Banner
             │                                       │
      ┌──────┴──────┐                         Klik Lokasi di Peta
      ▼             ▼                                │
   [Izinkan]    [Ditolak/Error]                      ▼
      │             │                     Set Koordinat Manual
      │             └─► Fallback Manual ─────────────┘
      ▼
 [Koordinat Didapat]
      │
      ├─► Simpan ke userLocation & sessionStorage
      ├─► Buat Marker Pemakai (Draggable + Radar Pulse)
      ├─► Buat Lingkaran Akurasi / Lingkaran Radius
      ├─► Buka Panel Kontrol Proximity (Radius & Sort)
      ├─► Hitung Jarak Seluruh Agen (Haversine)
      └─► Perbarui Tampilan Sidebar & Marker Peta
```

---

## 📂 3. Daftar File yang Terlibat

| File Path | Komponen | Peran Perubahan |
|-----------|----------|-----------------|
| `resources/views/radar.blade.php` | Blade View | UI Tombol GPS Navbar, Panel Proximity Sidebar (Prompt & Controls), Banner Pemilih Peta, Kontainer Jarak Modal Agen & HQ |
| `resources/js/app.js` | JavaScript | Logika Geolocation, Haversine, Marker Leaflet, Sort Terdekat, Radius Filter, Pencarian Agen Terdekat, dan Integrasi Modal |
| `resources/css/app.css` | Styling | CSS User Beacon Pulse, Animasi Ping Radar, Badge Jarak, Cursor Crosshair Pemilih Peta, Banner Responsif |
| `routes/web.php` | Routing | Mengalirkan data endpoint `/api/agents` dan `/api/hq` (sudah tersedia) |
| `app/Http/Controllers/AgentController.php` | Controller | Menyediakan data latitude & longitude kios agen dan 3 kantor biro cabang (sudah tersedia) |

---

## 🛠️ 4. Panduan Perubahan Baris Demi Baris (Step-by-Step)

### Tahap 1: Struktur Tampilan HTML/Blade (`resources/views/radar.blade.php`)

1. **Tombol "LOKASI SAYA" di Navbar Utama:**
   - Diberikan atribut `id="btn-user-location"`, ikon crosshair `id="icon-user-location"`, dan teks responsif `id="text-user-location"`.
2. **Panel Proximity Sidebar (Dua State: Prompt vs Aktif):**
   - Saat lokasi **belum aktif**: Tampilkan `#user-location-prompt` dengan tombol `Deteksi Lokasi GPS` dan `Tentukan Titik di Peta`.
   - Saat lokasi **sudah aktif**: Tampilkan `#user-proximity-controls` dengan *badge* status (`GPS AKTIF` atau `MANUAL`), tombol reset titik (`#btn-clear-user-location`), *dropdown* radius (`#filter-radius`), tombol urutkan (`#btn-sort-nearest`), dan tombol aksi cepat (`#btn-find-closest-agent`).
3. **Banner Panduan Klik Peta (`#map-pick-banner`):**
   - Diletakkan di atas kontainer peta `#radar-map` dengan posisi melayang tengah (*floating pill*). Muncul saat mode klik peta aktif dengan tombol batal.
4. **Kontainer Jarak di Modal Detail Kios Agen & Modal HQ:**
   - Disediakan `#modal-agent-distance-box-active` untuk menampilkan jarak riil dan tautan Google Maps jika lokasi sudah ada.
   - Disediakan `#modal-agent-distance-box-prompt` dengan tombol kecil `Deteksi Lokasi` jika pengunjung membuka modal sebelum mengaktifkan GPS.

---

### Tahap 2: Logika JavaScript (`resources/js/app.js`)

1. **Inisialisasi State Global Proximity:**
   ```javascript
   let userLocation = {
       lat: null,
       lng: null,
       accuracy: null,
       marker: null,
       accuracyCircle: null,
       radiusCircle: null,
       isManual: false
   };
   let userSelectedRadius = 'all'; // 'all', 1000, 3000, 5000, 10000, 20000, 35000
   let isSortedByNearest = true;
   let isPickingLocationOnMap = false;
   let userDistancePolyline = null;
   let currentOpenedModalTarget = null;
   ```

2. **Fungsi Deteksi Lokasi (`detectUserLocation`):**
   - Menggunakan `navigator.geolocation.getCurrentPosition()`.
   - Konfigurasi: `{ enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }`.
   - Jika berhasil: memanggil `setUserLocation(lat, lng, accuracy, false)`.
   - Jika gagal (izin ditolak / HTTP / timeout): menampilkan pesan penjelasan dan langsung mengaktifkan mode manual `startMapPickMode()`.

3. **Fungsi Set Lokasi Terpadu (`setUserLocation`):**
   - Menyimpan koordinat ke objek `userLocation` dan `sessionStorage`.
   - Mengubah tampilan tombol navbar menjadi hijau (`border-success`, ikon `bi-geo-alt-fill`).
   - Menyembunyikan `#user-location-prompt` dan membuka `#user-proximity-controls`.
   - Memperbarui status *badge* (`GPS AKTIF (±15m)` atau `MANUAL (GESER PIN)`).
   - Memanggil `renderUserLocationMarker()`, `processAgentsProximity()`, dan memperbarui modal yang sedang terbuka jika ada.

4. **Fungsi Marker Pemakai (`renderUserLocationMarker`):**
   - Dibuat dengan `L.divIcon` beranimasi radar ping biru (`user-beacon-pulse` + `user-beacon-dot`).
   - Properti `draggable: true` sehingga pengguna dapat menggeser pin secara presisi.
   - Event `dragend`: memperbarui koordinat pemakai, status menjadi manual, dan otomatis menghitung ulang jarak semua agen.

5. **Fungsi Penghitungan & Filter Proximity (`processAgentsProximity`):**
   - Menghitung properti `agent.distance` untuk semua kios dalam `currentAgents`.
   - Memfilter berdasarkan `userSelectedRadius` jika tidak sama dengan `'all'`.
   - Mengurutkan `filteredList` berdasarkan jarak terkecil ke terbesar jika `isSortedByNearest` aktif.
   - Memperbarui marker peta dan daftar kartu di *sidebar* secara serentak.

6. **Fungsi Aksi Cepat "TEMUKAN KIOS TERDEKAT" (`findClosestAgent`):**
   - Jika lokasi belum aktif, memanggil deteksi GPS terlebih dahulu.
   - Mengambil agen dengan nilai `agent.distance` terendah dari daftar agen wilayah aktif.
   - Melakukan `map.flyTo([agent.latitude, agent.longitude], 15)`.
   - Menggambar `drawRoutePolyline` garis putus-putus biru dari titik pemakai ke agen tersebut.
   - Membuka modal detail agen secara otomatis.

7. **Fungsi Visual Garis Rute (`drawRoutePolyline`):**
   - Menggunakan `L.polyline([origin, dest], { color: '#0073E6', weight: 3, dashArray: '6, 8', opacity: 0.8 })`.

8. **Fungsi Reset Lokasi (`clearUserLocation`):**
   - Menghapus marker pemakai, lingkaran akurasi, lingkaran radius, dan garis rute dari peta.
   - Menghapus kunci `radar_user_loc` dari `sessionStorage`.
   - Mengembalikan tombol navbar dan tampilan sidebar ke kondisi awal tanpa jarak.

---

### Tahap 3: Desain CSS & Animasi (`resources/css/app.css`)

1. **Animasi Radar Ping Titik Pemakai:**
   ```css
   .user-beacon-wrap {
       position: relative;
       width: 32px;
       height: 32px;
       display: flex;
       align-items: center;
       justify-content: center;
   }
   .user-beacon-dot {
       width: 16px;
       height: 16px;
       background-color: #0073E6;
       border: 3px solid #FFFFFF;
       border-radius: 50%;
       box-shadow: 0 2px 10px rgba(0, 115, 230, 0.7);
       z-index: 3;
   }
   .user-beacon-pulse {
       position: absolute;
       width: 32px;
       height: 32px;
       border-radius: 50%;
       background-color: rgba(0, 115, 230, 0.4);
       animation: user-radar-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
       z-index: 1;
   }
   @keyframes user-radar-ping {
       0% { transform: scale(0.5); opacity: 0.9; }
       100% { transform: scale(2.4); opacity: 0; }
   }
   ```
2. **Kursor Pemilih Peta (`map-picking-cursor`):**
   ```css
   .map-picking-cursor,
   .map-picking-cursor .leaflet-grab,
   .map-picking-cursor .leaflet-interactive {
       cursor: crosshair !important;
   }
   ```
3. **Pill Badge Jarak Agen:**
   ```css
   .badge-distance-pill {
       background-color: #EBF4FC;
       color: #004B87;
       font-size: 0.7rem;
       font-family: var(--font-mono);
       padding: 3px 8px;
       border-radius: 50rem;
       border: 1px solid #BAE0FD;
       display: inline-flex;
       align-items: center;
       gap: 3px;
       font-weight: 600;
   }
   ```

---

## 🔍 5. Panduan Debugging & Penanganan Masalah (Troubleshooting Guide)

Berikut adalah daftar potensi kendala saat pengujian atau *deployment*, beserta solusi konkretnya:

### Kasus A: Geolocation Ditolak atau Error di Jaringan Lokal (HTTP Non-Localhost)
- **Gejala:** Browser memunculkan alert "Tidak dapat mengakses lokasi Anda" atau `PERMISSION_DENIED`.
- **Penyebab:** Standar keamanan browser modern (Chrome, Edge, Safari, Firefox) hanya mengizinkan `navigator.geolocation` pada **Secure Context** (`https://` atau `http://localhost` / `http://127.0.0.1`). Mengakses web dari HP lewat IP WiFi (misal: `http://10.88.45.178:8000`) otomatis memblokir Geolocation API.
- **Perintah Debug & Solusi:**
  1. Fitur cadangan manual otomatis aktif: sistem langsung mengaktifkan `#map-pick-banner`.
  2. Pengguna cukup mengklik langsung titik mana saja di peta Mataraman atau mengklik tombol **"Tentukan Titik di Peta"**.
  3. Pin lokasi langsung tertanam dan dapat digeser kapan saja (*draggable*).
  4. Untuk mengaktifkan GPS riil di HP via LAN tanpa HTTPS, buka `chrome://flags/#unsafely-treat-insecure-origin-as-secure` di browser Chrome HP, tambahkan URL `http://10.88.45.178:8000`, lalu aktifkan (*enable*).

### Kasus B: Data Jarak Bernilai `NaN` atau `-`
- **Penyebab:** Ada baris agen di database yang nilai `latitude` atau `longitude`-nya `NULL`, `0`, atau string kosong.
- **Perintah Debug & Solusi:**
  Fungsi `calculateHaversineDistance()` sudah diproteksi dengan:
  ```javascript
  if (lat1 === null || lon1 === null || lat2 === null || lon2 === null) return null;
  if (isNaN(nLat1) || isNaN(nLon1) || isNaN(nLat2) || isNaN(nLon2)) return null;
  ```
  Di kartu *sidebar*, jika `distance === null`, elemen badge jarak tidak akan dirender sehingga tampilan tetap rapi tanpa merusak layout.

### Kasus C: Garis Rute (*Polyline*) Tidak Bersih Saat Menutup Modal
- **Penyebab:** Event modal Bootstrap tidak terpanggil atau objek layer Leaflet masih tertaut di peta.
- **Solusi:**
  Event listener modal Bootstrap:
  ```javascript
  document.getElementById('agentDetailModal').addEventListener('hidden.bs.modal', () => {
      currentOpenedModalTarget = null;
      if (userDistancePolyline && map) {
          map.removeLayer(userDistancePolyline);
          userDistancePolyline = null;
      }
  });
  ```
  Fungsi `drawRoutePolyline()` juga selalu memanggil `map.removeLayer(userDistancePolyline)` sebelum menggambar garis baru.

### Kasus D: Build Frontend Vite Mengalami Parse Error
- **Perintah Debug:**
  Jalankan di PowerShell:
  ```powershell
  npm run build
  ```
  Jika muncul pesan kesalahan syntax JavaScript, periksa keseimbangan kurung kurawal `{ }` pada `resources/js/app.js`. Build yang sukses akan menghasilkan status hijau:
  ```text
  ✓ built in X.XXs
  public/build/assets/app-XXXXXXXX.css
  public/build/assets/app-XXXXXXXX.js
  ```

---

## 🧪 6. Checklist Verifikasi & Pengujian Lokal (Tanpa Push ke GitHub)

Gunakan daftar periksa (*checklist*) di bawah ini untuk memastikan seluruh fungsi berhasil sebelum melaporkan hasil kepada pengguna:

- [x] **1. Build Aset:** Jalankan `npm run build` dan pastikan berhasil tanpa error.
- [x] **2. Tampilan Awal (Sebelum Lokasi Aktif):**
  - Kotak "PENCARIAN TERDEKAT" muncul di *sidebar* dengan tombol "Deteksi Lokasi GPS" dan "Tentukan Titik di Peta".
  - Kartu agen di *sidebar* menampilkan nama, tipe, status, alamat, dan tombol WA tanpa badge jarak.
- [x] **3. Fitur Deteksi GPS (Klik "LOKASI SAYA" / "Deteksi Lokasi GPS"):**
  - Browser meminta izin lokasi.
  - Pin pemakai warna biru dengan animasi pulsa radar muncul di koordinat pengguna.
  - Lingkaran akurasi transparan muncul mengelilingi titik.
  - Tombol navbar berubah menjadi hijau bertuliskan "LOKASI ANDA".
  - Status panel berubah menjadi "GPS AKTIF (±..m)".
  - Kartu agen di *sidebar* otomatis terisi badge jarak (contoh: `1.2 km`).
  - Posisi tersimpan di `sessionStorage` (cek dengan refresh halaman, pin tetap ada).
- [x] **4. Fitur Penentuan Lokasi Manual:**
  - Klik "Tentukan Titik di Peta" atau klik langsung di area peta.
  - Banner "Klik pada peta untuk menempatkan titik lokasi Anda" muncul.
  - Kursor berubah menjadi crosshair.
  - Begitu peta diklik, pin pemakai tertanam di titik tersebut.
  - Status badge menunjukkan "MANUAL (GESER PIN)".
  - Geser (*drag*) pin ke titik lain: jarak semua agen di *sidebar* langsung terhitung ulang seketika.
- [x] **5. Fitur Pengukuran Jarak ke Kios Agen Terpilih:**
  - Klik salah satu kartu agen di *sidebar* atau marker agen di peta.
  - Garis putus-putus biru (*dashed polyline*) otomatis terhubung dari posisi pemakai ke kios tersebut.
  - Modal detail agen terbuka dan menampilkan kotak biru berisi: **"Jarak dari Lokasi Anda: X.X km"**.
  - Klik tombol "Dapatkan Rute" di modal: tab baru Google Maps Direction terbuka dengan koordinat pemakai sebagai asal dan kios sebagai tujuan.
- [x] **6. Fitur Pengukuran Jarak ke Kantor Cabang (HQ):**
  - Klik salah satu dari 3 Kantor Cabang Radar (Tulungagung, Blitar, Trenggalek) di peta.
  - Garis rute visual terhubung dari posisi pemakai ke kantor biro tersebut.
  - Modal cabang menampilkan jarak riil dan rute Google Maps.
- [x] **7. Fitur Filter Radius:**
  - Ubah dropdown dari "Semua Jarak" ke `< 3 KM` atau `< 5 KM`.
  - Lingkaran batas radius berwarna hijau muncul di sekitar posisi pemakai.
  - Kios yang berada di luar radius otomatis tersembunyi dari daftar dan marker klaster peta.
  - Jumlah agen pada badge filter berkurang sesuai kios yang masuk radius.
- [x] **8. Fitur Urutkan Terdekat (*Sort Nearest*):**
  - Klik tombol "Terdekat" di samping dropdown radius.
  - Daftar agen diurutkan secara presisi mulai dari meter/kilometer terkecil ke terbesar.
- [x] **9. Fitur Aksi Cepat "TEMUKAN KIOS TERDEKAT":**
  - Klik tombol hijau "TEMUKAN KIOS TERDEKAT".
  - Peta otomatis bergerak (*flyTo*) ke kios yang paling dekat dari posisi pemakai.
  - Garis rute visual terhubung langsung.
  - Modal detail kios tersebut langsung terbuka di layar.
- [x] **10. Fitur Reset / Hapus Titik Lokasi:**
  - Klik tombol ikon silang merah `x` di samping status GPS.
  - Pin pemakai, lingkaran radius, dan garis rute langsung terhapus dari peta.
  - Tampilan *sidebar* kembali ke mode prompt awal.

---

> ⚠️ **Catatan Penting Pengembang:**  
> Sesuai instruksi pengguna, **JANGAN melakukan perintah `git push origin main`**. Biarkan perubahan tersimpan pada branch lokal agar pengguna dapat membuka peramban, meninjau interaksi peta, dan memverifikasi seluruh fungsi secara langsung.

