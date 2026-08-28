<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cabangs = [
            [
                'id' => 1,
                'nama_cabang' => 'Kantor Radar Tulungagung',
                'kode_cabang' => 'tulungagung',
                'alamat' => 'Jl. I Gusti Ngurah Rai No. 34 / Jl. Jayeng Kusuma, Bago, Kec. Tulungagung',
                'telepon' => '(0355) 321888',
                'latitude' => -8.059625,
                'longitude' => 111.9071825,
                'warna' => '#004B87',
            ],
            [
                'id' => 2,
                'nama_cabang' => 'Kantor Radar Blitar',
                'kode_cabang' => 'blitar',
                'alamat' => 'Jl. Mastrip No. 12 / Jl. Kenanga, Kepanjenkidul, Kota Blitar',
                'telepon' => '(0342) 811234',
                'latitude' => -8.0931251,
                'longitude' => 112.1789795,
                'warna' => '#D90429',
            ],
            [
                'id' => 3,
                'nama_cabang' => 'Kantor Radar Trenggalek',
                'kode_cabang' => 'trenggalek',
                'alamat' => 'Jl. Panglima Sudirman / Jl. Brigjen Soetran No. 05, Trenggalek',
                'telepon' => '(0355) 791456',
                'latitude' => -8.0669383,
                'longitude' => 111.70837,
                'warna' => '#E5A900',
            ],
        ];

        foreach ($cabangs as $cabang) {
            Cabang::updateOrCreate(['id' => $cabang['id']], $cabang);
        }
    }
}
