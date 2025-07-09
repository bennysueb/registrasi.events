@extends('template.template')
@section('content')
<div class="main-content">
	<section class="section">
		<div class="section-header">
			<h1>Aplikasi</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></div>
				<div class="breadcrumb-item">App</div>
			</div>
		</div>

		<div class="section-body">
			<h2 class="section-title">Setting Aplikasi</h2>

			<div class="card">
				<div class="card-body">
					<form action="{{ url('setting/update') }}" method="POST" enctype="multipart/form-data"
						autocomplete="off">
						@method('PUT')
						@csrf
						<div class="row">
							<div class="col-xl-6">
								<div class="form-group">
									<label>Nama Aplikasi</label>
									<input name="name_app" type="text" value="{{ old('name_app', $setting->name_app) }}" class="form-control @error('name_app') is-invalid @enderror">
									@error('name_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
								</div>
								<div class="form-group">
									<label>Tema</label>
									<input name="theme_app" type="text" value="{{ old('theme_app', $setting->theme_app) }}" class="form-control @error('theme_app') is-invalid @enderror">
									@error('theme_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
								</div>
								<div class="form-group">
									<label>Deskripsi Aplikasi</label>
									<textarea name="description_app" class="form-control @error('description_app') is-invalid @enderror">{{ old('description_app', $setting->description_app) }}</textarea>
									@error('description_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
								</div>
								<div class="form-group">
									<label>Author Aplikasi</label>
									<input name="author_app" type="text" value="{{ old('author_app', $setting->author_app) }}" class="form-control @error('author_app') is-invalid @enderror">
									@error('author_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
								</div>
								<div class="form-group">
									<label>Kata Kunci Aplikasi</label>
									<input name="keywords_app" type="text" value="{{ old('keywords_app', $setting->keywords_app) }}" class="form-control @error('keywords_app') is-invalid @enderror">
									@error('keywords_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
								</div>

							</div>

							<div class="col-xl-6">
								<div class="form-group">
									<label>Logo Aplikasi</label>
									<input type="file" name="logo_app" class="form-control @error('logo_app') is-invalid @enderror"
										onchange="document.getElementById('imagePreviewLogo').src = window.URL.createObjectURL(this.files[0])">
									@error('logo_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
									@php
									if ($setting->logo_app != '') {
									if (file_exists(public_path('img/app/' . $setting->logo_app))) {
									$img = asset('img/app/' . $setting->logo_app);
									} else {
									$img = asset('asset/front/image-not-found.jpg');
									}
									} else {
									$img = asset('template/assets/img/logo.png');
									}
									@endphp
									<img id="imagePreviewLogo" src="{{ $img }}" onerror="this.style.display='none'" onload="this.style.display='block'" style="width:100px; margin-top:10px; border:0px solid;" />
								</div>

								<div class="form-group">
									<label>Favicon Aplikasi</label>
									<input type="file" name="favicon_app" class="form-control @error('favicon_app') is-invalid @enderror"
										onchange="document.getElementById('imagePreviewLogo').src = window.URL.createObjectURL(this.files[0])">
									@error('favicon_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
									@php
									if ($setting->favicon_app != '') {
									if (file_exists(public_path('img/app/' . $setting->favicon_app))) {
									$img = asset('img/app/' . $setting->favicon_app);
									} else {
									$img = asset('asset/front/image-not-found.jpg');
									}
									} else {
									$img = asset('template/assets/img/logo.png');
									}
									@endphp
									<img id="imagePreviewLogo" src="{{ $img }}" onerror="this.style.display='none'" onload="this.style.display='block'" style="width:100px; margin-top:10px; border:0px solid;" />
								</div>

								<div class="form-group">
									<label>Warna Background (Registrasi, etc)</label>
									<input class="form-control" name="color_bg_app" type="color" value="{{ old('color_bg_app', $setting->color_bg_app) }}">
								</div>
								<div class="form-group">
									<label>Gambar Background (Registrasi, etc)</label>
									<label>
										<input type="checkbox" name="image_bg_status" value="1" class="custom-switch-input" @if ($setting->image_bg_status == 1) checked @endif>
										<span class="custom-switch-indicator"></span>
									</label>
									<input type="file" name="image_bg_app" class="form-control @error('image_bg_app') is-invalid @enderror"
										onchange="document.getElementById('imagePreviewBg').src = window.URL.createObjectURL(this.files[0])">
									@error('image_bg_app')
									<small class="text-danger"> {{ $message }} </small>
									@enderror
									<img id="imagePreviewBg" src="{{ asset('img/app/'.$setting->image_bg_app) }}" onerror="this.style.display='none'" onload="this.style.display='block'" style="width:250px; margin-top:10px; border:0px solid;" />
								</div>
								<div class="text-right">
									<button class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
								</div>
							</div>
					</form>
				</div>
			</div>

		</div>
	</section>
</div>
@endsection