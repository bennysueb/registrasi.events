@extends('template.template')
@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>Undangan</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Invitation</div>
        <div class="breadcrumb-item">Edit</div>
      </div>
    </div>

    <div class="section-body">
      <h2 class="section-title">Edit Undangan</h2>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4 style="font-weight:normal; font-size:14px;">* required</h4>
              <div class="card-header-action">
                <a class="btn btn-sm btn-secondary" href="{{ url('invite') }}"><i class="fa fa-arrow-left"></i> Kembali</a>
              </div>
            </div>
            <div class="card-body">
              <form action="{{ url('invite/update/'. $invitation->id_invitation ) }}" method="POST" autocomplete="off">
                @method('PUT')
                @csrf
                <div class="row">
                  <div class="col-xl-6">
                    <div class="form-group">
                      <div class="row">
                        <div class="col-md-6">
                          <label for="">Student Name *</label>
                          <input class="form-control" type="text" value="{{ $invitation->name_guest }}" readonly>
                          @error('guest')
                          <small class="text-danger"> {{ $message }} </small>
                          @enderror
                        </div>
                        <div class="col-md-6">
                          <label for="">QrCode</label>
                          <input class="form-control" type="text" value="{{ $invitation->qrcode_invitation }}" readonly>
                        </div>
                      </div>
                    </div>

                    <div class="form-group" style="display: none;">
                      <label for="">Jenis Tamu *</label>
                      <select class="form-control" name="type" id="">
                        <option disabled value="Online">ONLINE</option>
                        <option @if ( $invitation->type_invitation == "Visitor") selected @endif value="Visitor">VISITOR</option>
                        <option value="Invitation">INVITATION</option>
                      </select>
                      @error('type')
                      <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>

                    <div class="form-group">
                      <label for="">RFID TAG</label>
                      <input class="form-control" name="rfid_number" value="{{ $invitation->rfid_number_invitation }}" type="text" autofocus>
                      @error('rfid_number')
                      <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>

                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <div class="row">
                        <div class="col-md-6">
                          <label for="">Email</label>
                          <input id="email" class="form-control" type="text" readonly value="{{ $invitation->email_guest }}">
                        </div>
                        <div class="col-md-6">
                          <label for="">Telp</label>
                          <input id="telp" class="form-control" type="text" readonly value="{{ $invitation->phone_guest }}">
                        </div>
                      </div>
                    </div>

                    <div class="form-group">
                      <label for="">University *</label>
                      <input id="university" class="form-control" type="text" readonly value="{{ $invitation->university_guest }}">
                    </div>
                    <div class="form-group">
                      <label for="">Faculty *</label>
                      <input id="faculty" class="form-control" type="text" readonly value="{{ $invitation->faculty_guest }}">
                    </div>

                    <div class="form-group">
                      <label for="">NIM *</label>
                      <input id="nim" class="form-control" type="text" readonly value="{{ $invitation->nim_guest }}">
                    </div>

                  </div>
                  <div class="col">
                    <div class="form-group">
                      <button class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection