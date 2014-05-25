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
			'type'    => 'notify',
			'body' => $body,
			'header'  => 'Just a Heads Up',
		];

		$this->send( $params, $subject );
	}

	public function error( $subject, $body )
	{
		$params = [
			'type'    => 'error',
			'body' => $body,
			'header'  => 'Got Some Bad News For You',
		];

		$this->send( $params, $subject );
	}

	protected function send( $params, $subject )
	{
		$default_params = [
			'sign_off' => $this->sign_offs[ array_rand( $this->sign_offs ) ]
		];

		$params = array_merge( $params, $default_params );

		\Mail::send('emails.notification.steve', $params, function ( $message ) use ( $subject ) {
			$message->from( getenv( 'email.default.from.email'), getenv( 'email.default.from.name') );
			$message->to( getenv( 'email.default.to.email'), getenv( 'email.default.to.name') );
			$message->subject( $subject );
		});
	}

}