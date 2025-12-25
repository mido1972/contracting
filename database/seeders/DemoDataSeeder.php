<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // Helpers (safe + schema-aware)
        // =========================
        $columns = fn (string $table): array => Schema::getColumnListing($table);

        $filter = function (string $table, array $data) use ($columns): array {
            $cols = array_flip($columns($table));
            return array_intersect_key($data, $cols);
        };

        $upsertGetId = function (string $table, array $unique, array $data) use ($filter): int {
            $payload = $filter($table, array_merge($unique, $data));
            DB::table($table)->updateOrInsert($unique, $payload);

            $id = DB::table($table)->where($unique)->value('id');
            return $id ? (int) $id : 0;
        };

        $ensurePivot = function (string $table, array $unique, array $data = []) use ($filter): void {
            DB::table($table)->updateOrInsert($unique, $filter($table, array_merge($unique, $data)));
        };

        $pick = fn (array $arr) => $arr[array_rand($arr)];

        // =========================
        // 1) Admin User
        // =========================
        $admin = User::firstOrCreate(
            ['email' => 'admin@thiqah.local'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // =========================
        // 2) Company: أضواء الخليل للمقاولات
        // =========================
        $companyId = $upsertGetId(
            'companies',
            ['code' => 'ADWAA'],
            [
                'name'            => 'أضواء الخليل للمقاولات',
                'name_ar'         => 'أضواء الخليل للمقاولات',
                'name_en'         => 'Adwaa Al-Khalil Contracting',
                'currency_code'   => 'SAR',
                'currency_symbol' => 'SAR',
                'locale'          => 'ar',
                'timezone'        => 'Asia/Riyadh',
                'is_active'       => true,
            ]
        );

        // =========================
        // 3) Branches: مكة / المدينة / الرياض
        // =========================
        $branches = [
            ['code' => 'MKK', 'name_ar' => 'مكة',             'name_en' => 'Makkah',  'currency_code' => 'SAR', 'currency_symbol' => 'SAR', 'timezone' => 'Asia/Riyadh'],
            ['code' => 'MAD', 'name_ar' => 'المدينة المنورة', 'name_en' => 'Madinah', 'currency_code' => 'SAR', 'currency_symbol' => 'SAR', 'timezone' => 'Asia/Riyadh'],
            ['code' => 'RYD', 'name_ar' => 'الرياض',          'name_en' => 'Riyadh',  'currency_code' => 'SAR', 'currency_symbol' => 'SAR', 'timezone' => 'Asia/Riyadh'],
        ];

        $branchIdsByCode = [];
        foreach ($branches as $b) {
            $branchId = $upsertGetId(
                'branches',
                ['company_id' => $companyId, 'code' => $b['code']],
                [
                    'name'            => $b['name_ar'],
                    'name_ar'         => $b['name_ar'],
                    'name_en'         => $b['name_en'],
                    'currency_code'   => $b['currency_code'],
                    'currency_symbol' => $b['currency_symbol'],
                    'locale'          => 'ar',
                    'timezone'        => $b['timezone'],
                    'is_active'       => true,
                ]
            );

            $branchIdsByCode[$b['code']] = $branchId;
        }

        // =========================
        // 4) Units (lots) - units.code NOT NULL
        // =========================
        $units = [
            ['code' => 'NO',   'ar' => 'رقم',      'en' => 'No.'],
            ['code' => 'EA',   'ar' => 'قطعة',     'en' => 'Each'],
            ['code' => 'SET',  'ar' => 'طقم',      'en' => 'Set'],
            ['code' => 'LOT',  'ar' => 'دفعة',     'en' => 'Lot'],
            ['code' => 'M',    'ar' => 'متر طولي',  'en' => 'm'],
            ['code' => 'M2',   'ar' => 'متر مربع',  'en' => 'm²'],
            ['code' => 'M3',   'ar' => 'متر مكعب',  'en' => 'm³'],
            ['code' => 'CM',   'ar' => 'سنتيمتر',   'en' => 'cm'],
            ['code' => 'MM',   'ar' => 'ملِّيمتر',  'en' => 'mm'],
            ['code' => 'KM',   'ar' => 'كيلومتر',   'en' => 'km'],
            ['code' => 'L',    'ar' => 'لتر',       'en' => 'L'],
            ['code' => 'GAL',  'ar' => 'جالون',     'en' => 'Gallon'],
            ['code' => 'KG',   'ar' => 'كيلوجرام',  'en' => 'kg'],
            ['code' => 'TON',  'ar' => 'طن',        'en' => 'ton'],
            ['code' => 'G',    'ar' => 'جرام',      'en' => 'g'],
            ['code' => 'HR',   'ar' => 'ساعة',      'en' => 'hour'],
            ['code' => 'DAY',  'ar' => 'يوم',       'en' => 'day'],
            ['code' => 'WK',   'ar' => 'أسبوع',     'en' => 'week'],
            ['code' => 'MON',  'ar' => 'شهر',       'en' => 'month'],
            ['code' => 'TRIP', 'ar' => 'مشوار',     'en' => 'trip'],
            ['code' => 'BAG',  'ar' => 'كيس',       'en' => 'bag'],
            ['code' => 'BOX',  'ar' => 'صندوق',     'en' => 'box'],
            ['code' => 'ROLL', 'ar' => 'لفة',       'en' => 'roll'],
            ['code' => 'PAIR', 'ar' => 'زوج',       'en' => 'pair'],
        ];

        $unitIds = [];
        foreach ($units as $u) {
            $id = $upsertGetId(
                'units',
                ['code' => $u['code']],
                [
                    'name'      => $u['ar'],
                    'name_ar'   => $u['ar'],
                    'name_en'   => $u['en'],
                    'is_active' => true,
                ]
            );
            $unitIds[$u['code']] = $id;
        }

        $unitIdsList = array_values($unitIds);

        // =========================
        // 5) Work Item Categories (many) - work_item_categories.code NOT NULL
        // =========================
        $categories = [
            ['code' => 'SITE', 'ar' => 'أعمال الموقع العام',      'en' => 'Site Works'],
            ['code' => 'CIV',  'ar' => 'أعمال مدنية',             'en' => 'Civil Works'],
            ['code' => 'CONC', 'ar' => 'أعمال خرسانات',           'en' => 'Concrete Works'],
            ['code' => 'BLD',  'ar' => 'أعمال مباني',             'en' => 'Block & Masonry'],
            ['code' => 'FIN',  'ar' => 'أعمال تشطيبات',           'en' => 'Finishes'],
            ['code' => 'ELEC', 'ar' => 'أعمال كهرباء',            'en' => 'Electrical'],
            ['code' => 'MECH', 'ar' => 'أعمال ميكانيكا',          'en' => 'Mechanical'],
            ['code' => 'PLMB', 'ar' => 'أعمال سباكة',             'en' => 'Plumbing'],
            ['code' => 'HVAC', 'ar' => 'أعمال تكييف وتهوية',      'en' => 'HVAC'],
            ['code' => 'FIRE', 'ar' => 'أعمال مكافحة الحريق',     'en' => 'Fire Fighting'],
        ];

        $categoryIds = [];
        foreach ($categories as $c) {
            $id = $upsertGetId(
                'work_item_categories',
                ['code' => $c['code']],
                [
                    'name'      => $c['ar'],
                    'name_ar'   => $c['ar'],
                    'name_en'   => $c['en'],
                    'is_active' => true,
                ]
            );
            $categoryIds[$c['code']] = $id;
        }

        // =========================
        // 6) Work Items (a lot)
        // =========================
        $workItemsSeed = [
            ['cat' => 'SITE', 'code' => 'SITE-EXC',   'ar' => 'أعمال حفر عام',                 'en' => 'General excavation',      'unit' => 'M3'],
            ['cat' => 'SITE', 'code' => 'SITE-BKF',   'ar' => 'ردم ودك طبقات',                'en' => 'Backfilling & compaction','unit' => 'M3'],
            ['cat' => 'SITE', 'code' => 'SITE-HAUL',  'ar' => 'نقل ناتج الحفر',               'en' => 'Haul away',               'unit' => 'TRIP'],

            ['cat' => 'CONC', 'code' => 'CONC-PLN',   'ar' => 'خرسانة نظافة',                 'en' => 'Lean concrete',           'unit' => 'M3'],
            ['cat' => 'CONC', 'code' => 'CONC-RC',    'ar' => 'خرسانة مسلحة',                 'en' => 'Reinforced concrete',     'unit' => 'M3'],
            ['cat' => 'CONC', 'code' => 'CONC-REBAR', 'ar' => 'حديد تسليح (توريد وتركيب)',   'en' => 'Rebar supply & fix',      'unit' => 'TON'],
            ['cat' => 'CONC', 'code' => 'CONC-FORM',  'ar' => 'شدات خشبية',                   'en' => 'Formwork',                'unit' => 'M2'],

            ['cat' => 'BLD',  'code' => 'BLD-BLOCK',  'ar' => 'بناء بلوك',                    'en' => 'Blockwork',               'unit' => 'M2'],
            ['cat' => 'BLD',  'code' => 'BLD-PLSTR',  'ar' => 'لياسة داخلية',                 'en' => 'Internal plaster',        'unit' => 'M2'],

            ['cat' => 'FIN',  'code' => 'FIN-PAINT',  'ar' => 'دهانات داخلية',                'en' => 'Internal painting',       'unit' => 'M2'],
            ['cat' => 'FIN',  'code' => 'FIN-TILES',  'ar' => 'توريد وتركيب سيراميك',        'en' => 'Tiles supply & fix',      'unit' => 'M2'],
            ['cat' => 'FIN',  'code' => 'FIN-CEIL',   'ar' => 'أسقف مستعارة',                 'en' => 'Suspended ceiling',       'unit' => 'M2'],
            ['cat' => 'FIN',  'code' => 'FIN-DOOR',   'ar' => 'أبواب خشب',                     'en' => 'Wooden doors',            'unit' => 'EA'],

            ['cat' => 'ELEC', 'code' => 'ELEC-CABL',  'ar' => 'كابلات كهرباء',                'en' => 'Electrical cables',       'unit' => 'M'],
            ['cat' => 'ELEC', 'code' => 'ELEC-LGHT',  'ar' => 'وحدات إنارة',                  'en' => 'Lighting fixtures',       'unit' => 'EA'],
            ['cat' => 'ELEC', 'code' => 'ELEC-PANL',  'ar' => 'لوحات كهرباء',                 'en' => 'Electrical panels',       'unit' => 'EA'],

            ['cat' => 'PLMB', 'code' => 'PLMB-PIP',   'ar' => 'مواسير تغذية',                 'en' => 'Supply piping',           'unit' => 'M'],
            ['cat' => 'PLMB', 'code' => 'PLMB-DREN',  'ar' => 'صرف صحي',                      'en' => 'Drainage',                'unit' => 'M'],
            ['cat' => 'PLMB', 'code' => 'PLMB-FIX',   'ar' => 'أطقم صحية',                    'en' => 'Sanitary fixtures',       'unit' => 'SET'],

            ['cat' => 'HVAC', 'code' => 'HVAC-UNIT',  'ar' => 'وحدات تكييف',                  'en' => 'AC units',                'unit' => 'EA'],
            ['cat' => 'HVAC', 'code' => 'HVAC-DUCT',  'ar' => 'دكت تكييف',                    'en' => 'HVAC ducting',            'unit' => 'M2'],

            ['cat' => 'FIRE', 'code' => 'FIRE-PIP',   'ar' => 'شبكة مواسير حريق',             'en' => 'Fire pipes network',      'unit' => 'M'],
            ['cat' => 'FIRE', 'code' => 'FIRE-PUMP',  'ar' => 'مضخة حريق',                    'en' => 'Fire pump',               'unit' => 'EA'],
        ];

        $expanded = [];
        foreach ($workItemsSeed as $base) {
            $expanded[] = $base;
        }

        $extraNames = ['مادة + عمالة', 'توريد فقط', 'تركيب فقط', 'فحص واختبار', 'صيانة', 'تمديدات', 'تشغيل وتسليم'];

        foreach ($categories as $c) {
            for ($i = 1; $i <= 10; $i++) {
                $suffix = $extraNames[$i % count($extraNames)];
                $code = $c['code'] . '-X' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $expanded[] = [
                    'cat'  => $c['code'],
                    'code' => $code,
                    'ar'   => $c['ar'] . " - بند إضافي {$i} ({$suffix})",
                    'en'   => $c['en'] . " - Extra Item {$i}",
                    'unit' => $pick(array_keys($unitIds)),
                ];
            }
        }

        $workItemIds = [];
        foreach ($expanded as $w) {
            $catId = $categoryIds[$w['cat']] ?? null;
            if (!$catId) {
                continue;
            }

            $unitId = $unitIds[$w['unit']] ?? $pick($unitIdsList);

            $id = $upsertGetId(
                'work_items',
                ['code' => $w['code']],
                [
                    'category_id' => $catId,
                    'unit_id'     => $unitId,
                    'name'        => $w['ar'],
                    'name_ar'     => $w['ar'],
                    'name_en'     => $w['en'],
                    'is_active'   => true,
                ]
            );

            if ($id <= 0) {
                $id = (int) DB::table('work_items')->where('name', $w['ar'])->value('id');
            }

            if ($id > 0) {
                $workItemIds[] = $id;
            }
        }

        if (count($workItemIds) === 0) {
            $fallbackId = $upsertGetId(
                'work_items',
                ['code' => 'CONC-FALLBACK'],
                [
                    'category_id' => $categoryIds['CONC'],
                    'name'        => 'بند تجريبي',
                    'name_ar'     => 'بند تجريبي',
                    'name_en'     => 'Demo item',
                    'is_active'   => true,
                ]
            );

            if ($fallbackId > 0) {
                $workItemIds[] = $fallbackId;
            }
        }

        // =========================
        // 7) Projects + BOQs + Items (many)
        // =========================
        $projectTemplates = [
            ['code' => 'PRJ-MKK-001', 'name' => 'مشروع مكة - مجمع سكني',        'branch' => 'MKK'],
            ['code' => 'PRJ-MKK-002', 'name' => 'مشروع مكة - توسعة مبنى إداري', 'branch' => 'MKK'],
            ['code' => 'PRJ-MAD-001', 'name' => 'مشروع المدينة - مركز خدمات',   'branch' => 'MAD'],
            ['code' => 'PRJ-MAD-002', 'name' => 'مشروع المدينة - صيانة عامة',   'branch' => 'MAD'],
            ['code' => 'PRJ-RYD-001', 'name' => 'مشروع الرياض - برج إداري',     'branch' => 'RYD'],
            ['code' => 'PRJ-RYD-002', 'name' => 'مشروع الرياض - فيلات سكنية',   'branch' => 'RYD'],
        ];

        $boqTemplates = [
            ['code_prefix' => 'CIV', 'name' => 'مقايسة الأعمال المدنية'],
            ['code_prefix' => 'FIN', 'name' => 'مقايسة التشطيبات'],
            ['code_prefix' => 'MEP', 'name' => 'مقايسة الأعمال الكهروميكانيكية'],
        ];

        // ✅ FIX: حالات صحيحة مطابقة للنظام (بدل APPROVED)
        $statuses = ['DRAFT', 'DRAFT', 'DRAFT', 'SUBMITTED']; // أغلبها Draft

        foreach ($projectTemplates as $p) {
            $branchId = $branchIdsByCode[$p['branch']] ?? $pick(array_values($branchIdsByCode));

            $projectId = $upsertGetId(
                'projects',
                ['code' => $p['code']],
                [
                    'company_id' => $companyId,
                    'branch_id'  => $branchId,
                    'name'       => $p['name'],
                    'status'     => 'active',
                    'notes'      => null,
                ]
            );

            // لكل مشروع: 2 BOQ
            for ($b = 1; $b <= 2; $b++) {
                $tpl = $boqTemplates[($b - 1) % count($boqTemplates)];
                $boqCode = "{$tpl['code_prefix']}-" . $p['code'] . "-B" . $b;

                $boqId = $upsertGetId(
                    'boqs',
                    ['code' => $boqCode],
                    [
                        'company_id'   => $companyId,
                        'branch_id'    => $branchId,
                        'name'         => $tpl['name'] . " ({$p['branch']})",
                        'status'       => $pick($statuses),
                        'notes'        => null,
                        'total_amount' => 0,
                        // ✅ FIX: حذف legacy project_ref (الأساس هو project_id)
                        'project_id'   => $projectId,
                    ]
                );

                // لو projects عنده boq_id واحد فقط، اربط أول BOQ
                if ($b === 1) {
                    DB::table('projects')->where('id', $projectId)->update($filter('projects', ['boq_id' => $boqId]));
                }

                // Create items: 12 إلى 25 بند لكل BOQ
                $itemsCount = rand(12, 25);
                $sort = 1;

                for ($i = 1; $i <= $itemsCount; $i++) {
                    $workItemId = $workItemIds[array_rand($workItemIds)];
                    $unitId     = $pick($unitIdsList);

                    $qty   = rand(1, 20) + (rand(0, 9) / 10);
                    $price = rand(50, 1500) + (rand(0, 99) / 100);
                    $total = round($qty * $price, 2);

                    DB::table('boq_items')->updateOrInsert(
                        ['boq_id' => $boqId, 'sort_order' => $sort],
                        $filter('boq_items', [
                            'boq_id'       => $boqId,
                            'work_item_id' => $workItemId,
                            'unit_id'      => $unitId,
                            'quantity'     => $qty,
                            'unit_price'   => $price,
                            'total_price'  => $total,
                            'sort_order'   => $sort,
                            'notes'        => ($i % 5 === 0) ? 'ملاحظة تجريبية' : null,
                        ])
                    );

                    $sort++;
                }

                // Update BOQ total
                $sum = (float) DB::table('boq_items')->where('boq_id', $boqId)->sum('total_price');
                DB::table('boqs')->where('id', $boqId)->update($filter('boqs', ['total_amount' => round($sum, 2)]));
            }
        }

        // =========================
        // 8) Membership + Current Context for Admin
        // =========================
        $ensurePivot('company_user', ['company_id' => $companyId, 'user_id' => $admin->id], ['is_default' => true]);

        foreach ($branchIdsByCode as $code => $branchId) {
            $ensurePivot('branch_user', ['branch_id' => $branchId, 'user_id' => $admin->id], ['is_default' => $code === 'MKK']);
        }

        DB::table('users')->where('id', $admin->id)->update(
            $filter('users', [
                'current_company_id' => $companyId,
                'current_branch_id'  => $branchIdsByCode['MKK'] ?? $pick(array_values($branchIdsByCode)),
            ])
        );
    }
}
