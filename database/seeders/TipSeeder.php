<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tips')->insert([
            [
                'title' => '5 Cara Menabung yang Benar',
                'thumbnail'=> 'celengan.jpg',
                'url' => 'https://www.cimbniaga.co.id/id/inspirasi/perencanaan/cara-menabung-yang-benar-menurut-pakar-keuangan'     ,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Pahami Jenis-jenis Investasi',
                'thumbnail'=> 'investasi.jpg',
                'url' => 'https://www.cimbniaga.co.id/id/inspirasi/perencanaan/tujuan-investasi-yang-baik-seperti-apa'     ,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'aup Untung Selangit dari Investasi Risiko Tinggi',
                'thumbnail'=> 'investasi2.jpg',
                'url' => 'https://invest.cermati.com/artikel/investasi-risiko-tinggi'     ,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
