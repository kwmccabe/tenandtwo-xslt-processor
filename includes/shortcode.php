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
     * add_shortcodes   : xsl_transform, xsl_transform_alias
     * add_shortcode    : xml_select
     * add_shortcode    : xslt_test
     */
    public static function init()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }

        $options = get_option( XSLT_OPTS, array() );

        if (!shortcode_exists('xsl_transform') && !empty($options['sc_transform'])) {
            add_shortcode( 'xsl_transform',       array('XSLT_Processor_Shortcode', 'xsl_transform') );
            add_shortcode( 'xsl_transform_alias', array('XSLT_Processor_Shortcode', 'xsl_transform') );
        }

        if (!shortcode_exists('xml_select') && !empty($options['sc_select'])) {
            add_shortcode( 'xml_select',        array('XSLT_Processor_Shortcode', 'xml_select') );
        }

//         if (WP_DEBUG && !shortcode_exists('htmlentities')) {
//             add_shortcode( 'htmlentities',      array('XSLT_Processor_Shortcode', 'htmlentities') );
//         }

        if (WP_DEBUG && !shortcode_exists('xslt_test')) {
            add_shortcode( 'xslt_test',         array('XSLT_Processor_Shortcode', 'xslt_test') );
        }

        add_filter( 'no_texturize_shortcodes',  array('XSLT_Processor_Shortcode', 'no_texturize_shortcodes') );
    }


    /**
     * shortcode : xslt_test
     */
    public static function no_texturize_shortcodes( $shortcodes )
    {
        if (shortcode_exists('xsl_transform'))  { $shortcodes[] = 'xsl_transform'; }
        if (shortcode_exists('xml_select'))     { $shortcodes[] = 'xml_select'; }
        if (shortcode_exists('xml_select_alt')) { $shortcodes[] = 'xml_select_alt'; }
        if (shortcode_exists('xslt_test'))      { $shortcodes[] = 'xslt_test'; }
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
     * shortcode : xsl_transform
     *
     * @param content           : xml string data
     *
     * @param attribute 'xml'
     * @param 'xml_file'        : local filepath
     * @param 'xml_url'         : remote filepath
     * @param 'xml_id'          : WP post_id
     * @param 'xml_name'        : WP post_name
     *
     * @param attribute 'xsl'
     * @param 'xsl_file'        : local filepath
     * @param 'xsl_url'         : remote filepath
     * @param 'xsl_id'          : WP post_id
     * @param 'xsl_name'        : WP post_name
     *
     * @param attribute 'XYZ'   : passed to stylesheet as <param name="XYZ" />
     *
     * @see https://www.php.net/manual/en/book.xsl.php
     */
    public static function xsl_transform( $attrs, $content )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        $params = array(
            "xml_type"  => "file",
            "xml_value" => XSLT_PLUGIN_DIR."xsl/default.xml",
            "xsl_type"  => "file",
            "xsl_value" => XSLT_PLUGIN_DIR."xsl/default.xsl",
            //"out_file" => "/path/to/output.txt"
            //"some_param" => "some value"
        );

        $options = get_option( XSLT_OPTS, array() );

        // get cache_minutes for XSLT_Processor_Util::getRemoteFile()
        $cache_minutes = $options['cache_default'] ?? XSLT_CACHE_DEFAULT;
        if (isset($attrs['cache']) && 0 <= intval($attrs['cache'])) {
            $cache_minutes = intval($attrs['cache']);
        }

        // convert generic 'xml' param to type 'xml_*'
        $attrs = array_change_key_case( (array)$attrs, CASE_LOWER );
        if (!empty($attrs['xml'])) {
            $url_parts  = parse_url($attrs['xml']);
            $path_parts = pathinfo($attrs['xml']);
            if (!empty($url_parts['scheme']) && !empty($url_parts['host'])) {
                $attrs['xml_url'] = $attrs['xml'];
            } else if (!empty($path_parts['basename']) && !empty($path_parts['extension'])) {
                $attrs['xml_file'] = $url_parts['path'];
            } else if (is_numeric($attrs['xml'])) {
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
            } else if (!empty($path_parts['basename']) && !empty($path_parts['extension'])) {
                $attrs['xsl_file'] = $url_parts['path'];
            } else if (is_numeric($attrs['xsl'])) {
                $attrs['xsl_id'] = $attrs['xsl'];
            } else {
                $attrs['xsl_name'] = sanitize_title($attrs['xsl']);
            }
        }

        // set xml_value and xml_type
        if (!empty($attrs['xml_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR."xsl";
            $path = XSLT_Processor_Util::getFileExistsLocal( $attrs['xml_file'], $search_paths );
            if (empty($path))
                { return sprintf( __( "File '%s' not found in search path '%s'", XSLT_TEXT ), $attrs['xml_file'], implode(":",$search_paths) ); }

            $params['xml_type']  = 'file';
            $params['xml_value'] = $path;
        }
        if (!empty($attrs['xml_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $attrs['xml_url'] );
            if (empty($path))
                { return sprintf( __( "Unknown file '%s'", XSLT_TEXT ), $attrs['xml_url'] ); }

            $params['xml_type']  = 'string';
            $params['xml_value'] = XSLT_Processor_Util::getRemoteFile( $attrs['xml_url'], $cache_minutes );
        }
        if (!empty($attrs['xml_id']) || !empty($attrs['xml_name'])) {
            $id = $attrs['xml_id'] ?? $attrs['xml_name'];
            $post_type = (!empty($options['post_type_xml'])) ? array('xml') : null;
            $post_content = XSLT_Processor_WP::getPostContent( $id, $post_type );
            if ($post_content === false)
                { return sprintf( __( "XML '%s' not found", XSLT_TEXT ), $id ); }

            $params['xml_type']  = 'string';
            $params['xml_value'] = $post_content;
        }

        // xml_value : set in shortcode body (overrides attributes)
        if (!empty($content)) {
            $params['xml_type']  = "string";
            $params['xml_value'] = do_shortcode( $content );
            $params['xml_value'] =  preg_replace(['|<p>|i','|</p>|i','|<br.?/>|i'], '', trim($params['xml_value']));
            //$params['xml_value'] =  esc_xml($params['xml_value']);
        }

        // set xsl_value and xsl_type
        if (!empty($attrs['xsl_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR."xsl";
            $path = XSLT_Processor_Util::getFileExistsLocal( $attrs['xsl_file'], $search_paths );
            if (empty($path))
                { return sprintf( __( "File '%s' not found in search path '%s'", XSLT_TEXT ), $attrs['xsl_file'], implode(":",$search_paths) ); }

            $params['xsl_type'] = 'file';
            $params['xsl_value'] = $path;
        }
        if (!empty($attrs['xsl_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $attrs['xsl_url'] );
            if (empty($path))
                { return sprintf( __( "Unknown file '%s'", XSLT_TEXT ), $attrs['xsl_url'] ); }

            $params['xsl_type'] = 'string';
            $params['xsl_value'] = XSLT_Processor_Util::getRemoteFile( $attrs['xsl_url'], $cache_minutes );
        }
        if (!empty($attrs['xsl_id']) || !empty($attrs['xsl_name'])) {
            $id = $attrs['xsl_id'] ?? $attrs['xsl_name'];
            $post_type = (!empty($options['post_type_xsl'])) ? array('xsl') : null;
            $post_content = XSLT_Processor_WP::getPostContent( $id, $post_type );
            if ($post_content === false)
                { return sprintf( __( "XSL '%s' not found", XSLT_TEXT ), $id); }

            $params['xsl_type'] = 'string';
            $params['xsl_value'] = $post_content;
        }

        // optional stylesheet params
        $exclude_params = array(
            "xml_file", "xml_url", "xml_id", "xml_name",
            "xsl_file", "xsl_url", "xsl_id", "xsl_name",
            "xsl_type", "xsl_value", "xml_type", "xml_value",
            "out_file",
            );
        foreach ($attrs as $key => $val) {
            if (in_array($key,$exclude_params)) { continue; }
            if (is_array($val)) { continue; }
            $params[$key] = $val;
        }

        // post-filter boolean attributes
        $attrs_bool = XSLT_Processor_WP::getShortcodeBooleans( array(
            'htmlentities' => false,
        ), $attrs, 'xml_transform' );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        $rv = $XSLT_Processor_XSL->transform( $params );
        return ($attrs_bool['htmlentities']) ? htmlentities( $rv ) : $rv;
    }

    /**
     * xml_select
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
     * @param 'strip-declaration' => yes (dflt) | no
     * @param 'strip-namespaces'  => no (dflt) | yes
     * @param 'htmlentities'      => no (dflt) | yes
     *
     */
    public static function xml_select( $attrs, $content )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XML;
        if (empty($XSLT_Processor_XML)) { $XSLT_Processor_XML = new XSLT_Processor_XML(); }

        $params = array(
            "xml_type"   => "file",
            "xml_value"  => XSLT_PLUGIN_DIR."xsl/default.xml",
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
        $attrs = array_change_key_case( (array)$attrs, CASE_LOWER );
        if (!empty($attrs['xml'])) {
            $url_parts  = parse_url($attrs['xml']);
            $path_parts = pathinfo($attrs['xml']);
            if (!empty($url_parts['scheme']) && !empty($url_parts['host'])) {
                $attrs['xml_url'] = $attrs['xml'];
            } else if (!empty($path_parts['basename']) && !empty($path_parts['extension'])) {
                $attrs['xml_file'] = $url_parts['path'];
            } else if (is_numeric($attrs['xml'])) {
                $attrs['xml_id'] = $attrs['xml'];
            } else {
                $attrs['xml_name'] = sanitize_title($attrs['xml']);
            }
        }

        // set xml_value and xml_type
        if (!empty($attrs['xml_file'])) {
            $search_paths = explode("\n", $options['search_path'] ?? '');
            $search_paths[] = XSLT_PLUGIN_DIR."xsl";
            $path = XSLT_Processor_Util::getFileExistsLocal( $attrs['xml_file'], $search_paths );
            if (empty($path))
                { return sprintf( __( "File '%s' not found in search path '%s'", XSLT_TEXT ), $attrs['xml_file'], implode(":",$search_paths) ); }

            $params['xml_type'] = 'file';
            $params['xml_value'] = $path;
            $params['attributes'] = array( 'xml' => $path );
        }
        if (!empty($attrs['xml_url'])) {
            $path = XSLT_Processor_Util::getFileExistsRemote( $attrs['xml_url'] );
            if (empty($path))
                { return sprintf( __( "Unknown file '%s'", XSLT_TEXT ), $attrs['xml_url'] ); }

            $params['xml_type'] = 'string';
            $params['xml_value'] = XSLT_Processor_Util::getRemoteFile( $attrs['xml_url'], $cache_minutes );
            $params['attributes'] = array( 'xml' => $path );
        }
        if (!empty($attrs['xml_id']) || !empty($attrs['xml_name'])) {
            $id = $attrs['xml_id'] ?? $attrs['xml_name'];
            $post_type = (!empty($options['post_type_xml'])) ? array('xml') : null;
            $post_content = XSLT_Processor_WP::getPostContent( $id, $post_type );
            if ($post_content === false)
                { return sprintf( __( "XML '%s' not found", XSLT_TEXT ), $id ); }

            $post = (is_numeric($id))
                ? get_post($id)
                : XSLT_Processor_WP::getPostByName( $id, $post_type );

            $params['xml_type']   = 'string';
            $params['xml_value']  = $post_content;
            $params['attributes'] = array(
                $post->post_type => $post->post_name,
                'id'             => $post->ID,
                );
        }

        // pre or post-filter per boolean attributes
        $attrs_bool = XSLT_Processor_WP::getShortcodeBooleans( array(
            'strip-declaration' => true,
            'strip-namespaces'  => false,
            'htmlentities'      => false,
        ), $attrs, 'xml_select' );

        if ($attrs_bool['strip-declaration'])
            { $params['xml_value'] = XSLT_Processor_Util::removeXmlDeclaration( $params['xml_value'] ); }
        if ($attrs_bool['strip-namespaces'])
            { $params['xml_value'] = XSLT_Processor_Util::removeXmlNamespaces( $params['xml_value'] ); }

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
        // [xml_select xml="onix-xml" select="//onix:Product" xmlns="onix" onix="http://www.editeur.org/onix/2.1/reference" /]
        if (!empty($attrs['xmlns'])) {
            $xmlns = explode(' ', $attrs['xmlns']);
            foreach ($xmlns as $ns) {
                if (empty($attrs[$ns])) { continue; }
                $params['namespaces'][$ns] = $attrs[$ns];
            }
        }

// if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        $rv = ($params['xml_type'] == 'file')
            ? $XSLT_Processor_XML->decode_file( $params['xml_value'], $params['select'], $params['format'], $params['root'], $params['attributes'], $params['namespaces'] )
            : $XSLT_Processor_XML->decode_string( $params['xml_value'], $params['select'], $params['format'], $params['root'], $params['attributes'], $params['namespaces'] );
        return ($attrs_bool['htmlentities']) ? htmlentities( $rv ) : $rv;
    }


}  // end XSLT_Processor_Shortcode
