<?php

namespace WooYouPay\controllers;

class CliController extends \WP_CLI_Command {

	/**
	 * Example: Not in use
	 */
	public function example( $args = array() ) {
		\WP_CLI::error( 'First arg: ' . $args[0] );
		\WP_CLI::success( 'Success Message' );
	}
}
