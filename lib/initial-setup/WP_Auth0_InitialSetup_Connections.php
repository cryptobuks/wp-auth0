<?php

class WP_Auth0_InitialSetup_Connections {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function render( $step ) {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/connections.php';
	}

	/**
	 * TODO: Deprecate, not used
	 */
	public function update_connection() {

		$provider_name = $_POST['connection'];

		if ( $provider_name == 'auth0' ) {
			$this->toggle_db();
		} else {
			$this->toggle_social( $provider_name );
		}
	}

	/**
	 * TODO: Deprecate when self::update_connection() is deprecated
	 */
	protected function toggle_db() {
		exit;
	}

	/**
	 * TODO: Deprecate when self::update_connection() is deprecated
	 */
	protected function toggle_social( $provider_name ) {

		$provider_options = array(
			'facebook'      => array(
				'public_profile'  => true,
				'email'           => true,
				'user_birthday'   => true,
				'publish_actions' => true,
			),
			'twitter'       => array(
				'profile' => true,
			),
			'google-oauth2' => array(
				'google_plus' => true,
				'email'       => true,
				'profile'     => true,
			),
		);

		$input     = array();
		$old_input = array();

		$operations = new WP_Auth0_Api_Operations( $this->a0_options );

		$old_input[ "social_{$provider_name}" ]        = $this->a0_options->get_connection( "social_{$provider_name}" );
		$old_input[ "social_{$provider_name}_key" ]    = $this->a0_options->get_connection( "social_{$provider_name}_key" );
		$old_input[ "social_{$provider_name}_secret" ] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

		$input[ "social_{$provider_name}" ]        = ( $_POST['enabled'] === 'true' );
		$input[ "social_{$provider_name}_key" ]    = $this->a0_options->get_connection( "social_{$provider_name}_key" );
		$input[ "social_{$provider_name}_secret" ] = $this->a0_options->get_connection( "social_{$provider_name}_secret" );

		try {
			$options = isset( $provider_options[ $provider_name ] ) ? $provider_options[ $provider_name ] : null;
			$input   = $operations->social_validation( $this->a0_options->get( 'auth0_app_token' ), $old_input, $input, $provider_name, $options );
		} catch ( Exception $e ) {
			exit( $e->getMessage() );
		}

		foreach ( $input as $key => $value ) {
			$this->a0_options->set_connection( $key, $value );
		}

		exit;
	}

	public function callback() {
		wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5' ) );
	}

	public function add_validation_error( $error ) {
		wp_redirect(
			admin_url(
				'admin.php?page=wpa0-setup&step=5&error=' .
				urlencode( 'There was an error setting up your connections.' )
			)
		);
		exit;
	}
}
