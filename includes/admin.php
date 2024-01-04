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

        $filter_name = 'plugin_action_links_' . XSLT_PLUGIN_NAME."/".XSLT_PLUGIN_NAME.".php";
        add_filter( $filter_name, array('XSLT_Processor_Admin', 'render_action_links') );

        add_filter( 'upload_mimes', array('XSLT_Processor_Admin', 'xslt_mime_types') );
        // risky? stops "Sorry, you are not allowed to upload this file type."
        if (!defined('ALLOW_UNFILTERED_UPLOADS'))
            { define('ALLOW_UNFILTERED_UPLOADS', true); }

    }

    /**
     * add xml/xsl to allowed upload types
     */
    public static function xslt_mime_types( $mimes )
    {
        $mimes['xml']  = 'text/xml';
        $mimes['xsl']  = 'application/xslt+xml';
        $mimes['xslt'] = 'application/xslt+xml';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('mimes'),true), E_USER_NOTICE); }
        return $mimes;
    }


    /**
     * return validated options array
     *  sc_transform    boolean
     *  sc_select       boolean
     *  search_path     string
     *  cache_default   integer, minutes
     */
    public static function validate_options( $input )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('input'),true), E_USER_NOTICE); }

        $options = array(
            'post_type_xsl' => !empty( $input['post_type_xsl'] ) ? 1 : 0,
            'post_type_xml' => !empty( $input['post_type_xml'] ) ? 1 : 0,
            'sc_transform'  => !empty( $input['sc_transform'] )  ? 1 : 0,
            'sc_select'     => !empty( $input['sc_select'] )     ? 1 : 0,
            'cache_default' => XSLT_CACHE_DEFAULT,
            'search_path'   => "",
            );

        if (!defined( 'LIBXSLT_VERSION' ))
        {
            $options['post_type_xsl'] = 0;
            $options['post_type_xml'] = 0;
            $options['sc_transform']  = 0;
            $options['sc_select']     = 0;
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
            'post_type_xsl' => __( 'Activate Content Type <b>XSL Stylesheet</b>', XSLT_TEXT ),
            'post_type_xml' => __( 'Activate Content Type <b>XML Document</b>', XSLT_TEXT ),
            'sc_transform'  => __( 'Activate Shortcode <b>[xsl_transform/]</b>', XSLT_TEXT ),
            'sc_select'     => __( 'Activate Shortcode <b>[xml_select/]</b>', XSLT_TEXT ),
            'cache_default' => __( 'Cache Lifetime', XSLT_TEXT ),
            'search_path'   => __( 'Local File Search Paths', XSLT_TEXT ),
            );

        $msg = "";
        foreach($diffs as $key => $val)
        {
            $label = $labels[$key] ?? $key;
            $pre   = $before[$key] ?? "unset";
            $post  = $after[$key]  ?? "unset";
            if (in_array($key,array('post_type_xsl','post_type_xml','sc_transform','sc_select')))
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
            $msg = "<b>XSLT Processor Settings updated:</b><br/>".$msg;
            XSLT_Processor_Notice::success( $msg );
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('diffs','msg'),true), E_USER_NOTICE); }
        }

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

        // add field 'sc_transform'
        add_settings_field(
            'xslt_processor_sc_transform',
            __( 'Activate Shortcodes', XSLT_TEXT ),
            array('XSLT_Processor_Admin','render_setting_sc_transform'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                'label_for' => 'xslt_processor_sc_transform',   // wrap title in label
                //'class'  => 'classname',                        // add to tr
            ));

        // add field 'sc_select'
        add_settings_field(
            'xslt_processor_sc_select',
           '',
            array('XSLT_Processor_Admin','render_setting_sc_select'),
            'xslt_processor_settings',
            'xslt_processor_settings_main',
            array(
                 //'label_for' => 'xslt_processor_sc_select',
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
     * render_action_links
     * @uses settings_fields( string $option_group )
     * @uses do_settings_sections( string $page )
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
     * render section header
     */
    public static function render_section_main()
    {
        if (!defined( 'LIBXSLT_VERSION' )) {
            echo 'PHP\'s XSL extension is NOT available';
            return;
        }
        _e( 'PHP\'s <a href="https://www.php.net/manual/en/book.xsl.php" target="_blank">XSL extension</a> is available', XSLT_TEXT );
        echo '&nbsp;:&nbsp;';
        echo 'XSLT v'.LIBXSLT_DOTTED_VERSION;
        echo ', EXSLT v'.LIBEXSLT_DOTTED_VERSION;
        echo ', LIBXML v'.LIBXML_DOTTED_VERSION;

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

        echo '<b>'.__( 'XSL Stylesheet', XSLT_TEXT ).'</b>';
        _e( ' - Save and manage XSL stylesheets in Wordpress Admin', XSLT_TEXT );
        echo '</p>';


        $value = !empty($options['post_type_xml']);
        echo '<p>';
        echo '<input type="checkbox"'
            . ' id="xslt_processor_post_type_xml" name="'.XSLT_OPTS.'[post_type_xml]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<b>'.__( 'XML Document', XSLT_TEXT ).'</b>';
        _e( ' - Save and manage XML data in Wordpress Admin', XSLT_TEXT );
        echo '</p>';

//         echo '<p>';
//         _e( '- <code><b>xsl="</b>{id|slug}<b>"</b></code>', XSLT_TEXT );
//         echo '<br />';
//         _e( '- <code><b>xml="</b>{id|slug}<b>"</b></code>', XSLT_TEXT );
//         echo '</p>';
    }

    /**
     * render settings field: sc_transform
     */
    public static function render_setting_sc_transform()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_transform']);
        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_transform" name="'.XSLT_OPTS.'[sc_transform]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<b>[xsl_transform]</b>';
        _e( ' - Process XML data using an XSL stylesheet', XSLT_TEXT );

        echo '<p>';
        _e( '- The <code><b>xml</b></code> attribute specifies an XML data source.'
            . '  Alternatively, XML can be provided directly in the body of shortcode.', XSLT_TEXT );
        echo '<br/>';
        _e( '- The <code><b>xsl</b></code> attribute specifies an XSL stylesheet to use for the transform.'
            . '  The default stylesheet will print the source XML.', XSLT_TEXT );

        echo '<ul>';
        echo '<li>'.__( 'Usage', XSLT_TEXT ).':</li>';
        echo '<li><code><b>[xsl_transform xsl="</b>{file|url|id|slug}<b>" xml="</b>{file|url|id|slug}<b>" /]</b></code></li>';
        echo '<li><code><b>[xsl_transform xsl="</b>{file|url|id|slug}<b>"]</b>'
            . htmlentities('<DATA>...</DATA>')
            . '<b>[/xsl_transform]</b></code></li>';
        echo '<li><code><b>[xsl_transform xsl="</b>{file|url|id|slug}<b>"]</b>'
            . '[xml_select /]'
            . '<b>[/xsl_transform]</b></code></li>';
        echo '</ul>';

        echo '</p>';
    }

    /**
     * render settings field: sc_select
     */
    public static function render_setting_sc_select()
    {
        $options = get_option( XSLT_OPTS, array() );
        $value = !empty($options['sc_select']);
        echo '<input type="checkbox"'
            . ' id="xslt_processor_sc_select" name="'.XSLT_OPTS.'[sc_select]"'
            . ' value="1"'
            . ((defined( 'LIBXSLT_VERSION' ) && $value) ? ' checked': '')
            . ' />';

        echo '<b>[xml_select]</b>';
        _e( ' - Filter XML data using an XPath select statement', XSLT_TEXT );

        echo '<p>';
        _e( '- The <code><b>xml</b></code> attribute specifies an XML data source, and is required.', XSLT_TEXT );
        echo '<br/>';
        _e( '- The <code><b>select</b></code> attribute specifies an XPath statement used to filter the XML.'
            . '  Alternatively, complex select statements with quotes, brackets or other special syntax can be specified in the body of the shortcode.'
            . '  The default <code>select="/"</code> returns all nodes in the XML.', XSLT_TEXT );

        echo '<ul>';
        echo '<li>'.__( 'Usage', XSLT_TEXT ).':</li>';
        echo '<li><code><b>[xml_select xml="</b>{file|url|id|slug}<b>" select="</b>//nodename<b>" /]</b></code></li>';
        echo '<li><code><b>[xml_select xml="</b>{file|url|id|slug}<b>"]</b>//nodename[@id="1234"]<b>[/xml_select]</b></code></li>';
        echo '</ul>';

        echo '</p>';
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

        echo '<p>';
        _e( '- Remote files are cached locally when <code><b>xml="</b>{url}<b>"</b></code> or <code><b>xsl="</b>{url}<b>"</b></code>.', XSLT_TEXT );
        echo '<br/>';
        _e( '- Use <code><b>cache="</b>{minutes}<b>"</b></code> to override.', XSLT_TEXT );
        echo '</p>';
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
            . '</textarea>"';

        echo '<p>';
        _e( 'Specify local directories containing XSL stylesheets and XML data files.', XSLT_TEXT );
        echo '<br/>';
        printf(
            __( 'The default path <code>%sxsl</code> will be searched last.', XSLT_TEXT ),
            XSLT_PLUGIN_DIR
            );
        echo '</p>';
    }


}  // end XSLT_Processor_Admin

