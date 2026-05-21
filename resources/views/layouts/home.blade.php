<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LIMS | AFMSL</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
            background-color: #243949;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-family: Arial, Helvetica, sans-serif;
        }

        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .video-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1;
        }

        #background-video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto;
            height: 100%;
            min-width: 100%;
            min-height: 100%;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        .heading {
            position: relative;
            text-align: center;
            z-index: 2;
        }

        .heading h1 {
            font-size: 5em;
            margin: 0;
            font-weight: bold;
        }

        .heading h2 {
            font-size: 1.5em;
            margin-top: 10px;
            color: #ccc;
        }

        .login-btn-designed-effect {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition-duration: .3s;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
            background-color: rgba(92, 83, 83, 0.41);
            margin-top: 20px;
            z-index: 2;
        }

        .login-btn-designed-effect-sign {
            width: 100%;
            transition-duration: .3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-btn-designed-effect-sign svg {
            width: 17px;
        }

        .login-btn-designed-effect-sign svg path {
            fill: white;
        }

        .login-btn-designed-effect-text {
            position: absolute;
            right: 0%;
            width: 0%;
            opacity: 0;
            color: white;
            font-size: 1em;
            font-weight: 400;
            transition-duration: .3s;
        }

        .login-btn-designed-effect:hover {
            width: 125px;
            transition-duration: .3s;
        }

        .login-btn-designed-effect:hover .login-btn-designed-effect-sign {
            width: 30%;
            padding-left: 20px;
        }

        .login-btn-designed-effect:hover .login-btn-designed-effect-text {
            opacity: 1;
            width: 70%;
            padding-right: 10px;
        }
    </style>
</head>

<body>
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="{{ asset('gifshort458.mp4') }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <div class="heading">
        <h1>LIMS</h1>
        <h2>Laboratory Information Management System</h2>
    </div>




    <button class="login-btn-designed-effect" onclick="window.location.href='{{ route('login') }}'">
        <div class="login-btn-designed-effect-sign">
            <svg viewBox="0 0 512 512">
                <path
                    d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z">
                </path>
            </svg>
        </div>
        <div class="login-btn-designed-effect-text">Login</div>
    </button>





</body>

</html>
