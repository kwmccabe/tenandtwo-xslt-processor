<?php
/**
 * XML
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );


/**
 * Encodes PHP values (arrays,scalars) as XML strings
 * Decodes XML strings as a PHP array or JSON string
 */
class XSLT_Processor_XML
{

    /**
     * for tidy parse and repair
     */
    private static $tidy_conf = array(
            'char-encoding'         => 'utf8',  // character encoding for both the input and output
            'doctype'               => 'omit',  // auto, omit, html5, strict, transitional, user
            'drop-empty-elements'   => false,   // discard empty elements.
            //'force-output'          => true,    // produce output even if errors are encountered.
            'numeric-entities'      => true,
            'preserve-entities'     => true,    // preserve well-formed entities as found in the input
            'quote-ampersand'       => true,    // output unadorned & characters as &amp;
            //'quote-marks'           => true,    // output " characters as &quot;
            //'quote-nbsp'            => true,    // output non-breaking space characters as entities, rather than as the Unicode character value 160 (decimal)
            'tidy-mark'             => false,   // add a meta element to the document head to indicate that the document has been tidied
            'wrap'                  => 0,       // margin for line wrapping
            );
    private static $tidy_conf_html = array(
            'output-xhtml'          => true,    // pretty print output, writing it as exstensible HTML.
            'show-body-only'        => true,    // print only the contents of the body tag as an HTML fragment
            // HTML5 tags
            //'new-blocklevel-tags'   => 'article aside audio bdi canvas details dialog figcaption figure footer header hgroup main menu menuitem nav section source summary template track video',
            //'new-empty-tags'        => 'command embed keygen source track wbr',
            //'new-inline-tags'       => 'audio command datalist embed keygen mark menuitem meter output progress source time video wbr',
            );
    private static $tidy_conf_xml = array(
            'input-xml'             => true,    // use the XML parser rather than the error correcting HTML parser.
            'output-xml'            => true,    // pretty print output, writing it as well-formed XML
            );

