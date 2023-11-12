<?php
/**
 * Plugin Name: Order Alerts Twilio WooCommerce
 * Description: Sends order alerts via Twilio for WooCommerce.
 * Version: 1.0
 * Author: Vasim Shaikh
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'vendor/Twilio/autoload.php'; // Include the Twilio PHP SDK

use Twilio\Rest\Client;

class Order_Alerts_Twilio_WooCommerce {

    private $twilio_account_sid;
    private $twilio_auth_token;
    private $twilio_phone_number;

    public function __construct() {
        // Load Twilio settings from the database
        $this->load_twilio_settings();

        // Add action hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('woocommerce_order_status_changed', array($this, 'send_order_alert'), 10, 4);
    }

    private function load_twilio_settings() {
        // Load Twilio settings from the database
        $this->twilio_account_sid = get_option('twilio_account_sid');
        $this->twilio_auth_token = get_option('twilio_auth_token');
        $this->twilio_phone_number = get_option('twilio_phone_number');
    }


    public function send_order_alert($order_id) {
        // Get the order object
        $order = wc_get_order($order_id);

        // Check if the order object is valid
        if ($order) {
            // Get the order status
            $order_status = $order->get_status();
                // Get customer phone number (replace with your logic to retrieve customer phone number)
                $phone_number = $order->get_billing_phone();

                // Send order alert via Twilio (replace with your Twilio integration logic)
                $this->send_twilio_sms($phone_number, 'Alert: Order #' . $order_id . ' status has been changed. : '.$order_status);
        }

    } 

    public function add_admin_menu() {
        // Add a submenu under "WooCommerce"
        add_submenu_page(
            'woocommerce',
            'Twilio Settings',
            'Twilio Settings',
            'manage_options',
            'twilio_settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h2>Twilio Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('twilio_settings_group');
                do_settings_sections('twilio_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        // Register Twilio settings
        register_setting('twilio_settings_group', 'twilio_account_sid');
        register_setting('twilio_settings_group', 'twilio_auth_token');
        register_setting('twilio_settings_group', 'twilio_phone_number');

        // Add settings section
        add_settings_section(
            'twilio_settings_section',
            'API Settings',
            array($this, 'render_settings_section'),
            'twilio_settings'
        );

        // Add settings fields
        add_settings_field(
            'twilio_account_sid',
            'Account SID',
            array($this, 'render_text_field'),
            'twilio_settings',
            'twilio_settings_section',
            array('label_for' => 'twilio_account_sid')
        );

        add_settings_field(
            'twilio_auth_token',
            'Auth Token',
            array($this, 'render_text_field'),
            'twilio_settings',
            'twilio_settings_section',
            array('label_for' => 'twilio_auth_token')
        );

        add_settings_field(
            'twilio_phone_number',
            'Twilio Phone Number',
            array($this, 'render_text_field'),
            'twilio_settings',
            'twilio_settings_section',
            array('label_for' => 'twilio_phone_number')
        );
    }

    public function render_settings_section() {
        echo 'Configure your Twilio API settings below:';
    }

    public function render_text_field($args) {
        $option_name = $args['label_for'];
        $option_value = get_option($option_name);
        ?>
        <input
            type="text"
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr($option_name); ?>"
            value="<?php echo esc_attr($option_value); ?>"
        />
        <?php
    }

    // Rest of the class remains the same...
}


$order_alerts_twilio_wooCommerce = new Order_Alerts_Twilio_WooCommerce();