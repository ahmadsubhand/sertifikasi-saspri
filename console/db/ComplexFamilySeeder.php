<?php

namespace console\db;

use Yii;
use Faker\Factory;
use yii\db\Connection;

/**
 * Class ComplexFamilySeeder
 * * Dataset realistis dan beragam untuk simulasi silsilah sapi Digiternak.
 */
class ComplexFamilySeeder
{
    private Connection $db;
    private int $eidSeq = 1;
    private array $livestock = [];
    private array $maleIds = [];
    private array $femaleIds = [];
    private int $user_id = 14; // Budiman Sujatmiko - SASPRI 1

    public function __construct()
    {
        $this->db = Yii::$app->db;
    }

    public function run()
    {
        echo "Memulai seeding Complex Family...\n";
        
        // Bersihkan data lama agar tidak duplikat
        $this->purge();

        $faker = Factory::create('id_ID');

        // --- 1. Buat 10 Kandang ---
        $cageIds = [];
        for ($i = 0; $i < 10; $i++) {
            $this->db->createCommand()->insert('{{%cage}}', [
                'user_id' => $this->user_id,
                'name' => "Kandang " . chr(65 + $i),
                'location' => $faker->city,
                'capacity' => 30,
                'description' => 'Kandang Sapi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])->execute();
            
            $cageIds[] = (int)$this->db->getLastInsertID();
        }

        // Helper closures untuk mempercepat pembuatan sapi
        $addCow = function(string $gender, int $cageId, array $opt = []) use ($faker) {
            return $this->createCow($faker, $gender, $cageId, $opt);
        };

        // --- 10 Sapi Mandiri ---
        for ($i = 0; $i < 10; $i++) {
            $addCow($faker->randomElement(['Jantan', 'Betina']), $cageIds[$i % 10]);
        }

        // --- 15 Sapi Hanya Ayah ---
        for ($i = 0; $i < 15; $i++) {
            $fatherId = $this->randomMale();
            $addCow($faker->randomElement(['Jantan', 'Betina']), $cageIds[array_rand($cageIds)], ['father_id' => $fatherId]);
        }

        // --- 15 Sapi Hanya Ibu ---
        for ($i = 0; $i < 15; $i++) {
            $motherId = $this->randomFemale();
            $addCow($faker->randomElement(['Jantan', 'Betina']), $cageIds[array_rand($cageIds)], ['mother_id' => $motherId]);
        }

        // --- 20 Sapi Ayah & Ibu ---
        for ($i = 0; $i < 20; $i++) {
            $fatherId = $this->randomMale();
            $motherId = $this->randomFemale();
            $addCow($faker->randomElement(['Jantan', 'Betina']), $cageIds[array_rand($cageIds)], ['father_id' => $fatherId, 'mother_id' => $motherId]);
        }

        // --- 20 Pasangan Tanpa Anak ---
        for ($i = 0; $i < 20; $i++) {
            $male = $addCow('Jantan', $cageIds[array_rand($cageIds)]);
            $female = $addCow('Betina', $cageIds[array_rand($cageIds)]);
            $this->setPartners([$male['id']], [$female['id']]);
        }

        // --- 10 Pasangan Dengan Anak 1-3 ---
        for ($i = 0; $i < 10; $i++) {
            $father = $addCow('Jantan', $cageIds[array_rand($cageIds)]);
            $mother = $addCow('Betina', $cageIds[array_rand($cageIds)]);
            $this->setPartners([$father['id']], [$mother['id']]);
            
            $kids = $faker->numberBetween(1, 3);
            for ($k = 0; $k < $kids; $k++) {
                $addCow($faker->randomElement(['Jantan', 'Betina']), $cageIds[array_rand($cageIds)], [
                    'father_id' => $father['id'], 
                    'mother_id' => $mother['id']
                ]);
            }
        }

        // --- Keluarga Spesial ---
        $father = $addCow('Jantan', $cageIds[array_rand($cageIds)]);
        $mother = $addCow('Betina', $cageIds[array_rand($cageIds)]);
        $this->setPartners([$father['id']], [$mother['id']]);
        
        for ($s = 0; $s < 2; $s++) {
            $addCow($faker->randomElement(['Jantan', 'Betina']), $cageIds[array_rand($cageIds)], [
                'father_id' => $father['id'], 
                'mother_id' => $mother['id']
            ]);
        }
        
        $special = $addCow('Jantan', $cageIds[array_rand($cageIds)], [
            'father_id' => $father['id'], 
            'mother_id' => $mother['id'], 
            'name' => 'Spesial'
        ]);
        
        $partnerCount = $faker->numberBetween(1, 5);
        $partnerIds = [];
        for ($p = 0; $p < $partnerCount; $p++) {
            $partnerIds[] = $addCow('Betina', $cageIds[array_rand($cageIds)])['id'];
        }
        $this->setPartners([$special['id']], $partnerIds);

        // --- Insert Data Pelengkap (BCS, Note, Family Tree) ---
        foreach ($this->livestock as $cow) {
            $this->db->createCommand()->insert('{{%bcs}}', [
                'livestock_id' => $cow['id'],
                'body_weight' => $cow['body_weight'],
                'chest_size' => $cow['chest_size'],
                'hips' => $cow['hips'],
                'bcs_image' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])->execute();

            $this->db->createCommand()->insert('{{%note}}', [
                'user_id' => $this->user_id,
                'livestock_name' => $cow['name'],
                'livestock_id' => $cow['id'],
                'livestock_vid' => $cow['vid'],
                'livestock_cage' => 'K' . $cow['cage_id'],
                'location' => $faker->city,
                'livestock_feed' => 'Rumput gajah',
                
                // Kolom baru pengganti feed_weight dan costs
                'forage_weight' => $faker->numberBetween(1, 10),
                'forage_costs' => $faker->numberBetween(15000, 50000),
                'consentrate_weight' => $faker->numberBetween(1, 5),
                'consentrate_costs' => $faker->numberBetween(20000, 60000),
                'additive_weight' => $faker->numberBetween(1, 2),
                'additive_costs' => $faker->numberBetween(5000, 20000),
                
                // Kolom kesehatan
                'vaccine' => $faker->numberBetween(0, 50000),
                'vitamin' => $faker->numberBetween(0, 20000),
                'pregnancy_check' => $faker->numberBetween(0, 100000),
                'antibiotics' => $faker->numberBetween(0, 30000),
                'anthelmintic' => $faker->numberBetween(0, 15000),
                'insemination' => $faker->numberBetween(0, 150000),
                
                // Tanggal note
                'note_date' => $faker->unique()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                
                'details' => 'Auto seed',
                'documentation' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])->execute();

            $this->db->createCommand()->insert('{{%cow_family_tree}}', [
                'main_cow_id' => $cow['id'],
                'father_id' => $cow['father_id'],
                'mother_id' => $cow['mother_id'],
                'partners' => json_encode($cow['partners']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])->execute();
        }

        echo "Seeding Complex Family selesai!\n";
    }

    private function createCow($faker, string $gender, int $cageId, array $opt = []): array
    {
        $eid = 900000000000000000 + $this->eidSeq++;
        $vid = strtoupper($faker->bothify('??####'));
        $birth = $faker->dateTimeBetween('-4 years', '-1 year');
        
        $now = new \DateTime();
        $age = ($now->diff($birth)->y * 12) + $now->diff($birth)->m;
        
        $chest = $faker->numberBetween(70, 120);
        $weight = $faker->numberBetween(100, 300);
        $hips = $faker->numberBetween(70, 120);
        
        $name = $opt['name'] ?? 'sapi' . str_pad((string)($this->eidSeq - 1), 2, '0', STR_PAD_LEFT);

        $data = [
            'user_id' => $this->user_id,
            'eid' => $eid,
            'vid' => $vid,
            'cage_id' => $cageId,
            'father_id' => $opt['father_id'] ?? null,
            'mother_id' => $opt['mother_id'] ?? null,
            'partner_id' => null,
            'name' => $name,
            'birthdate' => $birth->format('Y-m-d'),
            'age' => $age,
            'gender' => $gender,
            'type_of_livestock' => 'Sapi',
            'breed_of_livestock' => $faker->randomElement(['Madura', 'Bali', 'Limousin', 'Brahman']),
            'purpose' => $faker->randomElement(['Indukan', 'Penggemukan', 'Tabungan', 'Belum Tahu']),
            'maintenance' => $faker->randomElement(['Kandang', 'Gembala', 'Campuran']),
            'source' => $faker->randomElement(['Sejak Lahir', 'Bantuan Pemerintah', 'Beli', 'Beli dari Luar Kelompok', 'Beli dari Dalam Kelompok', 'Inseminasi Buatan', 'Kawin Alam', 'Tidak Tahu']),
            'ownership_status' => $faker->randomElement(['Sendiri', 'Kelompok', 'Titipan']),
            'reproduction' => ($gender === 'Betina') ? $faker->randomElement(['Tidak Bunting', 'Bunting 1 bulan', 'Bunting 2 bulan']) : 'Tidak Bunting',
            'chest_size' => $chest,
            'body_weight' => $weight,
            'hips' => $hips,
            'health' => $faker->randomElement(['Sehat', 'Sakit', 'Sehat', 'Sehat', 'Sehat']),
            'livestock_image' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'partners' => [],
        ];

        $dbInsert = $data;
        unset($dbInsert['hips'], $dbInsert['partners']);
        
        $this->db->createCommand()->insert('{{%livestock}}', $dbInsert)->execute();
        $data['id'] = (int)$this->db->getLastInsertID();
        
        $this->livestock[] = $data;
        if ($gender === 'Jantan') {
            $this->maleIds[] = $data['id'];
        } else {
            $this->femaleIds[] = $data['id'];
        }
        
        return $data;
    }

    private function randomMale(): int
    {
        return $this->maleIds[array_rand($this->maleIds)];
    }

    private function randomFemale(): int
    {
        return $this->femaleIds[array_rand($this->femaleIds)];
    }

    private function setPartners(array $males, array $females): void
    {
        foreach ($this->livestock as &$cow) {
            if (in_array($cow['id'], $males)) {
                $cow['partners'] = array_merge($cow['partners'], $females);
            }
            if (in_array($cow['id'], $females)) {
                $cow['partners'] = array_merge($cow['partners'], $males);
            }
        }
    }

    private function purge(): void
    {
        $this->db->createCommand('DELETE FROM {{%note_images}}')->execute();
        $this->db->createCommand('DELETE FROM {{%note}}')->execute();
        $this->db->createCommand('DELETE FROM {{%bcs}}')->execute();
        $this->db->createCommand('DELETE FROM {{%cow_family_tree}}')->execute();
        $this->db->createCommand('DELETE FROM {{%livestock}}')->execute();
        $this->db->createCommand('DELETE FROM {{%cage}} WHERE description="Kandang Sapi"')->execute();
    }
}