<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>طباعة مقايسة - {{ $boq->code ?? $boq->id }}</title>

    <style>
        /* =========================
           EMBED LOCAL FONTS (Works in Browser + DomPDF)
           ========================= */
        @font-face{
            font-family: "CairoLocal";
            font-style: normal;
            font-weight: 400;
            src: url("{{ public_path('fonts/cairo/Cairo-Regular.ttf') }}") format("truetype");
        }
        @font-face{
            font-family: "CairoLocal";
            font-style: normal;
            font-weight: 600;
            src: url("{{ public_path('fonts/cairo/Cairo-SemiBold.ttf') }}") format("truetype");
        }
        @font-face{
            font-family: "CairoLocal";
            font-style: normal;
            font-weight: 700;
            src: url("{{ public_path('fonts/cairo/Cairo-Bold.ttf') }}") format("truetype");
        }

        /* =========================
           A4 PAGE SETUP
           ========================= */
        @page {
            size: A4;
            margin: 12mm 10mm 14mm 10mm;
        }

        :root{
            --border:#2b2b2b;
            --muted:#666;
            --bg:#f3f5f7;
        }

        * { box-sizing: border-box; }

        body{
            font-family: "CairoLocal", "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            background: #fff;
        }

        .page{
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            background: #fff;
        }

        /* toolbar on screen only */
        .toolbar{
            width: 210mm;
            margin: 10mm auto 0 auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn{
            border: 1px solid #ddd;
            background: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: 12px;
        }
        .btn:hover{ background: #f7f7f7; }

        .container{ width: 100%; padding: 0 0; }

        .header{
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid var(--border);
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .header .block{ width: 50%; }
        .title{
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 6px 0;
        }

        .kv{ margin: 0; line-height: 1.7; }
        .kv b{ font-weight: 700; }
        .muted{ color: var(--muted); }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid var(--border);
            table-layout: fixed;
        }

        th, td{
            border: 1px solid var(--border);
            padding: 6px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        thead th{
            background: var(--bg);
            font-weight: 700;
            text-align: center;
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        td.num{
            text-align: left;
            direction: ltr;
            white-space: nowrap;
        }

        td.center{ text-align: center; }
        td.wrap{ white-space: normal; }

        tr{ page-break-inside: avoid; }

        .totals{
            margin-top: 10px;
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .totals-row{
            display: flex;
            justify-content: space-between;
            align-items: center;
            line-height: 1.9;
        }

        .totals-row .label{ font-weight: 700; }
        .totals-row .value{ direction:ltr; text-align:left; font-weight: 700; }

        .notes{
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .notes h4{
            margin: 0 0 6px 0;
            font-size: 13px;
        }

        .footer{
            position: fixed;
            bottom: 8mm;
            left: 10mm;
            right: 10mm;
            font-size: 10px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pages:after{
            content: counter(page) " / " counter(pages);
        }

        @media print {
            .toolbar { display: none !important; }
            .page { width: auto; min-height: auto; margin: 0; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>

<body>

{{-- Toolbar (screen only) --}}
@if(empty($isPdf))
<div class="toolbar">
    <button class="btn" onclick="window.print()">🖨️ طباعة</button>
</div>
@endif

<div class="page">
    <div class="container">

        <div class="header">
            <div class="block">
                <p class="title">مقايسة أعمال</p>
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
                <th style="width:44px;">م</th>
                <th>البند</th>
                <th style="width:70px;">الوحدة</th>
                <th style="width:80px;">الكمية</th>
                <th style="width:95px;">سعر الوحدة</th>
                <th style="width:110px;">الإجمالي</th>
                <th style="width:160px;">ملاحظات</th>
            </tr>
            </thead>

            <tbody>
            @forelse($items as $i => $it)
                <tr>
                    <td class="center">{{ $it->sort_order ?? ($i + 1) }}</td>
                    <td class="wrap">{{ $it->workItem?->name ?? '-' }}</td>
                    <td class="center">{{ $it->unit?->name ?? '-' }}</td>
                    <td class="num">{{ number_format((float) $it->quantity, 3) }}</td>
                    <td class="num">{{ number_format((float) $it->unit_price, 2) }}</td>
                    <td class="num">{{ number_format((float) $it->total_price, 2) }}</td>
                    <td class="wrap">{{ $it->notes ?? '' }}</td>
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
                <div class="label">الإجمالي ({{ $currency }})</div>
                <div class="value">{{ number_format((float) $total_amount, 2) }}</div>
            </div>
            <div class="totals-row muted">
                <div class="label">إجمالي البنود (حسابيًا)</div>
                <div class="value">{{ number_format((float) $subtotal, 2) }}</div>
            </div>
        </div>

        @if(!empty($boq->notes))
            <div class="notes">
                <h4>ملاحظات</h4>
                <div class="wrap">{{ $boq->notes }}</div>
            </div>
        @endif

    </div>
</div>

<div class="footer">
    <div>BOQ Report</div>
    <div>صفحة <span class="pages"></span></div>
</div>

</body>
</html>
