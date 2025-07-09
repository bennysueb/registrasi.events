<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
	<title>{{ $event->type_event }} - {{ $event->name_event }}</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="description" content="{{ mySetting()->description_app }}">
	<meta name="keywords" content="{{ mySetting()->keywords_app }}">
	<meta name="author" content="{{ mySetting()->author_app }}">

	<!-- General CSS Files -->
	<link rel="stylesheet" href="{{ asset('template/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('template/node_modules/@fortawesome/fontawesome-free/css/all.css') }}">
	<!-- JS Libraies -->
	<link rel="stylesheet" href="{{ asset('plugin/sweetalert2/dist/sweetalert2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('template/node_modules/izitoast/dist/css/iziToast.min.css') }}">
	<!-- Template CSS -->
	<link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">
	<link rel="stylesheet" href="{{ asset('template/assets/css/components.css') }}">
	<link rel="icon" type="image/x-icon" href="{{ mySetting()->favicon_app != '' ? asset('img/app/'.mySetting()->favicon_app) : asset('template/assets/img/logo.png') }}">


	<style>
		.custom-bg {
			background-image: url("{{ mySetting()->image_bg_app != '' ? asset('img/app/'.mySetting()->image_bg_app) : asset('template/assets/img/logo.png') }}");
			height: 100%;
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
		}
	</style>
</head>

