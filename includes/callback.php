<?php
/**
 * XSLT_Callback
 *
 * usage:
 * require_once plugin_dir_path( __FILE__ ) . 'includes/callback.php';
 *
 * XSLT usage:
 * <xsl:variable name="PARAMS">$params = array();</xsl:variable>
 * <xsl:copy-of select="php:function('XSLT_Callback','method',string($PARAMS))/RESULT" />
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */
defined( 'ABSPATH' ) or die( 'Not for browsing' );


/**
 * Globally scoped function for generic XSLT callback
 * Dispatches to class XSLT_Processor_Callback::$method
 *
 * @param string $method : XSLT_Processor_Callback method name
 * @param string $input  : eval to create $params array
 * @return DomDocument
 */
function XSLT_Callback( $method, $input ) {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('method','input'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(debug_backtrace(),true), E_USER_NOTICE); }

    eval($input); unset($input);
    if (empty($params)) { $params = array(); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('method','params'),true), E_USER_NOTICE); }

    //$xml = "<".'?xml version="1.0" encoding="UTF-8"?'.">\n";
    $xml = (method_exists( "XSLT_Processor_Callback", $method ))
        ? XSLT_Processor_Callback::$method( $params )
        : "<RESULT><ERROR>Method XSLT_Processor_Callback::".$method." does not exist</ERROR></RESULT>";
    unset($params);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('method','xml'),true), E_USER_NOTICE); }

    $doc = new DomDocument('1.0', 'utf-8');
    $doc->loadXML($xml);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r($doc->saveXML(),true), E_USER_NOTICE); }
    return $doc;
}


/**
 * Callback class for XSLT_Callback()
 * string $xml = XSLT_Processor_Callback::$method( $params )
 * string $xml = '<RESULT template="name">' . $rv . '</RESULT>';
 */
class XSLT_Processor_Callback {

