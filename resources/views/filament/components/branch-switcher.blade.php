@php
    $user = auth()->user();

    $branches = \App\Models\Branch::query()
        ->where('company_id', $user->current_company_id)
        ->orderBy('code')
        ->get(['id', 'code', 'name_ar', 'name_en']);

    $current = (int) $user->current_branch_id;
@endphp

<div class="flex items-center">
    <form method="POST" action="{{ route('filament.branch.switch') }}">
        @csrf

        <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-sm">
            <span class="text-sm text-gray-500 whitespace-nowrap">الفرع</span>

            <select
                name="branch_id"
                onchange="this.form.submit()"
                class="bg-transparent border-0 p-0 pr-6 text-sm font-semibold focus:ring-0 focus:outline-none"
            >
                @foreach ($branches as $b)
                    @php
                        $label = $b->name_ar ?: ($b->name_en ?: $b->code);
                    @endphp

                    <option value="{{ $b->id }}" @selected($current === (int) $b->id)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>