<body class="custom-bg">
	<script src="{{ asset('template/node_modules/jquery/dist/jquery.min.js') }}"></script>

	<div id="app">
		<div class="main-wrapper">
			<div class="container-fluid">

				<div class="row">

					<div class="col-sm-3 d-none d-lg-block">
						@if ($event->image_left_event != '' && $event->image_left_status == 1)
						<img src="{{ asset('img/event/' . $event->image_left_event) }}">
						@endif
					</div>
					<div class="col-lg-6 col-md-12 col-sm-12">

						<div class="text-center" style="color: {{ $event->color_text_event ?? "#e3eaef" }}">
							<div class="mt-4"></div>
							@php
							if ($event->image_event != ''):
							if (file_exists(public_path('img/event/' . $event->image_event))) {
							$img = asset('img/event/' . $event->image_event);
							} else {
							$img = asset('asset/front/image-not-found.jpg');
							}
							else:
							$img = asset('asset/front/default.png');
							endif;
							@endphp
							@if ($event->image_top_status == 1)
							<img src="{{ $img }}" style="width: 300px; border: 0px solid #eee;" alt="">
							@endif
							<div class="mt-4 text-center">
								<div class="h5">{{ $event->type_event }}</div>
								<div class="h2" style="margin-top:20px; font-weight: 800;">
									{!! nl2br($event->title_event) !!}
									{!! nl2br($event->name_event) !!}
								</div>
								<div style="margin:25px 0 20px 0;">
									<div class="h6 mb-10">
										<i>Kepada Yth.</i>
									</div>
									<div class="h5 bd-highlight">
										<!-- <b>{{ $invt->name_guest }}</b> -->

										@if ($invt->kehadiran == 'Diwakilkan' && !empty($invt->namawakil_guest))
										<div class="text-muted small">
											(Mewakili {{ $invt->name_guest }})
										</div>
										@endif
										<b>{{ $invt->namawakil_guest != null ? $invt->namawakil_guest : $invt->name_guest }}</b>


										<h6 class="font-weight-normal">NIM: <b>{{ $invt->nim_guest }}</b></h6>
										<h6 class="font-weight-normal">Fakultas: <b>{{ $invt->faculty_guest }}</b></h6>
									</div>


								</div>
							</div>
						</div>

						<div class="d-flex justify-content-center py-3">
							<div class="mx-3 text-right">
								<h6>{{ \Carbon\Carbon::parse($event->end_event)->isoFormat('DD MMMM YYYY') }}</h6>
							</div>
							<div style="padding: 0 1px; background-color: {{ $event->color_text_event }}"></div>
							<div class="mx-3 text-left">
								<h6>{{ \Carbon\Carbon::parse($event->start_event)->isoFormat('HH:mm') }} -
									{{ \Carbon\Carbon::parse($event->end_event)->isoFormat('HH:mm') }} WIB
								</h6>
							</div>
						</div>




						<div class="text-center py-3">
							<div class="text-center">
								<h5>{{ $event->place_event }}</h5>
							</div>
							<div class="text-center">
								<h6 class="font-weight-normal">{{ $event->location_event }}</h6>
							</div>
						</div>
						<div class="text-center">

							<h5 class="pt-6" style="margin:5px 0 0 0;">
								{{ strtoupper($invt->category_guest) }}
								{{ $invt->table_number_invitation != null ? '- '.ucwords($invt->table_number_invitation) : '' }}
							</h5>
							<div>
								{{ $invt->information_invitation }}
							</div>

							<p class="mt-3"><i>{!! nl2br($event->information_event) !!}</i></p>

						</div>
						<div class="text-center mt-4">
							<img src="{{ asset('/img/qrCode/' . $invt->qrcode_invitation . '.png') }}" class="rounded" style="width: 150px" alt="">
							<h5 class="mt-3">
								<div id="qrcode" class="h6" style="cursor:pointer">
									<span>
										{{ $invt->qrcode_invitation }}
									</span>
									<i class="far fa-copy"></i>
								</div>
							</h5>

							<a class="shadow-none btn rounded-pill btn-warning my-2"
								href="{{ url('download/' . $invt->qrcode_invitation) }}">Download QrCode</a>
						</div>

						<p class="py-2" style="font-size:12px;">* Simpan Kode QR ini dan tunjukkan kepada petugas registrasi untuk menukarkan Kartu Identitas Anda dilokasi acara.</p>

						<div style="margin:40px 0 10px 0; text-align:center; font-size:13px; color:{{ $event->color_text_event ?? "#e3eaef" }}">
							Developed by 3flo.conn
						</div>
					</div>


				</div>
				<div class="col-sm-3 d-none d-lg-block">
					@if ($event->image_right_event != '' && $event->image_right_status == 1)
					<img src="{{ asset('img/event/' . $event->image_right_event) }}">
					@endif
				</div>

			</div>

		</div>
	</div>
	</div>

	<!-- General JS Scripts -->
	<script src="{{ asset('template/node_modules/popper.js/dist/umd/popper.min.js') }}"></script>
	<script src="{{ asset('template/node_modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('template/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
	<script src="{{ asset('template/assets/js/stisla.js') }}"></script>
	<!-- JS Libraies -->
	<script src="{{ asset('template/node_modules/izitoast/dist/js/iziToast.min.js') }}"></script>
	<script src="{{ asset('plugin/sweetalert2/dist/sweetalert2.min.js') }}"></script>
	<!-- Template JS File -->
	<script src="{{ asset('template/assets/js/scripts.js') }}"></script>

	<script>
		$(document).ready(function() {
			$('#qrcode').click(function() {
				let textToCopy = $('#qrcode span').text();
				let tempTextarea = $('<textarea>');
				$('body').append(tempTextarea);
				tempTextarea.val(textToCopy).select();
				document.execCommand('copy');
				tempTextarea.remove();
				iziToast.success({
					title: 'Success',
					message: "The QRcode is successfully copied",
					position: 'bottomCenter'
				});
			});
		});
	</script>

	<script>
		$(document).ready(function() {
			let fromRegister = "{{ session('register-success') }}";
			let userEmail = "{{ session('register-email') }}";

			if (fromRegister) {
				Swal.fire({
					title: "Registrasi Berhasil",
					html: `
                ${fromRegister}<br>
                E-tiket akan dikirim ke email Anda: <b>${userEmail}</b><br><br>
                Follow Instagram kami untuk info selanjutnya:<br>
                <a href="https://www.instagram.com/aigis.events" target="_blank">@aigis.events</a>
            `,
					icon: "success",
					confirmButtonText: "Follow Sekarang",
					confirmButtonColor: "#006932",
				}).then(() => {
					window.location.href = "https://www.instagram.com/aigis.events";
				});
			}
		});
	</script>

</body>

</html>