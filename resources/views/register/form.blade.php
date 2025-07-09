@extends('template.scan')
@section('content')





<title>Registration Form - {{ mySetting()->name_app != '' ? mySetting()->name_app : env('APP_NAME') }}</title>
<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8 col-xl-6">
            <div class="card mt-5">
                <div class="card-header d-block text-center" style="padding: 75px; background-image: url({{ asset('img/event/' . $event->image_register_event) }}); background-size: contain;background-repeat: no-repeat;">
                    <!-- <img src="{{ mySetting()->logo_app != '' ? asset('img/app/'.mySetting()->logo_app) : asset('template/assets/img/logo.png') }}" alt="logo" width="200"> -->
                    <!-- <h4 style="color:rgb(253, 253, 253);"> FORM ATTENDANCE CONFIRMATION</h4> -->
                </div>
                <div class="card-body">
                    <form id="form-register" action="{{ url('register-process') }}" method="POST" autocomplete="off">
                        @method('POST')
                        @csrf
                        <div class="row">
                            <div class="col-12">

                                <div class="form-group">
                                    <label for="">Full Name <span style="color: red;">*</span></label>
                                    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" type="text" placeholder="Enter Your Full Name" autofocus>
                                    @error('name')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span style="color: red;">*</span></label>
                                    <input
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        name="email"
                                        value="{{ old('email') }}"
                                        type="email"
                                        placeholder="e.g: yourname@mail.com"
                                        required>
                                    @error('email')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="">Phone Number <span style="color: red;">*</span></label>
                                    <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" type="number" placeholder="Enter your Phone Number">
                                    @error('phone')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="">University <span style="color: red;">*</span></label>
                                    <input class="form-control @error('university') is-invalid @enderror" name="university" value="{{ old('university') }}" type="text" placeholder="Enter Your University" autofocus>
                                    @error('university')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="">Faculty <span style="color: red;">*</span></label>
                                    <input class="form-control @error('faculty') is-invalid @enderror" name="faculty" value="{{ old('faculty') }}" type="text" placeholder="Enter Your Faculty" autofocus>
                                    @error('faculty')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="">NIM <span style="color: red;">*</span></label>
                                    <input class="form-control @error('nim') is-invalid @enderror" name="nim" value="{{ old('nim') }}" type="number" placeholder="Enter Your Student ID Number" autofocus>
                                    @error('nim')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="">Digital Signature <span style="color: red;">*</span></label>
                                    <div style="border: 1px solid #e5e5e5; padding: 5px; position: relative;">
                                        <canvas id="signature-pad" style="width: 100%; height: 200px; border: 1px solid black;"></canvas>
                                    </div>
                                    <button type="button" id="clear-signature" class="btn btn-danger btn-sm mt-2">Clear Signature</button>
                                    <input type="hidden" name="signature" id="signature-data" required>
                                    @error('signature')
                                    <small class="text-danger"> {{ $message }} </small>
                                    @enderror
                                </div>

                                <div>
                                    <button type="button" id="simpan" class="btn btn-block btn-primary btn-lg"><i class="fa fa-paper-plane" style="margin-right: 10px;"></i>SUBMIT</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="simple-footer text-muted">
                Copyright &copy; 2025 - TOYOTA X AIGIS
            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="modal-validation" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header" style="margin-top: 30px; margin-bottom: -35px;">
                <h5>Dear <span id="valid-name"></span></h5>
            </div>

            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Please check your email again.</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-header" style="margin-top: -30px; margin-bottom: -20px;">
                <p>We will send you an E-Ticket via your registered email.</p>
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
            var university = $("input[name='university']").val();
            var faculty = $("input[name='faculty']").val();
            $("#valid-name").text(name)
            $("#valid-email").val(email)
            $("#valid-phone").val(phone)
            $("#valid-university").val(university)
            $("#valid-faculty").val(faculty)
            $("#modal-validation").modal('show');
        })

        $("#simpan-data").click(function() {
            $("#form-register").submit();
        })

    })
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