<?php

namespace Database\Seeders;

use App\Models\SecondarySchool;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SecondarySchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'name' => 'Kamuzu Academy',
                'code' => 'KAM001',
                'region' => 'Central',
                'district' => 'Kasungu',
                'address' => 'P.O. Box 72, Kasungu',
                'phone' => '+265 1 234 567',
                'email' => 'info@kamuzuacademy.edu.mw',
                'capacity' => 200,
                'facilities' => ['Library', 'Science Lab', 'Computer Lab', 'Sports Field'],
                'is_active' => true
            ],
            [
                'name' => 'Lilongwe Girls Secondary School',
                'code' => 'LGS002',
                'region' => 'Central',
                'district' => 'Lilongwe',
                'address' => 'Area 47, Lilongwe',
                'phone' => '+265 1 789 123',
                'email' => 'admin@lgss.edu.mw',
                'capacity' => 150,
                'facilities' => ['Library', 'Science Lab', 'Home Economics Room'],
                'is_active' => true
            ],
            [
                'name' => 'Zomba Catholic Secondary School',
                'code' => 'ZCS003',
                'region' => 'Southern',
                'district' => 'Zomba',
                'address' => 'Zomba Town, Zomba',
                'phone' => '+265 1 524 789',
                'email' => 'office@zcss.edu.mw',
                'capacity' => 180,
                'facilities' => ['Library', 'Science Lab', 'Chapel', 'Sports Ground'],
                'is_active' => true
            ],
            [
                'name' => 'Blantyre Secondary School',
                'code' => 'BSS004',
                'region' => 'Southern',
                'district' => 'Blantyre',
                'address' => 'Limbe, Blantyre',
                'phone' => '+265 1 643 210',
                'email' => 'info@bss.edu.mw',
                'capacity' => 220,
                'facilities' => ['Library', 'Multiple Science Labs', 'Computer Lab', 'Auditorium'],
                'is_active' => true
            ],
            [
                'name' => 'Mzuzu Academy',
                'code' => 'MZA005',
                'region' => 'Northern',
                'district' => 'Mzuzu',
                'address' => 'Mzuzu City, Mzuzu',
                'phone' => '+265 1 333 456',
                'email' => 'admin@mzuzuacademy.edu.mw',
                'capacity' => 160,
                'facilities' => ['Library', 'Science Lab', 'Computer Lab', 'Workshop'],
                'is_active' => true
            ],
            [
                'name' => 'Karonga Secondary School',
                'code' => 'KRS006',
                'region' => 'Northern',
                'district' => 'Karonga',
                'address' => 'Karonga Boma, Karonga',
                'phone' => '+265 1 362 789',
                'email' => 'kss@education.gov.mw',
                'capacity' => 120,
                'facilities' => ['Library', 'Science Lab', 'Agriculture Plot'],
                'is_active' => true
            ],
            [
                'name' => 'Dedza Secondary School',
                'code' => 'DSS007',
                'region' => 'Central',
                'district' => 'Dedza',
                'address' => 'Dedza Boma, Dedza',
                'phone' => '+265 1 262 543',
                'email' => 'dedza.secondary@gmail.com',
                'capacity' => 140,
                'facilities' => ['Library', 'Science Lab', 'Technical Drawing Room'],
                'is_active' => true
            ],
            [
                'name' => 'Nsanje Secondary School',
                'code' => 'NSS008',
                'region' => 'Southern',
                'district' => 'Nsanje',
                'address' => 'Nsanje Boma, Nsanje',
                'phone' => '+265 1 645 321',
                'email' => 'nsanje.sec@education.gov.mw',
                'capacity' => 100,
                'facilities' => ['Library', 'Basic Science Lab'],
                'is_active' => true
            ],
            [
                'name' => 'Rumphi Secondary School',
                'code' => 'RSS009',
                'region' => 'Northern',
                'district' => 'Rumphi',
                'address' => 'Rumphi Boma, Rumphi',
                'phone' => '+265 1 355 678',
                'email' => 'rumphi.secondary@yahoo.com',
                'capacity' => 90,
                'facilities' => ['Library', 'Science Lab'],
                'is_active' => true
            ],
            [
                'name' => 'Thyolo Secondary School',
                'code' => 'TSS010',
                'region' => 'Southern',
                'district' => 'Thyolo',
                'address' => 'Thyolo Boma, Thyolo',
                'phone' => '+265 1 634 567',
                'email' => 'thyolo.sec@education.gov.mw',
                'capacity' => 130,
                'facilities' => ['Library', 'Science Lab', 'Agriculture Plot'],
                'is_active' => true
            ]
        ];

        foreach ($schools as $school) {
            SecondarySchool::create($school);
        }
    }
}
