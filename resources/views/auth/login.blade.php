
<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>MoniForm | Monitor Form MyAds</title>



    <link rel="stylesheet"

        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <link rel="stylesheet" href="{{asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css')}}">

    <link rel="stylesheet" href="{{asset('vendor/adminlte/dist/css/adminlte.min.css')}}">


    <style>
        /* ---- reset ---- */

        body {

            margin: 0;

            font: normal 75% Arial, Helvetica, sans-serif;

            overflow: hidden;

        }



        canvas {

            display: block;

            vertical-align: bottom;

        }



        /* ---- particles.js container ---- */

        #particles-js {

            position: absolute;

            width: 100%;

            height: 100%;

            background-color: #124170;

            background-repeat: no-repeat;

            background-size: cover;

            background-position: 50% 50%;

        }



        /* ---- login form styling ---- */

        .login-box {

            position: absolute;

            background: rgba(255, 255, 255, 0.8);

            border-radius: 8px;

            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);

            max-width: 400px;

            padding: 20px;

            z-index: 1;

            top: 50%;

            left: 50%;

            transform: translate(-50%, -50%);

        }



        .card-header {

            background: #124170;

            color: #fff;

        }



        /* ---- input styling ---- */

        .input-group input {

            border: 1px solid #124170;

            border-radius: 5px;

            padding: 10px;

            font-size: 16px;

            width: 100%;

        }



        .input-group input:focus {

            border-color: #124170;

            box-shadow: 0 0 5px rgba(211, 81, 194, 0.5);

            outline: none;

        }



        /* ---- button styling ---- */

        .btn-danger {

            background-color: #124170;

            border-color: #124170;

            color: #fff;

            padding: 10px;

            font-size: 16px;

            border-radius: 5px;

            width: 100%;

            cursor: pointer;

            transition: background-color 0.3s;

        }



        .btn-danger:hover {

            background-color: #124170;

            border-color: #124170;

        }



        @keyframes pulse {

            0% {

                box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);

            }



            50% {

                box-shadow: 0 0 20px rgba(255, 255, 255, 0.7);

            }



            100% {

                box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);

            }

        }



        .body-pulse {

            animation: pulse 1s ease infinite;

        }
    </style>

</head>



<body>

    <!-- particles.js container -->

    <div id="particles-js"></div>



    <!-- login box -->

    <div class="login-box">

        <div class="card card-outline">

            <div class="card-header text-center" style="background-color: #ffffff !important">
                <h1 style="color: #124170;">MoniForm</h1>

            </div>

            <div class="card-body">

                <form method="POST" action="{{ url('login') }}">

                    @csrf

                    <div class="input-group mb-3">

                        <input autocomplete="on" class="form-control" type="text" name="email"

                            placeholder="Masukkan Email" value="{{ old('email') }}" required>

                    </div>

                    @error('email')

                    <div class="alert alert-danger">{{ $message }}</div>

                    @enderror

                    <div class="input-group mb-3">

                        <input autocomplete="off" class="form-control" type="password" name="password"

                            placeholder="Masukkan Password" required>

                    </div>

                    @error('password')

                    <div class="alert alert-danger">{{ $message }}</div>

                    @enderror

                    <div class="row">

                        <div class="col-12">

                            <button type="submit" class="btn btn-danger btn-block">Sign In</button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <!-- particles.js lib -->

    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

    <script>
        particlesJS("particles-js", {

            "particles": {

                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },

                "color": {
                    "value": "#ffffff"
                },

                "shape": {

                    "type": "circle",

                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },

                    "polygon": {
                        "nb_sides": 3
                    },

                    "image": {
                        "src": "img/github.svg",
                        "width": 100,
                        "height": 100
                    }

                },

                "opacity": {
                    "value": 0.78,
                    "random": false,
                    "anim": {
                        "enable": false
                    }
                },

                "size": {
                    "value": 8,
                    "random": true,
                    "anim": {
                        "enable": false
                    }
                },

                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                },

                "move": {
                    "enable": true,
                    "speed": 6,
                    "direction": "none",
                    "random": true
                }

            },

            "interactivity": {

                "detect_on": "canvas",

                "events": {

                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    },

                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },

                    "resize": true

                },

                "modes": {

                    "grab": {
                        "distance": 400,
                        "line_linked": {
                            "opacity": 1
                        }
                    },

                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },

                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },

                    "push": {
                        "particles_nb": 4
                    },

                    "remove": {
                        "particles_nb": 2
                    }

                }

            },

            "retina_detect": true

        });
    </script>

</body>



</html>