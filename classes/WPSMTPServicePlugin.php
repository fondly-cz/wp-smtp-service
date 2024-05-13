<?php
namespace FondlyCz\WpSmtpService;

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Nette\Mail\SendmailMailerException;
use Nette\Mail\SmtpException;

class WPSMTPServicePlugin {

    // Constructor
    public function __construct() {
        
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));

        // Add a filter to add the configure link in the plugins list
        add_filter('plugin_action_links_wp-smtp-service/wp-smtp-service.php', array($this, 'add_configure_link'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
    }

    // Method to add admin menu
    public function add_admin_menu() {
        add_options_page('WP SMTP Service', 'WP SMTP Service', 'manage_options', 'wp_smtp_service', array($this, 'options_page'));
    }

    // Method to initialize plugin settings
    public function settings_init() {
        register_setting('wp_smtp_service_options', 'wp_smtp_service_options', array($this, 'sanitize'));
        
        add_settings_section('wp_smtp_service_options_section', 'SMTP Settings', array($this, 'section_callback'), 'wp_smtp_service');
        
        add_settings_field('smtp_host', 'SMTP Host', array($this, 'smtp_host_callback'), 'wp_smtp_service', 'wp_smtp_service_options_section');
        add_settings_field('smtp_port', 'SMTP Port', array($this, 'smtp_port_callback'), 'wp_smtp_service', 'wp_smtp_service_options_section');
        add_settings_field('smtp_username', 'SMTP Username', array($this, 'smtp_username_callback'), 'wp_smtp_service', 'wp_smtp_service_options_section');
        add_settings_field('smtp_password', 'SMTP Password', array($this, 'smtp_password_callback'), 'wp_smtp_service', 'wp_smtp_service_options_section');
        add_settings_field('smtp_encryption', 'SMTP Encryption', array($this, 'smtp_encryption_callback'), 'wp_smtp_service', 'wp_smtp_service_options_section');
    }

    // Method to display the plugin options page
    public function options_page() {
        ?>
        <div class="wrap">
            <h2>WP SMTP Service Settings</h2>



            <form action="options.php" method="post">
                <?php
                settings_fields('wp_smtp_service_options');
                do_settings_sections('wp_smtp_service');
                submit_button();
                ?>

                <p><a id="send-test-mail-btn" href="<?php echo esc_url(admin_url('options-general.php?page=wp_smtp_service&send_test_mail=true')); ?>"><?php esc_html_e('Send testing mail!', 'wp-smtp-service'); ?></a></p>
            </form>
        </div>
        <?php
    }

    // Method to add a configure link in the plugins list
    public function add_configure_link($links) {
        $configure_link = '<a href="' . admin_url('options-general.php?page=wp_smtp_service') . '">' . esc_html__('Settings') . '</a>';
        array_unshift($links, $configure_link);
        return $links;
    }

    // Method to sanitize and validate settings
    public function sanitize($input) {
        // Add sanitization logic here if needed
        return $input;
    }

    // Callback for the SMTP Host field
    public function smtp_host_callback() {
        $options = get_option('wp_smtp_service_options');
        echo "<input type='text' name='wp_smtp_service_options[smtp_host]' value='" . esc_attr($options['smtp_host']) . "' />";
    }

    // Callback for the SMTP Port field
    public function smtp_port_callback() {
        $options = get_option('wp_smtp_service_options');
        echo "<input type='text' name='wp_smtp_service_options[smtp_port]' value='" . esc_attr($options['smtp_port']) . "' />";
    }

    // Callback for the SMTP Username field
    public function smtp_username_callback() {
        $options = get_option('wp_smtp_service_options');
        echo "<input type='text' name='wp_smtp_service_options[smtp_username]' value='" . esc_attr($options['smtp_username']) . "' />";
    }

    // Callback for the SMTP Password field
    public function smtp_password_callback() {
        $options = get_option('wp_smtp_service_options');
        echo "<input type='password' name='wp_smtp_service_options[smtp_password]' value='" . esc_attr($options['smtp_password']) . "' />";
    }

    // Callback for the SMTP Encryption field
    public function smtp_encryption_callback() {
        $options = get_option('wp_smtp_service_options');
        $encryption_options = array('None', 'SSL', 'TLS');
        echo "<select name='wp_smtp_service_options[smtp_encryption]'>";
        foreach ($encryption_options as $option) {
            echo "<option value='$option' " . selected($options['smtp_encryption'], $option, false) . ">$option</option>";
        }
        echo "</select>";
    }

    // Callback for the section
    public function section_callback() {
        echo '<div id="message-container"></div>';
        echo '<p>Configure your SMTP settings below:</p>';
    }

    // Method to customize the wp_mail function
    public function customize_wp_mail($args) {
        $to      = $args['to'];
        $subject = $args['subject'];
        $message = $args['message'];
        $headers = $args['headers'];

        // Use Nette\Mail to send the email with SMTP
        $message = new \Nette\Mail\Message();
        $message->setFrom($headers['From']);
        $message->addTo($to);
        $message->setSubject($subject);
        $message->setHtmlBody($message);

        // Retrieve SMTP settings from options
        $smtpOptions = get_option('wp_smtp_service_options');

        // Instantiate the Nette\Mail\SmtpMailer class with named parameters
        $smtpMailer = new \Nette\Mail\SmtpMailer(
            host: $smtpOptions['smtp_host'],      // Replace with your SMTP host
            port: $smtpOptions['smtp_port'],      // Replace with your SMTP port
            username: $smtpOptions['smtp_username'],  // Replace with your SMTP username
            password: $smtpOptions['smtp_password'],  // Replace with your SMTP password
            encryption: $smtpOptions['smtp_encryption']   // Replace with your SMTP encryption type (e.g., 'ssl', 'tls', or '')
        );
        $result = $smtpMailer->send($message);

        return $result;
    }

     // Method to send a test email
     public function send_test_email() {
        $to = 'info@martinkokes.cz'; // Replace with the recipient email address
        $subject = 'Your email works!';
        $body = 'This is a test email sent from your WordPress site. If you received this, your email configuration is working correctly.';

        // Use Nette\Mail to send the test email with SMTP
        $message = new \Nette\Mail\Message();
        $message->setFrom('webmaster@liborcinka.cz'); // Replace with the sender email address
        $message->addTo($to);
        $message->setSubject($subject);
        $message->setHtmlBody($body);

        $smtpMailer = $this->create_mailer();
        try {
            $smtpMailer->send($message);
        } catch (\Nette\Mail\SmtpException $th) {
            echo '<div class="notice notice-error"><p>Test email failed to send!</p></div>';
            echo '<div class="notice notice-error"><p>' . $th->getMessage() . '</p></div>';
            return;
        }

        echo '<div class="notice notice-success"><p>Test email sent successfully!</p></div>';
        
    }

    private function create_mailer() {
        // Retrieve SMTP settings from options
        $smtpOptions = get_option('wp_smtp_service_options');

        // Instantiate the Nette\Mail\SmtpMailer class with named parameters
        $smtpMailer = new \Nette\Mail\SmtpMailer(
            host: $smtpOptions['smtp_host'],
            port: $smtpOptions['smtp_port'],
            username: $smtpOptions['smtp_username'],
            password: $smtpOptions['smtp_password'],
            encryption: strtolower($smtpOptions['smtp_encryption']),
        );
        return $smtpMailer;
    }

    public function enqueue_custom_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-smtp-service', plugin_dir_url(__FILE__) . 'wp-smtp-service.js', array('jquery'), '1.0', true);
    }
    
}
