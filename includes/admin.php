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

        load_plugin_textdomain( XSLT_PLUGIN_NAME, false, XSLT_PLUGIN_DIR.'/languages' );

        add_action( 'admin_menu', array('XSLT_Processor_Admin', 'register_pages') );
        add_action( 'admin_init', array('XSLT_Processor_Admin', 'register_settings') );
        add_action( 'admin_enqueue_scripts', array('XSLT_Processor_Admin', 'register_styles') );

        $filter_name = 'plugin_action_links_' . XSLT_PLUGIN_NAME."/".XSLT_PLUGIN_NAME.".php";
        add_filter( $filter_name, array('XSLT_Processor_Admin', 'render_action_links') );

        add_filter( 'upload_mimes', array('XSLT_Processor_Admin', 'xslt_mime_types') );
        // risky? fixes "Sorry, you are not allowed to upload this file type." error
        if (!defined('ALLOW_UNFILTERED_UPLOADS'))
            { define('ALLOW_UNFILTERED_UPLOADS', true); }
    }


    /**
     * return validated options array
     *  sc_xsl_transform    boolean
     *  sc_xml_select       boolean
     *  sc_csv_select       boolean
     *  search_path         string
     *  cache_default       integer, minutes
     */
    public static function validate_options( $input )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('input'),true), E_USER_NOTICE); }

        $options = array(
            'post_type_xsl'    => !empty( $input['post_type_xsl'] )    ? 1 : 0,
            'post_type_xml'    => !empty( $input['post_type_xml'] )    ? 1 : 0,
            'sc_xsl_transform' => !empty( $input['sc_xsl_transform'] ) ? 1 : 0,
            'sc_xml_select'    => !empty( $input['sc_xml_select'] )    ? 1 : 0,
            'sc_csv_select'    => !empty( $input['sc_csv_select'] )    ? 1 : 0,
            'cache_default'    => XSLT_CACHE_DEFAULT,
            'search_path'      => "",
            );

        if (!defined( 'LIBXSLT_VERSION' ))
        {
            $options['post_type_xsl']    = 0;
            $options['post_type_xml']    = 0;
            $options['sc_xsl_transform'] = 0;
            $options['sc_xml_select']    = 0;
            $options['sc_csv_select']    = 0;
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
            'post_type_xsl'     => __( 'Activate Content Type <strong>XSL Stylesheet</strong>', XSLT_TEXT ),
            'post_type_xml'     => __( 'Activate Content Type <strong>XML Document</strong>', XSLT_TEXT ),
            'sc_xsl_transform'  => __( 'Activate Shortcode <strong>[xsl_transform/]</strong>', XSLT_TEXT ),
            'sc_xml_select'     => __( 'Activate Shortcode <strong>[xml_select/]</strong>', XSLT_TEXT ),
            'sc_csv_select'     => __( 'Activate Shortcode <strong>[csv_select/]</strong>', XSLT_TEXT ),
            'cache_default'     => __( 'Cache Lifetime', XSLT_TEXT ),
            'search_path'       => __( 'Local File Search Paths', XSLT_TEXT ),
            );

        $msg = "";
        foreach( $diffs as $key => $val )
        {
            $label = $labels[$key] ?? $key;
            $pre   = $before[$key] ?? "unset";
            $post  = $after[$key]  ?? "unset";
            if (in_array($key,array('post_type_xsl','post_type_xml','sc_xsl_transform','sc_xml_select','sc_csv_select')))
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
            $msg .= "$label : $pre => $post";
        }
        if ($msg) {
            $msg = "<strong>XSLT Processor Settings updated:</strong><br/>".$msg;
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
        echo '<h1>'.__( 'XSLT Processor Settings', XSLT_TEXT ).'</h1>';
        echo '<form action="options.php" method="post">';

        echo '<hr size="1" />';
        settings_fields( 'xslt_processor_settings' );       // option_group
        do_settings_sections( 'xslt_processor_settings' );  // page_name
        echo '<hr size="1" />';
        submit_button();

        echo '</form>';
        echo '</div>';

//echo print_r(get_option( XSLT_OPTS, array() ), true);
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
            '', //__( 'Main', XSLT_TEXT )
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
            __( 'Activate Content Types', XSLT_TEXT ),
            array('XSLT_Processor_Admin','render_setting_post_types'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_post_type_xsl',
            ));

        // add field 'sc_xsl_transform'
        add_settings_field(
            'xslt_processor_sc_xsl_transform',
            __( 'Activate Shortcodes', XSLT_TEXT ),
            array('XSLT_Processor_Admin','render_setting_sc_xsl_transform'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_sc_xsl_transform', // wrap title in label
                //'class'  => 'classname',                        // add to tr
            ));

        // add field 'sc_xml_select'
        add_settings_field(
            'xslt_processor_sc_xml_select',
           '',
            array('XSLT_Processor_Admin','render_setting_sc_xml_select'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                 //'label_for' => 'xslt_processor_sc_xml_select',
            ));

        // add field 'sc_csv_select'
        add_settings_field(
            'xslt_processor_sc_csv_select',
           '',
            array('XSLT_Processor_Admin','render_setting_sc_csv_select'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                 //'label_for' => 'xslt_processor_sc_csv_select',
            ));

        // add field 'cache_default'
        add_settings_field(
            'xslt_processor_cache_default',
            __( 'Cache Lifetime', XSLT_TEXT ),
            array('XSLT_Processor_Admin','render_setting_cache_default'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_cache_default',
            ));

        // add field 'search_path'
        add_settings_field(
            'xslt_processor_search_path',
            __( 'Local File Search Paths', XSLT_TEXT ),
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
        if (!defined( 'LIBXSLT_VERSION' )) {
            _e( 'PHP\'s <a href="https://www.php.net/manual/en/book.xsl.php" target="_blank">XSL extension</a> is NOT available', XSLT_TEXT );
            return;
        }
        _e( 'PHP\'s <a href="https://www.php.net/manual/en/book.xsl.php" target="_blank">XSL extension</a> is available', XSLT_TEXT );
        echo '&nbsp;:&nbsp;';
        echo 'XSLT v'.LIBXSLT_DOTTED_VERSION;
        echo ', EXSLT v'.LIBEXSLT_DOTTED_VERSION;
        echo ', LIBXML v'.LIBXML_DOTTED_VERSION;

        if (WP_DEBUG) {
            echo '<br/>';
            if (!extension_loaded('tidy')) {
                _e( 'PHP\'s <a href="https://www.php.net/manual/en/book.tidy.php" target="_blank">Tidy extension</a> is NOT available', XSLT_TEXT );
                return;
            }
            _e( 'PHP\'s <a href="https://www.php.net/manual/en/book.tidy.php" target="_blank">Tidy extension</a> is available', XSLT_TEXT );
            echo '&nbsp;:&nbsp;';
            echo 'Release '.tidy_get_release();
        }
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
            . ' id="xslt_processor_post_type_xsl" name="'.XSLT_OPTS.'[post_type_xsl]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<strong>'.__( 'XSL Stylesheets', XSLT_TEXT ).'</strong>';
        _e( ' - Save and manage XSL stylesheets in Wordpress Admin', XSLT_TEXT );
        echo '</p>';


        $value = !empty($options['post_type_xml']);
        echo '<p>';
        echo '<input type="checkbox"'
            . ' id="xslt_processor_post_type_xml" name="'.XSLT_OPTS.'[post_type_xml]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<strong>'.__( 'XML Documents', XSLT_TEXT ).'</strong>';
        _e( ' - Save and manage XML data in Wordpress Admin', XSLT_TEXT );
        echo '</p>';
    }

    /**
     * render settings field: sc_xsl_transform
     */
    public static function render_setting_sc_xsl_transform()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_xsl_transform']);
        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_xsl_transform" name="'.XSLT_OPTS.'[sc_xsl_transform]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<strong>[xsl_transform]</strong>';
        _e( ' - Process XML data using an XSL stylesheet', XSLT_TEXT );
        echo '<ul>';
        //echo '<li>'.__( 'Usage', XSLT_TEXT ).':</li>';
        echo '<li><code><strong>[xsl_transform xsl="</strong>{file|url|id|slug}<strong>" xml="</strong>{file|url|id|slug}<strong>" /]</strong></code></li>';
        echo '<li><code><strong>[xsl_transform xsl="</strong>{file|url|id|slug}<strong>"]</strong>'
            . '[xml_select/]'
            . '<strong>[/xsl_transform]</strong></code></li>';
        echo '<li><code><strong>[xsl_transform xsl="</strong>{file|url|id|slug}<strong>"]</strong>'
            . '[csv_select/]'
            . '<strong>[/xsl_transform]</strong></code></li>';
        echo '<li><a href="'.XSLT_PLUGIN_URI.'xslt-processor/shortcodes/xsl-transform/" target="_blank">'.__('View all options', XSLT_TEXT).'</a> <span class="dashicons dashicons-external"></li>';
        echo '</ul>';
    }

    /**
     * render settings field: sc_xml_select
     */
    public static function render_setting_sc_xml_select()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_xml_select']);
        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_xml_select" name="'.XSLT_OPTS.'[sc_xml_select]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<strong>[xml_select]</strong>';
        _e( ' - Filter XML data using an XPath select statement', XSLT_TEXT );
        echo '<ul>';
        //echo '<li>'.__( 'Usage', XSLT_TEXT ).':</li>';
        echo '<li><code><strong>[xml_select xml="</strong>{file|url|id|slug}<strong>" select="</strong>//nodename<strong>" /]</strong></code></li>';
        echo '<li><code><strong>[xml_select xml="</strong>{file|url|id|slug}<strong>"]</strong>//nodename[@id="1234"]<strong>[/xml_select]</strong></code></li>';
        echo '<li><code><strong>[xml_select xmlns="</strong>{ns1}+<strong>" ns1="</strong>{namespace-uri-1}<strong>" select="</strong>//ns1:nodename<strong>" /]</strong></code></li>';
        echo '<li><a href="'.XSLT_PLUGIN_URI.'xslt-processor/shortcodes/xml-select/" target="_blank">'.__('View all options', XSLT_TEXT).'</a> <span class="dashicons dashicons-external"></li>';
        echo '</ul>';
    }

    /**
     * render settings field: sc_csv_select
     */
    public static function render_setting_sc_csv_select()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_csv_select']);
        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_csv_select" name="'.XSLT_OPTS.'[sc_csv_select]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<strong>[csv_select]</strong>';
        _e( ' - Convert CSV data to XML', XSLT_TEXT );
        echo '<ul>';
        //echo '<li>'.__( 'Usage', XSLT_TEXT ).':</li>';
       echo '<li><code><strong>[csv_select csv="</strong>{file|url}<strong>" key_row="</strong>{num}<strong>" /]</strong></code></li>';
