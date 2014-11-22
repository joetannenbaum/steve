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
		@if (Session::get('success_message'))
			<div class="alert alert-success" role="alert">{{ Session::get('success_message') }}</div>
		@elseif (Session::get('error_message'))
			<div class="alert alert-danger" role="alert">{{ Session::get('error_message') }}</div>
		@endif
		<form role="form" method="post">
		  <div class="form-group">
		    <input type="text" name="title" class="form-control" placeholder="Title" />
		  </div>
		  <div class="form-group">
		    <input type="text" name="url" class="form-control" placeholder="URL" />
		  </div>
		  <div class="form-group">
		    <input type="password" name="password" class="form-control" placeholder="Password" />
		  </div>
		  <button type="submit" class="btn btn-default">Submit</button>
		</form>
	</div>
</body>
</html>
