=== WC Vendors ===
Contributors: wcvendors, digitalchild
Tags: woocommerce, vendor, shops, product vendor, product vendors, seller
Donate link: http://www.wcvendors.com/
Author URI: http://www.wcvendors.com/
Plugin URI: http://www.wcvendors.com/
Requires at least: 4.0
Tested up to: 4.0
Stable tag: 1.1.4
License: GPLv2 or later

The free multi-vendor plugin for WooCommerce.  Now you can allow anyone to open a store on your site!

== Description ==
Create your own marketplace and allow vendors to sell just like etsy, Envato, or Amazon type sites! This allows other users to sell tangible products, virtual products, or downloads on your site. With this plugin, your vendors receive commissions you set on products they sell from your store.

## Features ## 
* Instantly pay users their commission as soon as an order is placed
* Or, pay commission on a schedule. Weekly, biweekly, monthly, or manually.
* Users can submit products for admin review
* Users can view live sales and reports for their products
* Users can comment on their orders (eg, to inform customers of a tracking number)
* Users can export their orders to a CSV file

== Installation ==
1. Download and install from WordPress.org.
2. Configure as you see fit, under WooCommerce / WC Vendors.
3. Configure email notifications under WooCommerce / Settings
4. View Commissions under WooCommerce / Commissions and WooCommerce / Reports / WC Vendors
5. For more help, visit WCVendors.com and say hello on the community forums.

== Frequently Asked Questions ==

= What version of WooCommerce do you support ? =

Woocommerce 2.1 or above is supported.

= What version of php has been tested ? =

PHP 5.4 has been tested. 

= Where do I get help ? =

You can post a support question on the support tab, however you'll get more help over at our community forums (http://www.wcvendors.com)

== Screenshots ==

1. General options - Configure default commission, how users can register and more.
2. Product options pag - allows you to hide specific options from the add new product window. 
3. Capabilities options - restrict what your vendors can see and do. 
4. Pages options - what pages will load the relevant vendor templates. These can be customised. 
5. User paypment info - define how your users get paid and when. 
6. WC Vendors Paypal Adaptive payments setup.
7. Email template options for the relevant WC Vendor emails. 

== Upgrade Notice ==
No Upgrade required at this time.

== Changelog ==
## October 14, 2014 ##
* version 1.1.4 (First release on WordPress.org)
* Resolved shipping bug
* Commission totals are now properly displayed on the WooCommerce / WC Vendors / Payments tab
* Number of internal bug fixes

## August 09, 2014 ##
* version 1.1.3 (Initial Public Release)
* Numerous bug fixes
* New Shortcodes:  These new shortcodes are based on the WooCommerce included shortcodes.  They have been modified to show output based on the vendor you specify.  All other arguments to the shortcodes from WooCommerce will also work on these shortcodes.

          Recent Products Shortcode
          [wcv_recent_products vendor=\"VENDOR-LOGIN-NAME\" per_page=3]

          Products Shortcode
          [wcv_products vendor=\"VENDOR-LOGIN-NAME\"]

          Featured Products Shortcode
          [wcv_featured_products vendor=\"VENDOR-LOGIN-NAME\"]

          Sale Products
          [wcv_sale_products vendor=\"VENDOR-LOGIN-NAME\"]

          Top Rated Products on sale
          [wcv_top_rated_products vendor=\"VENDOR-LOGIN-NAME\"]

          Best Selling Products on sale
          [wcv_best_selling_products vendor=\"VENDOR-LOGIN-NAME\"]
