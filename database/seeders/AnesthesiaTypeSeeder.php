<?php

namespace Database\Seeders;

use App\Models\AnesthesiaType;
use Illuminate\Database\Seeder;

class AnesthesiaTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Spinal Anesthesia (SA)',
                'code' => 'SA',
                'description' => 'Subarachnoid block for Caesarean sections and lower abdominal / pelvic surgeries.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'General Anesthesia (GA)',
                'code' => 'GA',
                'description' => 'Endotracheal intubation / total intravenous anaesthesia for major surgeries.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Local Anesthesia (LA)',
                'code' => 'LA',
                'description' => 'Local infiltration / field block for minor procedures and suturing.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Epidural Anesthesia (EA)',
                'code' => 'EA',
                'description' => 'Epidural catheter block for painless labor and post-op analgesia.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Combined Spinal-Epidural (CSE)',
                'code' => 'CSE',
                'description' => 'Dual technique offering rapid onset of SA and prolonged analgesia of EA.',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Saddle Block',
                'code' => 'SB',
                'description' => 'Low spinal block for perineal, rectal and perianal procedures.',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Sedation / MAC',
                'code' => 'MAC',
                'description' => 'Monitored Anesthesia Care with IV sedation for diagnostic / short procedures.',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Spinal',
                'code' => 'Spinal',
                'description' => 'Spinal anesthesia standard designation.',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            AnesthesiaType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}

