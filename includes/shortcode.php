<?php
/**
 * Shortcodes
 *
 * usage:
 * require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';
 * XSLT_Processor_Shortcode::init();
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */
defined( 'ABSPATH' ) or die( 'Not for browsing' );


class XSLT_Processor_Shortcode
{

    /**
     * add_shortcodes   : xslt_transform_xml, xslt_transform_alias
     * add_shortcode    : xslt_select_xml
     * add_shortcode    : xslt_select_csv
     * add_shortcode    : xslt_test
     */
    public static function init()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }

        $options = get_option( XSLT_OPTS, array() );

        add_filter( 'no_texturize_shortcodes',  array('XSLT_Processor_Shortcode', 'no_texturize_shortcodes') );

        if (!empty($options['sc_transform_xml'])) {
            if (!shortcode_exists('xslt_transform_xml'))
                { add_shortcode( 'xslt_transform_xml',       array('XSLT_Processor_Shortcode', 'xslt_transform_xml') ); }
            elseif (WP_DEBUG)
                { trigger_error(__METHOD__." : shortcode 'xslt_transform_xml' already exists", E_USER_NOTICE); }

            if (!shortcode_exists('xslt_transform_alias'))
                { add_shortcode( 'xslt_transform_alias', array('XSLT_Processor_Shortcode', 'xslt_transform_xml') ); }
            elseif (WP_DEBUG)
                { trigger_error(__METHOD__." : shortcode 'xslt_transform_alias' already exists", E_USER_NOTICE); }
        }

        if (!empty($options['sc_select_xml'])) {
            if (!shortcode_exists('xslt_select_xml'))
                { add_shortcode( 'xslt_select_xml', array('XSLT_Processor_Shortcode', 'xslt_select_xml') ); }
            elseif (WP_DEBUG)
                { trigger_error(__METHOD__." : shortcode 'xslt_select_xml' already exists", E_USER_NOTICE); }
        }

        if (!empty($options['sc_select_csv'])) {
            if (!shortcode_exists('xslt_select_csv'))
                { add_shortcode( 'xslt_select_csv', array('XSLT_Processor_Shortcode', 'xslt_select_csv') ); }
            elseif (WP_DEBUG)
                { trigger_error(__METHOD__." : shortcode 'xslt_select_csv' already exists", E_USER_NOTICE); }
        }

        if (WP_DEBUG && !shortcode_exists('xslt_test'))
            { add_shortcode( 'xslt_test', array('XSLT_Processor_Shortcode', 'xslt_test') ); }
    }


    /**
     * content filter : no_texturize_shortcodes
     */
    public static function no_texturize_shortcodes( $shortcodes )
    {
        if (shortcode_exists('xslt_transform_xml'))   { $shortcodes[] = 'xslt_transform_xml'; }
        if (shortcode_exists('xslt_transform_alias')) { $shortcodes[] = 'xslt_transform_alias'; }
        if (shortcode_exists('xslt_select_xml'))      { $shortcodes[] = 'xslt_select_xml'; }
        if (shortcode_exists('xslt_select_csv'))      { $shortcodes[] = 'xslt_select_csv'; }
        if (shortcode_exists('xslt_test'))            { $shortcodes[] = 'xslt_test'; }
        return $shortcodes;
    }


    /**
     * shortcode : xslt_test
     */
    public static function xslt_test( $attrs, $content )
    {
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content'),true), E_USER_NOTICE); }
        $result = "this is a test";
        return print_r($result,true);
    }

    /**
     * shortcode : xslt_transform_xml
     *
     * @param content           : xml string data
     *
     * @param attribute 'xml'
     * @param 'xml_file'        : local filepath
     * @param 'xml_url'         : remote filepath
     * @param 'xml_id'          : WP post_id
     * @param 'xml_name'        : WP post_name
     * @param attribute 'tidy'  : no (dflt) | yes/html | xml
     *
     * @param attribute 'xsl'
     * @param 'xsl_file'        : local filepath
     * @param 'xsl_url'         : remote filepath
     * @param 'xsl_id'          : WP post_id
     * @param 'xsl_name'        : WP post_name
     *
     * @param attribute 'XYZ'   : passed to stylesheet as <param name="XYZ" />
     *
     * @param htmlentities      : no (dflt) | yes
     * @param outfile           : local filepath, writable
     *
     * @see https://www.php.net/manual/en/book.xsl.php
     */
    public static function xslt_transform_xml( $attrs, $content )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        $attrs = array_change_key_case( (array)$attrs, CASE_LOWER );
        $params = array(
            "xml_type"  => "file",
            "xml_value" => XSLT_PLUGIN_DIR.'xsl/default.xml',
            "xsl_type"  => "file",
            "xsl_value" => XSLT_PLUGIN_DIR.'xsl/default.xsl',
            "outfile"   => null,
            //"some_param" => "some value",
        );
        $options = get_option( XSLT_OPTS, array() );

        // get cache_minutes for XSLT_Processor_Util::getRemoteFile()
        $cache_minutes = $options['cache_default'] ?? XSLT_CACHE_DEFAULT;
        if (isset($attrs['cache']) && 0 <= intval($attrs['cache'])) {
            $cache_minutes = intval($attrs['cache']);
        }

        // convert generic 'xml' param to type 'xml_*'
        if (!empty($attrs['xml'])) {
            $url_parts  = parse_url($attrs['xml']);
            $path_parts = pathinfo($attrs['xml']);
            if (!empty($url_parts['scheme']) && !empty($url_parts['host'])) {
                $attrs['xml_url'] = $attrs['xml'];
            } elseif (!empty($path_parts['basename']) && !empty($path_parts['extension'])) {
                $attrs['xml_file'] = $url_parts['path'];
            } elseif (is_numeric($attrs['xml'])) {
                $attrs['xml_id'] = $attrs['xml'];
            } else {
                $attrs['xml_name'] = sanitize_title($attrs['xml']);
            }
        }

        // convert generic 'xsl' param to type 'xsl_*'
        if (!empty($attrs['xsl'])) {
            $url_parts  = parse_url($attrs['xsl']);
            $path_parts = pathinfo($attrs['xsl']);
            if (!empty($url_parts['scheme']) && !empty($url_parts['host'])) {
                $attrs['xsl_url'] = $attrs['xsl'];
            } elseif (!empty($path_parts['basename']) && !empty($path_parts['extension'])) {
                $attrs['xsl_file'] = $url_parts['path'];
            } elseif (is_numeric($attrs['xsl'])) {
                $attrs['xsl_id'] = $attrs['xsl'];
            } else {
                $attrs['xsl_name'] = sanitize_title($attrs['xsl']);
            }
        }

        // set xml_value and xml_type
        if (!empty($attrs['xml_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR.'xsl';
            $path = XSLT_Processor_Util::getFileExistsLocal( $attrs['xml_file'], $search_paths );
            if (empty($path))
                { return sprintf( esc_html__( "File '%s' not found in search path '%s'", 'tenandtwo-xslt-processor' ), $attrs['xml_file'], implode(":",$search_paths) ); }

            $params['xml_type']  = 'file';
            $params['xml_value'] = $path;
        }
        if (!empty($attrs['xml_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $attrs['xml_url'] );
            if (empty($path))
                { return sprintf( esc_html__( "Unknown file '%s'", 'tenandtwo-xslt-processor' ), $attrs['xml_url'] ); }

            $params['xml_type']  = 'string';
            $params['xml_value'] = XSLT_Processor_Util::getRemoteFile( $attrs['xml_url'], $cache_minutes );
        }
        if (!empty($attrs['xml_id']) || !empty($attrs['xml_name'])) {
            $id = $attrs['xml_id'] ?? $attrs['xml_name'];
            $post_type = (!empty($options['post_type_xml'])) ? array(XSLT_POST_TYPE_XML) : null;
            $post = XSLT_Processor_WP::getPostItem( $id, $post_type );
            if ($post === false)
                { return sprintf( esc_html__( "XML '%s' not found", 'tenandtwo-xslt-processor' ), $id ); }

            $params['xml_type']  = 'string';
            $params['xml_value'] = XSLT_Processor_WP::getPostContent( $post );
        }

        // xml_value : set in shortcode body (overrides attributes)
        if (!empty($content)) {
            $params['xml_type']  = "string";
            $params['xml_value'] = do_shortcode( $content );
        }

        // set xsl_value and xsl_type
        if (!empty($attrs['xsl_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR.'xsl';
            $path = XSLT_Processor_Util::getFileExistsLocal( $attrs['xsl_file'], $search_paths );
            if (empty($path))
                { return sprintf( esc_html__( "File '%s' not found in search path '%s'", 'tenandtwo-xslt-processor' ), $attrs['xsl_file'], implode(":",$search_paths) ); }

            $params['xsl_type']  = 'file';
            $params['xsl_value'] = $path;
        }
        if (!empty($attrs['xsl_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $attrs['xsl_url'] );
            if (empty($path))
                { return sprintf( esc_html__( "Unknown file '%s'", 'tenandtwo-xslt-processor' ), $attrs['xsl_url'] ); }

            $params['xsl_type'] = 'string';
            $params['xsl_value'] = XSLT_Processor_Util::getRemoteFile( $attrs['xsl_url'], $cache_minutes );
        }
        if (!empty($attrs['xsl_id']) || !empty($attrs['xsl_name'])) {
            $id = $attrs['xsl_id'] ?? $attrs['xsl_name'];
            $post_type = (!empty($options['post_type_xsl'])) ? array(XSLT_POST_TYPE_XSL) : null;
            $post = XSLT_Processor_WP::getPostItem( $id, $post_type );
            if ($post === false)
                { return sprintf( esc_html__( "XSL '%s' not found", 'tenandtwo-xslt-processor' ), $id ); }

            $params['xsl_type'] = 'string';
            $params['xsl_value'] = XSLT_Processor_WP::getPostContent( $post );
        }

        // outfile
        if (!empty($attrs['outfile'])) {
            $path_parts = pathinfo($attrs['outfile']);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('path_parts'),true), E_USER_NOTICE); }

            $dirname = (is_dir($path_parts['dirname'])) ? realpath($path_parts['dirname']) : '';
            if (empty($dirname))
                { return sprintf( esc_html__( "Directory '%s' not found", 'tenandtwo-xslt-processor' ), $path_parts['dirname'] ); }
            if (!is_writable($dirname))
                { return sprintf( esc_html__( "Directory '%s' not writable", 'tenandtwo-xslt-processor' ), $dirname ); }

            $outfile = rtrim($dirname,'/').'/'.$path_parts['basename'];
            $path = XSLT_Processor_Util::getFileExistsLocal( $outfile );
            if (!empty($path) && !is_writable($path))
                { return sprintf( esc_html__( "File '%s' not writable", 'tenandtwo-xslt-processor' ), $outfile ); }

            $params['outfile'] = $outfile;
        }

        // optional stylesheet params
        $exclude_params = array(
            "xml_file", "xml_url", "xml_id", "xml_name",
            "xsl_file", "xsl_url", "xsl_id", "xsl_name",
            "xsl_type", "xsl_value", "xml_type", "xml_value", "outfile",
            "htmlentities", "tidy",
            );
        foreach( $attrs as $key => $val ) {
            if (in_array($key,$exclude_params)) { continue; }
            if (is_array($val)) { continue; }
            if (is_int($key)) { $key = $val; $val = true; }
            $params[$key] = $val;
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }

        // pre or post-filter per boolean attributes
        $attrs_bool = XSLT_Processor_WP::getShortcodeBooleans( array(
            'tidy' => false,
            'htmlentities' => false,
        ), $attrs, 'xslt_transform_xml' );

        if ($attrs_bool['tidy'] && extension_loaded('tidy'))
        {
            $tidy_type = (!empty($attrs['tidy']) && $attrs['tidy'] == 'xml') ? 'xml' : 'html';
            $params['xml_value'] = ($params['xml_type'] == 'file')
                ? XSLT_Processor_XML::tidy_file( $params['xml_value'], $tidy_type )
                : XSLT_Processor_XML::tidy_string( $params['xml_value'], $tidy_type );
            $params['xml_type'] = 'string';
        }

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        $rv = $XSLT_Processor_XSL->transform( $params );

//         if (!empty($params['outfile'])) {}

        return ($attrs_bool['htmlentities']) ? htmlentities( $rv ) : $rv;
    }

    /**
     * shortcode : xslt_select_xml
     * @param attribute 'xml'
     * @param 'xml_file'        : local filepath
     * @param 'xml_url'         : remote filepath
     * @param 'xml_id'          : WP post_id
     * @param 'xml_name'        : WP post_name
     *
     * @param attribute 'select' : xpath (overridden by content)
     * @param content            : xpath used for 'select' within xml
     * @param attribute 'cache'  : minutes, if xml={url}
     * @param attribute 'format' : output 'xml', 'json', 'php'
     * @param attribute 'root  ' : nodename for result
     *
     * @param attribute 'tidy'              : no (dflt) | yes/html | xml
     * @param attribute 'strip-declaration' : yes (dflt) | no
     * @param attribute 'strip-namespaces'  : no (dflt) | yes
     * @param attribute 'htmlentities'      : no (dflt) | yes
     *
     */
    public static function xslt_select_xml( $attrs, $content )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content'),true), E_USER_NOTICE); }

        $attrs  = array_change_key_case( (array)$attrs, CASE_LOWER );
        $params = array(
            "xml_type"   => "file",
            "xml_value"  => XSLT_PLUGIN_DIR.'xsl/default.xml',
            "select"     => "/",
            "format"     => "xml",
            "root"       => 'RESULT',
            "attributes" => array(),
            "namespaces"   => array(),
            //"xsl_keys"   => array(),
        );
        $options = get_option( XSLT_OPTS, array() );

        // get cache_minutes for XSLT_Processor_Util::getRemoteFile()
        $cache_minutes = $options['cache_default'] ?? XSLT_CACHE_DEFAULT;
        if (isset($attrs['cache']) && 0 <= intval($attrs['cache'])) {
            $cache_minutes = intval($attrs['cache']);
        }

        // convert generic 'xml' param to type 'xml_*'
        if (!empty($attrs['xml'])) {
            $url_parts  = parse_url($attrs['xml']);
            $path_parts = pathinfo($attrs['xml']);
            if (!empty($url_parts['scheme']) && !empty($url_parts['host'])) {
                $attrs['xml_url'] = $attrs['xml'];
            } elseif (!empty($path_parts['basename']) && !empty($path_parts['extension'])) {
                $attrs['xml_file'] = $url_parts['path'];
            } elseif (is_numeric($attrs['xml'])) {
                $attrs['xml_id'] = $attrs['xml'];
            } else {
                $attrs['xml_name'] = sanitize_title($attrs['xml']);
            }
        }

        // set xml_value and xml_type
        if (!empty($attrs['xml_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR.'xsl';
            $path = XSLT_Processor_Util::getFileExistsLocal( $attrs['xml_file'], $search_paths );
            if (empty($path))
                { return sprintf( esc_html__( "File '%s' not found in search path '%s'", 'tenandtwo-xslt-processor' ), $attrs['xml_file'], implode(":",$search_paths) ); }

            $params['xml_type']  = 'file';
            $params['xml_value'] = $path;
            $params['attributes'] = array( 'xml' => $path );
        }
        if (!empty($attrs['xml_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $attrs['xml_url'] );
            if (empty($path))
                { return sprintf( esc_html__( "Unknown file '%s'", 'tenandtwo-xslt-processor' ), $attrs['xml_url'] ); }

            $params['xml_type'] = 'string';
            $params['xml_value'] = XSLT_Processor_Util::getRemoteFile( $path, $cache_minutes );
            $params['attributes'] = array( 'xml' => $path );
        }
        if (!empty($attrs['xml_id']) || !empty($attrs['xml_name'])) {
            $id = $attrs['xml_id'] ?? $attrs['xml_name'];
            $post_type = (!empty($options['post_type_xml'])) ? array(XSLT_POST_TYPE_XML) : null;
            $post = XSLT_Processor_WP::getPostItem( $id, $post_type );
            if ($post === false)
                { return sprintf( esc_html__( "XML '%s' not found", 'tenandtwo-xslt-processor' ), $id ); }
            $post_type_attr = ($post->post_type == XSLT_POST_TYPE_XML) ? 'xml' : $post->post_type;

            $params['xml_type']   = 'string';
            $params['xml_value']  = XSLT_Processor_WP::getPostContent( $post );
            $params['attributes'] = array(
                $post_type_attr => $post->post_name,
                'id'            => $post->ID,
                );
        }

        // pre or post-filter per boolean attributes
        $attrs_bool = XSLT_Processor_WP::getShortcodeBooleans( array(
            'tidy'              => false,
            'strip-declaration' => true,
            'strip-namespaces'  => false,
            'htmlentities'      => false,
        ), $attrs, 'xslt_select_xml' );

        if ($attrs_bool['tidy'] && extension_loaded('tidy'))
        {
            $tidy_type = (!empty($attrs['tidy']) && $attrs['tidy'] == 'xml') ? 'xml' : 'html';
            $params['xml_value'] = ($params['xml_type'] == 'file')
                ? XSLT_Processor_XML::tidy_file( $params['xml_value'], $tidy_type )
                : XSLT_Processor_XML::tidy_string( $params['xml_value'], $tidy_type );
            $params['xml_type'] = 'string';
        }

        if ($attrs_bool['strip-declaration'])
            { $params['xml_value'] = XSLT_Processor_XML::strip_declaration( $params['xml_value'] ); }
        if ($attrs_bool['strip-namespaces'])
            { $params['xml_value'] = XSLT_Processor_XML::strip_namespaces( $params['xml_value'] ); }

        // select path : set in 'select' attribute
        if (!empty($attrs['select'])) {
            $params['select'] = $attrs['select'];
        }
        // select path : set in shortcode body (overrides attribute)
        if (!empty($content)) {
            $params['select'] = trim( $content );
        }

        // output format : set in 'format' attribute
        if (!empty($attrs['format']) && in_array($attrs['format'], array('xml','json'))) {
            $params['format'] = $attrs['format'];
        }

        // root node name in result : set in 'root' attribute
        if (isset($attrs['root'])) {
            $params['root'] = trim( $attrs['root'] );
        }

        // namespace array : set in 'xmlns' attribute
        // eg, [xslt_select_xml xml="input-xml" xmlns="ns1" ns1="uri:namespace-one" select="//ns1:node" /]
        if (!empty($attrs['xmlns'])) {
            $xmlns = explode(' ', $attrs['xmlns']);
            foreach( $xmlns as $ns ) {
                if (empty($attrs[$ns])) { continue; }
                $params['namespaces'][$ns] = $attrs[$ns];
            }
        }

// if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        $rv = ($params['xml_type'] == 'file')
            ? XSLT_Processor_XML::decode_file( $params['xml_value'], $params['select'], $params['format'], $params['root'], $params['attributes'], $params['namespaces'] )
            : XSLT_Processor_XML::decode_string( $params['xml_value'], $params['select'], $params['format'], $params['root'], $params['attributes'], $params['namespaces'] );
        return ($attrs_bool['htmlentities']) ? htmlentities( $rv ) : $rv;
    }


    /**
     * shortcode : xslt_select_csv
     * @param attribute 'csv'
     * @param 'csv_file'        : local filepath
     * @param 'csv_url'         : remote filepath
     *
     * @param attribute 'cache' : minutes, if csv={url}
     *
     * args for fgetcsv()
     * @param attribute 'separator' : ",",
     * @param attribute 'enclosure' : "\"",
     * @param attribute 'escape'    : "\\",
     *
     * args for transform()
     * @param attribute 'key_row' : row number for column labels
     * @param attribute 'col'     : return column number(s), letter(s), or label(s)
     * @param attribute 'key_col' : col number, letter, or label for key matching
     * @param attribute 'key'     : value(s) for key_col matching
     * @param attribute 'row'     : return row number(s)
     * @param attribute 'class'   : 'table' (dflt) | css classname(s) for result <table>
     *
     * @param attribute 'htmlentities'  : no (dflt) | yes
     */
    public static function xslt_select_csv( $attrs, $content )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content'),true), E_USER_NOTICE); }

        $attrs  = array_change_key_case( (array)$attrs, CASE_LOWER );
        $params = array(
            "csv_type"  => "file",
            "csv_value" => "",
        );
        $options = get_option( XSLT_OPTS, array() );

        // get cache_minutes for XSLT_Processor_Util::getRemoteFile()
        $cache_minutes = $options['cache_default'] ?? XSLT_CACHE_DEFAULT;
        if (isset($attrs['cache']) && 0 <= intval($attrs['cache'])) {
            $cache_minutes = intval($attrs['cache']);
        }

        // convert generic 'csv' param to type 'csv_*'
        if (!empty($attrs['csv'])) {
            $url_parts  = parse_url($attrs['csv']);
            $path_parts = pathinfo($attrs['csv']);
            if (!empty($url_parts['scheme']) && !empty($url_parts['host'])) {
                $params['csv_url'] = $attrs['csv'];
            } else {
                $params['csv_file'] = $url_parts['path'];
            }
        }

        // set csv_value and csv_type
        if (!empty($params['csv_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR.'xsl';
            $path = XSLT_Processor_Util::getFileExistsLocal( $params['csv_file'], $search_paths );
            if (empty($path))
                { return sprintf( esc_html__( "File '%s' not found in search path '%s'", 'tenandtwo-xslt-processor' ), $params['csv_file'], implode(":",$search_paths) ); }

            $params['csv_type']  = 'file';
            $params['csv_value'] = $path;

            //$params['csv_type']  = 'string';
            //$params['csv_value'] = XSLT_Processor_Util::getLocalFile( $path, $cache_minutes );
        }
        if (!empty($params['csv_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $params['csv_url'] );
            if (empty($path))
                { return sprintf( esc_html__( "Unknown file '%s'", 'tenandtwo-xslt-processor' ), $params['csv_url'] ); }

            $params['csv_type'] = 'string';
            $params['csv_value'] = XSLT_Processor_Util::getRemoteFile( $path, $cache_minutes );
        }

        // csv_value : set in shortcode body (overrides attributes)
        if (!empty($content)) {
            $params['csv_type']  = "string";
            $params['csv_value'] = do_shortcode( $content );
        }

        // pre or post-filter per boolean attributes
        $attrs_bool = XSLT_Processor_WP::getShortcodeBooleans( array(
            'htmlentities' => false,
        ), $attrs, 'xslt_select_csv' );

        $attrs_read = shortcode_atts( array(
            'separator' => ",",
            'enclosure' => "\"",
            'escape'    => "\\",
        ), $attrs, 'xslt_select_csv' );

        $attrs_write = shortcode_atts( array(
            'key_row'   => 0,
            'col'       => 0,
            'key_col'   => 0,
            'key'       => '',
            'row'       => 0,
            'class'     => 'table',
        ), $attrs, 'xslt_select_csv' );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','attrs_read','attrs_write'),true), E_USER_NOTICE); }


// if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        $rv = ($params['csv_type'] == 'file')
            ? XSLT_Processor_CSV::decode_file( $params['csv_value'], $attrs_read, $attrs_write )
            : XSLT_Processor_CSV::decode_string( $params['csv_value'], $attrs_read, $attrs_write );
        return ($attrs_bool['htmlentities']) ? htmlentities( $rv ) : $rv;
    }


}  // end XSLT_Processor_Shortcode
