<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();
        $categories = DB::table('categories')->pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            Transaction::create([
                'description' => $faker->words(2, true),  // ex: "Payment Invoice"
                'amount' => $faker->randomFloat(2, 10, 2000), // 10 a 2000
                'currency' => 'GBP',
                'type' => $faker->randomElement(['income', 'expense']),
                'date' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'category_id' => $faker->randomElement($categories),
                'user_id' => 1, // ou outro usuário existente
            ]);
        }
    }
}
