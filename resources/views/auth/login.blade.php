<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{ URL::asset('build/images/favicon-32x32.png') }}" type="image/png">
    <title>Login Page | TDS</title>

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
              <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
                @endif

                <!-- Error Message -->
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ url('/TDS/auth/login') }}" method="POST">
                    @csrf
                  <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                    <p class="lead fw-normal mb-1 me-3">Sign in</p>
                  </div>
                  <!-- Email input -->
                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="text" id="form3Example3" class="form-control form-control-lg"
                      placeholder="Enter username" name="username" />
                      <input type="hidden" name="device_identifier" id="device_identifier">
                    <label class="form-label" for="form3Example3">Username</label>
                  </div>

                  <!-- Password input -->
                  <div data-mdb-input-init class="form-outline mb-3 position-relative">
                    <input type="password" id="form3Example4" class="form-control form-control-lg"
                      placeholder="Enter password" name="password" />
                    <label class="form-label" for="form3Example4">Password</label>
                    <!-- Eye icon for toggling password visibility -->
                    <i class="material-icons-outlined position-absolute" id="togglePassword" style="right: 15px; top: 30%; transform: translateY(-50%); cursor: pointer;">
                        visibility
                    </i>
                </div>
                  <a href="{{url('/TDS/auth/forgot-password')}}">Forget Password</a>
                  <div class="text-center text-lg-start mt-4 pt-2">
                    <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                      style="padding-left: 2.5rem; padding-right: 2.5rem;">Login</button>
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
  <script>
    (function () {
        const deviceIdKey = 'device_identifier';
        let deviceId = localStorage.getItem(deviceIdKey) || '{{ cookie('device_identifier') }}';

        // If no deviceId exists, generate a UUID
        if (!deviceId) {
            deviceId = crypto.randomUUID(); // Modern browsers support crypto.randomUUID()
            localStorage.setItem(deviceIdKey, deviceId);
        }

        // Set the form input value
        document.getElementById('device_identifier').value = deviceId;
    })();
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('form3Example4');
        const icon = this;
        // Toggle the type attribute
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        // Toggle the icon text
        icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
    });
  </script>
</body>

</html>

