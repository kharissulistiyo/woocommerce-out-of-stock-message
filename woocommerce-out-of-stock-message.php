<?php
/*
 * Plugin Name: WooCommerce Out of Stock Message
 * Plugin URI: http://codecanyon.net/
 * Description: WooCommerce Extension which allows you to supply a literal message for out of stock products. You can also add links and/or contact info which visitors can get information from. So You'll never lose your potential customers.
 * Version: 1.0
 * Author: Kharis Sulistiyono
 * Author URI: http://kharisulistiyo.github.io
 * Requires at least: 3.8
 * Tested up to: 3.9.1
 *
 * Text Domain: wc_oosm
 *
 * Copyright: @ 2014 Kharis Sulistiyono.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly



/*
 * Scripts
 * Admin screen
 */

function wc_oosm_scripts(){

	wp_register_script( 'wc-oosm-js', plugin_dir_url(plugin_basename(__FILE__)) . 'scripts/wc-oosm.js', array('jquery'), '1.0' );

	wp_enqueue_style( 'wc-oosm-css', plugin_dir_url(plugin_basename(__FILE__)) . 'scripts/wc-oosm.css', '1.0' );

	wp_enqueue_script( 'wc-oosm-js');

}

add_action( 'admin_enqueue_scripts', 'wc_oosm_scripts' );


/*
* Scripts
* Front end
*/

function wc_oosm_scripts_frontend(){

	wp_enqueue_style( 'wc-oosm-front-css', plugin_dir_url(plugin_basename(__FILE__)) . 'scripts/wc-oosm-frontend.css', '1.0' );

}

add_action( 'wp_enqueue_scripts', 'wc_oosm_scripts_frontend' );

/*
 * Fields
 */

function wc_oosm_textbox(){

		global $post;

    $val = '';
    $get_saved_val = maybe_unserialize(get_post_meta($post->ID, '_out_of_stock_note', true));
    if($get_saved_val != ''){
      $val = $get_saved_val;
    }

		woocommerce_wp_textarea_input(  array(
				'id' => '_out_of_stock_note',
				'wrapper_class' => 'outofstock_field',
				'label' => __( 'Out of Stock Note', 'woocommerce' ),
				'desc_tip' => 'true',
				'value' => $val,
				'description' => __( 'Enter an optional note to out of stock item.', 'woocommerce' ),
				'style' => 'width:70%;'
			)
		);

		woocommerce_wp_checkbox( array(
				'id' => '_wc_oosm_use_global_note',
				'wrapper_class' => 'outofstock_field',
				'label' => __( 'Use Global Note', 'woocommerce' ),
				'cbvalue' => 'yes',
				'value' => esc_attr( $post->_wc_oosm_use_global_note )
			)
		);


}

add_action('woocommerce_product_options_inventory_product_data', 'wc_oosm_textbox', 11);


// Saving the value

function wc_oosm_product_save_data($post_id, $post){


		$note = $_POST['_out_of_stock_note'];
		$global_checkbox = wc_clean($_POST['_wc_oosm_use_global_note']);

    // save the data to the database
		update_post_meta($post_id, '_out_of_stock_note', $note);
		update_post_meta($post_id, '_wc_oosm_use_global_note', $global_checkbox);



}

add_action('woocommerce_process_product_meta', 'wc_oosm_product_save_data', 10, 2);




// Display message

function wc_oosm_display_outofstock_message(){

	global $post, $product;
	$get_saved_val = maybe_unserialize(get_post_meta($post->ID, '_out_of_stock_note', true));
	$global_checkbox = maybe_unserialize(get_post_meta($post->ID, '_wc_oosm_use_global_note', true));
	$global_note = get_option('woocommerce_out_of_stock_note');

	if($get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes'){ ?>

		<div class="outofstock-message">

		<?php echo $get_saved_val; ?>

		</div><!-- /.outofstock-message -->

	<?php }

	if($global_checkbox == 'yes' && !$product->is_in_stock() ){ ?>

		<div class="outofstock-message">

		<?php echo $global_note; ?>

		</div><!-- /.outofstock-message -->

	<?php }


}

add_action('woocommerce_single_product_summary', 'wc_oosm_display_outofstock_message', 6);




// New inventory setting field

add_filter('woocommerce_inventory_settings', 'wc_oosm_setting', 3 );

