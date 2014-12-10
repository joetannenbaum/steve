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
        {{ Form::open(array('url' => 'authorization-done')) }}
          <p>Choose which devices to push to:</p>
          @foreach ($devices as $device)
          <div class="checkbox">
            <label>
              <input type="checkbox" name="devices[]" value="{{ $device->iden }}|{{ $device->nickname }}" />
              {{ $device->nickname }}
            </label>
          </div>
          @endforeach
          <button type="submit" class="btn btn-default">Submit</button>
        {{ Form::close() }}
    </div>
</body>
</html>