//        echo '<li><code><strong>[csv_select separator="</strong>,<strong>" enclosure="</strong>\\&quot;<strong>" escape="</strong>\\\\<strong>" /]</strong></code></li>';
        echo '<li><code><strong>[csv_select col="</strong>{num|letter|label}+<strong>" /]</strong></code></li>';
        echo '<li><code><strong>[csv_select key_col="</strong>{num|letter|label}<strong>" key="</strong>{val}+<strong>" /]</strong></code></li>';
        echo '<li><code><strong>[csv_select row="</strong>{num}+<strong>" /]</strong></code></li>';
        echo '<li><a href="'.XSLT_PLUGIN_URI.'xslt-processor/shortcodes/csv-select/" target="_blank">'.__('View all options', XSLT_TEXT).'</a> <span class="dashicons dashicons-external"></li>';
        echo '</ul>';
    }

    /**
     * render settings field: cache_default
     */
    public static function render_setting_cache_default()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = $options['cache_default'] ?? XSLT_CACHE_DEFAULT;

        echo '<input type="text" size="6"'
            . ' id="xslt_processor_cache_default" name="'.XSLT_OPTS.'[cache_default]"'
            . ' value="'.$value.'"'
            . ' />';
        _e( ' Minutes', XSLT_TEXT );
        echo '<ul>';
        echo '<li>'.__( '- Remote files are cached locally when <code><strong>xsl="</strong>{url}<strong>"</strong></code>, <code><strong>xml="</strong>{url}<strong>"</strong></code>, or <code><strong>csv="</strong>{url}<strong>"</strong></code>.', XSLT_TEXT ).'</li>';
        echo '<li>'.__( '- Use <code><strong>cache="</strong>{minutes}<strong>"</strong></code> to override.', XSLT_TEXT ).'</li>';
        echo '</ul>';
    }

    /**
     * render settings field: search_path
     */
    public static function render_setting_search_path()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = $options['search_path'] ?? '';

        echo '<textarea rows="4" cols="80"'
            . ' id="xslt_processor_search_path" name="'.XSLT_OPTS.'[search_path]"'
            . '>' . esc_textarea($value)
            . '</textarea>';

        echo '<p>';
        _e( 'Specify local directories containing XSL stylesheets and XML data files.', XSLT_TEXT );
        echo '<br/>';
        printf(
            __( 'The default path <code>%s</code> will be searched last.', XSLT_TEXT ),
            XSLT_PLUGIN_DIR.'xsl'
            );
        echo '</p>';
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
                . __( 'Settings', XSLT_TEXT )
                . '</a>'
            );
        return array_merge($links,$actions);
    }

    /**
     * add xml/xsl to allowed file upload types
     */
    public static function xslt_mime_types( $mimes )
    {
        $mimes['xml']  = 'text/xml';
        $mimes['xsl']  = 'application/xslt+xml';
        $mimes['xslt'] = 'application/xslt+xml';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('mimes'),true), E_USER_NOTICE); }
        return $mimes;
    }

}  // end XSLT_Processor_Admin