function wc_oosm_setting(){


	$setting = array(

				array(	'title' => __( 'Inventory Options', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'inventory_options' ),

				array(
					'title' => __( 'Manage Stock', 'woocommerce' ),
					'desc' 		=> __( 'Enable stock management', 'woocommerce' ),
					'id' 		=> 'woocommerce_manage_stock',
					'default'	=> 'yes',
					'type' 		=> 'checkbox'
				),

				array(
					'title' => __( 'Out of Stock Note', 'woocommerce' ),
					'desc' 		=> __( 'Global note for out of stock products.', 'woocommerce' ),
					'id' 		=> 'woocommerce_out_of_stock_note',
					'css' 		=> 'width:60%; height: 125px;',
					'type' 		=> 'textarea',
					'autoload'  => false
				),

				array(
					'title' => __( 'Hold Stock (minutes)', 'woocommerce' ),
					'desc' 		=> __( 'Hold stock (for unpaid orders) for x minutes. When this limit is reached, the pending order will be cancelled. Leave blank to disable.', 'woocommerce' ),
					'id' 		=> 'woocommerce_hold_stock_minutes',
					'type' 		=> 'number',
					'custom_attributes' => array(
						'min' 	=> 0,
						'step' 	=> 1
					),
					'css' 		=> 'width:50px;',
					'default'	=> '60',
					'autoload'  => false
				),

				array(
					'title' => __( 'Notifications', 'woocommerce' ),
					'desc' 		=> __( 'Enable low stock notifications', 'woocommerce' ),
					'id' 		=> 'woocommerce_notify_low_stock',
					'default'	=> 'yes',
					'type' 		=> 'checkbox',
					'checkboxgroup' => 'start',
					'autoload'      => false
				),

				array(
					'desc' 		=> __( 'Enable out of stock notifications', 'woocommerce' ),
					'id' 		=> 'woocommerce_notify_no_stock',
					'default'	=> 'yes',
					'type' 		=> 'checkbox',
					'checkboxgroup' => 'end',
					'autoload'      => false
				),

				array(
					'title' => __( 'Notification Recipient', 'woocommerce' ),
					'desc' 		=> '',
					'id' 		=> 'woocommerce_stock_email_recipient',
					'type' 		=> 'email',
					'default'	=> get_option( 'admin_email' ),
					'autoload'      => false
				),

				array(
					'title' => __( 'Low Stock Threshold', 'woocommerce' ),
					'desc' 		=> '',
					'id' 		=> 'woocommerce_notify_low_stock_amount',
					'css' 		=> 'width:50px;',
					'type' 		=> 'number',
					'custom_attributes' => array(
						'min' 	=> 0,
						'step' 	=> 1
					),
					'default'	=> '2',
					'autoload'      => false
				),

				array(
					'title' => __( 'Out Of Stock Threshold', 'woocommerce' ),
					'desc' 		=> '',
					'id' 		=> 'woocommerce_notify_no_stock_amount',
					'css' 		=> 'width:50px;',
					'type' 		=> 'number',
					'custom_attributes' => array(
						'min' 	=> 0,
						'step' 	=> 1
					),
					'default'	=> '0',
					'autoload'      => false
				),

				array(
					'title' => __( 'Out Of Stock Visibility', 'woocommerce' ),
					'desc' 		=> __( 'Hide out of stock items from the catalog', 'woocommerce' ),
					'id' 		=> 'woocommerce_hide_out_of_stock_items',
					'default'	=> 'no',
					'type' 		=> 'checkbox'
				),

				array(
					'title' => __( 'Stock Display Format', 'woocommerce' ),
					'desc' 		=> __( 'This controls how stock is displayed on the frontend.', 'woocommerce' ),
					'id' 		=> 'woocommerce_stock_format',
					'css' 		=> 'min-width:150px;',
					'default'	=> '',
					'type' 		=> 'select',
					'options' => array(
						''  			=> __( 'Always show stock e.g. "12 in stock"', 'woocommerce' ),
						'low_amount'	=> __( 'Only show stock when low e.g. "Only 2 left in stock" vs. "In Stock"', 'woocommerce' ),
						'no_amount' 	=> __( 'Never show stock amount', 'woocommerce' ),
					),
					'desc_tip'	=>  true,
				),

				array( 'type' => 'sectionend', 'id' => 'inventory_options'),

			);

			return $setting;


}
