<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Steve Jobs | Offliner</title>
    <meta name="robots" content="noindex, nofollow" />
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body style="padding-top: 50px;">
    <div class="container">
        <div class="alert alert-success" role="alert">Pocket authorized!</div>
        {{ Form::open(array('url' => 'authorize-pushbullet')) }}

          <p><a href="https://www.pushbullet.com/account" target="_blank">Head over to your Pushbullet account</a> and paste your access token below.</p>

          <div class="form-group">
            <input type="text" name="token" class="form-control" placeholder="Access Token" />
          </div>

          <button type="submit" class="btn btn-default">Submit</button>
        {{ Form::close() }}
    </div>
</body>
</html>