    /**
     * @param string $value     xml
     * @param array $conf       tidy options
     * @param string $encoding  ascii, latin0, latin1, raw, utf8, iso2022, mac, win1252, ibm858, utf16, utf16le, utf16be, big5, and shiftjis.
     * @return array            validation warnings (int) errors (int) and message (str)
     */
    public static function tidy_validate( $xml, $conf = null, $encoding = 'utf8' )
    {
        $result = array( 'warnings' => 0, 'errors' => 0, 'message' => '' );

        if (!extension_loaded('tidy')) {
            trigger_error(__METHOD__.' ERROR : PHP\'s Tidy extension is NOT available', E_USER_ERROR);
            return false;
        }
        $config = (is_array($conf)) ? $conf : array_merge( self::$tidy_conf, ($conf == 'xml') ? self::$tidy_conf_xml : self::$tidy_conf_html );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml','config','encoding'),true), E_USER_NOTICE); }

        $tidy = new tidy();
        $tidy->parseString( $xml, $config, $encoding );
        //$tidy->diagnose();            // adds summary line
        if ($tidy->getStatus() == 0)    // 0=noerr, 1=warnings, 2=errors
            { return $result; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('tidy'),true), E_USER_NOTICE); }

        $result = array(
            //'access'   => tidy_access_count($tidy),
            'warnings' => tidy_warning_count($tidy),
            'errors'   => tidy_error_count($tidy),
            'message'  => nl2br(htmlentities($tidy->errorBuffer, ENT_HTML5, 'UTF-8', false)),
            );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     *  tidy_repair_string(string $string, array|string|null $config = null, ?string $encoding = null): string|false
     *
     * @param string $value     xml
     * @param array $conf       tidy options
     * @param string $encoding  ascii, latin0, latin1, raw, utf8, iso2022, mac, win1252, ibm858, utf16, utf16le, utf16be, big5, and shiftjis.
     * @return string|false
     */
    public static function tidy_string( $xml, $conf = null, $encoding = 'utf8' )
    {
        if (!function_exists('tidy_repair_string')) {
            trigger_error(__METHOD__.' ERROR : PHP\'s Tidy extension is NOT available', E_USER_ERROR);
            return false;
        }
        $config = (is_array($conf)) ? $conf : array_merge( self::$tidy_conf, ($conf == 'xml') ? self::$tidy_conf_xml : self::$tidy_conf_html );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml','config','encoding'),true), E_USER_NOTICE); }
        $rv = tidy_repair_string( $xml, $config, $encoding );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     *  tidy_repair_file(string $filename, array|string|null $config = null, ?string $encoding = null, bool $useIncludePath = false): string|false
     *
     * @param string $file      filepath to xml
     * @param array $conf       tidy options
     * @param string $encoding  ascii, latin0, latin1, raw, utf8, iso2022, mac, win1252, ibm858, utf16, utf16le, utf16be, big5, and shiftjis.
     * @return string|false
     */
    public static function tidy_file( $file, $conf = null, $encoding = 'utf8' )
    {
        if (!function_exists('tidy_repair_file')) {
            trigger_error(__METHOD__.' ERROR : PHP\'s Tidy extension is NOT available', E_USER_ERROR);
            return false;
        }
        $config = (is_array($conf)) ? $conf : array_merge( self::$tidy_conf, ($conf == 'xml') ? self::$tidy_conf_xml : self::$tidy_conf_html );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('file','config','encoding'),true), E_USER_NOTICE); }
        $rv = tidy_repair_file( $file, $config, $encoding);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * remove header : < ? xml version ...
     * removes one (1) max
     *
     * @param string $xml   : xml value
     * @return string       : xml without version header
     */
    public static function strip_declaration( $xml )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }
        $xml = preg_replace('|<\?xml[^>]+\?>|i', '', $xml, 1);
        return trim( $xml );
    }

    /**
     * remove header : <!DOCTYPE ... >
     * NODE: does not work with nested, eg <!ELEMENT>
     *
     * @param string $xml   : xml value
     * @return string       : xml without DOCTYPE header
     */
    public static function strip_doctype( $xml )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }
        $xml = preg_replace('|<\!DOCTYPE[^>]*>|i', '', $xml, 1);
        return trim( $xml );
    }

    /**
     * remove xmlns="uri"
     * remove xmlns:key="uri"
     * change <key:nodename> -to- <nodename>
     *
     * @param string $xml   : xml value
     * @return string       : xml without xmlns attributes
     */
    public static function strip_namespaces( $xml )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }

        $xml = preg_replace('|xmlns[\s]*?=[\s]*?\"[^\"]*?\"|i', '', $xml, -1);
        $xml = preg_replace('|xmlns[\s]*?=[\s]*?\'[^\"]*?\'|i', '', $xml, -1);

        $xml = preg_replace('|xmlns:[a-z]+[\s]*?=[\s]*?\"[^\"]*?\"|i', '', $xml, -1);
        $xml = preg_replace('|xmlns:[a-z]+[\s]*?=[\s]*?\'[^\"]*?\'|i', '', $xml, -1);

        $xml = preg_replace('|(<[/]?)[a-z][^:\s>]*?:|i', '\1', $xml, -1);

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }
        return trim( $xml );
    }


    /**
     * decode XML file as xml, json, or php array
     *
     * @param string $xml       well-formed xml
     * @param string $path      xpath expression
     * @param string $xsl_keys  eg, array(array("name" => 'title_name', "match" => '//Title', "use" => '@name'))
     * @param string $format    'xml' (dflt) | 'php' | 'json'
     * @param string $root      'RESULT' (dflt) | nodename
     * @param array $attributes key="val" added to root node
     * @param array $namespaces xmlns:key="val" added to stylesheet declaration
     * @param array $xsl_keys   eg, array(array("name" => 'title_name', "match" => '//Title', "use" => '@name'))
     * @return string
     */
    public static function decode_string(
        $xml,
        $path = '/',
        $format = 'xml',
        $root = 'RESULT',
        $attributes = array(),
        $namespaces = array(),
        $xsl_keys = array()
        )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml','path','format','root','attributes','namespaces','xsl_keys'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        if (empty($xml))
        {
            $err = "Missing input xml value";
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        $path = htmlspecialchars($path, ENT_COMPAT | ENT_IGNORE, "UTF-8");

        $attrs = '';
        foreach( $attributes as $key => $val )
        {
            $attrs .= ' '.$key.'="'.htmlspecialchars($val, ENT_COMPAT | ENT_IGNORE, "UTF-8").'"';
        }

        $xmlns = $ex = '';
        foreach( $namespaces as $key => $val )
        {
            $xmlns .= ' xmlns:'.$key.'="'.$val.'"';
            if (strlen($ex)) { $ex .= ' '; }
            $ex .= $key;
        }
        if (strlen($ex)) {
            $xmlns .= ' exclude-result-prefixes="'. $ex.'"';
        }

        $keys = '';
        foreach( $xsl_keys as $conf )
        {
            $keys .= '<xsl:key name="'.$conf['name'].'" match="'.$conf['match'].'" use="'.$conf['use'].'" />';
        }

        // sub selection of data from xml string
        $xsl = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"'
    .$xmlns.'>
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />
    '.$keys.'
    <xsl:template match="/">
        '.(($root) ? '<'.$root.$attrs.' select="'.$path.'">' : '').'
            <xsl:copy-of select="'.$path.'" />
        '.(($root) ? '</'.$root.'>' : '').'
    </xsl:template>
</xsl:stylesheet>';

        $transform = array(
            'xml_type'    => 'string'
            , 'xml_value' => $xml
            , 'xsl_type'  => 'string'
            , 'xsl_value' => $xsl
            );
        $result = $XSLT_Processor_XSL->transform( $transform );
        if (empty($result)) { $result = '<RESULT/>'; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xsl'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('transform'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }

        // convert XML to serialized array (format = 'php'|'json')
        if (in_array($format, array('php','json')))
        {
            $result = self::transcode_xml( $result, $format );
        }
        return $result;
    }


    /**
     * decode XML file as xml, json, or php array
     *
     * @param string $file      filepath
     * @param string $path      xpath expression
     * @param string $xsl_keys  eg, array(array("name" => 'title_name', "match" => '//Title', "use" => '@name'))
     * @param string $format    'xml' (dflt) | 'php' | 'json'
     * @param string $root      'RESULT' (dflt) | nodename
     * @param array $attributes key="val" added to root node
     * @param array $namespaces xmlns:key="val" added to stylesheet declaration
     * @param array $xsl_keys   eg, array(array("name" => 'title_name', "match" => '//Title', "use" => '@name'))
     * @return string
     */
    public static function decode_file(
        $file,
        $path = '/',
        $format = 'xml',
        $root = 'RESULT',
        $attributes = array(),
        $namespaces = array(),
        $xsl_keys = array()
        )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('file','path','format','root','attributes','namespaces','xsl_keys'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        if (!is_file($file))
        {
            trigger_error(__METHOD__.' ERROR : invalid input file ('.$file.')', E_USER_NOTICE);
            return "Unkown file '".$file."'";
        }

        $path = htmlspecialchars($path, ENT_COMPAT | ENT_IGNORE, "UTF-8");

        $attrs = '';
        foreach( $attributes as $key => $val )
        {
            $attrs .= ' '.$key.'="'.htmlspecialchars($val, ENT_COMPAT | ENT_IGNORE, "UTF-8").'"';
        }

        $xmlns = $ex = '';
        foreach( $namespaces as $key => $val )
        {
            $xmlns .= ' xmlns:'.$key.'="'.$val.'"';
            if (strlen($ex)) { $ex .= ' '; }
            $ex .= $key;
        }
        if (strlen($ex)) {
            $xmlns .= ' exclude-result-prefixes="'. $ex.'"';
        }

        $keys = '';
        foreach( $xsl_keys as $conf )
        {
            $keys .= '<xsl:key name="'.$conf['name'].'" match="'.$conf['match'].'" use="'.$conf['use'].'" />';
        }

        // sub selection of data from xml file
        $xsl = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"'
    .$xmlns.'>
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />
    '.$keys.'
    <xsl:template match="/">
        '.(($root) ? '<'.$root.$attrs.' select="'.$path.'">' : '').'
            <xsl:copy-of select="'.$path.'" />
        '.(($root) ? '</'.$root.'>' : '').'
    </xsl:template>
</xsl:stylesheet>';

        $transform = array(
            'xml_type'    => 'file'
            , 'xml_value' => $file
            , 'xsl_type'  => 'string'
            , 'xsl_value' => $xsl
            );
        $result = $XSLT_Processor_XSL->transform( $transform );
        if (empty($result)) { $result = '<RESULT/>'; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('transform'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }

        // convert XML to serialized array (format = 'php'|'json')
        if (in_array($format, array('php','json')))
        {
            $result = self::transcode_xml( $result, $format );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }
        }
        return $result;
    }


    /**
     * decode XML as php or json array
     *
     * @param string $xml       xml to decode as array
     * @param string $format    'php' (dflt) | 'json'
     * @return array|string
     */
    public static function transcode_xml( $xml, $format = 'php' )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml','format'),true), E_USER_NOTICE); }

        if (!in_array($format, array('php','json')))
            { trigger_error(__METHOD__.' ERROR : invalid decode format ('.$format.')', E_USER_ERROR); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        if (empty($xml))
        {
            $xml = '<RESULT/>';
        }

        $xsl = '<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:exslt="http://exslt.org/common"
        exclude-result-prefixes="exslt"
        >
    <xsl:include href="'.XSLT_PLUGIN_DIR.'/xsl/util.xsl" />
    <xsl:include href="'.XSLT_PLUGIN_DIR.'/xsl/string.xsl" />
    <xsl:output method="text" encoding="UTF-8" />
    <xsl:template match="/">
        <xsl:call-template name="util-nodeset-to-php">
            <xsl:with-param name="nodes" select="exslt:node-set(/)" />
        </xsl:call-template>
    </xsl:template>
</xsl:stylesheet>';

        $transform = array(
            'xml_type'    => 'string'
            , 'xml_value' => $xml
            , "xsl_type"  => 'string'
            , "xsl_value" => $xsl
            );
        $subresult = $XSLT_Processor_XSL->transform( $transform );
        eval("\$result = array(".$subresult.");");
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('transform'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('subresult'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }

        // convert to json string
        if ($format == 'json')
        {
            $result = json_encode($result);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }
        }

        return $result;
    }


    /**
     * encode array as XML string
     * 'count' attribute added to top level node
     * value format for setting attributes on node data :
     *    array('attributes' => array('attr' => 'val'), 'cdata' => 'any scalar value')
     * SimpleXMLElement(s) result available via reference param 'xml'
     *
     * @param array $array_values       array to encode
     * @param SimpleXMLElement &$xml    passed by reference
     * @param bool $header              true = include xml version header
     * @return string
     */
    public static function encode_array( $array_values, &$xml = null, $header = true )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('array_values'),true), E_USER_NOTICE); }

        if (empty($xml))
            { $xml = new SimpleXMLElement( '<RESULT/>' ); }

        if (is_array($array_values) && empty($array_values['attributes']['count']))
            { $xml->addAttribute( 'count', count($array_values) ); }
        self::encode_array_element( $array_values, $xml );

        $rv = $xml->asXML();
        if (extension_loaded('tidy'))
            { $rv = self::tidy_string( $rv, 'xml' ); }
        if (!$header)
            { $rv = self::strip_declaration( $rv ); }

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * private, recursive method for XML::encode_array
     */
    private static function encode_array_element( $value, &$xml )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('value'),true), E_USER_NOTICE); }

        if (!is_array($value))
        {
            $xml[0] = html_entity_decode($value,ENT_XML1,"UTF-8");
            return;
        }

        if (!empty($value['attributes']) && is_array($value['attributes']))
        {
            foreach( $value['attributes'] as $k => $v )
                { $xml->addAttribute($k, $v); }

            unset($value['attributes']);
        }

        foreach( $value as $key => $val )
        {
            if (is_numeric($key)) { $key = 'key_'.$key; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('key'),true), E_USER_NOTICE); }
            $child = $xml->addChild( $key );
            if (isset($val['attributes']) && is_array($val['attributes']))
            {
                foreach( $val['attributes'] as $k => $v )
                    { $child->addAttribute($k, $v); }
                if (!empty($val['cdata']) && is_scalar($val['cdata']))
                    { self::encode_array_element( $val['cdata'], $child ); }

                unset($val['attributes'],$val['cdata']);
            }
            self::encode_array_element( $val, $child );
        }
    }


}	// end class XSLT_Processor_XML
