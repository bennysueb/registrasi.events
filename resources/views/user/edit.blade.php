@extends('template.template')
@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>User</h1>
      <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Data user</div>
      </div>
    </div>

    <div class="section-body">
      <h2 class="section-title">Edit User</h2>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4 style="font-weight:normal; font-size:14px;">* required</h4>
              <div class="card-header-action">
                <a class="btn btn-sm btn-secondary" href="{{ url('user') }}"><i class="fa fa-arrow-left"></i> Kembali</a>
              </div>
            </div>
            <div class="card-body">
              <form action="{{ url('user/update/'. $user->id ) }}" method="POST" autocomplete="off">
                @method('PUT')
                @csrf
                <div class="row">
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="">Nama Akun *</label>
                      <input class="form-control" name="name" value="{{ old('name') ? old('name') : $user->name }}" type="text">
                      @error('name')
                        <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>
                    <div class="form-group">
                      <label for="">Email *</label>
                      <input class="form-control" name="email" value="{{ old('email') ? old('email') : $user->email }}" type="text">
                      @error('email')
                        <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>
                    <div class="form-group">
                      <label for="">Role *</label>
                      @if ($user->id == 1)
                        @php $disabled = "disabled" @endphp
                      @else
                        @php $disabled = "" @endphp
                      @endif
                      <select class="form-control" name="role">
                        <option value="1">Admin</option>
                        <option {{ $user->role == "2" ? "selected" : "" }} value="2" {{ $disabled }}>Resepsionis</option>
                      </select>
                      @error('role')
                        <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>
                    <div class="form-group">
                      <label for="">Username *</label>
                      <input class="form-control" name="username" value="{{ old('username') ? old('username') : $user->username }}" type="text">
                      @error('username')
                        <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>
                    <div class="form-group">
                      <label>Password * <small>(Kosongkan jika tidak diganti)</small></label>
                      <div class="input-group">
                        <input type="password" name="password" class="form-control" value="{{ old("password") }}">
                        <div class="input-group-prepend show-pass" style="cursor: pointer">
                          <div class="input-group-text">
                            <i id="icon-lock" class="fas fa-lock"></i>
                          </div>
                        </div>
                      </div>
                      @error('password')
                      <small class="text-danger"> {{ $message }} </small>
                      @enderror
                    </div>
                    <div class="form-group">
                      <label for="">Keterangan User</label>
                      <input class="form-control" name="information" value="{{ old('information') }}" type="text">
                      @error('information') 
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
      </div>
    </div>
  </section>
</div>

<script>
  $(document).ready(function(){
    $(".show-pass").click(function(){
      var pass = $("input[name='password']").attr("type");
      $("#icon-lock").removeClass();
      if(pass == "password"){
        $("#icon-lock").addClass("fas fa-lock-open");
        $("input[name='password']").attr("type", "text");
      }else{
        $("#icon-lock").addClass("fas fa-lock");
        $("input[name='password']").attr("type", "password");
      }

    })
  })
</script>

@endsection
