<?php
/**
 * Plugin Name: WooCommerce Embed Slides
 * Plugin URI: https://github.com/PinchOfCode/woocommerce-embed-slides/
 * Description: Allows to add slide decks by slides.com in your product, instead of featured image.
 * Version: 1.0.0
 * Author: Pinch Of Code
 * Author URI: http://pinchofcode.com
 * Requires at least: 3.8
 * Tested up to: 3.9.2
 *
 * License:  GPL-2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/PinchOfCode/woocommerce-embed-slides/
 *
 * Text Domain: woocommerce-embed-slides
 * Domain Path: /i18n/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	/**
	 * Checks if WooCommerce is active. If not, print an admin notice and exit.
	 *
	 * @return void
	 */
	add_action( 'admin_notices', 'woocommerce_custom_stock_status_wc_inactive' );
    function woocommerce_custom_stock_status_wc_inactive(){
	    echo '<div class="error"><p>WooCommerce Embed Slides ' . __('is enabled but not effective. It requires <a href="http://wordpress.org/plugins/woocommerce/" target="_blank" title="WooCommerce - excelling eCommerce">WooCommerce</a> in order to work.', 'woocommerce-embed-slides' ) . '</p></div>';
	}

	return;
}

if ( ! class_exists( 'WC_Embed_Slides' ) ) :

class WC_Embed_Slides {
	/**
	 * __construct method
	 */
	public function __construct() {
		add_filter( 'plugin_action_links', array( $this, 'add_plugin_links' ), 10, 4 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_product_data_option' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'input_field_update' ) );

		add_action( 'woocommerce_before_single_product_summary', array( $this, 'check_if_available' ), 20 );

		$this->load_textdomain();
	}

	/**
	 * Print custom slide deck field in Product Data
	 *
	 * @return void
	 */
	public function add_product_data_option() {
		woocommerce_wp_text_input( array(
			'id' => '_slide_frame_code',
			'label' => __( 'Slide Deck Code', 'woocommerce-embed-slides' ),
			'placeholder' => 'http://slid.es/<username>/<deckname>',
			'description' => __( 'Paste here the share code from <a href="http://slides.com" title="Slides">Slides</a>. Learn <a href="http://help.slides.com/knowledgebase/articles/234819-sharing-decks" title="Sharing Decks">how to share a deck</a>.', 'woocommerce-embed-slides' ),
			'type' => 'text',
		) );
	}

	/**
	 * Save custom post text fields for simple products
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function input_field_update( $post_id ) {
		update_post_meta( $post_id, '_slide_frame_code', esc_attr( $_POST['_slide_frame_code'] ) );
	}

	/**
	 * Check if a slide desk URL is available in the product settings.
	 *
	 * @return void
	 */
	public function check_if_available() {
		if( is_product() ) {
			$slide_deck = get_post_meta( get_the_ID(), '_slide_frame_code', true );

			if ( ! empty( $slide_deck ) ) {
				add_filter( 'woocommerce_single_product_image_html', array( $this, 'show_slide_deck' ), 20 );
			}
		}
	}

	/**
	 * Replace the featured image with a slide deck.
	 *
	 * @param string $html
	 * @return string
	 */
	public function show_slide_deck( $html ) {
		$size      = wc_get_image_size( apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );
		$width     = $size['width'];
        $height    = $size['height'];
        $slide_url = get_post_meta( get_the_ID(), '_slide_frame_code', true );

        // Remove any useless parameter in the URL
        if ( false === strpos( $slide_url, '/embed' ) ) {
        	$slide_url  = preg_replace( '/([#\/\d]+)*$/', '', $slide_url );
        	$slide_url .= '/embed';
        }

		$html = '<div class="product-slide-deck">';
			$html .= '<iframe src="' . $slide_url . '" width="' . $width . '" height="' . $height . '" scrolling="no" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>';
		$html .= '</div>';

		return apply_filters( 'woocommerce_embed_slides_html', $html, $slide_url, $size );
	}

	/**
	 * Load plugin textdomain
	 *
	 * @return void
	 */
	public function load_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-embed-slides' );
		$dir    = trailingslashit( WP_LANG_DIR );

		load_textdomain( 'woocommerce-embed-slides', $dir . 'woocommerce-embed-slides/woocommerce-embed-slides-' . $locale . '.mo' );
		load_textdomain( 'woocommerce-embed-slides', $dir . 'plugins/woocommerce-embed-slides-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-embed-slides', false, dirname( plugin_basename ( __FILE__ ) ) . '/i18n/' );
	}

	/**
	 * Add plugins links in Appearance > Plugins
	 *
	 * @param array $links
	 * @param string $file
	 * @return void
	 */
	public function add_plugin_links( $links, $file ) {
	    if( $file == plugin_basename( __FILE__ ) ) {
	        $donate_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@pinchofcode.com&item_name=Donation+for+Pinch+Of+Code" title="' . __( 'Donate', 'woocommerce-embed-slides' ) . '" target="_blank">' . __( 'Donate', 'woocommerce-embed-slides' ) . '</a>';
	        array_unshift( $links, $donate_link );
	    }

	    return $links;
	}
}

endif; // if ! class_exists

$WC_Embed_Slides = new WC_Embed_Slides();