<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{ URL::asset('build/images/favicon-32x32.png') }}" type="image/png">
    <title>@yield('title') | TDS</title>

    @yield('css')
    @stack('styles')
    @include('layouts.head-css')

    <style>
        td{
            font-size: 16px;
        }
        th{
            font-size: 16px;
        }
        td button {
            width: 40px;
            height: 35px;
            padding: 2px!important;
            font-size: 10px;
        }
        @media only screen and (max-width: 600px) {
            td{
            font-size: 12px;
            }
            th{
                font-size: 12px;
            }
            td button {
                width: 35px;
                height: 30px;
                padding: 4px!important;
                font-size: 10px;
            }
            td button i{
                font-size: 2px;
            }
        }

        html[data-bs-theme='dark'] {
            background-color: #121212;
            color: #ffffff;
        }

        html[data-bs-theme='light'] {
            background-color: #ffffff;
            color: #000000;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>

@include('layouts.topbar')
@include('layouts.sidebar')

<!--start main wrapper-->
<main class="main-wrapper">
    <div class="main-content">

        @yield('content')

    </div>
</main>
<!--end main wrapper-->

<!--start overlay-->
    <div class="overlay btn-toggle"></div>
<!--end overlay-->

  @include('layouts.footer')

  @include('layouts.right-sidebar')

  @include('layouts.vendor-scripts')

  @yield('scripts')
    <script>

    flatpickr('.date-picker', {
        altInput: true,
        altFormat: "d M Y",
        dateFormat: "Y-m-d",
    });

        window.history.pushState(null, '', window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, '', window.location.href);
        };

        $(document).ready(function () {
            // On page load, check the saved theme in localStorage
            const savedTheme = localStorage.getItem('theme') || 'light'; // Default to light
            $("html").attr("data-bs-theme", savedTheme); // Apply the theme
            $(".dark-mode i").text(savedTheme === 'dark' ? 'light_mode' : 'dark_mode'); // Update the icon

            // Click handler for toggling the theme

            $(".dark-mode").click(function () {
                const currentTheme = $("html").attr("data-bs-theme"); // Get the current theme
                const newTheme = currentTheme === 'dark' ? 'dark' : 'light'; // Toggle the theme

                // Update the theme attribute
                $("html").attr("data-bs-theme", newTheme);

                // Update the icon text
                $(".dark-mode i").text(newTheme === 'dark' ? 'light_mode' : 'dark_mode');

                // Save the new theme in localStorage
                localStorage.setItem('theme', newTheme);
                console.log(localStorage.getItem('theme'));

            });
        });
    </script>
</body>

</html>
