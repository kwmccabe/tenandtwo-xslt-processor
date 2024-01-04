<?php
/**
 * XSLT_Callback
 *
 * @package           tenandtwo-plugins
 * @subpackage        xslt-processor
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
        $rv = "<RESULT>" . XSLT_Processor_Util::getMicrotime() . "</RESULT>";
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
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
        $rv = "<RESULT>" . XSLT_Processor_Util::getDateTime( $params ) . "</RESULT>";
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
        if (empty($params['path'])) { return '<RESULT/>'; }
        $rv = "<RESULT>" . XSLT_Processor_Util::getFileExistsLocal( $params['path'] ) . "</RESULT>";
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
        if (empty($params['url'])) { return '<RESULT/>'; }
        $rv = "<RESULT>" . XSLT_Processor_Util::getFileExistsRemote( $params['url'] ) . "</RESULT>";
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
        if (empty($params['value'])) { return '<RESULT/>'; }
        $rv = "<RESULT>" . trim(html_entity_decode(stripslashes($params['value']), ENT_QUOTES|ENT_XML1, 'UTF-8'), "\xc2\xa0") . "</RESULT>";
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * apply strip_tags() to $params['value']
     * br and p tags, unless excluded, are replaced with spaces
     *
     * @see string.xsl, template name="string-strip-tags"
     * @param array $params
     * - value : string
     * - tags : string
     * @return string  XML
     */
    public static function getStripTags( $params )
    {
        if (empty($params['value']))        { return '<RESULT/>'; }
        if (empty($params['allowed_tags'])) { $params['allowed_tags'] = ''; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }

        $result = trim(html_entity_decode(stripslashes($params['value']), ENT_QUOTES|ENT_XML1, 'UTF-8'));
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }

        if (stripos($params['allowed_tags'],'<br>') === false)
            { $result = str_ireplace(array('<br>','<br/>','<br />'),' ',$result); }
        if (stripos($params['allowed_tags'],'<p>') === false)
            { $result = str_ireplace(array('<p>','<p/>'),' ',$result); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }

        $result = strip_tags($result,$params['allowed_tags']);
        $result = trim(htmlentities($result, ENT_QUOTES|ENT_XML1, 'UTF-8', false));
        $rv = "<RESULT>" . $result . "</RESULT>";
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
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
		if (empty($params['global']))  { $params['global'] = '_REQUEST'; }
		if (empty($params['index']))   { $params['index'] = ''; }

		global ${$params['global']};
		if (!isset(${$params['global']})) { return '<RESULT global="'.$params['global'].'" />'; }

		global $XSLT_Processor_XML;
        if (empty($XSLT_Processor_XML)) { $XSLT_Processor_XML = new XSLT_Processor_XML(); }

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
        $xml->addAttribute( 'global', $params['global'] );
        $xml->addAttribute( 'index', $params['index'] );
        $rv = $XSLT_Processor_XML->encode_array( $subparams, $xml, false );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see util.xsl, template name="util-byte-size"
     * @see XSLT_Processor_Util::getByteSize()
     * @param array $params
     * - bytes : int
     * @return string  XML
     */
    public static function getByteSize( $params )
    {
        if (empty($params['bytes'])) { return '<RESULT/>'; }
        $rv = "<RESULT>" . XSLT_Processor_Util::getByteSize( $params['bytes'] ) . "</RESULT>";
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * @see wp.xsl, template name="wp-sanitize-title"
     * @see XSLT_Processor_WP::getSanitizeTitle()
     * @param array $params
     * - title : string
     * @return string  XML
     */
    public static function getSanitizeTitle( $params )
    {
        if (empty($params['title'])) { return '<RESULT/>'; }
        $rv = "<RESULT>" . XSLT_Processor_WP::getSanitizeTitle( $params['title'] ) . "</RESULT>";
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see wp.xsl, template name="wp-xml-select"
     * @see XSLT_Processor_Shortcode::xml_select()
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
    public static function getXmlSelect( $params )
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
        $rv = "<RESULT>" . XSLT_Processor_Shortcode::xml_select( $attrs, '' ) . "</RESULT>";
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }


} // end class XSLT_Processor_Callback
