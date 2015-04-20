<?php

/**
 *
 *
 * @author Matt Gates <http://mgates.me>
 * @package
 */


class WCV_Vendor_Cart
{


	/**
	 *
	 */
	function __construct()
	{
		add_filter( 'woocommerce_get_item_data', array( 'WCV_Vendor_Cart', 'sold_by' ), 10, 2 );
		add_action( 'woocommerce_product_meta_start', array( 'WCV_Vendor_Cart', 'sold_by_meta' ), 10, 2 );
	}


	/**
	 *
	 *
	 * @param unknown $values
	 * @param unknown $cart_item
	 *
	 * @return unknown
	 */
	public static function sold_by( $values, $cart_item )
	{
		$vendor_id = $cart_item[ 'data' ]->post->post_author;
		$sold_by   = WCV_Vendors::is_vendor( $vendor_id )
			? sprintf( '<a href="%s" target="_TOP">%s</a>', WCV_Vendors::get_vendor_shop_page( $vendor_id ), WCV_Vendors::get_vendor_sold_by( $vendor_id ) )
			: get_bloginfo( 'name' );

		$values[ ] = array(
			'name'    => apply_filters('wcvendors_cart_sold_by', __( 'Sold by', 'wcvendors' )),
			'display' => $sold_by,
		);

		return $values;
	}


	/**
	 *
	 */
	public static function sold_by_meta()
	{
		$vendor_id = get_the_author_meta( 'ID' );
		$sold_by = WCV_Vendors::is_vendor( $vendor_id )
			? sprintf( '<a href="%s" class="wcvendors_cart_sold_by_meta">%s</a>', WCV_Vendors::get_vendor_shop_page( $vendor_id ), WCV_Vendors::get_vendor_sold_by( $vendor_id ) )
			: get_bloginfo( 'name' );

		echo apply_filters('wcvendors_cart_sold_by_meta', __( 'Sold by: ', 'wcvendors' )) . $sold_by . '<br/>';
	}

}
