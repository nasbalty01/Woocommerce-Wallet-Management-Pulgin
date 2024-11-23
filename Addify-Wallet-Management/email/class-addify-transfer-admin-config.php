<?php
class Addify_Transfer_Admin extends WC_Email {

	public function __construct() {
		$this->id             = 'Addify_transfer_admin'; // Unique ID to Store Emails Settings
		$this->title          = __( 'Wallet transfer admin', 'woo_addf_wm' ); // Title of email to show in Settings
		$this->customer_email =     false; // Set true for customer email and false for admin email.
		$this->template_base  = ADDF_WM_DIR; // Base directory of template 
		$this->template_html  = 'email/html/addify-setting-mail-html.php'; // HTML template path.
		$this->template_plain = 'email/plain/addify-setting-mail-plain.php'; // Plain template path.
		$this->placeholders   = array( // Placeholders/Variables to be used in email
			'{enable}'                  => '',
			'{recipent}'                => '',
			'{subject}'                 => '',
			'{heading}'                 => '',
			'{additional_content}'      => '',
			'{transfer_amount}'             => '',
			'{sender_email}'       => '',
			'{recipient_email}'       => '',
		);

		// Call to the  parent constructor.
		parent::__construct(); // Must call constructor of parent class
		// Trigger function  for this customer email cancelled order.
		add_action( 'share_email_notification', array( $this, 'trigger' ), 10, 3 ); // action hook(s) to trigger email 
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}
	// Step 3: change default subject of email by overriding the parent class method
	// Ex.
	public function get_default_subject() {
		return __( 'Transfer amount notification', 'woo_addf_wm' );
	}
	// Step 4: change default heading of email by overriding the parent class method
	public function get_default_heading() {
		return __( 'Transfer amount notification', 'woo_addf_wm' );
	}
	 
	
	// Step 5: Must over ride trigger method to replace your placeholders and send email
	public function trigger( $reciver_email, $sendr_email, $pay_amount ) {

		
		$this->setup_locale();
		$admin_email = get_option( 'admin_email' );
		$admin_user = get_user_by( 'email', $admin_email );
		$admin_username = $admin_user ? $admin_user->user_login : '';

		$formatted_amount = wc_price( $pay_amount );
		
		$this->placeholders['{recipent}']    = $admin_username;
		$this->placeholders['{transfer_amount}']  = $formatted_amount;
		$this->placeholders['{sender_email}']    = $sendr_email;
		$this->placeholders['{recipient_email}']  = $reciver_email;

		if ( $this->is_enabled() ) {
			$this->recipient =  $admin_email; // Set the recipient email
			$email_sent = $this->send( $this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			return $email_sent; // Return the result of the send function
		}

		$this->restore_locale();
	}
	// Step 6: Override the get_content_html method to add your template of html email
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
	// Note: Path and default path in wc_get_template_html() can be defined as, path is defined path to over-ride email template and default path is path to your plugin template.
	// Read more about wc_get_template and wc_locate_template() to understand the over-riding templates in WooCommerce.
	// Step 7: Override the get_content_plain method to add your template of plain email
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_html,
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

if (class_exists('Addify_Transfer_Admin')) {
	new Addify_Transfer_Admin();
}
