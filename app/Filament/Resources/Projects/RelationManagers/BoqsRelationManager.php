<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BoqsRelationManager extends RelationManager
{
    /**
     * Project::boqs()
     */
    protected static string $relationship = 'boqs';

    protected static ?string $title = 'المقايسات';

    /**
     * Filament v4: Schema (مش Forms\Form)
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود المقايسة')
                    ->maxLength(50)
                    ->helperText('اختياري (يمكن توليده لاحقًا)'),

                TextInput::make('name')
                    ->label('اسم المقايسة')
                    ->required()
                    ->maxLength(255),

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

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        $currency = fn (): string =>
            $this->getOwnerRecord()?->currencyCode()
            ?? config('app.currency_default', 'SAR');

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->money($currency)
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var \App\Models\Project $project */
                        $project = $this->getOwnerRecord();

                        // تثبيت الـ Context تلقائيًا
                        $data['company_id'] = $project->company_id;
                        $data['branch_id']  = $project->branch_id;
                        $data['project_id'] = $project->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
