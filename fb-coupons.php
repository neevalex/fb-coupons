<?php
/*
Plugin Name: Facebook Coupons Generator
Plugin URI:  https://github.com/neevalex/fb-coupons
Description: Allows generation of a temporary coupons for a facebook (or any other host) visitors with [fb_coupons] shortcode
Version:     1.0.0
Author:      neev alex
Author URI:  https://neevalex.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


add_action( 'wp_ajax_get_fbcoupons', 'get_fbcoupons' );
add_action( 'wp_ajax_nopriv_get_fbcoupons', 'get_fbcoupons' );
function get_fbcoupons() {

	check_ajax_referer( 'fb_coupons', 'security' );

	$coupon_code   = 'FB' . rand( 10000, 99999 ); // Code
	$amount        = 10; // Amount
	$discount_type = 'percent'; // Type: fixed_cart, percent, fixed_product, percent_product

	$coupon = array(
		'post_title'   => $coupon_code,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'shop_coupon',
	);

	$new_coupon_id = wp_insert_post( $coupon );

	// Add meta
	update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
	update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
	update_post_meta( $new_coupon_id, 'individual_use', 'yes' );
	update_post_meta( $new_coupon_id, 'product_ids', '' );
	update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
	update_post_meta( $new_coupon_id, 'usage_limit', '1' );
	update_post_meta( $new_coupon_id, 'expiry_date', date( 'Y-m-d', strtotime( ' +1 day' ) ) );
	update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
	update_post_meta( $new_coupon_id, 'free_shipping', 'yes' );

	$return['response'] = 'ok';
	$return['html']     = '<div class="fb-coupon" style="text-transform:uppercase;">Discount coupon: <strong>' . $coupon_code . '</strong></div>';

	echo json_encode( $return );
	exit;
}




function fb_shortcode_func( $atts ) {
	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
		$atts = shortcode_atts(
			array(
				'title' => 'Get Coupon',
				'host'  => 'facebook.com',
			),
			$atts,
			'bartag'
		);

		$host = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST );

		if ( substr( $host, 0 - strlen( $atts['host'] ) ) == $atts['host'] ) {
			$ajax_params = array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'fb_coupons' ),
			);

			$button = '<button class="btn button fb-coupons">' . $atts['title'] . '</button>';

			wp_enqueue_script( 'fb_coupons_script', plugin_dir_url( __FILE__ ) . 'fb-coupons.js' );
			wp_localize_script( 'fb_coupons_script', 'ajax_object', $ajax_params );

			return $button;
		}
	}

}

add_shortcode( 'fb_coupons', 'fb_shortcode_func' );
