<?php
/**
 * WCV_Shortcodes class. 
 *
 * @class 		WCV_Shortcodes
 * @version		1.0.0
 * @package		WCVendors/Classes
 * @category	Class
 * @author 		WC Vendors (Jamie Madden http://github.com/digitalchild)
 */
class WCV_Shortcodes {

	/**
	 * Initialise shortcodes
	 */
	function __construct() {
		// Define shortcodes

		// Recent Products 
		add_shortcode( 'wcv_recent_products', array( $this, 'recent_products'));
		// Products by vendor
		add_shortcode( 'wcv_products', array( $this, 'products'));
		//Featured products by vendor
		add_shortcode( 'wcv_featured_products', array( $this, 'featured_products'));
		// Sale products by vendor
		add_shortcode( 'wcv_sale_products', array( $this, 'sale_products'));
		// Top Rated products by vendor 
		add_shortcode( 'wcv_top_rated_products', array( $this, 'top_rated_products'));
		// Best Selling product 
		add_shortcode( 'wcv_best_selling_products', array( $this, 'best_selling_products'));
	}

	public static function get_vendor ( $slug ) { 

		$vendor_id = get_user_by('slug', $slug); 

		if (!empty($vendor_id)) { 
			$author = $vendor_id->ID; 
		} else $author = '';

		return $author; 

	}

	/*
		
		Get recent products based on vendor username 
	
	*/
	public static function recent_products( $atts ) {
			global $woocommerce_loop;
 
			extract( shortcode_atts( array(
				'per_page' 	=> '12',
				'vendor' 	=> '', 
				'columns' 	=> '4',
				'orderby' 	=> 'date',
				'order' 	=> 'desc'
			), $atts ) );
 
			$meta_query = WC()->query->get_meta_query();
			
			$args = array(
				'post_type'				=> 'product',
				'post_status'			=> 'publish',
				'author'				=> self::get_vendor($vendor), 
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' 		=> $per_page,
				'orderby' 				=> $orderby,
				'order' 				=> $order,
				'meta_query' 			=> $meta_query
			);
 
			ob_start();
 
			$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
 
			$woocommerce_loop['columns'] = $columns;
 
			if ( $products->have_posts() ) : ?>
 
				<?php woocommerce_product_loop_start(); ?>
 
					<?php while ( $products->have_posts() ) : $products->the_post(); ?>
 
						<?php wc_get_template_part( 'content', 'product' ); ?>
 
					<?php endwhile; // end of the loop. ?>
 
				<?php woocommerce_product_loop_end(); ?>
 
			<?php endif;
 
			wp_reset_postdata();
 
			return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List all products for a vendor shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function products( $atts ) {
		global $woocommerce_loop;

		if ( empty( $atts ) ) return '';

		extract( shortcode_atts( array(
			'vendor' 	=> '',
			'columns' 	=> '4',
			'orderby'   => 'title',
			'order'     => 'asc'
		), $atts ) );



		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'author'				=> self::get_vendor($vendor), 
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $orderby,
			'order' 				=> $order,
			'posts_per_page' 		=> -1,
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array('catalog', 'visible'),
					'compare' 	=> 'IN'
				)
			)
		);

		if ( isset( $atts['skus'] ) ) {
			$skus = explode( ',', $atts['skus'] );
			$skus = array_map( 'trim', $skus );
			$args['meta_query'][] = array(
				'key' 		=> '_sku',
				'value' 	=> $skus,
				'compare' 	=> 'IN'
			);
		}

		if ( isset( $atts['ids'] ) ) {
			$ids = explode( ',', $atts['ids'] );
			$ids = array_map( 'trim', $ids );
			$args['post__in'] = $ids;
		}

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}


	/**
	 * Output featured products
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function featured_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'vendor' => '',
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' 	=> 'date',
			'order' 	=> 'desc'
		), $atts ) );

		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'author'				=> self::get_vendor($vendor), 
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> $per_page,
			'orderby' 				=> $orderby,
			'order' 				=> $order,
			'meta_query'			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array('catalog', 'visible'),
					'compare'	=> 'IN'
				),
				array(
					'key' 		=> '_featured',
					'value' 	=> 'yes'
				)
			)
		);

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}
	
	/**
	 * List all products on sale
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function sale_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'vendor' 		=> '', 
			'per_page'      => '12',
			'columns'       => '4',
			'orderby'       => 'title',
			'order'         => 'asc'
		), $atts ) );

		// Get products on sale
		$product_ids_on_sale = wc_get_product_ids_on_sale();

		$meta_query   = array();
		$meta_query[] = WC()->query->visibility_meta_query();
		$meta_query[] = WC()->query->stock_status_meta_query();
		$meta_query   = array_filter( $meta_query );

		$args = array(
			'posts_per_page'	=> $per_page,
			'author'			=> self::get_vendor($vendor), 
			'orderby' 			=> $orderby,
			'order' 			=> $order,
			'no_found_rows' 	=> 1,
			'post_status' 		=> 'publish',
			'post_type' 		=> 'product',
			'meta_query' 		=> $meta_query,
			'post__in'			=> array_merge( array( 0 ), $product_ids_on_sale )
		);

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List top rated products on sale by vendor
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function top_rated_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'vendor'		=> '', 
			'per_page'      => '12',
			'columns'       => '4',
			'orderby'       => 'title',
			'order'         => 'asc'
			), $atts ) );

		$args = array(
			'post_type' 			=> 'product',
			'author'				=> self::get_vendor($vendor), 
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'   => 1,
			'orderby' 				=> $orderby,
			'order'					=> $order,
			'posts_per_page' 		=> $per_page,
			'meta_query' 			=> array(
				array(
					'key' 			=> '_visibility',
					'value' 		=> array('catalog', 'visible'),
					'compare' 		=> 'IN'
				)
			)
		);

		ob_start();

		add_filter( 'posts_clauses', array( WC_Shortcodes, 'order_by_rating_post_clauses' ) );

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		remove_filter( 'posts_clauses', array( WC_Shortcodes, 'order_by_rating_post_clauses' ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List best selling products on sale per vendor
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function best_selling_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'vendor'		=> '', 
			'per_page'      => '12',
			'columns'       => '4'
		), $atts ) );

		$args = array(
			'post_type' 			=> 'product',
			'post_status' 			=> 'publish',
			'author'				=> self::get_vendor($vendor), 
			'ignore_sticky_posts'   => 1,
			'posts_per_page'		=> $per_page,
			'meta_key' 		 		=> 'total_sales',
			'orderby' 		 		=> 'meta_value_num',
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array( 'catalog', 'visible' ),
					'compare' 	=> 'IN'
				)
			)
		);

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

}