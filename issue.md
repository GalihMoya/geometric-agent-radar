# Issue: Penambahan Fitur Rute dan Input Link Google Maps

## Deskripsi
Terdapat dua penambahan fitur utama yang perlu diimplementasikan pada aplikasi:
1. **Tombol "Dapatkan Rute" pada Popup Peta**: Menambahkan tombol/link pada popup marker agen di peta yang akan mengarahkan pengguna langsung ke Google Maps untuk rute perjalanan.
2. **Input Link Google Maps pada Form**: Menambahkan kolom input baru pada form Tambah Agen dan Edit Agen agar admin dapat langsung menempelkan (paste) tautan Google Maps, yang kemudian akan mempermudah pengisian lokasi.

---

## Tahapan Pengerjaan (Langkah demi Langkah)

Instruksi di bawah ini disusun sedemikian rupa agar detail dan terstruktur, sehingga akan sangat mudah diikuti oleh Junior Programmer maupun AI Assistant saat proses pengkodean (coding).

### Fitur 1: Tombol "Dapatkan Rute" pada Popup Agen di Peta

**Tujuan:** Saat marker agen diklik pada peta, popup informasi yang muncul harus memuat tombol "Dapatkan Rute" yang apabila diklik akan membuka tab baru ke arah rute Google Maps.

**Langkah-langkah:**
1. **Identifikasi File JavaScript/View Peta:**
   - Cari file JavaScript atau file view (HTML/Blade/PHP) yang bertanggung jawab untuk menampilkan peta dan me-render (membuat) marker lokasi agen.
   - Temukan baris kode yang mengatur isi/konten HTML dari popup marker (biasanya menggunakan fungsi seperti `bindPopup()` pada Leaflet.js atau `InfoWindow` pada Google Maps API).
2. **Penambahan Elemen HTML untuk Tombol:**
   - Di dalam string atau template literal HTML popup tersebut, tambahkan tag tautan `<a>` baru yang bergaya seperti tombol.
   - Gunakan format URL rute Google Maps: `https://www.google.com/maps/dir/?api=1&destination={LATITUDE},{LONGITUDE}`.
   - **Contoh Implementasi:**
     ```html
     <a href="https://www.google.com/maps/dir/?api=1&destination=${agent.latitude},${agent.longitude}" target="_blank" class="btn btn-primary btn-sm mt-2">
        📍 Dapatkan Rute
     </a>
     ```
   - *Catatan Kritis:* Pastikan variabel `${agent.latitude}` dan `${agent.longitude}` disesuaikan dengan nama variabel objek data yang benar di dalam iterasi (loop) marker Anda.
3. **Pengujian Terarah (Testing):**
   - Buka halaman utama peta pada browser.
   - Klik salah satu pin/marker agen.
   - Klik tombol "Dapatkan Rute". Validasi bahwa browser membuka tab baru yang menampilkan titik tujuan yang akurat di Google Maps.

---

### Fitur 2: Tempel (Paste) Tautan Google Maps pada Form

**Tujuan:** Menambahkan fitur agar admin hanya perlu melakukan _paste_ (tempel) URL lokasi Google Maps, dan sistem secara otomatis mengekstrak Latitude serta Longitude untuk mengisi formulir koordinat secara otomatis (auto-fill).

**Langkah-langkah:**
1. **Modifikasi Antarmuka Pengguna (UI) Form Tambah/Edit:**
   - Buka file view yang memuat form **Tambah Agen** dan **Edit Agen**.
   - Tambahkan elemen `<input>` teks baru tepat di atas input "Latitude" dan "Longitude" yang sudah ada.
   - **Contoh HTML:**
     ```html
     <div class="form-group mb-3">
         <label for="gmaps_link">Tempel Tautan (Link) Google Maps</label>
         <input type="url" id="gmaps_link" class="form-control" placeholder="Contoh: https://www.google.com/maps/place/.../@-6.200,106.816,15z/...">
         <small class="text-muted">Tempelkan link URL dari browser (bukan link share pendek) untuk mendapatkan koordinat otomatis.</small>
     </div>
     ```
2. **Pembuatan Logika Ekstraksi (JavaScript):**
   - Di bagian bawah file view tersebut, buat blok `<script>` baru.
   - Tambahkan event listener untuk memantau perubahan teks (event `input` atau `paste`) pada elemen `#gmaps_link`.
   - Gunakan _Regular Expression_ (RegEx) untuk mendeteksi pola koordinat (`@lat,lng`) dari URL yang diinputkan.
   - **Contoh Script Logika:**
     ```javascript
     document.getElementById('gmaps_link').addEventListener('input', function(e) {
         const url = e.target.value;
         
         // RegEx mendeteksi pola @latitude,longitude di URL Maps
         const regex = /@(-?\d+\.\d+),(-?\d+\.\d+)/;
         const match = url.match(regex);
         
         if (match && match.length >= 3) {
             const lat = match[1];
             const lng = match[2];
             
             // Auto-fill ke input koordinat form (Sesuaikan ID element-nya!)
             document.getElementById('latitude').value = lat;
             document.getElementById('longitude').value = lng;
             
             // Opsi UX: Berikan feedback visual bahwa koordinat berhasil diekstrak
             alert('Koordinat berhasil diekstrak dari link!');
         }
     });
     ```
   - *Catatan Kritis:* Pastikan ID `latitude` dan `longitude` di dalam skrip JavaScript sesuai dengan atribut `id` pada form input aplikasi yang sebenarnya.
3. **Pengujian Terarah (Testing):**
   - Buka halaman form Tambah Agen.
   - Buka Google Maps, cari sebuah lokasi, lalu **salin (copy) link dari address bar atas browser**.
   - Paste link ke field yang baru dibuat.
   - Validasi bahwa nilai Latitude dan Longitude seketika terisi di form yang sesuai.

---

## ⚠️ Batasan & Perhatian Khusus
- Untuk Fitur 2, metode ekstraksi via JavaScript (Frontend) mengandalkan **Link Panjang (Full URL)** dari address bar browser karena koordinat terlihat jelas dalam bentuk teks (`@lat,lng`). 
- Apabila Admin menempelkan **Link Pendek (Short URL)** dari tombol "Share" (misal: `https://maps.app.goo.gl/xxx`), skrip ini tidak akan bisa membacanya karena koordinat disembunyikan dalam URL sebelum _redirect_. 
- Solusi untuk saat ini adalah dengan memberikan panduan visual (`<small>`) kepada admin untuk menyalin langsung dari Address Bar browser agar praktis dan cepat, bukan via tombol Share. Bila URL pendek mutlak ingin di-_support_ ke depannya, implementasinya perlu berpindah menggunakan validasi Backend API.
