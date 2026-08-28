<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing agents
        Agent::truncate();

        $agents = [
            // ==========================================
            // TULUNGAGUNG REGION (CABANG ID: 1)
            // ==========================================
            [
                'nama_agen' => 'Kios Koran Barokah Alun-Alun',
                'nama_pemilik' => 'Bambang Pamungkas',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0812-3344-5501',
                'alamat_lengkap' => 'Jl. RA Kartini No. 12, Kampungdalem, Kec. Tulungagung',
                'latitude' => -8.0655,
                'longitude' => 111.9015,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Loper Koran Gayatri Sentosa',
                'nama_pemilik' => 'Agus Setiawan',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0812-3344-5502',
                'alamat_lengkap' => 'Jl. Raya Boyolangu KM 4, Desa Serut, Kec. Boyolangu',
                'latitude' => -8.1150,
                'longitude' => 111.8980,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Toko Buku & Majalah Rejoagung',
                'nama_pemilik' => 'Siti Rahmawati',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0813-7788-9903',
                'alamat_lengkap' => 'Jl. Pahlawan No. 45, Desa Rejoagung, Kec. Kedungwaru',
                'latitude' => -8.0450,
                'longitude' => 111.9210,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Lapak Koran Pertigaan Ngunut',
                'nama_pemilik' => 'Hendra Wijaya',
                'tipe_agen' => 'Lapak Koran',
                'nomor_whatsapp' => '0857-1122-3304',
                'alamat_lengkap' => 'Jl. Raya Ngunut No. 88, Desa Kromasan, Kec. Ngunut',
                'latitude' => -8.1100,
                'longitude' => 112.0120,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Agen Koran Campurdarat Marmer',
                'nama_pemilik' => 'Eko Prasetyo',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0858-4455-6605',
                'alamat_lengkap' => 'Jl. Raya Campurdarat No. 23, Desa Gamping, Kec. Campurdarat',
                'latitude' => -8.1720,
                'longitude' => 111.8540,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Kios Berkah Koran Kauman',
                'nama_pemilik' => 'Dian Lestari',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0812-9900-1106',
                'alamat_lengkap' => 'Jl. KH Wahid Hasyim No. 10, Desa Bolorejo, Kec. Kauman',
                'latitude' => -8.0580,
                'longitude' => 111.8620,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Loper Koran Sumbergempol Timur',
                'nama_pemilik' => 'Wahyu Hidayat',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0821-3344-7707',
                'alamat_lengkap' => 'Jl. Stasiun Sumbergempol, Desa Bendilwungu, Kec. Sumbergempol',
                'latitude' => -8.1020,
                'longitude' => 111.9540,
                'status' => 'nonaktif',
                'cabang_id' => 1,
            ],
            [
                'nama_agen' => 'Kios Baca Pasar Rejotangan',
                'nama_pemilik' => 'Nurul Aini',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0878-5566-7708',
                'alamat_lengkap' => 'Kompleks Pasar Rejotangan Kios B-4, Desa Buntaran, Kec. Rejotangan',
                'latitude' => -8.1340,
                'longitude' => 112.0650,
                'status' => 'aktif',
                'cabang_id' => 1,
            ],

            // ==========================================
            // BLITAR REGION (CABANG ID: 2)
            // ==========================================
            [
                'nama_agen' => 'Loper Koran Stasiun Blitar Kota',
                'nama_pemilik' => 'Budi Santoso',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0812-4455-6611',
                'alamat_lengkap' => 'Jl. Mastrip No. 04, Kel. Kepanjenkidul, Kota Blitar',
                'latitude' => -8.0990,
                'longitude' => 112.1620,
                'status' => 'aktif',
                'cabang_id' => 2,
            ],
            [
                'nama_agen' => 'Kios Majalah & Koran Makam Bung Karno',
                'nama_pemilik' => 'Sri Wahyuni',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0813-2233-4412',
                'alamat_lengkap' => 'Jl. Ir. Soekarno No. 55, Kel. Bendogerit, Kec. Sananwetan, Kota Blitar',
                'latitude' => -8.0850,
                'longitude' => 112.1750,
                'status' => 'aktif',
                'cabang_id' => 2,
            ],
            [
                'nama_agen' => 'Agen Utama Koran Wlingi Sentral',
                'nama_pemilik' => 'Ahmad Fauzi',
                'tipe_agen' => 'Agen Utama',
                'nomor_whatsapp' => '0856-7788-9913',
                'alamat_lengkap' => 'Jl. Urip Sumoharjo No. 19, Kel. Beru, Kec. Wlingi, Kab. Blitar',
                'latitude' => -8.0820,
                'longitude' => 112.3180,
                'status' => 'aktif',
                'cabang_id' => 2,
            ],
            [
                'nama_agen' => 'Kios Koran Simpang Srengat',
                'nama_pemilik' => 'Rina Kusumawati',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0819-0011-2214',
                'alamat_lengkap' => 'Jl. Raya Srengat No. 72, Desa Kauman, Kec. Srengat, Kab. Blitar',
                'latitude' => -8.0610,
                'longitude' => 112.0680,
                'status' => 'aktif',
                'cabang_id' => 2,
            ],
            [
                'nama_agen' => 'Loper Koran Garum Makmur',
                'nama_pemilik' => 'Sugeng Prayitno',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0822-6677-8815',
                'alamat_lengkap' => 'Jl. Raya Garum No. 30, Desa Pojok, Kec. Garum, Kab. Blitar',
                'latitude' => -8.0750,
                'longitude' => 112.2210,
                'status' => 'nonaktif',
                'cabang_id' => 2,
            ],
            [
                'nama_agen' => 'Lapak Koran Pertigaan Kanigoro',
                'nama_pemilik' => 'Endang Sulistyowati',
                'tipe_agen' => 'Lapak Koran',
                'nomor_whatsapp' => '0857-3344-5516',
                'alamat_lengkap' => 'Jl. Kusuma Bangsa No. 14, Kel. Satreyan, Kec. Kanigoro, Kab. Blitar',
                'latitude' => -8.1280,
                'longitude' => 112.2150,
                'status' => 'aktif',
                'cabang_id' => 2,
            ],

            // ==========================================
            // TRENGGALEK REGION (CABANG ID: 3)
            // ==========================================
            [
                'nama_agen' => 'Kios Koran Alun-Alun Trenggalek',
                'nama_pemilik' => 'Tri Wibowo',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0812-7788-9921',
                'alamat_lengkap' => 'Jl. Pemuda No. 08, Kel. Surodakan, Kec. Trenggalek',
                'latitude' => -8.0520,
                'longitude' => 111.7110,
                'status' => 'aktif',
                'cabang_id' => 3,
            ],
            [
                'nama_agen' => 'Sub-Agen Loper Koran Panggul Pesisir',
                'nama_pemilik' => 'Dewi Anggraini',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0813-8899-0022',
                'alamat_lengkap' => 'Jl. Raya Panggul - Pacitan No. 44, Desa Wonocoyo, Kec. Panggul',
                'latitude' => -8.2450,
                'longitude' => 111.4520,
                'status' => 'aktif',
                'cabang_id' => 3,
            ],
            [
                'nama_agen' => 'Kios Koran Prigi Bahari Watulimo',
                'nama_pemilik' => 'Joko Susilo',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0852-1122-3323',
                'alamat_lengkap' => 'Kawasan Wisata Pantai Prigi, Desa Tasikmadu, Kec. Watulimo',
                'latitude' => -8.2810,
                'longitude' => 111.7280,
                'status' => 'aktif',
                'cabang_id' => 3,
            ],
            [
                'nama_agen' => 'Loper Koran Durenan Sejahtera',
                'nama_pemilik' => 'Maya Indrawati',
                'tipe_agen' => 'Sub-Agen Loper',
                'nomor_whatsapp' => '0877-4455-6624',
                'alamat_lengkap' => 'Jl. Raya Durenan - Tulungagung No. 16, Desa Malasan, Kec. Durenan',
                'latitude' => -8.0850,
                'longitude' => 111.7950,
                'status' => 'aktif',
                'cabang_id' => 3,
            ],
            [
                'nama_agen' => 'Kios Baca Karangan Sentra',
                'nama_pemilik' => 'Bagus Setiawan',
                'tipe_agen' => 'Kios Eceran',
                'nomor_whatsapp' => '0823-5566-7725',
                'alamat_lengkap' => 'Jl. Raya Karangan No. 35, Desa Karangan, Kec. Karangan',
                'latitude' => -8.0710,
                'longitude' => 111.6680,
                'status' => 'nonaktif',
                'cabang_id' => 3,
            ],
        ];

        foreach ($agents as $agent) {
            Agent::create($agent);
        }
    }
}
