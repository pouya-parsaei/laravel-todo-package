<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('vendor/todo/css/authentication.css')}}">

    <title>Document</title>
</head>
<body>
<div class="login-box">
    <div class="lb-header">
        <a href="#" class="active" id="login-box-link">Login</a>
        <a href="#" id="signup-box-link">Sign Up</a>
    </div>

    <form class="email-login">
        <div class="u-form-group">
            <input name="email" type="email" placeholder="Email"/>
        </div>
        <div class="u-form-group">
            <input name="password" type="password" placeholder="Password"/>
        </div>
        <div class="u-form-group">
            <div class="alert-danger" id="loginError"></div>
            <button type="submit" id="login-button">Log in</button>
        </div>
    </form>
    <form class="email-signup">
        <div class="u-form-group">
            <input type="text" name="name" placeholder="Name" required/>
            <div class="alert-danger" id="nameError"></div>

        </div>
        <div class="u-form-group">
            <input type="email" name="email" placeholder="Email" required/>
            <div class="alert-danger" id="emailError"></div>
        </div>
        <div class="u-form-group">
            <input type="password" name="password" placeholder="Password" required/>
            <div class="alert-danger" id="passwordError"></div>
        </div>
        <div class="u-form-group">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required/>
        </div>
        <div class="u-form-group">
            <button type="submit" id="sign-up-button">Sign Up</button>
        </div>
    </form>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"
        integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13"
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(".email-signup").hide();
    $("#signup-box-link").click(function () {
        $(".email-login").fadeOut(100);
        $(".email-signup").delay(100).fadeIn(100);
        $("#login-box-link").removeClass("active");
        $("#signup-box-link").addClass("active");
    });
    $("#login-box-link").click(function () {
        $(".email-login").delay(100).fadeIn(100);
        ;
        $(".email-signup").fadeOut(100);
        $("#login-box-link").addClass("active");
        $("#signup-box-link").removeClass("active");
    });
</script>
<script>
    $(document).ready(function () {
        $('button#sign-up-button').click(function (e) {
            e.preventDefault();
            let form = $('form.email-signup');
            $.ajax({
                url: '{!! route('todo.register') !!}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: form.serialize(),
                success: function (response) {
                    console.log(response);
                    Swal.fire(
                        'Great',
                        'please login',
                        'success'
                    );
                    $(".email-login").delay(100).fadeIn(100);
                    $(".email-signup").fadeOut(100);
                    $("#login-box-link").addClass("active");
                    $("#signup-box-link").removeClass("active");

                },
                error: function (response) {
                    $('#nameError').text(response.responseJSON.name);
                    $('#emailError').text(response.responseJSON.email);
                    $('#passwordError').text(response.responseJSON.password);
                }
            })
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('button#login-button').click(function (e) {
            e.preventDefault();
            let form = $('form.email-login');
            $.ajax({
                url: '{!! route('todo.login') !!}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: form.serialize(),
                success: function (response) {
                    window.location.replace('{!! route('todo.home') !!}' + '?' + 'api_token='+ response.data.token);

                },
                error: function (response) {
                    $('#loginError').text(response.responseJSON.message);
                }
            })
        });
    });
</script>
</body>
</html>
