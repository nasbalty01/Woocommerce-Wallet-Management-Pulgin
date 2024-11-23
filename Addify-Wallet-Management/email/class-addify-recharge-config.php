<?php
class Addify_Recharge extends WC_Email {

	public function __construct() {
		$this->id             = 'Addify_recharge'; // Unique ID to Store Emails Settings
		$this->title          = __( 'Wallet recharge on request', 'woo_addf_wm' ); // Title of email to show in Settings
		$this->customer_email =     true; // Set true for customer email and false for admin email.
		$this->template_base  = ADDF_WM_DIR; // Base directory of template 
		$this->template_html  = 'email/html/addify-setting-mail-html.php'; // HTML template path.
		$this->template_plain = 'email/plain/addify-setting-mail-plain.php'; // Plain template path.
		$this->placeholders   = array( // Placeholders/Variables to be used in email
			'{enable}'                  => '',
			'{recipent}'                => '',
			'{subject}'                 => '',
			'{heading}'                 => '',
			'{additional_content}'      => '',
			'{recharge_amount}'             => '',
		);

		// Call to the  parent constructor.
		parent::__construct(); // Must call constructor of parent class
		// Trigger function  for this customer email cancelled order.
		add_action( 'share_email_notification', array( $this, 'trigger' ), 10, 3 ); // action hook(s) to trigger email 
	}
	// Step 3: change default subject of email by overriding the parent class method
	// Ex.
	public function get_default_subject() {
		return __( 'Recharge confirmation', 'woo_addf_wm' );
	}
	// Step 4: change default heading of email by overriding the parent class method
	public function get_default_heading() {
		return __( 'Wallet recharge confirmation', 'woo_addf_wm' );
	}
	 
	
	// Step 5: Must over ride trigger method to replace your placeholders and send email
	public function trigger( $receiver_email, $user_name, $amount ) {

		
		$this->setup_locale();
		$formatted_amount = wc_price( $amount );
		
		$this->placeholders['{recipent}']    = $user_name;
		$this->placeholders['{recharge_amount}']  = $formatted_amount;

		if ( $this->is_enabled()) {

			$this->send( $receiver_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

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

if (class_exists('Addify_Recharge')) {
	new Addify_Recharge();
}
