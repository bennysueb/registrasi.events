@extends('template.template')
@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>Undangan</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Invitation</div>
        <div class="breadcrumb-item">Add</div>
      </div>
    </div>

    <div class="section-body">
      <h2 class="section-title">Add Student E-Tiket </h2>

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
              <form action="{{ url('invite/store') }}" method="POST" autocomplete="off">
                @method('POST')
                @csrf
                <div class="row">
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="">Student Name *</label>
                      <select class="select2 form-control" name="guest" id="">
                        <option value="">- Pilih -</option>
                        @foreach ($guests as $guest)
                        <option @if (old('guest')==$guest->id_guest) selected @endif value="{{ $guest->id_guest }}">{{ $guest->invitation == null ? $guest->name_guest : "* " . $guest->name_guest }}</option>
                        @endforeach
                      </select>
                      @error('guest')
                      <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>

                    <div class="form-group">
                      <label for="">University</label>
                      <input id="university" class="form-control" type="text" readonly value="">
                    </div>

                    <div class="form-group">
                      <label for="">Faculty</label>
                      <input id="faculty" class="form-control" type="text" readonly value="">
                    </div>

                    <div class="form-group" style="display: none;">
                      <label for="">Jenis Tamu *</label>
                      <select class="form-control" name="type" id="">
                        <option @if (old('type')=="Visitor" ) selected @endif value="Visitor">Visitor</option>
                      </select>
                      @error('type')
                      <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>

                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <div class="row">
                        <div class="col-md-6">
                          <label for="">Email</label>
                          <input id="email" class="form-control" type="text" readonly value="">
                        </div>
                        <div class="col-md-6">
                          <label for="">Telp</label>
                          <input id="telp" class="form-control" type="text" readonly value="">
                        </div>
                      </div>

                    </div>
                    <div class="form-group">
                      <label for="">NIM</label>
                      <input id="nim" class="form-control" type="text" readonly value="">
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

<script>
  $(document).ready(function() {

    function getGuest() {
      $.ajax({
        url: "{{ url('invite/get-guest') }}",
        method: "GET",
        type: "JSON",
        data: {
          id_guest: $("select[name='guest']").val()
        },
        success: (res) => {
          var data = res.data;
          if (data) {
            $("#email").val(data.email_guest)
            $("#telp").val(data.phone_guest)
            $("#university").val(data.university_guest)
            $("#faculty").val(data.faculty_guest)
            $("#nim").val(data.nim_guest)
          }
        },
        error: () => {
          ;
          console.log("failed get data")
        }
      })
    }

    getGuest();

    $("select[name='guest']").change(function() {
      getGuest();
    })

  })
</script>

@endsection