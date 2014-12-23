<?php

namespace Steve\External;

class YouTube {

	public function parseVideoInfo( $garbage )
	{
	    parse_str( $garbage, $video_info );

	    if ( array_get( $video_info, 'url_encoded_fmt_stream_map' ) )
	    {
	    	$tmp_formats = explode( ',', $video_info['url_encoded_fmt_stream_map'] );

	    	$formats = [];

	    	foreach( $tmp_formats as $tf )
	    	{
	    		parse_str( $tf, $form );

	    		$form['url'] = urldecode( $form['url'] );

	    		parse_str( $form['url'], $form['url_attr'] );

	    		$formats[] = $form;
	    	}

	    	$video_info['formats'] = $formats;
	    	$video_info['best_format'] = head( $formats );
	    }
	    else
	    {
	    	return [
					'error'         => TRUE,
					'error_message' => array_get( $video_info, 'reason' ),
					'error_code'    => array_get( $video_info, 'errorcode' ),
	    		];
	    }

	    return $video_info;
	}

	public function getVideoInfo( $video_id )
	{
		$ch = curl_init();
	    curl_setopt( $ch , CURLOPT_URL , 'http://www.youtube.com/get_video_info?video_id=' . $video_id );
	    curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 );
	    curl_setopt( $ch , CURLOPT_CONNECTTIMEOUT , 3 );
	    $tmp = curl_exec( $ch );
	    curl_close( $ch );

	    return $this->parseVideoInfo( $tmp );
	}

}