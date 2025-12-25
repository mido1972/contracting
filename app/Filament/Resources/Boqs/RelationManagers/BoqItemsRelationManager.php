<?php

namespace App\Filament\Resources\Boqs\RelationManagers;

use App\Models\WorkItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BoqItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود المقايسة';

    protected function isDraft(): bool
    {
        return (string) ($this->getOwnerRecord()?->status ?? '') === 'DRAFT';
    }

    protected function guardDraftOnly(): void
    {
        if (! $this->isDraft()) {
            Notification::make()
                ->title('غير مسموح')
                ->body('لا يمكن تعديل بنود المقايسة إلا وهي في حالة مسودة (DRAFT).')
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    /**
     * يرجّع unit_id + price من جدول work_items بشكل "مؤكد"
     * - إذا default_price موجود: نستخدمه
     * - لو مش موجود أو NULL: نجرب أعمدة بديلة (فقط إذا موجودة فعلاً في الجدول)
     */
    private function getWorkItemDefaults(int $workItemId): array
    {
        $priceColumn = null;

        if (Schema::hasColumn('work_items', 'default_price')) {
            $priceColumn = 'default_price';
        } elseif (Schema::hasColumn('work_items', 'unit_price')) {
            $priceColumn = 'unit_price';
        } elseif (Schema::hasColumn('work_items', 'price')) {
            $priceColumn = 'price';
        } elseif (Schema::hasColumn('work_items', 'rate')) {
            $priceColumn = 'rate';
        }

        $select = ['id', 'unit_id'];
        if ($priceColumn) {
            $select[] = $priceColumn;
        }

        $row = WorkItem::query()
            ->select($select)
            ->whereKey($workItemId)
            ->first();

        if (! $row) {
            return ['unit_id' => null, 'price' => null];
        }

        $price = null;
        if ($priceColumn) {
            $price = $row->{$priceColumn};
        }

        return [
            'unit_id' => $row->unit_id,
            'price'   => $price, // ممكن تبقى NULL إذا القيمة في DB NULL
        ];
    }

    protected function itemForm(bool $isEdit = false): array
    {
        return [
            TextInput::make('sort_order')
                ->label('ترتيب')
                ->numeric()
                ->required()
                ->default(function (RelationManager $livewire) use ($isEdit): int {
                    if ($isEdit) {
                        return 0; // سيتم تجاهله في Edit (القيمة ستأتي من الريكورد)
                    }

                    $max = (int) $livewire->getOwnerRecord()
                        ->items()
                        ->max('sort_order');

                    return $max + 1;
                }),

            Select::make('work_item_id')
                ->label('بند العمل')
                ->relationship('workItem', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (! $state) {
                        return;
                    }

                    $defaults = $this->getWorkItemDefaults((int) $state);

                    // الوحدة
                    if (! empty($defaults['unit_id'])) {
                        $set('unit_id', (int) $defaults['unit_id']);
                    }

                    // السعر: لو الحالي 0 أو فاضي → حاول تملأه من DB
                    $currentPrice = (float) ($get('unit_price') ?? 0);

                    // لو DB راجعة NULL يبقى مفيش سعر مسجل فعلاً
                    if ($currentPrice <= 0 && $defaults['price'] !== null) {
                        $set('unit_price', (float) $defaults['price']);
                    }
                }),

            Select::make('unit_id')
                ->label('الوحدة')
                ->relationship('unit', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabled()
                ->dehydrated(),

            TextInput::make('quantity')
                ->label('الكمية')
                ->numeric()
                ->required()
                ->default(1),

            TextInput::make('unit_price')
                ->label('سعر الوحدة')
                ->numeric()
                ->required()
                ->default(0),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('sort_order')->orderBy('id'))

            ->columns([
                TextColumn::make('sort_order')
                    ->label('ترتيب'),

                TextColumn::make('workItem.name')
                    ->label('بند العمل')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('unit.name')
                    ->label('الوحدة'),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3)),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('total_price')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap()
                    ->searchable(),
            ])

            ->filters([
                SelectFilter::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'name'),
            ])

            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->before(fn () => $this->guardDraftOnly())
                    ->form($this->itemForm(false)),
            ])

            ->actions([
                EditAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->before(fn () => $this->guardDraftOnly())
                    ->form($this->itemForm(true)),

                DeleteAction::make()
                    ->visible(fn () => $this->isDraft())
                    ->before(fn () => $this->guardDraftOnly()),
            ]);
    }
}
