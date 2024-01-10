<?php
/**
 * Admin Notices
 *
 * Show dismissible admin notices
 * levels: error, warning, success, info
 *
 * NOTE : Messages only appear in Admin, NOT including gutenberg editor.
 *
 * usage:
 * require_once plugin_dir_path( __FILE__ ) . 'includes/notice.php';
 * XSLT_Processor_Notice::error('an error');
 * XSLT_Processor_Notice::warning('a warning');
 * XSLT_Processor_Notice::success('a success');
 * XSLT_Processor_Notice::info('some info');
 *
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );


/**
 * XSLT_Processor_Notice
 * All class methods static, most hooked.
 */
class XSLT_Processor_Notice
{

    /**
     *
     */
    public static function init()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }

        add_action( 'admin_notices', array('XSLT_Processor_Notice','display_notices') );

    }

    /**
     * add 'error', 'warning', 'success', 'info'
     */
    public static function error( $message = 'ERROR' )
        { self::add_notice( 'error', $message ); }

    public static function warning( $message = 'WARNING' )
        { self::add_notice( 'warning', $message ); }

    public static function success( $message = 'SUCCESS' )
        { self::add_notice( 'success', $message ); }

    public static function info( $message = 'INFO' )
        { self::add_notice( 'info', $message ); }


    /**
     * add notice
     */
    private static function add_notice( $level = 'error', $message = 'No Message' )
    {
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('level','message'),true), E_USER_NOTICE); }

        // get transient
        $cache_key = self::cache_key();
        $notices = get_transient( $cache_key );
        if (empty($notices)) { $notices = array(); }

        // append notice
        $LEVELS = array('error', 'warning', 'success', 'info');
        $notices[] = array(
            'level'   => (in_array($level,$LEVELS)) ? $level : 'error',
            'message' => $message,
            );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r($notices,true), E_USER_NOTICE); }

        // create/update transient
        $cache_minutes = 1;
        set_transient( $cache_key, $notices, 60 * $cache_minutes );  //
    }

    /**
     * display admin notices
     */
    public static function display_notices()
    {
        // get transient
        $cache_key = self::cache_key();
        $notices = get_transient( $cache_key );
        if (empty($notices)) { return; }
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r($notices,true), E_USER_NOTICE); }

        foreach( $notices as $notice ) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_html( strtolower( $notice['level'] )),
                wp_kses( $notice['message'], 'post' )
                );
        }
        // remove transient
        delete_transient( $cache_key );
    }

    /**
     * create cache key for current user/ip
     */
    private static function cache_key()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }

        $cache_params = array(
            'method' => 'md5',
            'data' => (is_user_logged_in()) ? get_current_user_id() : GetIP()
            );
        $cache_key = 'xslt-notice-' . XSLT_Processor_Util::getHash( $cache_params );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('cache_params','cache_key'),true), E_USER_NOTICE); }
        return $cache_key;
    }

}  // end XSLT_Processor_Notice
