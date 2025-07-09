<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Scan RFID - Check In</title>
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

    <h2>Scan RFID untuk Check-In</h2>
    <form id="formScanRfid">
        <input type="text" id="rfid" name="rfid" autofocus autocomplete="off" placeholder="Tempelkan Kartu RFID">
    </form>

    <div class="message" id="message"></div>

    <script>
        $(document).ready(function() {
            var scanning = true;
            $('#rfid').val(''); // Kosongkan input setiap kali halaman load
            $('#rfid').focus();

            // Submit manual via form
            $('#formScanRfid').on('submit', function(e) {
                e.preventDefault();
                var rfid = $('#rfid').val().trim();

                if (rfid != '' && scanning) {
                    scanning = false; // supaya tidak double submit

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
                            $('#rfid').val('').prop('disabled', true); // disable input

                            setTimeout(function() {
                                location.reload(); // reload page setelah 3 detik
                            }, 3000);
                        },
                        error: function() {
                            $('#message').css('color', 'red').html('Terjadi kesalahan koneksi.');
                            $('#rfid').val('').prop('disabled', true);
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        }
                    });
                }
            });

            // Deteksi input dari scanner
            $('#rfid').on('keypress', function(e) {
                if (e.which == 13) { // ENTER key ditekan
                    e.preventDefault(); // cegah submit normal
                    if (scanning) {
                        $('#formScanRfid').submit();
                    }
                }
            });

            // Fokus terus ke input (kalau user klik ke tempat lain)
            setInterval(function() {
                if (!$('#rfid').is(':focus')) {
                    $('#rfid').focus();
                }
            }, 1000);
        });
    </script>






</body>

</html>