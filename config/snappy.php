<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"',
        'timeout' => false,
        'options' => [
            'encoding' => 'utf-8',
            'page-size' => 'A4',
            'margin-top'    => '10mm',
            'margin-right'  => '10mm',
            'margin-bottom' => '15mm',
            'margin-left'   => '10mm',

            // RTL + Arabic
            'enable-local-file-access' => true,
            'print-media-type' => true,

            // Better rendering
            'disable-smart-shrinking' => true,
        ],
    ],

    'image' => [
        'enabled' => true,
       'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"',
        'timeout' => false,
        'options' => [],
    ],

];
