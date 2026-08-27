# Issue: Perubahan Lokasi Headquarter (Kantor Cabang) Radar

## Deskripsi
Lokasi Headquarter (HQ) atau Kantor Pusat Radar untuk wilayah Tulungagung, Blitar, dan Trenggalek perlu diperbarui agar sesuai dengan titik lokasi yang ada di Google Maps terbaru.

Berikut adalah tautan referensi lokasi Google Maps yang baru:
- **Radar Tulungagung**: [Google Maps](https://www.google.com/maps/place/Jawa+Pos+Radar+Tulungagung/@-8.058933,111.9062348,16z/data=!4m6!3m5!1s0x2e78e320dc2b1745:0x1172f903156039bc!8m2!3d-8.059625!4d111.9071825!16s%2Fg%2F11cnbh7b5g?entry=ttu&g_ep=EgoyMDI2MDgyNC4wIKXMDSoASAFQAw%3D%3D)
- **Radar Blitar**: [Google Maps](https://www.google.com/maps/place/Jawa+Pos+Radar+Blitar/@-8.0931251,112.1789795,17z/data=!3m1!4b1!4m6!3m5!1s0x2e78ec6f3fb12493:0xc163873ffb26aa21!8m2!3d-8.0931251!4d112.1789795!16s%2Fg%2F11dxq8cb6x?entry=ttu&g_ep=EgoyMDI2MDgyNC4wIKXMDSoASAFQAw%3D%3D)
- **Radar Trenggalek**: [Google Maps](google.com/maps/place/Jawa+Pos+Radar+Trenggalek/@-8.0670631,111.6993701,16z/data=!4m7!3m6!1s0x2e7905289af6ea97:0x11b51c5a3ffa2e7d!8m2!3d-8.0669383!4d111.70837!15sChByYWRhciB0cmVuZ2dhbGVrkgENbWVkaWFfY29tcGFueeABAA!16s%2Fg%2F1hm3lh0qw?entry=tts&g_ep=EgoyMDI2MDgyNC4wIPu8ASoASAFQAw%3D%3D&skid=6299b6ec-ddca-4523-9164-8b5b53bee089)

### Ekstraksi Koordinat Baru (Latitude, Longitude)
Dari link di atas, didapatkan koordinat akurat berikut:
1. **Tulungagung**
   - **Lama**: `-8.0645, 111.9025`
   - **Baru**: `-8.059625, 111.9071825`
2. **Blitar**
   - **Lama**: `-8.0983, 112.1681`
   - **Baru**: `-8.0931251, 112.1789795`
3. **Trenggalek**
   - **Lama**: `-8.0506, 111.7145`
   - **Baru**: `-8.0669383, 111.70837`

---

## Tahapan Pengerjaan (Langkah demi Langkah)

Untuk AI atau Junior Programmer yang bertugas, perhatikan bahwa data koordinat HQ ini tersebar di berbagai *file* (JSON, seeder, controller, view, dan test). 

Gunakan fitur _Global Search_ (atau `grep_search`) di *code editor* Anda untuk mencari angka koordinat lama, lalu gantilah dengan angka koordinat yang baru.

### Langkah 1: Update Data GeoJSON (Front-End Map)
Ubah array koordinat `[Longitude, Latitude]` pada file JSON berikut. *(Perhatikan formatnya dibalik: Bujur dulu, baru Lintang)*
1. Buka file `public/data/geojson/radar_hq.json`
2. Buka file `public/data/geojson/mataraman_regions.json` (pada properties `hq_coords`)
   - Cari dan ganti `[111.9025, -8.0645]` menjadi `[111.9071825, -8.059625]`
   - Cari dan ganti `[112.1681, -8.0983]` menjadi `[112.1789795, -8.0931251]`
   - Cari dan ganti `[111.7145, -8.0506]` menjadi `[111.70837, -8.0669383]`

### Langkah 2: Update Data Backend (Seeder & Controller)
Ubah nilai `latitude` dan `longitude` di file-file PHP berikut:
1. `database/seeders/CabangSeeder.php`
2. `app/Http/Controllers/AgentController.php` (pada default map center saat create)
   - Ganti angka `-8.0645` menjadi `-8.059625` dan `111.9025` menjadi `111.9071825` (dan seterusnya untuk Blitar dan Trenggalek).

### Langkah 3: Update View (Admin Maps Defaults)
Ubah titik tengah peta bawaan pada form JavaScript.
1. Buka `resources/views/admin/agents/create.blade.php`
2. Buka `resources/views/admin/agents/edit.blade.php`
   - Di dalam tag `<script>`, cari object `const defaultLocations = { tulungagung: [...], blitar: [...], trenggalek: [...] };` dan perbarui koordinatnya.

### Langkah 4: Update Unit Tests & Dokumentasi
1. Buka `tests/Feature/RadarMataramanTest.php` dan `tests/Feature/AdminAgentCrudTest.php`
   - Perbarui angka koordinat dalam blok _assertion_ `->assertEquals(-8.0645, ...)` atau data *factory* `['latitude' => -8.064500]` ke angka koordinat yang baru.
2. Buka `README.md`
   - Ganti referensi koordinat lama di bagian daftar HQ.

### Langkah 5: Verifikasi
Setelah semua file di atas diubah, jalankan pengujian berikut untuk memastikan tidak ada yang terlewat:
```bash
php artisan test
```
Pastikan semua *test* menghasilkan status **PASS**.
