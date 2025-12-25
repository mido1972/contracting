<?php

namespace App\Filament\Resources\WorkItems\Schemas;

use App\Models\WorkItemCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class WorkItemForm
{
    private static function normalizePrefix(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));
        return preg_replace('/[^A-Z0-9_-]/', '', $prefix) ?: 'CAT';
    }

    private static function nextSerialForCategory(int $categoryId): int
    {
        // ✅ Atomic counter باستخدام Transaction + FOR UPDATE
        return DB::transaction(function () use ($categoryId) {
            $row = DB::table('work_item_category_counters')
                ->where('category_id', $categoryId)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('work_item_category_counters')->insert([
                    'category_id'  => $categoryId,
                    'last_number'  => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                return 1;
            }

            $next = ((int) $row->last_number) + 1;

            DB::table('work_item_category_counters')
                ->where('category_id', $categoryId)
                ->update([
                    'last_number' => $next,
                    'updated_at'  => now(),
                ]);

            return $next;
        }, 3);
    }

    private static function nextCodeForCategory(int $categoryId): string
    {
        $cat = WorkItemCategory::query()->select(['id', 'code'])->find($categoryId);

        $prefix = $cat && filled($cat->code)
            ? self::normalizePrefix((string) $cat->code)
            : ('CAT' . $categoryId);

        $next = self::nextSerialForCategory($categoryId);

        return $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $record) {
                        if (! $state) {
                            return;
                        }

                        // ✅ في Edit لا نغيّر الكود
                        if ($record && filled($record->code)) {
                            return;
                        }

                        // ✅ في Create كل تغيير للتصنيف يولّد كود جديد (بدون تكرار)
                        $set('code', self::nextCodeForCategory((int) $state));
                    })
                    ->columnSpan(1),

                Select::make('parent_id')
                    ->label('البند الرئيسي')
                    ->relationship(
                        'parent',
                        'name',
                        fn ($query) => $query->whereNull('parent_id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->columnSpan(1),

                TextInput::make('code')
                    ->label('كود البند')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('يتم توليد الكود تلقائيًا حسب التصنيف (مسلسل لكل تصنيف).')
                    ->columnSpan(1),

                TextInput::make('name')
                    ->label('اسم البند')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->columnSpan(1),

                TextInput::make('default_price')
                    ->label('السعر الافتراضي')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(fn (callable $get) => filled($get('unit_id')))
                    ->helperText('لو اخترت وحدة لازم تحدد سعر افتراضي (يمكن 0).')
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
