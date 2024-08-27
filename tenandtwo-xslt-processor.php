<?php
/**
 * XSLT Processor
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Ten&Two XSLT Processor
 * Plugin URI:        https://wordpress.org/plugins/tenandtwo-xslt-processor/
 * Description:       Transform and display XML from local and remote sources using PHP's XSL extension.
 * Version:           1.0.7
 * Requires PHP:      7.4
 * Requires at least: 5.2
 * Author:            Ten & Two Systems
 * Author URI:        https://plugins.tenandtwo.com/
 * Text Domain:       tenandtwo-xslt-processor
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
defined( 'ABSPATH' ) or die( 'Not for browsing' );

define( 'XSLT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'XSLT_PLUGIN_NAME', basename(XSLT_PLUGIN_DIR) );
define( 'XSLT_PLUGIN_VERSION', '0.9.8' );
define( 'XSLT_PLUGIN_DOCS', 'https://plugins.tenandtwo.com/' );

define( 'XSLT_OPTS', 'xslt_processor_options' );  // option name
define( 'XSLT_CACHE_DEFAULT', '60' );     // minutes



/**
 * XSLT_Processor_Plugin
 * All class methods static and hooked.
 */
class XSLT_Processor_Plugin
{
    private static $initiated = false;

    /**
     * init
     */
    public static function init() {
        if (self::$initiated) { return; }

        // register pages, setting, options
        if (is_admin() && current_user_can('manage_options'))
        {
            require_once(XSLT_PLUGIN_DIR.'includes/admin.php');
            XSLT_Processor_Admin::init();

            require_once(XSLT_PLUGIN_DIR.'includes/notice.php');
            XSLT_Processor_Notice::init();
        }
        require_once(XSLT_PLUGIN_DIR.'includes/util.php');

        // return if libxslt missing
        if (!defined( 'LIBXSLT_VERSION' )) {
            trigger_error("LIBXSLT_VERSION NOT DEFINED", E_USER_NOTICE);
            return;
        }

        require_once(XSLT_PLUGIN_DIR.'includes/callback.php');
        require_once(XSLT_PLUGIN_DIR.'includes/csv.php');
        require_once(XSLT_PLUGIN_DIR.'includes/wp.php');
        require_once(XSLT_PLUGIN_DIR.'includes/xml.php');
        require_once(XSLT_PLUGIN_DIR.'includes/xsl.php');
        require_once(XSLT_PLUGIN_DIR.'tenandtwo-xslt-functions.php');

        $options = get_option( XSLT_OPTS, array() );

        // register shortcodes
        if (!empty($options['sc_transform_xml']) || !empty($options['sc_select_xml']) || !empty($options['sc_select_csv'])) {
            require_once(XSLT_PLUGIN_DIR.'includes/shortcode.php');
            XSLT_Processor_Shortcode::init();
        }

        // register post types
        if (!empty($options['post_type_xml']) || !empty($options['post_type_xsl'])) {
            require_once(XSLT_PLUGIN_DIR.'includes/post_type.php');
            XSLT_Processor_Post_Type::init();
        }

        // register wp-cli commands
        require_once(XSLT_PLUGIN_DIR.'includes/cli.php');
        XSLT_Processor_CLI::init();

        // register blocks ???

        self::$initiated = true;
    }

    /**
     * activate
     */
    public static function plugin_activation()
    {
        add_option( XSLT_OPTS, array() );
        flush_rewrite_rules();
    }

    /**
     * deactivate
     */
    public static function plugin_deactivation()
    {
        flush_rewrite_rules();
    }

    /**
     * uninstall
     */
    public static function plugin_uninstall()
    {
        delete_option( XSLT_OPTS );
    }


}  // end XSLT_Processor_Plugin

/**
 * MAIN
 */
register_activation_hook(   __FILE__, array('XSLT_Processor_Plugin', 'plugin_activation') );
register_deactivation_hook( __FILE__, array('XSLT_Processor_Plugin', 'plugin_deactivation') );
register_uninstall_hook(    __FILE__, array('XSLT_Processor_Plugin', 'plugin_uninstall') );

add_action( 'init', array('XSLT_Processor_Plugin', 'init') );
