<html>
	<head>
		<style type="text/css">
		table.steve-wrapper {
			margin:0 auto;
			width: 650px;
			display: block;
			height: 200px;
			border-collapse: collapse;
			border-bottom: 1px solid #000;
		}
		td.steve-image {
			width: 214px;
			padding: 0;
			vertical-align: bottom;
		}
		td.steve-message {
			background: #eaeaea;
			padding: 20px;
			vertical-align: top;
			font-family: Helvetica;
			font-size: 16px;
			line-height: 1.25em;
			color: #666;
			border-top-left-radius: 5px;
			border-top-right-radius: 5px;
		}
		h1.steve-header {
			margin: 10px 0 30px 0;
			line-height: 1em;
			font-size: 24px;
		}
		table.steve-wrapper.steve-error td.steve-message {
			background: #ffc9c9;
			color: #9c6161;
		}
		</style>
	</head>
	<body>
		<table class="steve-wrapper steve-{{ $type }}">
			<tr>
				<td class="steve-image"><img src="http://steve.joe.codes/images/emails/notification/steve-{{ $type }}-bg.jpg" /></td>
				<td class="steve-message">
					<h1 class="steve-header">{{ $header }}</h1>
					<p>{{ $body }}</p>
					<p>&nbsp;</p>
					<p>{{ $sign_off }},<br />Steve</p>
				</td>
			</tr>
		</table>
	</body>
</html>