<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>طباعة مقايسة - {{ $boq->code ?? $boq->id }}</title>

@php
    $isPdfMode = !empty($isPdf);
@endphp

<style>
/* =========================
   A4 SETUP
   ========================= */
@page{
    size: A4;
    margin: 12mm 10mm 14mm 10mm;
}

*{ box-sizing:border-box; }

/* =========================
   FONTS (HTTP only - safe for Browsershot/Chromium)
   ========================= */
@font-face{
    font-family: "Cairo";
    src: url('{{ asset("fonts/cairo/Cairo-Regular.ttf") }}') format("truetype");
    font-weight: 400;
    font-style: normal;
}
@font-face{
    font-family: "Cairo";
    src: url('{{ asset("fonts/cairo/Cairo-Bold.ttf") }}') format("truetype");
    font-weight: 700;
    font-style: normal;
}

html, body{
    margin:0;
    padding:0;
    background:#fff;
    color:#111;
    font-size:12px;
    font-family: "Cairo", Arial, "DejaVu Sans", sans-serif;
    direction:rtl;
    unicode-bidi:embed;
}

/* Numbers */
.num{
    direction:ltr !important;
    unicode-bidi:isolate !important;
    text-align:left;
    white-space:nowrap;
}

/* =========================
   LAYOUT
   ========================= */
.page{
    width:100%;
}

.toolbar{
    width:210mm;
    margin:10mm auto 0;
    display:flex;
    justify-content:flex-end;
}

.btn{
    padding:8px 12px;
    border:1px solid #ccc;
    background:#fff;
    cursor:pointer;
    font-size:12px;
}

.header{
    border:1px solid #000;
    padding:10px;
    margin-bottom:10px;
    display:flex;
    justify-content:space-between;
    gap:10px;
}

.header .block{ width:50%; }

.title{
    font-size:16px;
    font-weight:700;
    margin-bottom:6px;
}

.kv{
    margin:0;
    line-height:1.6;
}

.muted{ color:#666; }

/* =========================
   TABLE
   ========================= */
table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed; /* مهم لتثبيت الأعمدة وسرعة الـ PDF */
}

th, td{
    border:1px solid #000;
    padding:6px;
    vertical-align:top;
    word-wrap:break-word;
    overflow-wrap:break-word;
}

thead th{
    background:#eee;
    text-align:center;
    font-weight:700;
}

thead{ display:table-header-group; }
tr{ page-break-inside:avoid; }

.center{ text-align:center; }

/* =========================
   TOTALS
   ========================= */
.totals{
    margin-top:10px;
    border:1px solid #000;
    padding:10px;
}

.totals-row{
    display:flex;
    justify-content:space-between;
    line-height:1.8;
    font-weight:700;
}

/* =========================
   NOTES
   ========================= */
.notes{
    margin-top:10px;
    border:1px solid #000;
    padding:10px;
}

/* =========================
   PDF PERFORMANCE (Browsershot)
   ========================= */
@if($isPdfMode)
*{
    animation:none !important;
    transition:none !important;
}
@endif

/* =========================
   PRINT
   ========================= */
@media print{
    .toolbar{ display:none; }
}
</style>
</head>

<body>

@if(empty($isPdf))
<div class="toolbar">
    <button class="btn" onclick="window.print()">🖨️ طباعة</button>
</div>
@endif

<div class="page">

    <div class="header">
        <div class="block">
            <div class="title">مقايسة أعمال</div>
            <p class="kv"><b>رقم المقايسة:</b> {{ $boq->code ?? $boq->id }}</p>
            <p class="kv"><b>اسم المقايسة:</b> {{ $boq->name ?? '-' }}</p>
            <p class="kv"><b>الحالة:</b> {{ $boq->status ?? '-' }}</p>
        </div>

        <div class="block">
            <p class="kv"><b>الشركة:</b> {{ $boq->company?->name_ar ?? $boq->company?->name_en ?? '-' }}</p>
            <p class="kv"><b>الفرع:</b> {{ $boq->branch?->name_ar ?? $boq->branch?->name_en ?? '-' }}</p>
            <p class="kv"><b>المشروع:</b> {{ $boq->project?->name ?? '-' }}</p>
            <p class="kv muted"><b>تاريخ الطباعة:</b> {{ $printed_at?->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th style="width:40px;">م</th>
            <th>البند</th>
            <th style="width:70px;">الوحدة</th>
            <th style="width:80px;">الكمية</th>
            <th style="width:90px;">سعر الوحدة</th>
            <th style="width:100px;">الإجمالي</th>
            <th style="width:150px;">ملاحظات</th>
        </tr>
        </thead>

        <tbody>
        @forelse($items as $i => $it)
            <tr>
                <td class="center">{{ $it->sort_order ?? ($i + 1) }}</td>
                <td>{{ $it->workItem?->name ?? '-' }}</td>
                <td class="center">{{ $it->unit?->name ?? '-' }}</td>
                <td class="num">{{ number_format((float)$it->quantity, 3) }}</td>
                <td class="num">{{ number_format((float)$it->unit_price, 2) }}</td>
                <td class="num">{{ number_format((float)$it->total_price, 2) }}</td>
                <td>{{ $it->notes ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="center muted">لا توجد بنود</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <div>الإجمالي ({{ $currency }})</div>
            <div class="num">{{ number_format((float)$total_amount, 2) }}</div>
        </div>
        <div class="totals-row muted">
            <div>إجمالي البنود (حسابيًا)</div>
            <div class="num">{{ number_format((float)$subtotal, 2) }}</div>
        </div>
    </div>

    @if(!empty($boq->notes))
        <div class="notes">
            <b>ملاحظات</b><br>
            {{ $boq->notes }}
        </div>
    @endif

</div>

</body>
</html>
