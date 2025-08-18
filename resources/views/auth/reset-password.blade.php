<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{ URL::asset('build/images/favicon-32x32.png') }}" type="image/png">
    <title>Reset Password | TDS</title>

    <link href="{{ URL::asset('build/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('build/plugins/metismenu/metisMenu.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('build/plugins/metismenu/mm-vertical.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('build/plugins/simplebar/css/simplebar.css') }}">
    <!--bootstrap css-->
    <link href="{{ URL::asset('build/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
    <!--main css-->
    <link href="{{ URL::asset('build/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/sass/main.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/sass/dark-theme.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/sass/semi-dark.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/sass/bordered-theme.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/sass/responsive.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<!--start main wrapper-->
<main class="main-wrapper ms-0">
    <div class="main-content">

        <div class="container-fluid h-custom w-75">
            <div class="row d-flex justify-content-center align-items-center h-100">
              <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                  class="img-fluid" alt="Sample image">
              </div>

              @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
              <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                <form method="POST" action="{{ route('password.reset') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <!-- Email Address -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input type="email" id="form3Example4" class="form-control form-control-lg"
                          placeholder="Enter Email" name="email" />
                        <label class="form-label" for="form3Example4">Password</label>
                      </div>

                    <!-- Password -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input type="password" id="form3Example4" class="form-control form-control-lg"
                          placeholder="Confirm password" name="password" />
                        <label class="form-label" for="form3Example4">Password</label>
                      </div>

                    <!-- Confirm Password -->

                    <div data-mdb-input-init class="form-outline mb-3">
                        <input type="password" id="form3Example4" class="form-control form-control-lg"
                          placeholder="Confirm password" name="password_confirmation" />
                        <label class="form-label" for="form3Example4">Confirm Password</label>
                      </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                          style="padding-left: 2.5rem; padding-right: 2.5rem;">Reset Password</button>
                      </div>
                </form>
              </div>
            </div>
          </div>


    </div>
</main>
<!--end main wrapper-->

<!--start overlay-->
    <div class="overlay btn-toggle"></div>
<!--end overlay-->
  @include('layouts.vendor-scripts')

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @if (session('success'))
            <script>
                Swal.fire({
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            </script>
            @endif

            @if (session('error'))
            <script>
                Swal.fire({
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>
        @endif
</body>

</html>

