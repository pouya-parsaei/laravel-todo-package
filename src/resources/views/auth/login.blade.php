<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <title>Document</title>
</head>
<body>
<form action="{{ route('login') }}" method="post" id="login-form">
    @csrf
    <input type="email" name="email">
    <input type="password" name="password">
    <input type="submit" value="submit" id="login-button">
</form>
<script>
    $(document).ready(function () {
        $('#login-button').click(function (e) {
            e.preventDefault();
            $.ajax({
                url: '{!! route('login') !!}',
                method: 'POST',
                data: $('form#login-form').serialize(),
                success: function(response){
                    localStorage.setItem('api_token', 'Bearer'+' '+response.data.api_token);
                    window.location.replace("{!! route('home') !!}");
                }
            });
        });
    });
</script>
</body>
</html>

