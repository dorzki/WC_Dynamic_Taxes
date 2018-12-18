<?php
/**
 * Main plugin file.
 *
 * @package    dorzki\WooCommerce\Dynamic_Taxes
 * @subpackage Plugin
 * @author     Dor Zuberi <webmaster@dorzki.co.il>
 * @link       https://www.dorzki.co.il
 * @version    1.0.0
 */

namespace dorzki\WooCommerce\Dynamic_Taxes;

// Block if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Plugin
 *
 * @package dorzki\WooCommerce\Dynamic_Taxes
 */
class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var null|Plugin
	 */
	private static $instance = null;


	/* ------------------------------------------ */


	/**
	 * Plugin constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'register_settings_page' ] );

		add_action( 'woocommerce_cart_calculate_fees', [ $this, 'apply_dynamic_taxes' ] );

	}


	/* ------------------------------------------ */


	/**
	 * Register plugin settings fields.
	 */
	public function register_settings() {

		// Register new settings for plugin page.
		register_setting( 'wc_dynamic_taxes', 'wc_dynamic_taxes' );

		// Register a new section.
		add_settings_section(
			'wc_dynamic_taxes_general',
			__( 'General', 'dorzki-wc-dynamic-taxes' ),
			'__return_null',
			'dorzki-wc-dynamic-taxes'
		);

		// Register plugin settings fields.
		add_settings_field(
			'wc_dynamic_taxes_name',
			__( 'Tax Name', 'dorzki-wc-dynamic-taxes' ),
			[ $this, 'settings_page_field_output' ],
			'dorzki-wc-dynamic-taxes',
			'wc_dynamic_taxes_general',
			[
				'type'      => 'text',
				'label_for' => 'wc_dynamic_taxes_name',
			]
		);

		add_settings_field(
			'wc_dynamic_taxes_amount',
			__( 'Tax Value', 'dorzki-wc-dynamic-taxes' ),
			[ $this, 'settings_page_field_output' ],
			'dorzki-wc-dynamic-taxes',
			'wc_dynamic_taxes_general',
			[
				'type'      => 'text',
				'label_for' => 'wc_dynamic_taxes_amount',
			]
		);

		$cats = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'id=>name',
		] );

		add_settings_field(
			'wc_dynamic_taxes_category',
			__( 'Apply to Category', 'dorzki-wc-dynamic-taxes' ),
			[ $this, 'settings_page_field_output' ],
			'dorzki-wc-dynamic-taxes',
			'wc_dynamic_taxes_general',
			[
				'type'      => 'select',
				'label_for' => 'wc_dynamic_taxes_category',
				'options'   => $cats,
			]
		);

	}


	/**
	 * Register plugin settings page under woocommerce.
	 */
	public function register_settings_page() {

		add_submenu_page(
			'woocommerce',
			__( 'Dynamic Taxes', 'dorzki-wc-dynamic-taxes' ),
			__( 'Dynamic Taxes', 'dorzki-wc-dynamic-taxes' ),
			'manage_options',
			'dorzki-wc-dynamic-taxes',
			[ $this, 'settings_page_output' ]
		);

	}


	/* ------------------------------------------ */


	/**
	 * Determine if to add taxes and how much.
	 *
	 * @param \WC_Cart $cart current user cart.
	 */
	public function apply_dynamic_taxes( $cart ) {

		$tax_options = get_option( 'wc_dynamic_taxes' );

		if ( empty( $tax_options ) || empty( $tax_options['wc_dynamic_taxes_category'] ) ) {
			return;
		}

		$total_taxes = 0;

		foreach ( $cart->get_cart_contents() as $item ) {

			$cats = wp_get_post_terms( $item['data']->get_ID(), 'product_cat', [ 'fields' => 'ids' ] );

			$total_taxes += ( in_array( (int) $tax_options['wc_dynamic_taxes_category'], $cats, true ) );

		}

		if ( $total_taxes ) {

			$cart->add_fee( sprintf( '%d&times; %s', $total_taxes, $tax_options['wc_dynamic_taxes_name'] ), $total_taxes * $tax_options['wc_dynamic_taxes_amount'], false );

		}

	}


	/* ------------------------------------------ */


	/**
	 * Print the fields.
	 *
	 * @param array $args field arguments.
	 */
	public function settings_page_field_output( $args ) {

		if ( ! in_array( strtolower( $args['type'] ), [
			'select',
			'text',
			'tel',
			'email',
			'password',
			'date',
			'color',
			'search',
			'url',
		], true ) ) {
			return;
		}

		// Get field value.
		$value = get_option( 'wc_dynamic_taxes' );

		if ( 'select' === $args['type'] ) {

			echo "<select name='wc_dynamic_taxes[{$args['label_for']}]'>";
			echo "  <option value=''>" . esc_html__( '---', 'dorzki-wc-dynamic-taxes' ) . "</option>";

			foreach ( $args['options'] as $id => $name ) {

				echo "  <option value='{$id}' " . selected( $value[ $args['label_for'] ], $id, true ) . ">{$name}</option>";

			}

			echo "</select>";

		} else {

			echo "<input type='{$args['type']}' class='regular-text' name='wc_dynamic_taxes[{$args['label_for']}]' value='{$value[$args['label_for']]}'>";

		}

	}


	/**
	 * Display settings page output if the user have the right permissions.
	 */
	public function settings_page_output() {

		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include_once plugin_dir_path( __FILE__ ) . 'templates/settings-page.php';

	}


	/* ------------------------------------------ */


	/**
	 * Retrieve plugin instance.
	 *
	 * @return Plugin|null
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();

		}

		return self::$instance;

	}

}

// initiate plugin.
Plugin::get_instance();
