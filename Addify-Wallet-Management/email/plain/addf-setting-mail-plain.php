<?php
defined( 'ABSPATH' ) || exit;
		
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	
}
