<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label</title>

</head>

<body>
    <div class="label-content">
        <!-- Manual ID -->
        <strong>Name:</strong> {{ $device->name ?? '-' }}
    </div>
    <div class="label-content">
        <!-- Manual ID -->
        <strong>Sr No.:</strong> {{ $device->sr_no ?? '-' }}
    </div>

    <!-- QR Code -->
    <div class="qr-code mt-1">
        <img class="qrcodeimage"
            src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qrCodeText, 'QRCODE', 3, 3, [39, 48, 54]) }}"
            style="width: 100px;">
    </div>

    <div class="label-content">
        <!-- Manual ID -->
        <strong>Manual ID:</strong> {{ $device->manual_id ?? '-' }}
    </div>

    <script>
        // Trigger the print dialog automatically when the page is loaded, but do not close the window
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
