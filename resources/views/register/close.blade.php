@extends('template.scan')
@section('content')





<title>Registration Form - {{ mySetting()->name_app != '' ? mySetting()->name_app : env('APP_NAME') }}</title>
<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-xl-6">
            <div class="card mt-5">
                <div class="card-header d-block text-center" style="padding: 75px; background-image: url(https://aigis-moi.id/dont_delete/banner_register_aigis.png); background-size: contain;background-repeat: no-repeat;">
                    <!-- <img src="{{ mySetting()->logo_app != '' ? asset('img/app/'.mySetting()->logo_app) : asset('template/assets/img/logo.png') }}" alt="logo" width="200"> -->
                    <!-- <h4 style="color:rgb(253, 253, 253);"> FORM ATTENDANCE CONFIRMATION</h4> -->
                </div>
                <div class="card-body">
                    <h5> Mohon Maaf Pendaftaran Sudah Ditutup</h5>
                    <br>
                    Anda dapat menghadiri kegiatan ini secara online via zoom meeting dengan melakukan pendaftaran di
                    <a href="https://zlinks.id/fih-zoom">www.zlinks.id/fih-zoom</a>
                    <br>
                    Terimakasih.
                </div>
            </div>

            <div class="simple-footer text-muted">
                Copyright &copy; 2025 - AIGIS
            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="modal-validation" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header" style="margin-top: 30px; margin-bottom: -35px;">
                <h5>Hi <input style="border: none; background-color:#fff; font-weight: bold;" id="valid-name" disabled></h5>
            </div>

            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Please check your email again.</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-header" style="margin-top: -30px; margin-bottom: -20px;">
                <p>We will send you an E-Ticket via your registered email to redeem your ID Card.</p>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label for="">Your Email</label>
                    <input id="valid-email" disabled class="form-control form-control-sm" value="" type="text">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>
                <button id="simpan-data" type="button" class="btn btn-primary">YES SEND</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $("#simpan").click(function() {

            var name = $("input[name='name']").val();
            var email = $("input[name='email']").val();
            var phone = $("input[name='phone']").val();
            var type_institution = $("select[name='type_institution']").val(); // Fix: Correctly get the selected category Fix: Correctly get the selected category
            var media_type = $("input[name='media_type']").val();
            var institution = $("input[name='institution']").val();
            var occupation = $("input[name='occupation']").val();
            $("#valid-name").val(name)
            $("#valid-email").val(email)
            $("#valid-phone").val(phone)
            $("#valid-type_institution").val(type_institution)
            $("#valid-media_type").val(media_type)
            $("#valid-institution").val(institution)
            $("#valid-occupation").val(occupation)
            $("#modal-validation").modal('show');
        })

        $("#simpan-data").click(function() {
            $("#form-register").submit();
        })

    })
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var day1Checkbox = document.getElementById('day_1');
        var day3Checkbox = document.getElementById('day_3');
        var programOpening = document.getElementById('program_opening');
        var programConcert = document.getElementById('program_concert');

        function togglePrograms() {
            // Opening Ceremony
            if (day1Checkbox.checked) {
                programOpening.classList.add('show');
            } else {
                programOpening.classList.remove('show');
                programOpening.querySelector('input').checked = false;
            }

            // Green Concert
            if (day3Checkbox.checked) {
                programConcert.classList.add('show');
            } else {
                programConcert.classList.remove('show');
                programConcert.querySelector('input').checked = false;
            }
        }

        day1Checkbox.addEventListener('change', togglePrograms);
        day3Checkbox.addEventListener('change', togglePrograms);

        togglePrograms(); // initialize on page load
    });
</script>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        var type_institution = document.querySelector('#type_institution');
        var otherInstitution = document.querySelector('#otherInstitution');
        var mediaTypeOptions = document.querySelector('#mediaTypeOptions');

        // Add "Other" option if not exists
        var otherOptionExists = Array.from(type_institution.options).some(function(option) {
            return option.value === "other";
        });

        if (!otherOptionExists) {
            var otherOption = document.createElement("option");
            otherOption.value = "other";
            otherOption.textContent = "Other";
            type_institution.appendChild(otherOption);
        }

        // Initially hide fields
        otherInstitution.style.display = "none";
        mediaTypeOptions.style.display = "none";
        otherInstitution.value = "";

        type_institution.addEventListener("change", function() {
            var selectedValue = this.value;

            if (selectedValue === "other") {
                otherInstitution.style.display = "block";
            } else {
                otherInstitution.style.display = "none";
                otherInstitution.value = "";
            }

            if (selectedValue === "Media") {
                mediaTypeOptions.style.display = "block";
            } else {
                mediaTypeOptions.style.display = "none";
                // Optionally clear selection:
                var radios = mediaTypeOptions.querySelectorAll('input[type="radio"]');
                radios.forEach(r => r.checked = false);
            }
        });
    });
</script>




<!-- script signature -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var canvas = document.getElementById("signature-pad");
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)', // Warna background transparan
            penColor: 'black' // Warna tinta
        });

        var clearButton = document.getElementById("clear-signature");
        var signatureDataInput = document.getElementById("signature-data");

        clearButton.addEventListener("click", function() {
            signaturePad.clear();
            signatureDataInput.value = "";
        });

        // Simpan tanda tangan sebelum submit
        document.getElementById("simpan-data").addEventListener("click", function() {
            if (!signaturePad.isEmpty()) {
                signatureDataInput.value = signaturePad.toDataURL(); // Konversi ke base64
                document.getElementById("form-register").submit();
            } else {
                alert("Your digital signature please!");
            }
        });
    });
</script>



@endsection