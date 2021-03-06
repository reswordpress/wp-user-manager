<?php
/**
 * Handles the shortcode generator for WP User Manager.
 * Taken from the Give plugin and adapted to my needs.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2018, Alessandro Tesoro
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles the shortcode generator modal window.
 */
abstract class WPUM_Shortcode_Generator {

	/**
	 * The class extending the generator.
	 *
	 * @var object
	 */
	public $self;

	/**
	 * The shortcode extending the generator.
	 *
	 * @var string
	 */
	public $shortcode;

	/**
	 * The current shortcode tag.
	 *
	 * @var string
	 */
	public $shortcode_tag;

	/**
	 * Collection of validation errors for the modal window.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Set of required fields.
	 *
	 * @var array
	 */
	protected $required;

	/**
	 * Get things started.
	 *
	 * @param string $shortcode
	 */
	public function __construct( $shortcode ) {
		$this->shortcode_tag = $shortcode;
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function init() {

		if ( $this->shortcode_tag ) {
			$this->self = get_class( $this );

			$this->errors   = [];
			$this->required = [];

			// Generate the fields, errors, and requirements.
			$fields = $this->get_fields();

			$defaults = array(
				'btn_close' => esc_html__( 'Close', 'wp-user-manager' ),
				'btn_okay'  => esc_html__( 'Insert Shortcode', 'wp-user-manager' ),
				'errors'    => $this->errors,
				'fields'    => $fields,
				'label'     => '[' . $this->shortcode_tag . ']',
				'required'  => $this->required,
				'title'     => esc_html__( 'Insert Shortcode', 'wp-user-manager' ),
			);

			if ( user_can_richedit() ) {
				WPUM_Shortcode_Button::$shortcodes[ $this->shortcode_tag ] = wp_parse_args( $this->shortcode, $defaults );
			}

		}

	}

	/**
	 * List of fields for this shortcode.
	 *
	 * @return void
	 */
	public function define_fields() {
		return false;
	}

	/**
	 * Retrieve the list of defined fields.
	 *
	 * @param array $defined_fields
	 * @return void
	 */
	protected function generate_fields( $defined_fields ) {

		$fields = array();

		if ( is_array( $defined_fields ) ) {
			foreach ( $defined_fields as $field ) {

				$defaults = array(
					'label'       => false,
					'name'        => false,
					'options'     => array(),
					'placeholder' => false,
					'tooltip'     => false,
					'type'        => '',
				);

				$field  = wp_parse_args( (array) $field, $defaults );

				$method = 'generate_' . strtolower( $field['type'] );

				if ( method_exists( $this, $method ) ) {
					$field = call_user_func( array( $this, $method ), $field );
					if ( $field ) {
						$fields[] = $field;
					}
				}

			}
		}

		return $fields;

	}

	/**
	 * Generate the dialog fields.
	 *
	 * @return void
	 */
	protected function get_fields() {

		$defined_fields   = $this->define_fields();
		$generated_fields = $this->generate_fields( $defined_fields );

		$errors = array();

		if ( ! empty( $this->errors ) ) {
			foreach ( $this->required as $name => $alert ) {
				if ( false === array_search( $name, wpum_list_pluck( $generated_fields, 'name' ) ) ) {
					$errors[] = $this->errors[ $name ];
				}
			}
			$this->errors = $errors;
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return $generated_fields;

	}

	/**
	 * Generate a tinymce container field.
	 *
	 * @param array $field
	 * @return void
	 */
	protected function generate_container( $field ) {
		if ( array_key_exists( 'html', $field ) ) {
			return array(
				'type' => $field['type'],
				'html' => $field['html'],
			);
		}
		return false;
	}

	/**
	 * Generate a list field for the modal window.
	 *
	 * @param array $field
	 * @return void
	 */
	protected function generate_listbox( $field ) {

		$listbox = shortcode_atts( array(
			'label'    => '',
			'minWidth' => '',
			'name'     => false,
			'tooltip'  => '',
			'type'     => '',
			'value'    => '',
			'classes'  => ''
		), $field );

		if ( $this->validate( $field ) ) {
			$new_listbox = array();
			foreach ( $listbox as $key => $value ) {
				if ( $key == 'value' && empty( $value ) ) {
					$new_listbox[ $key ] = $listbox['name'];
				} else if ( $value ) {
					$new_listbox[ $key ] = $value;
				}
			}
			// do not reindex array!
			$field['options'] = array(
				'' => ( $field['placeholder'] ? $field['placeholder'] : esc_attr__( '- Select -', 'wp-user-manager' ) ),
			) + $field['options'];

			foreach ( $field['options'] as $value => $text ) {
				$new_listbox['values'][] = array(
					'text'  => $text,
					'value' => $value,
				);
			}

			return $new_listbox;
		}

		return false;
	}

	/**
	 * Generate a textbox for the window.
	 *
	 * @param [type] $field
	 * @return void
	 */
	protected function generate_textbox( $field ) {
		$textbox = shortcode_atts( array(
			'label'     => '',
			'maxLength' => '',
			'minHeight' => '',
			'minWidth'  => '',
			'multiline' => false,
			'name'      => false,
			'tooltip'   => '',
			'type'      => '',
			'value'     => '',
			'classes'   => ''
		), $field );
		if ( $this->validate( $field ) ) {
			return array_filter( $textbox, array( $this, 'return_textbox_value' ) );
		}
		return false;
	}

	/**
	 * Retrieve  the value of the textbox.
	 *
	 * @param string $value
	 * @return void
	 */
	function return_textbox_value( $value ) {
		return $value !== '';
	}

	/**
	 * Validate the modal window.
	 *
	 * @param array $field
	 * @return void
	 */
	protected function validate( $field ) {
		extract( shortcode_atts(
				array(
					'name'     => false,
					'required' => false,
					'label'    => '',
				), $field )
		);
		if ( $name ) {
			if ( isset( $required['error'] ) ) {
				$error = array(
					'type' => 'container',
					'html' => $required['error'],
				);
				$this->errors[ $name ] = $this->generate_container( $error );
			}
			if ( ! ! $required || is_array( $required ) ) {
				$alert = esc_html__( 'Some of the shortcode options are required.', 'wp-user-manager' );
				if ( isset( $required['alert'] ) ) {
					$alert = $required['alert'];
				} else if ( ! empty( $label ) ) {
					$alert = sprintf(
						/* translators: %s: option label */
						esc_html__( 'The "%s" option is required.', 'wp-user-manager' ),
						str_replace( ':', '', $label )
					);
				}
				$this->required[ $name ] = $alert;
			}
			return true;
		}
		return false;
	}

	/**
	 * Retrieve the yes or no option for listboxes.
	 *
	 * @return array
	 */
	protected function get_yes_no() {
		return [ 'yes' => esc_html__( 'Yes', 'wp-user-manager' ), 'no' => esc_html__( 'No', 'wp-user-manager' ) ];
	}

}
