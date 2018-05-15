<?php
/**
 * Plugin Name: PacPay - $PAC payments for WooCommerce
 * Plugin URI: https://paccoin.net/
 * Description: A WooCommerce payment gateway that enables direct $PAC payments for you shop.
 * Version: 0.0.5
 * Author: Paccoin
 * Author URI: https://paccoin.net/
 * Requires at least: 4.4.2
 * Tested up to: 4.4.2
 *
 * Text Domain: pacpay-woocommerce
 * Domain Path: /i18n/languages/
 *
 * @package PacPay
 * @category Core
 * @author BlackCarrotVentures, The Pac Team
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main instance of PacPay.
 *
 * Returns the main instance of DP to prevent the need to use globals.
 *
 * @since  0.0.1
 * @return PacPay
 */

if ( ! class_exists( 'PacPay' ) ) :

/**
 * Main PacPay Class.
 *
 * @class PacPay
 * @version    0.0.1
 */

final class PacPay {

    /**
     * PacPay version.
     *
     * @var string
     */
    public $version = '0.0.5';

    /**
     * The single instance of the class.
     *
     * @var PacPay
     * @since 0.0.1
     */
    protected static $_instance = null;

    /**
     * Required extensions for this plugin to function.
     *
     * @var PacPay
     * @since 0.0.1
     */
    private static $required_extensions = array(
        'gmp', 'bcmath', 'mcrypt', 'json', 'curl', 'openssl', 'gd'
    );

    /**
     * Main PacPay Instance.
     *
     * Ensures only one instance of PacPay is loaded or can be loaded.
     *
     * @since 0.0.1
     * @static
     * @see DP()
     * @return PacPay - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     * @since 0.0.1
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'pacpay-woocommerce' ), '0.0.1' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     * @since 0.0.1
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '0.0.1' );
    }

    /**
     * Auto-load in-accessible properties on demand.
     * @param mixed $key
     * @return mixed
     */
    public function __get( $key ) {
        if ( in_array( $key, array( 'payment_gateways', 'shipping', 'mailer', 'checkout' ) ) ) {
            return $this->$key();
        }
    }

    /**
     * PacPay Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        $this->init_gateways();
        do_action( 'pacpay_woocommerce_loaded' );
    }

    /**
     * Hook into actions and filters.
     * @since  0.0.1
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( 'DP_Install', 'install' ) );
        add_action( 'init', array( $this, 'init' ), 0 );

        register_deactivation_hook( __FILE__, array( 'DP_Install', 'deactivate' ) );
        register_uninstall_hook( __FILE__, array( 'DP_Install', 'uninstall' ) );
    }


    /**
     * Define DP Constants.
     */
    private function define_constants() {
        $upload_dir = wp_upload_dir();

        $this->define( 'DP_PLUGIN_FILE', __FILE__ );
        $this->define( 'DP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        $this->define( 'DP_VERSION', $this->version );
        $this->define( 'DP_PLUGIN_NAME', 'PacPay for WooCommerce' );
    }

    /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }


    /**
     * What type of request is this?
     * string $type ajax, frontend or admin.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        require_once(dirname(__FILE__) . '/includes/cron.php');
        require_once(dirname(__FILE__) . '/includes/class-dp-address-factory.php');
        require_once(dirname(__FILE__) . '/includes/class-dp-exchange-rate.php');
        require_once(dirname(__FILE__) . '/includes/class-insight-api.php');
        require_once(dirname(__FILE__) . '/includes/class-coin-util.php');
        require_once(dirname(__FILE__) . '/includes/class-gateways.php');
        require_once(dirname(__FILE__) . '/includes/class-dp-invoice.php');
        require_once(dirname(__FILE__) . '/includes/class-logger.php');
        require_once(dirname(__FILE__) . '/includes/class-dp-install.php');
    }

    /**
     * Init PacPay when WordPress/WooCommerce Initialises.
     */
    public function init() {
        // Before init action.
        do_action( 'before_pacpay_woocommerce_init' );

        // Set up localisation.
        $this->load_plugin_textdomain();

        // Add Ajax-initialised backend payment checks
        $this->add_ajax_actions();

        // Init action.
        do_action( 'pacpay_woocommerce_init' );
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     *
     * Locales found in:
     *      - WP_LANG_DIR/pacpay-woocommerce/pacpay-woocommerce-LOCALE.mo
     *      - WP_LANG_DIR/plugins/pacpay-woocommerce-LOCALE.mo
     */
    public function load_plugin_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'pacpay-woocommerce' );

        load_textdomain( 'pacpay-woocommerce', WP_LANG_DIR . '/PacPay-woocommerce/PacPay-woocommerce-' . $locale . '.mo' );
        load_plugin_textdomain( 'pacpay-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
    }

    /**
     * Get the template path.
     * @return string
     */
    public function template_path() {
        return dirname(__FILE__) . '/templates/';
    }

    /**
     * Hook into $this->load_gateways
     * @return string
     */
    private function init_gateways() {
        add_action('woocommerce_loaded', array( __CLASS__, 'load_gateways' ), 0 );
    }

    /**
     * Load all gateways that are enabled on admin screen.
     * @return string
     */
    public static function load_gateways() {
      // Nothing happens here if WooCommerce is not loaded
      if (!class_exists('WC_Payment_Gateway')) {
          return;
      }

      // Valid gateways that this plugin enables
      $enabled_gateways = array('pac');

      $gateway_classes['pac'] = require_once(dirname(DP_PLUGIN_FILE) . '/includes/gateways/class-wc-gateway-pacpay.php');

      // Hook payment gateway into WooCommerce
      foreach ( $enabled_gateways as $gateway ) {
          $class = $gateway_classes[ $gateway ];

          // Hook into WooCommerce - add necessary hooks and filters
          add_filter('woocommerce_payment_gateways', array($class, 'add_gateway_to_woocommerce') );
          add_filter('woocommerce_currencies',       array($class, 'add_this_currency') );
          add_filter('woocommerce_currency_symbol',  array($class, 'add_this_currency_symbol'), 10, 2);
      }
    }

    /**
     * Return a list of required extensions that are not loaded/installed.
     * @return array
     */
    public static function missing_required_extensions() {
        $not_loaded = array();
        foreach ( self::$required_extensions as $ext ) {
            if (!extension_loaded( $ext )) {
                $not_loaded[] = $ext;
            }
        }
        return $not_loaded;
    }


    /**
     * Add ajax callback for order processing
     * @return array
     */
    public static function add_ajax_actions() {
        // TODO: currently defined in 'cron.php'
        add_action( 'wp_ajax_check_order', 'process_order_callback' );
        add_action( 'wp_ajax_nopriv_check_order', 'process_order_callback' );
    }

}

endif;


function DP() {
    return PacPay::instance();
}

// Global for backwards compatibility.
$GLOBALS['pacpay-woocommerce'] = DP();
