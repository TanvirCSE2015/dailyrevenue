<?php

namespace Database\Seeders;

use App\Models\Plot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         for ($i = 1; $i <= 150; $i++) {

            $plotNumber = 'P-' . str_pad(
                $i,
                3,
                '0',
                STR_PAD_LEFT
            );

            Plot::firstOrCreate(
                [
                    'plot_number' => $plotNumber,
                ],
                [
                    'name' =>'শাপলা চত্ত্বর -' . $i,
                    'location' => 'শাপলা চত্ত্বর',
                    'length' => 5,
                    'width' => 6,
                    'status' => 'available',
                    'description' => 'শাপলা চত্ত্বরের প্লট',
                ]
            );
        }
    }
}
