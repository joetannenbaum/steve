<html>
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