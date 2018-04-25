<?php
/**
 * Handles the display of the directory shortcode generator.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2018, Alessandro Tesoro
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WPUM_Shortcode_Directory extends WPUM_Shortcode_Generator {

	/**
	 * Inject the editor for this shortcode.
	 */
	public function __construct() {
		$this->shortcode['title'] = esc_html__( 'Directory' );
		$this->shortcode['label'] = esc_html__( 'Directory' );
		parent::__construct( 'wpum_user_directory' );
	}

	/**
	 * Setup fields for the login shortcode window.
	 *
	 * @return array
	 */
	public function define_fields() {
		return [
			array(
				'type'    => 'textbox',
				'name'    => 'id',
				'label'   => esc_html__( 'Directory ID' ),
			)
		];
	}

}

new WPUM_Shortcode_Directory;
