<?php

namespace console\db;

use Yii;
use Faker\Factory;
use yii\db\Connection;

/**
 * Class EconomySeeder
 * Dataset untuk simulasi tabel price_list, history, dan history_change_log.
 */
class EconomySeeder
{
    private Connection $db;
    private int $user_id = 14; // Budiman Sujatmiko - SASPRI 1

    public function __construct()
    {
        $this->db = Yii::$app->db;
    }

    public function run()
    {
        echo "Memulai seeding Economy (Price List & History)...\n";
        
        // Bersihkan data lama agar tidak terjadi penumpukan atau error duplicate
        $this->purge();

        $faker = Factory::create('id_ID');

        // --- 1. Seed Tabel price_list ---
        $this->db->createCommand()->insert('{{%price_list}}', [
            'user_id' => $this->user_id,
            'land' => $faker->numberBetween(1000000, 5000000),
            'employee' => $faker->numberBetween(1, 5),
            'wage' => $faker->numberBetween(1500000, 3000000),
            'livestock_per_employee' => $faker->numberBetween(5, 15),
            'inflation' => 5,
            'margin' => 30,
            'electricity_water' => $faker->numberBetween(100000, 500000),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        // --- Ambil Sapi yang Dimiliki User ---
        // Kita butuh ID sapi untuk tabel history
        $livestockIds = $this->db->createCommand('SELECT id FROM {{%livestock}} WHERE user_id = :uid LIMIT 20')
            ->bindValue(':uid', $this->user_id)
            ->queryColumn();

        if (empty($livestockIds)) {
            echo "Peringatan: Tidak ada livestock ditemukan untuk user_id {$this->user_id}. History dibatalkan.\n";
            return;
        }

        // --- 2. Seed Tabel history & history_change_log ---
        foreach ($livestockIds as $livestockId) {
            
            // Insert History
            $this->db->createCommand()->insert('{{%history}}', [
                'date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'livestock_id' => $livestockId,
                'sell_price' => $faker->numberBetween(15000000, 25000000),
                'pedet_price' => $faker->numberBetween(3000000, 5000000),
                'additional_cost' => $faker->numberBetween(0, 500000),
                'business_type' => $faker->randomElement(['penggemukan', 'breeding']),
                'hpp_price' => $faker->numberBetween(10000000, 15000000),
                'forage_price' => $faker->numberBetween(300000, 600000),
                'concentrate_price' => $faker->numberBetween(100000, 300000),
                'additive_price' => $faker->numberBetween(50000, 150000),
                'insemination' => $faker->numberBetween(0, 100000),
                'vaccine' => $faker->numberBetween(100000, 500000),
                'vitamin' => $faker->numberBetween(50000, 100000),
                'pregnancy_check' => $faker->numberBetween(0, 100000),
                'antibiotics' => $faker->numberBetween(0, 100000),
                'anthelmintic' => $faker->numberBetween(50000, 100000),
                'cage_price' => $faker->numberBetween(500000, 1500000),
                'cage_productive_age' => 10,
                'workers_price' => $faker->numberBetween(1000000, 3000000),
                'workers_per_livestock' => 10,
                'margin' => 30,
                'inflation' => 5,
                'number_of_workers' => 2,
            ])->execute();

            $historyId = (int)$this->db->getLastInsertID();

            // Insert 1-3 History Change Log untuk setiap History
            $logCount = $faker->numberBetween(1, 3);
            for ($i = 0; $i < $logCount; $i++) {
                
                // Format json string sesuai dengan struktur di database
                $prevData = json_encode([
                    'sell_price' => $faker->numberBetween(13000000, 15000000),
                    'hpp_price' => $faker->numberBetween(9000000, 10000000)
                ]);
                $newData = json_encode([
                    'sell_price' => $faker->numberBetween(15000000, 17000000),
                    'hpp_price' => $faker->numberBetween(10000000, 11000000)
                ]);

                $this->db->createCommand()->insert('{{%history_change_log}}', [
                    'history_id' => $historyId,
                    'previous_data' => $prevData,
                    'new_data' => $newData,
                    'changed_at' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                ])->execute();
            }
        }

        echo "Seeding Economy selesai!\n";
    }

    private function purge(): void
    {
        // Harus berurutan dari child ke parent karena adanya Foreign Key
        $this->db->createCommand('DELETE FROM {{%history_change_log}}')->execute();
        $this->db->createCommand('DELETE FROM {{%history}}')->execute();
        $this->db->createCommand('DELETE FROM {{%price_list}} WHERE user_id = ' . $this->user_id)->execute();
    }
}