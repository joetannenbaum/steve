<?php

return [
	'mailgun' => [
	    'domain' => getenv( 'mailgun.domain' ),
	    'secret' => getenv( 'mailgun.key' ),
	],
];