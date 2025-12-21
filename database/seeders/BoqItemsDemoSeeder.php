<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\WorkItem;
use App\Models\Unit;

class BoqItemsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $boq = Boq::first(); // أو find(9)

        if (! $boq) {
            $this->command->error('No BOQ found');
            return;
        }

        $unit = Unit::first();
        $workItems = WorkItem::take(5)->get();

        $rows = [
            ['qty' => 10,  'price' => 1500],
            ['qty' => 25,  'price' => 800],
            ['qty' => 50,  'price' => 120],
            ['qty' => 5,   'price' => 3000],
            ['qty' => 100, 'price' => 45],
        ];

        foreach ($workItems as $i => $workItem) {
            BoqItem::create([
                'boq_id'        => $boq->id,
                'work_item_id' => $workItem->id,
                'unit_id'      => $unit->id,
                'quantity'     => $rows[$i]['qty'],
                'unit_price'   => $rows[$i]['price'],
                'sort_order'   => $i + 1,
                'notes'        => 'بند تجريبي',
            ]);
        }
    }
}