    /**
     * @see date.xsl, template name="date-microtime"
     * @see XSLT_Processor_Util::getMicrotime()
     * @param array $params
     * - none
     * @return string  XML
     */
    public static function getMicrotime( $params )
    {
        $rv = '<RESULT template="date-microtime">' . XSLT_Processor_Util::getMicrotime() . '</RESULT>';
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see date.xsl, template name="date-format"
     * @see XSLT_Processor_Util::getDateTime()
     * @param array $params
     * - time   : int
     * - value  : datetime string
     * - shift  : string, eg "+1 hours"
     * - format : string, eg "Y-m-d H:i:s"
     * @return string  XML
     */
    public static function getDateTime( $params )
    {
        $rv = '<RESULT template="date-format">' . XSLT_Processor_Util::getDateTime( $params ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * @see file.xsl, template name="file-exists-local"
     * @param array $params
     * - path  : local filepath
     * @return string  XML
     */
    public static function getFileExistsLocal( $params )
    {
        if (empty($params['path'])) { return '<RESULT template="file-exists-local" />'; }
        $rv = '<RESULT template="file-exists-local">' . XSLT_Processor_Util::getFileExistsLocal( $params['path'] ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see file.xsl, template name="file-exists-remote"
     * @param array $params
     * - url  : remote path
     * @return string  XML
     */
    public static function getFileExistsRemote( $params )
    {
        if (empty($params['url'])) { return '<RESULT template="file-exists-remote" />'; }
        $rv = '<RESULT template="file-exists-remote">' . XSLT_Processor_Util::getFileExistsRemote( $params['url'] ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see file.xsl, template name="file-listing-local"
     * @param array $params
     * - path  : local directory path
     * @return string  XML
     */
    public static function getFileListingLocal( $params )
    {
        if (empty($params['path']))   { return '<RESULT template="file-listing-local" />'; }
        if (empty($params['match']))  { $params['match'] = '.xml'; }
        if (empty($params['levels'])) { $params['levels'] = 1; }

        $options = get_option( XSLT_OPTS, array() );
        $search_paths = explode("\n", $options['search_path'] ?? '');

        $listing = XSLT_Processor_Util::getFileListingLocal(
            $params['path'],
            $params['match'],
            $params['levels'],
            $search_paths,
            'xml'
            );
        $rv = '<RESULT template="file-listing-local">' . $listing . '</RESULT>';
        if (extension_loaded('tidy'))
            { $rv = XSLT_Processor_XML::tidy_string( $rv, 'xml' ); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * apply html_entity_decode() to $params['value']
     *
     * @see string.xsl, template name="string-entity-decode"
     * @param array $params
     * - value : string
     * @return string  XML
     */
    public static function getHtmlEntityDecode( $params )
    {
        if (empty($params['value'])) { return '<RESULT template="string-entity-decode"/>'; }
        $rv = '<RESULT template="string-entity-decode">' . trim(html_entity_decode(stripslashes($params['value']), ENT_QUOTES|ENT_XML1, 'UTF-8'), "\xc2\xa0") . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * apply strip_tags() to $params['value']
     * <br> and simple <p> tags, unless excluded, are replaced with spaces
     *
     * @see string.xsl, template name="string-strip-tags"
     * @param array $params
     * - value : string
     * - tags : string
     * @return string  XML
     */
    public static function getStripTags( $params )
    {
        if (empty($params['value']))        { return '<RESULT template="string-strip-tags"/>'; }
        if (empty($params['allowed_tags'])) { $params['allowed_tags'] = ''; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }

        $rv = trim(html_entity_decode(stripslashes($params['value']), ENT_QUOTES|ENT_XML1, 'UTF-8'));
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }

        if (stripos($params['allowed_tags'], '<br>') === false)
            { $rv = str_ireplace(array('<br>','<br/>','<br />'), ' ', $rv); }
        if (stripos($params['allowed_tags'], '<p>') === false)
            { $rv = str_ireplace(array('<p>','<p/>'), ' ', $rv); }
        $rv = strip_tags( $rv, $params['allowed_tags'] );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }

        $rv = trim(htmlentities($rv, ENT_QUOTES|ENT_XML1, 'UTF-8', false));
        $rv = '<RESULT template="string-strip-tags">' . $rv . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * getSuperGlobal
     *
     * @see util.xsl, template name="util-super-global"
     * @param array $params
     * - global : string, dflt '_REQUEST'
     * - index  : string, for eval of global->index or global[index]
     * @return string  XML
     */
    public static function getSuperGlobal( $params )
    {
		if (empty($params['global']))  { $params['global'] = '_REQUEST'; }
		if (empty($params['index']))   { $params['index'] = ''; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }

		global ${$params['global']};
		if (!isset(${$params['global']}))
		    { return '<RESULT template="util-super-global" global="'.$params['global'].'" />'; }

//		eval("\$subparams = \$" . $params['global'] . $params['index'] . ";");
		$subparams = array();
		if (empty($params['index'])) {
			$subparams = ${$params['global']};
		} else {
		    eval("\$is_object = is_object(\$".$params['global'].");");
		    eval("\$is_array = is_array(\$".$params['global'].");");
            if ($is_object) { eval("\$subparams = isset(\$".$params['global']."->".$params['index'].") ? \$".$params['global']."->".$params['index'] . " : \$subparams;"); }
            if ($is_array)  { eval("\$subparams = isset(\$".$params['global']."['".$params['index']."']) ? \$".$params['global']."['".$params['index']."'] : '';"); }
		}
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('subparams','rv'),true), E_USER_NOTICE); }

        $xml = new SimpleXMLElement( '<RESULT/>' );
        $xml->addAttribute( 'template', 'util-super-global' );
        $xml->addAttribute( 'global', $params['global'] );
        $xml->addAttribute( 'index', $params['index'] );
        $rv = XSLT_Processor_XML::encode_array( $subparams, $xml, false );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * @see wp.xsl, template name="wp-post-item"
     *
     * @param array $params
     * - post : int (id) or str (slug)
     * - type : string, eg 'xslt_xml'
     * @return string  XML
     */
    public static function getPostItem( $params )
    {
        if (empty($params['post'])) { return '<RESULT template="wp-post-item"/>'; }
        if (empty($params['type'])) { $params['type'] = ''; }

        $post = XSLT_Processor_WP::getPostItem( $params['post'], $params['type'], OBJECT );
        if (empty($post))
            {  return '<RESULT template="wp-post-item" post="'.$params['post'].'" type="'.$params['type'].'"/>';  }
        $post_type = ($post->post_type == XSLT_POST_TYPE_XSL) ? 'xsl' : (($post->post_type == XSLT_POST_TYPE_XML) ? 'xml' : $post->post_type);

        $result = $post->to_array();
        $result['post_content'] = XSLT_Processor_WP::getPostContent( $post );
        if (empty($result['post_excerpt']))
            { $result['post_excerpt'] = get_the_excerpt( $post ); }

        $xml = new SimpleXMLElement( '<RESULT/>' );
        $xml->addAttribute( 'template', 'wp-post-item' );
        $xml->addAttribute( $post_type, $post->post_name );
        $xml->addAttribute( 'id', $post->ID );
        $rv = XSLT_Processor_XML::encode_array( $result, $xml, false );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see wp.xsl, template name="wp-post-meta"
     *
     * @param array $params
     * - post : int (id) or str (slug)
     * - type : string, eg 'xslt_xml'
     * @return string  XML
     */
    public static function getPostMeta( $params )
    {
        if (empty($params['post'])) { return '<RESULT template="wp-post-meta"/>'; }
        if (empty($params['type'])) { $params['type'] = ''; }

        $post = XSLT_Processor_WP::getPostItem( $params['post'], $params['type'], OBJECT );
        if (empty($post))
            {  return '<RESULT template="wp-post-meta" post="'.$params['post'].'" type="'.$params['type'].'"/>';  }
        $post_type = ($post->post_type == XSLT_POST_TYPE_XSL) ? 'xsl' : (($post->post_type == XSLT_POST_TYPE_XML) ? 'xml' : $post->post_type);

        $result = XSLT_Processor_WP::getPostMeta( $post );

        $xml = new SimpleXMLElement( '<RESULT/>' );
        $xml->addAttribute( 'template', 'wp-post-meta' );
        $xml->addAttribute( $post_type, $post->post_name );
        $xml->addAttribute( 'id', $post->ID );
        $rv = XSLT_Processor_XML::encode_array( $result, $xml, false );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see wp.xsl, template name="wp-size-format"
     * @see https://developer.wordpress.org/reference/functions/size_format/
     * @param array $params
     * - bytes : int
     * - decimals : int
     * @return string  XML
     */
    public static function getSizeFormat( $params )
    {
        if (empty($params['bytes']))    { $params['bytes'] = 0; }
		if (empty($params['decimals'])) { $params['decimals'] = 2; }
        $rv = '<RESULT template="wp-size-format">' . size_format( $params['bytes'], $params['decimals'] ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see wp.xsl, template name="wp-sanitize-title"
     * @see https://developer.wordpress.org/reference/functions/sanitize_title/
     * @param array $params
     * - title : string
     * @return string  XML
     */
    public static function getSanitizeTitle( $params )
    {
        if (empty($params['title'])) { return '<RESULT template="wp-sanitize-title"/>'; }
		if (empty($params['fallback_title']))   { $params['fallback_title'] = ''; }
		if (empty($params['context']))          { $params['context'] = 'save'; }
        $rv = '<RESULT template="wp-sanitize-title">' . sanitize_title( $params['title'], $params['fallback_title'], $params['context'] ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see wp.xsl, template name="wp-select-xml"
     * @see XSLT_Processor_Shortcode::xslt_select_xml()
     * @param array $params
     * - xml    : string file|url|id|slug
     * - select : string, xpath
     * - cache  : integer, minutes if xml={url}
     * - format : string, 'xml', 'json', 'php', or 'html'
     * - root   : string, nodename for result
     * - strip-declaration : string, yes (dflt) | no
     * - strip-namespaces  : string, no (dflt) | yes
     * @return string  XML
     */
    public static function getSelectXml( $params )
    {
        $attrs = array(
            'xml'    => $params['xml']    ?? '',
            'select' => $params['select'] ?? '/',
            'cache'  => $params['cache']  ?? -1,
            'format' => $params['format'] ?? 'xml',
            'root'   => $params['root']   ?? 'RESULT',
            //'strip-declaration' => $params['strip-declaration'] ?? 'yes',
            'strip-namespaces'  => $params['strip-namespaces']  ?? 'no',
        );
        $rv = '<RESULT template="wp-select-xml">' . XSLT_Processor_Shortcode::xslt_select_xml( $attrs, '' ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','attrs','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see wp.xsl, template name="wp-select-csv"
     * @see XSLT_Processor_Shortcode::xslt_select_csv()
     * @param array $params
     * - csv        : string file|url
     * read params:
     * - separator  : string, ","
     * - enclosure  : string, "\""
     * - escape     : string, "\\"
     * write params:
     * - key_row    : int, row number for column labels
     * - col        : string, column number(s), letter(s), or label(s)
     * - key_col    : string, column number, letter, or label for key matching
     * - key        : string, value(s) for key_col matching
     * - row        : string, row number(s)
     * - class      : string, "table"

     * @return string  XML
     */
    public static function getSelectCsv( $params )
    {
        $attrs = array(
            'csv'       => $params['csv']       ?? '',
            'separator' => $params['separator'] ?? ",",
            'enclosure' => $params['enclosure'] ?? "\"",
            'escape'    => $params['escape']    ?? "\\",
            'key_row'   => $params['key_row']   ?? 0,
            'col'       => $params['col']       ?? 0,
            'key_col'   => $params['key_col']   ?? 0,
            'key'       => $params['key']       ?? '',
            'row'       => $params['row']       ?? 0,
            'class'     => $params['class']     ?? 'table',
            //'htmlentities' => $params['htmlentities'] ?? 'no',
        );
        $rv = '<RESULT template="wp-select-csv">' . XSLT_Processor_Shortcode::xslt_select_csv( $attrs, '' ) . '</RESULT>';
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','attrs','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

} // end class XSLT_Processor_Callback
