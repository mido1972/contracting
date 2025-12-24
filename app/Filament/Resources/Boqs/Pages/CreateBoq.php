<?php

namespace App\Filament\Resources\Boqs\Pages;

use App\Filament\Resources\Boqs\BoqResource;
use App\Models\Branch;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBoq extends CreateRecord
{
    protected static string $resource = BoqResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        // 1) Branch context هو الأقوى
        if (filled($user->current_branch_id)) {
            $branchId = (int) $user->current_branch_id;

            $data['branch_id'] = $branchId;

            // company_id نجيبه من الفرع (أضمن من الاعتماد على current_company_id)
            $data['company_id'] = (int) Branch::whereKey($branchId)->value('company_id');

            if (! $data['company_id']) {
                Notification::make()
                    ->title('تعذّر تحديد الشركة المرتبطة بالفرع الحالي')
                    ->danger()
                    ->send();

                abort(403);
            }

            return $data;
        }

        // 2) لو مفيش فرع، نستخدم الشركة كحد أدنى
        if (filled($user->current_company_id)) {
            $data['company_id'] = (int) $user->current_company_id;

            // ✅ حماية: تأكد إن branch_id مش بيتبعت 0 بالخطأ
            if (isset($data['branch_id']) && ! filled($data['branch_id'])) {
                unset($data['branch_id']);
            }

            return $data;
        }

        // 3) لا فرع ولا شركة = ممنوع إنشاء
        Notification::make()
            ->title('يجب اختيار فرع/شركة أولًا قبل إنشاء مقايسة')
            ->warning()
            ->send();

        abort(403);
    }
}
