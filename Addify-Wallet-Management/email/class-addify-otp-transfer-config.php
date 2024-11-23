<?php
class Addify_OTP_Transfer extends WC_Email {

	public function __construct() {
		$this->id             = 'Addify_otp_transfer'; // Unique ID to Store Emails Settings
		$this->title          = __( 'Wallet OTP transfer', 'woo_addf_wm' ); // Title of email to show in Settings
		$this->customer_email = true; // Set true for customer email and false for admin email.
		$this->template_base  = ADDF_WM_DIR; // Base directory of template 
		$this->template_html  = 'email/html/addify-setting-mail-html.php'; // HTML template path.
		$this->template_plain = 'email/plain/addify-setting-mail-plain.php'; // Plain template path.
		$this->placeholders   = array( // Placeholders/Variables to be used in email
			'{enable}'                => '',
			'{recipient}'             => '', // Fixed typo
			'{subject}'               => '',
			'{heading}'               => '',
			'{additional_content}'     => '',
			'{otp}'                   => '',
		);

		// Call to the parent constructor.
		parent::__construct(); // Must call constructor of parent class
		// Trigger function for this customer email cancelled order.
		add_action( 'share_email_notification', array( $this, 'trigger' ), 10, 2 ); // action hook(s) to trigger email
	}

	public function get_default_subject() {
		return __( 'OTP for wallet transfer confirmation', 'woo_addf_wm' );
	}

	public function get_default_heading() {
		return __( 'Your OTP for wallet transfer', 'woo_addf_wm' );
	}

	// Override trigger method to replace your placeholders and send email
	public function trigger( $current_user_email, $otp ) {
		$this->setup_locale();
		
		$current_user = wp_get_current_user();
		$user_name = $current_user->user_login;

		$this->placeholders['{recipent}'] = $user_name; // Set the user name
		$this->placeholders['{otp}'] = $otp;

		if ( $this->is_enabled() ) {
		$this->recipient = $current_user_email; // Set the recipient email
		$email_sent = $this->send( $this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		return $email_sent; // Return the result of the send function
		}

		$this->restore_locale();
	}

	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'member'             => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			$this->template_base,
			$this->template_base
		);
	}

	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain, // Changed to plain template
			array(
				'member'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			$this->template_base,
			$this->template_base
		);
	}
}

if ( class_exists( 'Addify_OTP_Transfer' ) ) {
	new Addify_OTP_Transfer();
}
