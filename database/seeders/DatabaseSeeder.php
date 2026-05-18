<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LGD\StateSeeder::class,
            LGD\DistrictSeeder::class,
            LGD\BlockSeeder::class,
            LGD\PanchayatSeeder::class,
            LGD\SubdivisionSeeder::class,
            LGD\MunicipalitiesSeeder::class,
            LGD\WardSeeder::class,
            MasterMimeTypeSeeder::class,
            Bank\BankSeeder::class,
            Bank\IfscSeeder::class,
            DepartmentSeeder::class,
            CodemasterSeeder::class,
            SchemeSeeder::class,
            SchemeAttacheDocumentSeeder::class,
            OpTypeSeeder::class,
        ]);
    }
}
