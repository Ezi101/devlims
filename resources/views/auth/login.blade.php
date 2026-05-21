<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') | {{ config('app.name', 'POS') }}</title>

    <style>
        body,
        html {

            font-family: Arial, Helvetica, sans-serif;
        }

        /* Full-screen wrapper with background image */
        .background-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ asset('dummy/loginimage2.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;

        }

        /* Overlay on background image */
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.855);
            /* Dark semi-transparent overlay */
            z-index: 1;
        }

        .container {
            display: flex;
            width: 70%;
            max-width: 700px;
            height: 50%;
            max-height: 500px;
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
            z-index: 2;
            /* Ensure container appears above overlay */
            backdrop-filter: blur(4px);
        }

        .left-col {
            flex: 1;
            background-color: rgba(29, 31, 51, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            text-align: center;
        }

        .left-col img {
            width: 150px;
            animation: scaleUpL 2s ease-out forwards;
        }

        .right-col {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-left: -19px;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
        }

        .login-form h4 {
            margin: 20px 0;
            color: #333;
            font-weight: bold;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 15px;
            width: 100%;
        }

        label {
            margin-bottom: 8px;
            margin-left: 4px;
            color: #fff;
            font-weight: 600;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 9px;
            border-radius: 6px;
            border: 2px solid #00000050;
            background-color: rgba(201, 196, 196, 0.15);
            font-size: 15px;
            color: #fff;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 5px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #000;
            box-shadow: 0 0 5px rgba(43, 128, 236, 0.3);
            background-color: rgba(29, 31, 51, 0.5);
        }


        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            /* Subtle glow effect */
            border-color: #fff;
            /* Changing to a blue color for clarity */
            background-color: rgba(29, 31, 51, 0.5);
            /* Dark background on focus */
            color: white;
            /* White text to contrast the dark background */
        }


        .login-form button[type="submit"] {
            width: 70%;
            padding: 10px 0;
            border-radius: 8px;
            border: none;
            background: linear-gradient(to right, #2b80ec, #1d1f33);
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.5s ease, transform 0.3s ease, letter-spacing 0.3s ease;
            margin-left: 19%;
            margin-top: 15px;
        }

        .login-form button[type="submit"]:hover {
            transform: scale(1.01);
        }

        .login-form button[type="submit"]:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.2);
        }


        .login-form button[type="submit"]:disabled {
            letter-spacing: 2px;
            opacity: 0.7
        }


        /* Scale up animation */
        @keyframes scaleUpL {
            0% {
                transform: scale(1.3);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media (max-width: 1024px) {
            .container {
                position: fixed;
                top: 50px;

                width: 80%;
                max-width: 600px;
                flex-direction: column;
                overflow: scroll;
            }

            .left-col {
                padding: 10px;

            }

            .left-col h4 {
                display: none;
            }

            .left-col img {
                width: 100px;
            }

            .login-form h4 {
                font-size: 1.5em;
            }
        }

        /* Mobile and smaller */
        @media (max-width: 768px) {
            .container {
                position: fixed;
                top: 50px;


                width: 90%;
                overflow: scroll;
            }

            .left-col {
                padding: 10px;

            }

            .left-col h4 {
                display: none;
            }

            .left-col img {
                width: 90px;
            }

            .right-col {
                padding: 10px;
            }

            .login-form h4 {
                font-size: 1.4em;
            }
        }

        /* Small mobile screens */
        @media (max-width: 576px) {
            .container {
                position: fixed;
                top: 50px;

                width: 85%;
                overflow: scroll;
            }

            .left-col {
                padding: 10px;

            }

            .left-col h4 {
                display: none;
            }

            .left-col img {
                width: 80px;
            }

            .right-col {
                padding: 10px;
            }

            .login-form {
                max-width: 250px;
            }

            .login-form h4 {
                font-size: 1.2em;
            }

            input[type="text"],
            input[type="password"] {
                padding: 7px;
                font-size: 14px;
            }


        }
    </style>
</head>

<body>
    <div class="background-wrapper">
        <!-- Overlay -->
        <div class="background-overlay"></div>
        <div class="container">
            <!-- Left Column with Image and Title -->
            <div class="left-col">
                <div>
                    <img src="{{ asset('img/AFMS LOGO-01.png') }}" alt="Logo">
                    <h4>Armed Forces Medical Stores Laboratory</h4>
                </div>
            </div>

            <!-- Right Column with Form -->
            <div class="right-col">
                <div class="login-form">
                    <form method="POST" action="{{ route('login') }}" onsubmit="disableButton()">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" class="form-control" name="username"
                                value="{{ old('username') }}" required autofocus>
                            <!-- Error message for username -->
                            @if ($errors->has('username'))
                                <span class="error-message" style="color: red; font-size: 0.9em;">
                                    {{ $errors->first('username') }}
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div style="position: relative;">
                                <input id="password" type="password" class="form-control" name="password" required>
                                <button type="button" onclick="togglePasswordVisibility()"
                                    style="position: absolute; right: -15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #f9f9f9; cursor: pointer;margin-top:2px;opacity:0.6;">
                                    Show
                                </button>
                            </div>
                            <!-- Error message for password -->
                            @if ($errors->has('password'))
                                <span class="error-message" style="color: red; font-size: 0.9em;">
                                    {{ $errors->first('password') }}
                                </span>
                            @endif
                        </div>

                        <!-- General error message (e.g., for failed login) -->
                        @if ($errors->has('login'))
                            <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px;">
                                {{ $errors->first('login') }}
                            </div>
                        @endif

                        <button id="loginButton" type="submit">Login</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <script>
        function disableButton() {
            const button = document.getElementById('loginButton');
            button.disabled = true;
            button.innerHTML = 'Logging In...';
        }

        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleButton = passwordField.nextElementSibling;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.innerText = 'Hide';
            } else {
                passwordField.type = 'password';
                toggleButton.innerText = 'Show';
            }
        }
    </script>
</body>

</html>
