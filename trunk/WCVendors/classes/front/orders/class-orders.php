<?php

/**
 * My account views
 *
 * @author  Matt Gates <http://mgates.me>
 * @package ProductVendor
 */


class WCV_Orders
{


	/**
	 * __construct()
	 */
	function __construct()
	{
		$this->can_view_orders = WC_Vendors::$pv_options->get_option( 'can_show_orders' );
		$this->can_export_csv  = WC_Vendors::$pv_options->get_option( 'can_export_csv' );
		$this->can_view_emails = WC_Vendors::$pv_options->get_option( 'can_view_order_emails' );

		add_action( 'template_redirect', array( $this, 'check_access' ) );
		add_action( 'wp', array( $this, 'display_shortcodes' ) );
	}


	/**
	 *
	 */
	public function check_access()
	{
		if ( is_page( WC_Vendors::$pv_options->get_option( 'orders_page' ) ) && !is_user_logged_in() ) {
			wp_redirect( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) );
			exit;
		}
	}


	/**
	 *
	 */
	public function display_shortcodes()
	{
		if ( is_page( WC_Vendors::$pv_options->get_option( 'orders_page' ) ) && $this->can_view_orders ) {

			wp_enqueue_script( 'jquery' );

			$this->product_id = !empty( $_GET[ 'orders_for_product' ] ) ? (int) $_GET[ 'orders_for_product' ] : false;
			$products = array( $this->product_id );

			$_product = get_product( $this->product_id );
			$children = $_product->get_children();
			if ( !empty( $children ) ) {
				$products = array_merge($products, $children);
				$products = array_unique($products);
			}

			$this->orders = WCV_Queries::get_orders_for_products( $products, array( 'vendor_id' => get_current_user_id() ) );

			add_action( 'init', array( $this, 'verify_order_access' ) );
			add_shortcode( 'wcv_orders', array( $this, 'display_product_orders' ) );

			if ( $this->can_export_csv && !empty( $_POST[ 'export_orders' ] ) ) {
				$this->download_csv();
			}

		}

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function download_csv()
	{
		if ( !$this->orders ) return false;

		extract( WCV_Orders::format_order_details( $this->orders, $this->product_id ) );
		$headers = WCV_Orders::get_headers();

		// Export the CSV
		require_once wcv_plugin_dir . 'classes/front/orders/class-export-csv.php';
		WCV_Export_CSV::output_csv( $this->product_id, $headers, $body, $items );
	}


	/**
	 * Use views to display the Orders page
	 *
	 * @return html
	 */
	public function display_product_orders()
	{
		if ( !WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			ob_start();
			wc_get_template( 'denied.php', array(), 'wc-product-vendor/dashboard/', wcv_plugin_dir . 'views/dashboard/' );

			return ob_get_clean();
		}

		if ( empty( $_GET[ 'orders_for_product' ] ) ) {
			return __( 'You haven\'t selected a product\'s orders to view! Please go back to the Vendor Dashboard and click Show Orders on the product you\'d like to view.', 'wcvendors' );
		}

		if ( !$this->orders ) {
			return __( 'No orders.', 'wcvendors' );;
		}

		if ( !empty( $_POST[ 'submit_comment' ] ) ) {
			require_once wcv_plugin_dir . 'classes/front/orders/class-submit-comment.php';
			WCV_Submit_Comment::new_comment( $this->orders );
		}

		if ( isset( $_POST[ 'mark_shipped' ] ) ) {
			$order_id   = (int) $_POST[ 'order_id' ];
			$product_id = (int) $_POST[ 'product_id' ];
			exit;
		}

		if ( isset( $_POST[ 'update_tracking' ] ) ) {
			$order_id   = (int) $_POST[ 'order_id' ];
			$product_id = (int) $_POST[ 'product_id' ];

			$tracking_provider        = woocommerce_clean( $_POST[ 'tracking_provider' ] );
			$custom_tracking_provider = woocommerce_clean( $_POST[ 'custom_tracking_provider' ] );
			$custom_tracking_link     = woocommerce_clean( $_POST[ 'custom_tracking_link' ] );
			$tracking_number          = woocommerce_clean( $_POST[ 'tracking_number' ] );
			$date_shipped             = woocommerce_clean( strtotime( $_POST[ 'date_shipped' ] ) );

			$order    = new WC_Order( $order_id );
			$products = $order->get_items();
			foreach ( $products as $key => $value ) {
				if ( $value[ 'product_id' ] == $product_id || $value[ 'variation_id' ] == $product_id ) {
					$order_item_id = $key;
					break;
				}
			}
			if ( $order_item_id ) {
				woocommerce_delete_order_item_meta( $order_item_id, __( 'Tracking number', 'wcvendors' ) );
				woocommerce_add_order_item_meta( $order_item_id, __( 'Tracking number', 'wcvendors' ), $tracking_number );

				$message = __( 'Success. Your tracking number has been updated.', 'wcvendors' );
				wc_add_notice( $message, 'success' );

				// Update order data
				update_post_meta( $order_id, '_tracking_provider', $tracking_provider );
				update_post_meta( $order_id, '_custom_tracking_provider', $custom_tracking_provider );
				update_post_meta( $order_id, '_tracking_number', $tracking_number );
				update_post_meta( $order_id, '_custom_tracking_link', $custom_tracking_link );
				update_post_meta( $order_id, '_date_shipped', $date_shipped );
			}

		}

		$headers = WCV_Orders::get_headers();
		$all     = WCV_Orders::format_order_details( $this->orders, $this->product_id );

		wp_enqueue_style( 'pv_frontend_style', wcv_assets_url . 'css/wcv-frontend.css' );
		wp_enqueue_script( 'pv_frontend_script', wcv_assets_url . 'js/front-orders.js' );

		// WC Shipment Tracking Providers
		global $WC_Shipment_Tracking;

		$providers      = !empty( $WC_Shipment_Tracking->providers ) ? $WC_Shipment_Tracking->providers : false;
		$provider_array = array();

		if ( $providers ) {
			foreach ( $providers as $providerss ) {
				foreach ( $providerss as $provider => $format ) {
					$provider_array[ sanitize_title( $provider ) ] = urlencode( $format );
				}
			}
		}
		// End

		ob_start();
		// Show the Export CSV button
		if ( $this->can_export_csv ) {
			wc_get_template( 'csv-export.php', array(), 'wc-product-vendor/orders/', wcv_plugin_dir . 'views/orders/' );
		}

		wc_get_template( 'orders.php', array(
													 'headers'        => $headers,
													 'body'           => $all[ 'body' ],
													 'items'          => $all[ 'items' ],
													 'product_id'     => $all[ 'product_id' ],
													 'providers'      => $providers,
													 'provider_array' => $provider_array,
												), 'wc-product-vendor/orders/', wcv_plugin_dir . 'views/orders/' );

		return ob_get_clean();
	}


	/**
	 * Headers for the Orders page
	 *
	 * @return array
	 */
	public function get_headers()
	{
		$headers = array(
			'order'   => __( 'Order', 'wcvendors' ),
			'product' => __( 'Product Title', 'wcvendors' ),
			'name'    => __( 'Full name', 'wcvendors' ),
			'address' => __( 'Address', 'wcvendors' ),
			'city'    => __( 'City', 'wcvendors' ),
			'state'   => __( 'State', 'wcvendors' ),
			'zip'     => __( 'Zip', 'wcvendors' ),
			'email'   => __( 'Email address', 'wcvendors' ),
			'date'    => __( 'Date', 'wcvendors' ),
		);

		if ( !$this->can_view_emails ) {
			unset( $headers[ 'email' ] );
		}

		return $headers;
	}


	/**
	 * Format the orders with just the products we want
	 *
	 * @param object $orders
	 * @param int    $product_id
	 *
	 * @return array
	 */
	public function format_order_details( $orders, $product_id )
	{
		$body    = $items = array();
		$product = get_product( $product_id )->get_title();

		foreach ( $orders as $i => $order ) {
			$i          = $order->order_id;
			$order      = new WC_Order ( $i );
			$body[ $i ] = array(
				'order_number' => $order->get_order_number(),
				'product'      => $product,
				'name'         => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				'address'      => $order->shipping_address_1,
				'city'         => $order->shipping_city,
				'state'        => $order->shipping_state,
				'zip'          => $order->shipping_postcode,
				'email'        => $order->billing_email,
				'date'         => $order->order_date,
				'comments'     => wptexturize( $order->customer_note ),
			);

			if ( !$this->can_view_emails ) {
				unset( $body[ $i ][ 'email' ] );
			}

			$items[ $i ][ 'total_qty' ] = 0;
			foreach ( $order->get_items() as $line_id => $item ) {

				if ( $item[ 'product_id' ] != $product_id && $item[ 'variation_id' ] != $product_id ) continue;

				$items[ $i ][ 'items' ][ ] = $item;
				$items[ $i ][ 'total_qty' ] += $item[ 'qty' ];
			}

		}

		return array( 'body' => $body, 'items' => $items, 'product_id' => $product_id );
	}


	/**
	 * Verify the current user can view orders for a product
	 *
	 * @param int $product_id
	 */
	public function verify_order_access()
	{
		if ( !is_user_logged_in() || empty( $this->product_id ) ) {
			wp_safe_redirect( apply_filters( 'woocommerce_get_myaccount_page_id', get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ) );
			exit;
		}

		$product = get_post( $this->product_id );
		if ( empty ( $product ) || $product->post_type != 'product' || get_current_user_id() != $product->post_author ) {
			wp_safe_redirect( apply_filters( 'woocommerce_get_myaccount_page_id', get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ) );
			exit;
		}
	}

}