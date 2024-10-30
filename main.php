<?php
/*
Plugin Name: Buy Button for WooCommerce
Version: 1.0.2
Plugin URI: https://noorsplugin.com/buy-button-for-woocommerce-plugin/
Author: naa986
Author URI: https://noorsplugin.com/
Description: Create Buy Now buttons in WooCommerce
Text Domain: buy-button-for-woocommerce
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

class BUY_BUTTON_WC
{
    var $plugin_version = '1.0.2';
    var $plugin_url;
    var $plugin_path;
    
    function __construct()
    {
        define('BUY_BUTTON_WC_VERSION', $this->plugin_version);
        define('BUY_BUTTON_WC_SITE_URL',site_url());
        define('BUY_BUTTON_WC_URL', $this->plugin_url());
        define('BUY_BUTTON_WC_PATH', $this->plugin_path());
        $this->plugin_includes();
    }
    
    function plugin_includes()
    {
        add_action('init', array($this, 'init_handler'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('woocommerce_add_to_cart_redirect', array($this, 'add_to_cart_redirect_handler'), 10, 2);
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_add_to_cart'), 10, 3);
        add_shortcode('buy_button_wc', array($this, 'buy_button_handler'));
    }
    
    function plugin_url()
    {
        if($this->plugin_url) return $this->plugin_url;
        return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
    }
    
    function plugin_path(){ 	
        if ( $this->plugin_path ) return $this->plugin_path;		
        return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
    }  
    
    function init_handler()
    {
        load_plugin_textdomain('buy-button-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
    }
    
    function admin_notices() {
        if(!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>'.__('Buy Button for WooCommerce requires WooCommerce to be installed and active', 'buy-button-for-woocommerce').'</p></div>';
        }
    }    
    
    function add_to_cart_redirect_handler($url, $adding_to_cart)
    {
        if(!isset($_REQUEST['buy-button-wc']) || empty($_REQUEST['buy-button-wc'])){
            return $url;
        }
        $checkout_url = wc_get_checkout_url();
        if(isset($checkout_url) && !empty($checkout_url)){
            $url = $checkout_url;
        }
        return $url;
    }
    
    function validate_add_to_cart($passed_validation, $product_id, $quantity)
    {
        if(!isset($_REQUEST['buy-button-wc']) || empty($_REQUEST['buy-button-wc'])){
            return $passed_validation;
        }
        if(!WC()->cart->is_empty()){
            WC()->cart->empty_cart();
        }
        return $passed_validation;
    }
    
    function buy_button_handler($atts)
    {
        if(!isset($atts['id']) || empty($atts['id'])){
            return __('You need to provide a valid product ID in the shortcode', 'buy-button-for-woocommerce');    
        }
        $atts = array_map('sanitize_text_field', $atts);
        $button_text = 'Buy Now';
        if(isset($atts['button_text']) && !empty($atts['button_text'])){
            $button_text = $atts['button_text'];
        }
        $class = 'woocommerce';
        if(isset($atts['class']) && !empty($atts['class'])){
            $class = $class.' '.$atts['class'];
        }
        $button_code = '';
        $url = add_query_arg(array(
            'add-to-cart' => $atts['id'],
            'buy-button-wc' => 1,
        ));
        $button_code .= '<div class="'.esc_attr($class).'"><a href="'.esc_url($url).'" class="button" rel="nofollow">'.esc_html($button_text).'</a></div>';
        return $button_code;
    }

}

$GLOBALS['buy_button_wc'] = new BUY_BUTTON_WC();
