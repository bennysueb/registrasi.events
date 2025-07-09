@extends('template.template')
@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>Tamu</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Guest</div>
        <div class="breadcrumb-item">Add</div>
      </div>
    </div>

    <div class="section-body">
      <h2 class="section-title">Add Student</h2>

      <div class="card">
        <div class="card-header">
          <h4 style="font-weight:normal; font-size:14px;">* required</h4>
          <div class="card-header-action">
            <a class="btn btn-sm btn-secondary" href="{{ url('guest') }}"><i class="fa fa-arrow-left"></i> Kembali</a>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ url('guest/store') }}" method="POST" autocomplete="off">
            @method('POST')
            @csrf
            <div class="row">
              <div class="col-xl-6">
                <div class="form-group">
                  <label for="">Student Name *</label>
                  <input class="form-control" name="name" value="{{ old('name') }}" type="text" autofocus>
                  @error('name')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="">Email *</label>
                  <input class="form-control" name="email" value="{{ old('email') }}" type="text">
                  @error('email')
                  <small class="text-danger"> {{ $message }} </small>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="">Phone Number *</label>
                  <input class="form-control" name="phone" value="{{ old('phone') }}" type="text" placeholder="ex. 6281225764094">
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