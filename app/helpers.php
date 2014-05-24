<?php

if ( !function_exists('bdd') )
{
	function bdd()
	{
        $name = 'bdd';
        $kill = TRUE;
		$args = func_get_args();

        $var = array_shift( $args );

        if ( $args )
        {
            $next = array_shift( $args );

            if ( $next === FALSE )
            {
                $kill = FALSE;
            }
            else
            {
                $name = $next;
            }
        }

        if ( $args )
        {
            $kill = array_shift( $args );
        }

        $file = implode( '-', [ $name, microtime(TRUE) ] );
		$file = Str::slug( $file );
		$file  .= '.html';
		$file = storage_path( 'dumps/' . $file );

		file_put_contents( $file, htmlentities( json_encode( $var ) ) );

		echo "\n" . '**Dump written to: ' . $file . "**\n\n";

        if ( $kill )
        {
            die();
        }
	}
}