<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>RFID - Check In</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        input[type="text"] {
            font-size: 24px;
            padding: 10px;
            width: 300px;
            text-align: center;
        }

        .message {
            margin-top: 20px;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>FOR CHECK-IN USING RFID</h2>
    <form id="formScanRfid">
        <input type="text" id="rfid" name="rfid" autofocus autocomplete="off" placeholder="Tap the ID Card Here">
    </form>

    <div class="message" id="message"></div>

    <script>
        $(document).ready(function() {
            $('#rfid').focus();

            $('#formScanRfid').on('submit', function(e) {
                e.preventDefault();
                var rfid = $('#rfid').val();

                if (rfid != '') {
                    $.ajax({
                        url: "{{ url('scan/scanRfidProcess') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            rfid: rfid
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('#message').css('color', 'green');
                            } else if (response.status == "warning") {
                                $('#message').css('color', 'orange');
                            } else {
                                $('#message').css('color', 'red');
                            }
                            $('#message').html(response.message);
                            $('#rfid').val('');
                            $('#rfid').focus();
                        }
                    });
                }
            });

            // Biar otomatis submit setelah ketik (buat RFID scanner yang langsung input)
            $('#rfid').on('change', function() {
                $('#formScanRfid').submit();
            });
        });
    </script>

</body>

</html>