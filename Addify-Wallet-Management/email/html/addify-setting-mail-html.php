<?php
defined( 'ABSPATH' ) || exit;
/*
 * @hooked WC_Emails::email_header() Output the email header
*/

do_action( 'woocommerce_email_header', $email_heading, $email ); 
		
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
		
}
	/*
	* @hooked WC_Emails::email_footer() Output the email footer
	*/
do_action( 'woocommerce_email_footer', $email );
