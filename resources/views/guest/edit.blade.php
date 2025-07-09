@extends('template.template')
@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>Tamu</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Guest</div>
        <div class="breadcrumb-item">Edit</div>
      </div>
    </div>

    <div class="section-body">
      <h2 class="section-title">Edit Tamu</h2>

      <div class="card">
        <div class="card-header">
          <h4 style="font-weight:normal; font-size:14px;">* required</h4>
          <div class="card-header-action">
            <a class="btn btn-sm btn-secondary" href="{{ url('guest') }}"><i class="fa fa-arrow-left"></i> Kembali</a>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ url('guest/update/'. $guest->id_guest ) }}" method="POST" autocomplete="off">
            @method('PUT')
            @csrf
            <div class="row">
              <div class="col-xl-6">
                <div class="form-group">
                  <label for="">Student Name *</label>
                  <input class="form-control" name="name" value="{{ old('name', $guest->name_guest) }}" type="text">
                  @error('name')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="">Email *</label>
                  <input class="form-control" name="email" value="{{ old('email', $guest->email_guest) }}" type="text">
                  @error('email')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="">Phone Number *</label>
                  <input class="form-control" name="phone" value="{{ old('phone', $guest->phone_guest ) }}" type="text" placeholder="ex. 6281225764094">
                  @error('phone')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="">University *</label>
                  <input class="form-control" name="university" value="{{ old('university', $guest->university_guest) }}" type="text">
                  @error('university')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="">Faculty *</label>
                  <input class="form-control" name="faculty" value="{{ old('faculty', $guest->faculty_guest) }}" type="text">
                  @error('faculty')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>
                <div class="form-group">
                  <button class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </section>
</div>

@endsection