<?php

namespace App\Filament\Resources\Boqs\Schemas;

use App\Models\Project;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class BoqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('كود المقايسة')
                ->helperText('اتركه فارغًا ليتم توليده تلقائيًا')
                ->maxLength(50)
                ->nullable(),

            TextInput::make('name')
                ->label('اسم المقايسة')
                ->required()
                ->maxLength(255),

            /**
             * ✅ اختيار المشروع (FK)
             * - عند الإنشاء: مطلوب
             * - عند التعديل: مقفول (لمنع نقل مقايسة بين مشاريع بالخطأ)
             * - لو جاي من داخل Project RelationManager: غالبًا هيتثبت تلقائيًا
             */
            Select::make('project_id')
                ->label('المشروع')
                ->searchable()
                ->preload()
                ->relationship(
                    name: 'project',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query) =>
                        $query->forCurrentContext()->orderBy('code')
                )
                ->getOptionLabelFromRecordUsing(
                    fn (Project $p) => "{$p->code} - {$p->name}"
                )
                ->placeholder('اختر المشروع')
                ->required(fn ($record) => $record === null)     // required on create
                ->disabled(fn ($record) => $record !== null)     // locked on edit
                ->dehydrated(true),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    'DRAFT'     => 'مسودة',
                    'SUBMITTED' => 'مُرسلة',
                    'AWARDED'   => 'تمت الترسية',
                    'CANCELLED' => 'ملغاة',
                ])
                ->default('DRAFT')
                ->required(),

            /**
             * ✅ إجمالي المقايسة (عرض فقط)
             * يظهر في Edit فقط
             */
            Placeholder::make('total_amount_view')
                ->label('إجمالي المقايسة')
                ->visible(fn ($record) => $record !== null)
                ->content(function ($record) {
                    $total = (float) ($record?->total_amount ?? 0);
                    $txt = number_format($total, 2);

                    return new HtmlString(
                        '<div style="display:inline-block;padding:10px 14px;border-radius:10px;background:#f5f5f5;font-weight:700;font-size:18px;">'
                        . $txt .
                        '</div>'
                    );
                }),
        ]);
    }
}
