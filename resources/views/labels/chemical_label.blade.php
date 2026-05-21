<!DOCTYPE html>
<html>

<head>
    <title>Chemical Label</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .label {
            width: 300px;
            height: 150px;
            padding: 10px;
            border: 1px dashed #333;
            text-align: center;
            margin: 0 auto;
        }

        .barcode {
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <style>
        .label {
            width: 2.5in;
            height: 1.5in;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            font-family: Arial, sans-serif;
            margin: 10px auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    
        .label h4 {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 6px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    
        .barcode img {
            max-width: 100%;
            height: 1in;
            display: block;
            margin: 0 auto;
        }
    
        @media print {
            .label {
                page-break-inside: avoid;
            }
        }
    </style>
    
        <div class="label">
            <h4>{{ $chemical->name }}</h4>
            <div class="barcode">
                @if(!empty($chemical->id))

                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG((string) $chemical->id, 'C128', 1.5, 40, [0, 0, 0], false) }}" alt="Barcode">
                @endif
            </div>
        </div>
    
    <div class="no-print" style="text-align:center; margin-top:70px;">
        <button onclick="window.print();">Print Label</button>
    </div>
</body>

</html>
