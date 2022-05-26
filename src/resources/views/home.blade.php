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
<div>
    <h1>
        home
    </h1>
</div>
<div>
    <h3>
        create label
    </h3>
    <div>
        <form action="" method="post" id="label-form">
            @csrf
            <input type="text" name="name">
            <button type="submit" id="create-label">submit</button>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#create-label').click(function (e) {
            e.preventDefault();
            $.ajax({
                url: '{!! route('labels.store') !!}',
                headers: {
                    "Authorization": localStorage.getItem('api_token'),
                    "Cookie": "XSRF-TOKEN=eyJpdiI6ImRiOE5LSHYweVJXMUJQT05CTWxUblE9PSIsInZhbHVlIjoiL2N0bTVDV2k4eFVPeE9YdE5ySE01Sll1RkJmWEUxS3Q5RGdqSXNOZ1F6WTlJSFFJNlpNS2Y2cExsQnlaaS8vV1FpcmpBaXFaTUFseHovd2NmNEhGQkpkcVBkOVExcjVadXFKWXd3U1lSdDRxSUVoSEEvS0YzSU5wSGdVcUgzRWQiLCJtYWMiOiI3ZDAzNDU3ZjI3NDRhMjdiMmIyNzc0YWVlOGRlY2E3ODVlMzc2YzExZTMyYzgzMDViNjhiMTUwMTZkMjE5ZWNiIn0%3D; laravel_session=eyJpdiI6InR3cmpuaUc1bE5VS1dUUzd1dk91clE9PSIsInZhbHVlIjoiSG1FbTdOTDZDeDZPWFpxL3hWN1pPWXJBbndUeWpOc05pNUVYMzE4Qm95d0g0ZEUzdzAxcWcxOVZMQXBGS3crMWxwY01Kdm5Gc2RqZTNWdkhnaXR3ZU1PVEN0TnVUbGxVdzU5aGJVeWg4WWV0RnZjdEgySGRvV2xEODkySUl0aFgiLCJtYWMiOiJlYzgxZjc4MmZhZGI2OGYxMTkwZGZkNGVkZGYzYjg1ZWQ4M2NhOWQ4MjJhMGYyM2I5NDNmYTQ3Mjg2ODY0MzBmIn0%3D"
                },
                method: 'POST',
                data: $('form#label-form').serialize(),
                success: function(response){
                    console.log(response);
                }
            });
        });
    });
</script>
</body>
</html>

