<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\District;

class StateDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "Johor" => ["Johor Bahru", "Muar", "Batu Pahat", "Kluang", "Pontian", "Segamat", "Kota Tinggi", "Mersing", "Tangkak", "Kulai"],
            "Kedah" => ["Kubang Pasu", "Langkawi", "Padang Terap", "Pokok Sena", "Kota Setar", "Pendang", "Yan", "Sik", "Kuala Muda", "Baling", "Kulim", "Bandar Baharu"],
            "Kelantan" => ["Kota Bharu", "Pasir Mas", "Tumpat", "Bachok", "Kuala Krai", "Machang", "Tanah Merah", "Jeli", "Pasir Puteh", "Gua Musang"],
            "Melaka" => ["Melaka Tengah", "Alor Gajah", "Jasin"],
            "Negeri Sembilan" => ["Seremban", "Port Dickson", "Rembau", "Jempol", "Tampin", "Kuala Pilah", "Jelebu"],
            "Pahang" => ["Kuantan", "Temerloh", "Bentong", "Raub", "Jerantut", "Pekan", "Rompin", "Maran"],
            "Perak" => ["Bagan Datuk", "Batang Padang", "Hilir Perak", "Hulu Perak", "Kampar", "Kerian", "Kinta", "Kuala Kangsar", "Larut, Matang & Selama", "Manjung", "Muallim", "Perak Tengah"],
            "Perlis" => ["Kangar", "Arau", "Padang Besar"],
            "Pulau Pinang" => ["Seberang Perai", "Timur Laut", "Barat Daya"],
            "Sabah" => ["Kota Kinabalu", "Tuaran", "Kota Belud", "Penampang", "Kota Belud", "Kota Marudu", "Pitas", "Ranau", "Papar", "Putatan", "Tambunan", "Kuala Penyu", "Beaufort", "Sipitang", "Tenom", "Keningau", "Nabawan", "Tongod", "Kalabakan", "Tawau", "Semporna", "Kunak", "Lahad Datu", "Kinabatangan", "Sandakan", "Telupid", "Beluran"],
            "Sarawak" => ["Kuching", "Sibu", "Miri", "Bintulu", "Serian", "Samarahan", "Kapit", "Sri Aman", "Betong", "Sarikei", "Mukah"],
            "Selangor" => ["Petaling", "Hulu Langat", "Sepang", "Klang", "Gombak", "Kuala Langat", "Kuala Selangor", "Sabak Bernam", "Hulu Selangor"],
            "Terengganu" => ["Kuala Terengganu", "Kemaman", "Dungun", "Marang", "Besut", "Setiu", "Hulu Terengganu", "Kuala Nerus"],
            "Kuala Lumpur" => ["Kuala Lumpur"],
            "Labuan" => ["Labuan"],
            "Putrajaya" => ["Putrajaya"]
        ];

        foreach ($data as $stateName => $districts) {
            $state = State::updateOrCreate(['name' => $stateName]);
            foreach ($districts as $districtName) {
                District::updateOrCreate([
                    'name' => $districtName,
                    'state_id' => $state->id
                ]);
            }
        }
    }
}
