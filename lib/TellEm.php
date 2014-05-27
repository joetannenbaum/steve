<?php

namespace Steve\Notify;

class TellEm {

	protected $sign_offs = [
		'Hugs',
		'Respectfully yours',
		'Keep on keepin\' on',
	];

	public function notify( $subject, $body )
	{
		$params = [
			'type'   => 'notify',
			'body'   => $body,
			'header' => 'Just a Heads Up',
		];

		$this->send( $params, $subject );
	}

	public function error( $subject, $body )
	{
		$params = [
			'type'   => 'error',
			'body'   => $body,
			'header' => 'Got Some Bad News For You',
		];

		$this->send( $params, $subject );
	}

	protected function send( $params, $subject )
	{
		$default_params = [
			'sign_off' => $this->sign_offs[ array_rand( $this->sign_offs ) ]
		];

		$params = array_merge( $params, $default_params );

		$template = $this->getTemplateFile(
			app_path('views/emails/notification/steve.blade.php'),
			public_path('css/emails/notification/style.css'),
			array_only( $params, 'type' )
		);

		echo \View::make( $template, $params ); die();

		\Mail::send($template, $params, function ( $message ) use ( $subject ) {
			$message->from( getenv( 'email.default.from.email'), getenv( 'email.default.from.name') );
			$message->to( getenv( 'email.default.to.email'), getenv( 'email.default.to.name') );
			$message->subject( $subject );
		});
	}

	protected function getTemplateFile( $html_path, $css_path, $replacements )
	{
		list( $find, $replace ) = array_divide( $replacements );

		$path_info = pathinfo( $html_path );

		$filename = str_replace( '.blade.php', '', $path_info['basename'] );

		$filename = 'inlined/'
						. $filename
						. '-' . \Str::slug( implode( '.', $replace ) )
						. '-' . filemtime( $html_path )
						. '-' . filemtime( $css_path )
						. '.blade.php';

		$filepath = app_path( 'views/' . $filename );

		if ( !file_exists( $filepath ) )
		{
			$inliner = \App::make('css_inliner');

			$html = file_get_contents( $html_path );

			$find = array_map( function ( $f ) { return '{{ $' . $f . ' }}'; }, $find );

			$html = str_replace( $find, $replace, $html );

			$css = file_get_contents( $css_path );

			$inliner->setCSS( $css );
			$inliner->setHTML( $html );

			file_put_contents( $filepath, $inliner->convert() );
		}

		$filename = str_replace( '/', '.', $filename );
		$filename = str_replace( '.blade.php', '', $filename );

		return $filename;
	}

}