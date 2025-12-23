<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Project;
use App\Models\Boq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultSetupSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Admin User
        |--------------------------------------------------------------------------
        */
        User::firstOrCreate(
            ['email' => 'admin@thiqah.local'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Companies (AR / EN)
        |--------------------------------------------------------------------------
        */
        $companyKSA = Company::firstOrCreate(
            ['code' => 'KSA'],
            [
                'name_ar'         => 'ثقة – السعودية',
                'name_en'         => 'Thiqah Saudi',
                'currency_code'   => 'SAR',
                'currency_symbol'=> '﷼',
                'locale'          => 'ar',
                'timezone'        => 'Asia/Riyadh',
                'is_active'       => true,
            ]
        );

        $companyEGY = Company::firstOrCreate(
            ['code' => 'EGY'],
            [
                'name_ar'         => 'ثقة – مصر',
                'name_en'         => 'Thiqah Egypt',
                'currency_code'   => 'EGP',
                'currency_symbol'=> 'E£',
                'locale'          => 'ar',
                'timezone'        => 'Africa/Cairo',
                'is_active'       => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Default Branches
        |--------------------------------------------------------------------------
        */
        $branchKSA = Branch::firstOrCreate(
            [
                'company_id' => $companyKSA->id,
                'code'       => 'MAIN',
            ],
            [
                'name_ar'       => 'الفرع الرئيسي – السعودية',
                'name_en'       => 'Main Branch – KSA',
                'currency_code' => null, // inherit from company
                'is_active'     => true,
            ]
        );

        $branchEGY = Branch::firstOrCreate(
            [
                'company_id' => $companyEGY->id,
                'code'       => 'MAIN',
            ],
            [
                'name_ar'       => 'الفرع الرئيسي – مصر',
                'name_en'       => 'Main Branch – Egypt',
                'currency_code' => null,
                'is_active'     => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | 4️⃣ ربط أي Projects قديمة (إن وجدت)
        |--------------------------------------------------------------------------
        */
        Project::whereNull('company_id')->update([
            'company_id' => $companyKSA->id,
            'branch_id'  => $branchKSA->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ ربط أي BOQs قديمة
        |--------------------------------------------------------------------------
        */
        Boq::whereNull('company_id')->update([
            'company_id' => $companyKSA->id,
            'branch_id'  => $branchKSA->id,
        ]);
    }
}
