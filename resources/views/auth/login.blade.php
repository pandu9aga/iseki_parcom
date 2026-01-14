@extends('layouts.main')
@section('content')
<div class="container-fluid p-0">
    <section class="resume-section">
        <div class="resume-section-content">
            <h3 class="mb-3">
                Detective
                <span class="text-primary">AI</span>
            </h3>
            <div class="subheading mb-5">
                Login
            </div>
            <form role="form" class="text-start" action="{{ route('login.auth') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($errors->any())
                  <div>
                      @foreach ($errors->all() as $error)
                        <p style="color:red;">{{ $error }}</p>
                      @endforeach
                  </div>
                @endif
                <!-- Username input -->
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="text" id="form2Example1" name="Username_User" class="form-control" required />
                    <label class="form-label" for="form2Example1">Username</label>
                </div>

                <!-- Password input -->
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="password" id="form2Example2" name="Password_User" class="form-control" required />
                    <label class="form-label" for="form2Example2">Password</label>
                </div>

                <!-- Submit button -->
                <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary text-white btn-block mb-4">Login</button>
            </form>
        </div>
    </section>
</div>
@endsection

@section('style')
<link href="{{asset('assets/datatables/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{asset('assets/datatables/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
@endsection