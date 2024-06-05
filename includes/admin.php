<?php
/**
 * Admin
 *
 * usage:
 * require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
 * XSLT_Processor_Admin::init();
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */
defined( 'ABSPATH' ) or die( 'Not for browsing' );

/**
 * XSLT_Processor_Admin
 * All class methods static and hooked.
 */
class XSLT_Processor_Admin
{

    /**
     * init
     */
    public static function init()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }

        load_plugin_textdomain( 'tenandtwo-xslt-processor', false, XSLT_PLUGIN_DIR.'/languages' );

        add_action( 'admin_menu', array('XSLT_Processor_Admin', 'register_pages') );
        add_action( 'admin_init', array('XSLT_Processor_Admin', 'register_settings') );
        add_action( 'admin_enqueue_scripts', array('XSLT_Processor_Admin', 'register_styles') );

        $filter_name = 'plugin_action_links_' . XSLT_PLUGIN_NAME."/".XSLT_PLUGIN_NAME.".php";
        add_filter( $filter_name, array('XSLT_Processor_Admin', 'render_action_links') );

        add_filter( 'upload_mimes', array('XSLT_Processor_Admin', 'xslt_mime_types') );
    }


    /**
     * return validated options array
     *  sc_transform_xml    boolean
     *  sc_select_xml       boolean
     *  sc_select_csv       boolean
     *  search_path         string
     *  cache_default       integer, minutes
     */
    public static function validate_options( $input )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('input'),true), E_USER_NOTICE); }

        $options = array(
            'post_type_xsl'     => !empty( $input['post_type_xsl'] )    ? 1 : 0,
            'post_type_xml'     => !empty( $input['post_type_xml'] )    ? 1 : 0,
            'sc_transform_xml'  => !empty( $input['sc_transform_xml'] ) ? 1 : 0,
            'sc_select_xml'     => !empty( $input['sc_select_xml'] )    ? 1 : 0,
            'sc_select_csv'     => !empty( $input['sc_select_csv'] )    ? 1 : 0,
            'cache_default'     => XSLT_CACHE_DEFAULT,
            'search_path'       => "",
            );

        if (!defined( 'LIBXSLT_VERSION' ))
        {
            $options['post_type_xsl']    = 0;
            $options['post_type_xml']    = 0;
            $options['sc_transform_xml'] = 0;
            $options['sc_select_xml']    = 0;
            $options['sc_select_csv']    = 0;
        }

        if (isset($input['cache_default']) && 0 <= intval($input['cache_default']))
            { $options['cache_default'] = intval($input['cache_default']); }

        if (!empty($input['search_path']))
            { $options['search_path'] = join("\n", XSLT_Processor_Util::getRealPaths( $input['search_path'] )); }

        // admin_notice for changes
        self::options_update_notice( $options );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('options'),true), E_USER_NOTICE); }
        return $options;
    }

    /**
     * create admin_notice for changes
     *  after    array
     */
    public static function options_update_notice( $after )
    {
        $before = get_option( XSLT_OPTS, array() );
        $diffs = array_diff_assoc($after,$before);
        if (empty($diffs)) { return; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('before','after','diffs'),true), E_USER_NOTICE); }

        $labels = array(
            'post_type_xsl'     => esc_html__( 'Activate Content Type', 'tenandtwo-xslt-processor' ).'<strong>'.esc_html__( 'XSL Stylesheet', 'tenandtwo-xslt-processor' ).'</strong>',
            'post_type_xml'     => esc_html__( 'Activate Content Type', 'tenandtwo-xslt-processor' ).'<strong>'.esc_html__( 'XML Document', 'tenandtwo-xslt-processor' ).'</strong>',
            'sc_transform_xml'  => esc_html__( 'Activate Shortcode', 'tenandtwo-xslt-processor' ).' <strong>[xslt_transform_xml/]</strong>',
            'sc_select_xml'     => esc_html__( 'Activate Shortcode', 'tenandtwo-xslt-processor' ).' <strong>[xslt_select_xml/]</strong>',
            'sc_select_csv'     => esc_html__( 'Activate Shortcode', 'tenandtwo-xslt-processor' ).' <strong>[xslt_select_csv/]</strong>',
            'cache_default'     => esc_html__( 'Cache Lifetime', 'tenandtwo-xslt-processor' ),
            'search_path'       => esc_html__( 'Local File Search Paths', 'tenandtwo-xslt-processor' ),
            );

        $msg = "";
        foreach( $diffs as $key => $val )
        {
            $label = $labels[$key] ?? $key;
            $pre   = $before[$key] ?? "unset";
            $post  = $after[$key]  ?? "unset";
            if (in_array($key,array('post_type_xsl','post_type_xml','sc_transform_xml','sc_select_xml','sc_select_csv')))
            {
                $pre  = ($pre == 1)  ? 'TRUE' : 'FALSE';
                $post = ($post == 1) ? 'TRUE' : 'FALSE';
            }
            if ($key == 'search_path')
            {
                $pre  = "'".str_replace("\n"," ",$pre)."'";
                $post = "'".str_replace("\n"," ",$post)."'";
            }
            if ($msg) { $msg .= "<br/>"; }
            $msg .= $label . esc_html(" : $pre => $post");
        }
        if ($msg) {
            $msg = '<strong>'.esc_html__( 'XSLT Processor Settings updated', 'tenandtwo-xslt-processor' ).':</strong><br/>'.$msg;
            XSLT_Processor_Notice::success( $msg );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('diffs','msg'),true), E_USER_NOTICE); }
        }

    }


    /**
     * register_pages
     * @uses add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', int $position = null )
     */
    public static function register_pages()
    {
        add_options_page(
            'settings.php',
            'XSLT Processor',
            'manage_options',                 // capability
            'xslt_processor_settings',        // page_name
            array('XSLT_Processor_Admin','render_page_settings'),
            //1,                              // menu position
            );
    }

    /**
     * render_page_settings
     * @uses settings_fields( string $option_group )
     * @uses do_settings_sections( string $page )
     */
    public static function render_page_settings()
    {
        echo '<div class="wrap">';
        echo '<h1>'.esc_html(_x( 'XSLT Processor Settings', 'settings page title', 'tenandtwo-xslt-processor' )).'</h1>';
        echo '<form action="options.php" method="post">';

        echo '<hr size="1" />';
        settings_fields( 'xslt_processor_settings' );       // option_group
        do_settings_sections( 'xslt_processor_settings' );  // page_name
        echo '<hr size="1" />';
        submit_button();

        echo '</form>';
        echo '</div>';

//if (WP_DEBUG) { print_r( get_option( XSLT_OPTS, array() )); }
    }


    /**
     * register_settings
     * @uses register_setting( string $option_group, string $option_name, array $args = array() )
     * @uses add_settings_section( string $id, string $title, callable $callback, string $page, array $args = array() )
     * @uses add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
     */
    public static function register_settings()
    {
        // register setting group and options array
        register_setting(
            'xslt_processor_settings',
            XSLT_OPTS,
            array(
                'type' => 'array',
                'sanitize_callback' => array('XSLT_Processor_Admin','validate_options'),
                'default' => array(),
            ));

        // add section 'main' to settings page
        add_settings_section(
            'xslt_processor_settings_main',
            '', //esc_html(_x( 'Main', 'section title', 'tenandtwo-xslt-processor' ))
            array('XSLT_Processor_Admin','render_section_main'),
            'xslt_processor_settings',
            array(
//                 'before_section' => '<p>before</p>',
//                 'after_section'  => '<p>after</p>',
//                 'section_class'  => 'xslt_processor_settings',
            ));

        // add field 'post_type_xsl'
        // add field 'post_type_xml'
        add_settings_field(
            'xslt_processor_post_type_xsl',
            esc_html(_x( 'Activate Content Types', 'field_label', 'tenandtwo-xslt-processor' )),
            array('XSLT_Processor_Admin','render_setting_post_types'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_post_type_xsl',
            ));

        // add field 'sc_transform_xml'
        add_settings_field(
            'xslt_processor_sc_transform_xml',
            esc_html(_x( 'Activate Shortcodes', 'field_label', 'tenandtwo-xslt-processor' )),
            array('XSLT_Processor_Admin','render_setting_sc_transform_xml'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_sc_transform_xml', // wrap title in label
                //'class'  => 'classname',                        // add to tr
            ));

        // add field 'sc_select_xml'
        add_settings_field(
            'xslt_processor_sc_select_xml',
           '',
            array('XSLT_Processor_Admin','render_setting_sc_select_xml'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                 //'label_for' => 'xslt_processor_sc_select_xml',
            ));

        // add field 'sc_select_csv'
        add_settings_field(
            'xslt_processor_sc_select_csv',
           '',
            array('XSLT_Processor_Admin','render_setting_sc_select_csv'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                 //'label_for' => 'xslt_processor_sc_select_csv',
            ));

        // add field 'cache_default'
        add_settings_field(
            'xslt_processor_cache_default',
            esc_html(_x( 'Cache Lifetime', 'field_label', 'tenandtwo-xslt-processor' )),
            array('XSLT_Processor_Admin','render_setting_cache_default'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_cache_default',
            ));

        // add field 'search_path'
        add_settings_field(
            'xslt_processor_search_path',
            esc_html(_x( 'Local File Search Paths', 'field_label', 'tenandtwo-xslt-processor' )),
            array('XSLT_Processor_Admin','render_setting_search_path'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_search_path',
            ));

    }

    /**
     * render section header
     */
    public static function render_section_main()
    {
        if (!defined( 'LIBXML_VERSION' )) {
            echo 'PHP\'s <a href="https://www.php.net/manual/en/book.libxml.php" target="_blank">'.esc_html__( 'LIBXML extension', 'tenandtwo-xslt-processor' ).'</a> ';
            esc_html_e( 'is NOT available', 'tenandtwo-xslt-processor' );
            return;
        }

        echo 'PHP\'s <a href="https://www.php.net/manual/en/book.xsl.php" target="_blank">'.esc_html__( 'XSL extension', 'tenandtwo-xslt-processor' ).'</a> ';
        if (!defined( 'LIBXSLT_VERSION' )) {
            esc_html_e( 'is NOT available', 'tenandtwo-xslt-processor' );
            return;
        }
        esc_html_e( 'is available', 'tenandtwo-xslt-processor' );
        echo '&nbsp;:&nbsp;';
        echo esc_html(
            'XSLT v'.LIBXSLT_DOTTED_VERSION
            .', EXSLT v'.LIBEXSLT_DOTTED_VERSION
            .', LIBXML v'.LIBXML_DOTTED_VERSION
            );

        echo '<br/>';
        echo 'PHP\'s <a href="https://www.php.net/manual/en/book.tidy.php" target="_blank">'.esc_html__( 'Tidy extension', 'tenandtwo-xslt-processor' ).'</a> ';
        if (!extension_loaded('tidy')) {
            esc_html_e( 'is NOT available', 'tenandtwo-xslt-processor' );
            return;
        }
        esc_html_e( 'is available', 'tenandtwo-xslt-processor' );
        echo '&nbsp;:&nbsp;';
        echo esc_html('Release '.tidy_get_release());
    }

    /**
     * render settings field: post_type_xml
     * render settings field: post_type_xsl
     */
    public static function render_setting_post_types()
    {
        $options = get_option( XSLT_OPTS, array() );

        $value = !empty($options['post_type_xsl']);
        echo '<p>';
        echo '<input type="checkbox"'
            . ' id="xslt_processor_post_type_xsl" name="'.esc_attr(XSLT_OPTS.'[post_type_xsl]').'"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        $html = '<strong>'.esc_html__( 'XSL Stylesheets', 'tenandtwo-xslt-processor' ).'</strong>';
        $html .= __( ' - Save and manage XSL stylesheets in Wordpress Admin', 'tenandtwo-xslt-processor' );
        echo wp_kses($html, 'post');
        echo '</p>';


        $value = !empty($options['post_type_xml']);
        echo '<p>';
        echo '<input type="checkbox"'
            . ' id="xslt_processor_post_type_xml" name="'.esc_attr(XSLT_OPTS.'[post_type_xml]').'"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        $html = '<strong>'.esc_html__( 'XML Documents', 'tenandtwo-xslt-processor' ).'</strong>';
        $html .= __( ' - Save and manage XML data in Wordpress Admin', 'tenandtwo-xslt-processor' );
        echo wp_kses($html, 'post');
        echo '</p>';
    }

    /**
     * render settings field: sc_transform_xml
     */
    public static function render_setting_sc_transform_xml()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_transform_xml']);

        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_transform_xml" name="'.esc_attr(XSLT_OPTS.'[sc_transform_xml]').'"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        $html = '<strong>[xslt_transform_xml]</strong>';
        $html .= __( ' - Process XML data using an XSL stylesheet', 'tenandtwo-xslt-processor' );
        $html .= '<ul>';
        //$html .= '<li>'.esc_html__( 'Usage', 'tenandtwo-xslt-processor' ).':</li>';
        $html .= '<li><code><strong>[xslt_transform_xml xsl="</strong>{file|url|id|slug}<strong>" xml="</strong>{file|url|id|slug}<strong>" /]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_transform_xml xsl="</strong>{file|url|id|slug}<strong>"]</strong>'
            . '[xslt_select_xml/]'
            . '<strong>[/xslt_transform_xml]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_transform_xml xsl="</strong>{file|url|id|slug}<strong>"]</strong>'
            . '[xslt_select_csv/]'
            . '<strong>[/xslt_transform_xml]</strong></code></li>';
        $html .= '<li><a href="'.XSLT_PLUGIN_DOCS.'xslt-processor/shortcodes/xslt-transform-xml/" target="_blank">'.esc_html__( 'View all options', 'tenandtwo-xslt-processor' ).'</a> <span class="dashicons dashicons-external"></span></li>';
        $html .= '</ul>';
        echo wp_kses($html, 'post');
    }

    /**
     * render settings field: sc_select_xml
     */
    public static function render_setting_sc_select_xml()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_select_xml']);

        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_select_xml" name="'.esc_attr(XSLT_OPTS.'[sc_select_xml]').'"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        $html = '<strong>[xslt_select_xml]</strong>';
        $html .= __( ' - Filter XML data using an XPath select statement', 'tenandtwo-xslt-processor' );
        $html .= '<ul>';
        //$html .= '<li>'.esc_html__( 'Usage', 'tenandtwo-xslt-processor' ).':</li>';
        $html .= '<li><code><strong>[xslt_select_xml xml="</strong>{file|url|id|slug}<strong>" select="</strong>//nodename<strong>" /]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_select_xml xml="</strong>{file|url|id|slug}<strong>"]</strong>//nodename[@id="1234"]<strong>[/xslt_select_xml]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_select_xml xmlns="</strong>{ns1}+<strong>" ns1="</strong>{namespace-uri-1}<strong>" select="</strong>//ns1:nodename<strong>" /]</strong></code></li>';
        $html .= '<li><a href="'.XSLT_PLUGIN_DOCS.'xslt-processor/shortcodes/xslt-select-xml/" target="_blank">'.esc_html__( 'View all options', 'tenandtwo-xslt-processor' ).'</a> <span class="dashicons dashicons-external"></span></li>';
        $html .= '</ul>';
        echo wp_kses($html, 'post');
    }

    /**
     * render settings field: sc_select_csv
     */
    public static function render_setting_sc_select_csv()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_select_csv']);

        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_select_csv" name="'.esc_attr(XSLT_OPTS.'[sc_select_csv]').'"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        $html = '<strong>[xslt_select_csv]</strong>';
        $html .= __( ' - Convert CSV data to XML', 'tenandtwo-xslt-processor' );
        $html .= '<ul>';
        //$html .= '<li>'.esc_html__( 'Usage', 'tenandtwo-xslt-processor' ).':</li>';
        $html .= '<li><code><strong>[xslt_select_csv csv="</strong>{file|url}<strong>" key_row="</strong>{num}<strong>" /]</strong></code></li>';
        //$html .= '<li><code><strong>[xslt_select_csv separator="</strong>,<strong>" enclosure="</strong>\\&quot;<strong>" escape="</strong>\\\\<strong>" /]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_select_csv col="</strong>{num|letter|label}+<strong>" /]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_select_csv key_col="</strong>{num|letter|label}<strong>" key="</strong>{val}+<strong>" /]</strong></code></li>';
        $html .= '<li><code><strong>[xslt_select_csv row="</strong>{num}+<strong>" /]</strong></code></li>';
        $html .= '<li><a href="'.XSLT_PLUGIN_DOCS.'xslt-processor/shortcodes/xslt-select-csv/" target="_blank">'.esc_html__( 'View all options', 'tenandtwo-xslt-processor' ).'</a> <span class="dashicons dashicons-external"></span></li>';
        $html .= '</ul>';
        echo wp_kses($html, 'post');
    }

    /**
     * render settings field: cache_default
     */
    public static function render_setting_cache_default()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = $options['cache_default'] ?? XSLT_CACHE_DEFAULT;

        echo '<input type="text" size="6"'
            . ' id="xslt_processor_cache_default" name="'.esc_attr(XSLT_OPTS.'[cache_default]').'"'
            . ' value="'.esc_attr($value).'"'
            . ' />';

        $html = ' '.esc_html__( 'Minutes', 'tenandtwo-xslt-processor' );
        $html .= '<ul>';
        $html .= '<li>'.__( '- Remote files are cached locally when', 'tenandtwo-xslt-processor' )
            . ' <code><strong>xsl="</strong>{url}<strong>"</strong></code>, <code><strong>xml="</strong>{url}<strong>"</strong></code>, or <code><strong>csv="</strong>{url}<strong>"</strong></code></li>';
        $html .= '<li>'.__( '- Use', 'tenandtwo-xslt-processor' )
            . ' <code><strong>cache="</strong>{minutes}<strong>"</strong></code> '
            . __( 'to override', 'tenandtwo-xslt-processor' ).'</li>';
        $html .= '</ul>';
        echo wp_kses($html, 'post');
    }

    /**
     * render settings field: search_path
     */
    public static function render_setting_search_path()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = $options['search_path'] ?? '';

        echo '<textarea rows="4" cols="80"'
            . ' id="xslt_processor_search_path" name="'.esc_attr(XSLT_OPTS.'[search_path]').'"'
            . '>' . esc_textarea($value)
            . '</textarea>';

        $html = '<p>';
        $html .= __( '- Specify local directories containing XSL stylesheets and XML data files', 'tenandtwo-xslt-processor' );
        $html .= '<br/>';
        $html .= __( '- The default path', 'tenandtwo-xslt-processor' )
            . ' <code>'.XSLT_PLUGIN_DIR.'xsl</code> '
            . __( 'will be searched last', 'tenandtwo-xslt-processor' );
        $html .= '</p>';
        echo wp_kses($html, 'post');
    }


    /**
     * add css for admin
     */
    public static function register_styles()
    {
        wp_enqueue_style( 'xslt-admin', plugin_dir_url(__FILE__) . 'css/xslt-admin.css' );
    }

    /**
     * render_action_links below plugin name
     */
    public static function render_action_links( $actions )
    {
        $links = array(
            '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=xslt_processor_settings') ) .'">'
                . esc_html(_x( 'Settings', 'plugins action link', 'tenandtwo-xslt-processor' ))
                . '</a>'
            );
        return array_merge( $links, $actions );
    }

    /**
     * add xml/xsl to allowed file upload types
     */
    public static function xslt_mime_types( $mimes )
    {
        $mimes['xml']      = 'text/xml';
        $mimes['xsl|xslt'] = 'application/xslt+xml';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('mimes'),true), E_USER_NOTICE); }
        return $mimes;
    }

}  // end XSLT_Processor_Admin
