<?php
/**
 * Util
 *
 * @package           tenandtwo-plugins
 * @subpackage        xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );


class XSLT_Processor_Util
{

    /**
     * debug util for timing
     *
     * <code>
     * $starttime = XSLT_Processor_Util::getMicrotime();
     * // ...timed code...
     * $stoptime = XSLT_Processor_Util::getMicrotime();
     * $runtime = sprintf("%.4f",($stoptime - $starttime)) . " seconds";
     * </code>
     *
     * @see date.xsl, template name="date-microtime"
     * @param none
     * @return float          current microtime with milliseconds
     */
    public static function getMicrotime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * return date($params['format'], $params['time'] + (strtotime($params['shift']) - time()));
     * - or -
     * return date($params['format'], strtotime($params['value']) + (strtotime($params['shift']) - time()));
     *
     * @see http://www.php.net/manual/en/function.date.php
     * @see http://www.php.net/manual/en/function.strtotime.php
     *
     * @see date.xsl, template name="date-format"
     * @param array $params
     * - time  : int
     * - value : datetime string
     * - shift : string, eg "+1 hours"
     * - format : string, eg "Y-m-d H:i:s"
     * @return string  XML
     */
    public static function getDateTime( $params )
    {
        if (empty($params['time']))   { $params['time']   = 0; }
        if (empty($params['value']))  { $params['value']  = date("Y-m-d H:i:s"); }
        if (empty($params['shift']))  { $params['shift']  = ''; }
        if (empty($params['format'])) { $params['format'] = "Y-m-d H:i:s"; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }

        $value = (!empty($params['time'])) ? $params['time'] : strtotime($params['value']);
        $shift = (empty($params['shift'])) ? 0 : strtotime($params['shift']) - time();
        $dt = $value + $shift;
        $rv = date($params['format'], $dt);

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('value','shift','dt'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : date('Y-m-d H:i:s',value) = ".date("Y-m-d H:i:s",$value), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : date('Y-m-d H:i:s',dt) = ".date("Y-m-d H:i:s",$dt), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }
        return $rv;
    }


    /**
     * get array of valid, local realpaths from string|array of path(s)
	 * @param array|string $input   paths to check
     * @return array                array of valid realpaths
     */
    public static function getRealPaths( $input )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('input'),true), E_USER_NOTICE); }

        $delim = '|';
        $valid = 'A-z0-9-_+\.\/:"*?<>"=';
        // to string
        if (is_array($input))
            { $input = join($delim, $input); }
        $input = preg_replace("|[^$valid]+|", $delim, $input);
        // to array
        $result = explode($delim, $input);
        foreach ($result as $key => $path)
        {
            $result[$key] = realpath($path);
            if (empty($path) || empty($result[$key]))
                { unset($result[$key]); }
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('input','result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * check local file exists
     * __DOCUMENT_ROOT__, __WP_CONTENT_DIR__ and __XSLT_PLUGIN_DIR__ automatically replaced
     *
     * @see file.xsl, template name="file-exists-local"
     * @param string : local path passed to realpath()
     * @return string : realpath or empty
     */
    public static function getFileExistsLocal( $file, $search_paths = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('file','search_paths'),true), E_USER_NOTICE); }

        if (strpos($file,'__') !== false)
        {
            $search  = array('__DOCUMENT_ROOT__', '__WP_CONTENT_DIR__', '__XSLT_PLUGIN_DIR__');
            $replace = array($_SERVER['DOCUMENT_ROOT'], WP_CONTENT_DIR, XSLT_PLUGIN_DIR);
            $file = str_replace($search, $replace, $file);
        }

        if (file_exists($file))
        {
            return realpath( $file ) ? realpath( $file ) : $file;
        }

        foreach ($search_paths as $path)
        {
            if (empty($path)) { continue; }
            if (file_exists($path."/".$file))
            {
                $file = $path."/".$file;
                return realpath( $file ) ? realpath( $file ) : $file;
            }
        }
        return '';
    }

    /**
     * check remote file exists
     *
     * @see file.xsl, template name="file-exists-remote"
     * @param string : remote path passed to cURL
     * @return string : url or empty
     */
    public static function getFileExistsRemote( $url )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url'),true), E_USER_NOTICE); }

        $response = wp_remote_head( $url );
        if (is_wp_error( $response ))
        {
            $err = $response->get_error_message();
            trigger_error(__METHOD__." : wp_remote_head ERROR : ".print_r($err,true), E_USER_NOTICE);
            return print_r($err,true);
        }
        $response_code = wp_remote_retrieve_response_code( $response );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','response_code','response'),true), E_USER_NOTICE); }
        return ($response_code < 400) ? $url : '';
    }


    /**
     * retrieve remote file body
     *
     * @param string  : remote path passed to cURL
     * @param integer : cache_minutes for wp transient
     * @return string : page body
     */
    public static function getRemoteFile( $url, $cache_minutes = 1 )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','cache_minutes'),true), E_USER_NOTICE); }

        if ($cache_minutes > 0)
        {
            $cache_params = array( 'method' => 'md5', 'data' => $url );
            $cache_key = self::getHash( $cache_params );
            $body = get_transient( $cache_key );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','cache_key','body'),true), E_USER_NOTICE); }
            if ($body !== false)
            {
if (WP_DEBUG) { trigger_error(__METHOD__." : CACHE GET : $cache_key", E_USER_NOTICE); }
                return $body;
            }
        }

        $request_args = array(
            'timeout' => 30
            );
        $response = wp_remote_get( $url, $request_args );
        if (is_wp_error( $response ))
        {
            $err = $response->get_error_message();
            trigger_error(__METHOD__." : wp_remote_get ERROR : ".print_r($err,true), E_USER_NOTICE);
            return print_r($err,true);
        }
        $response_code = wp_remote_retrieve_response_code( $response );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','response_code','response'),true), E_USER_NOTICE); }

        $body = wp_remote_retrieve_body( $response );
        $body = self::utf8_clean( $body );
        //$body = self::removeXmlDeclaration( $body );
        //$body = self::removeXmlNamespaces( $body );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','response_code','body'),true), E_USER_NOTICE); }

        if (0 < $cache_minutes && $response_code < 400)
        {
if (WP_DEBUG) { trigger_error(__METHOD__." : CACHE SET : $cache_key", E_USER_NOTICE); }
            set_transient( $cache_key, $body, 60 * $cache_minutes );
        }
        return $body;
    }

// function custom_timeout_extend( $time ) { return 20; }
// add_filter( 'http_request_timeout', 'custom_timeout_extend' );


    /**
     * convert bytes to human-readable string, eg "1.23 MB"
     *
     * @see util.xsl, template name="util-byte-size"
     * @param integer $bytes       integer value to convert
     * @return string              file size string, eg, "12.34 KB"
     */
    public static function getByteSize( $bytes )
    {
        $label_arr = array(" bytes"," KB"," MB"," GB"," TB"," PB"," Exabytes"," Zettabytes"," Yottabytes"," Brontobytes"," Geopbytes");
        $label_idx = 0;
        $cur_bytes = intval($bytes);
        while ($cur_bytes >= 1024 && !empty($label_arr[$label_idx+1]))
            { $label_idx++; $cur_bytes /= 1024; }
        $rv = round($cur_bytes,2).$label_arr[$label_idx];
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('bytes','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * @see util.xsl, template name="util-hash"
     * @param array $params
     * - method : string, dflt=md5, sha256, sha384, sha512, ...
     * - data   : string
     * - raw_output : bool
     * @return string
     */
    public static function getHash( $params )
    {
        if (empty($params['method'])) { $params['method'] = "md5"; }
        if (empty($params['data']))   { $params['data'] = ''; }
        $params['raw_output'] = (!empty($params['raw_output']) && $params['raw_output'] != 'false');

        $rv = hash( $params['method'], $params['data'], $params['raw_output'] );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * remove header : < ? xml version ...
     * removes one (1) max
     *
     * @param string $xml    xml value
     * @return string        xml without version header
     */
    public static function removeXmlDeclaration( $xml )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }
        $xml = preg_replace('|<\?xml[^>]+\?>|i', '', $xml, 1);
        return trim( $xml );
    }

    /**
     * remove header : <!DOCTYPE ... >
     * NODE: does not work with nested, eg <!ELEMENT>
     *
     * @param string $xml    xml value
     * @return string        xml without DOCTYPE header
     */
    public static function removeXmlDoctype( $xml )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }
        $xml = preg_replace('|<\!DOCTYPE[^>]+>|i', '', $xml, 1);
        return trim( $xml );
    }

    /**
     * remove xmlns="uri"
     * remove xmlns:key="uri"
     * change <key:nodename> -to- <nodename>
     *
     * @param string $xml    xml value
     * @return string        xml without xmlns attributes
     */
    public static function removeXmlNamespaces( $xml )
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
     * remove non-utf8 chars from file
     */
    public static function utf8_clean_file( $infile, $outfile = '' )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('infile','outfile'),true), E_USER_NOTICE); }
        if (!file_exists($infile))
            { trigger_error(__METHOD__.' ERROR : invalid input file ('.$infile.')', E_USER_ERROR); }
        if (empty($outfile))
            { $outfile = $infile; }

        $utf8_with_bom = chr(239).chr(187).chr(191);
        $tmpfile = $infile.".tmp";

        $fo = fopen($tmpfile, 'w+');
        $fi = fopen($infile, 'r');
        while ($line = fgets($fi))
        {
            if (strpos($line,$utf8_with_bom) === 0) { $line = substr($line,3); }
            $line = preg_replace_callback(XSLT_Processor_Util::utf8_clean_regex, "XSLT_Processor_Util::utf8_clean_callback", $line);
            fwrite($fo, $line);
        }
        fclose($fi);
        fclose($fo);

        return (rename( $tmpfile, $outfile )) ? $outfile : false;
    }

    /**
     * remove non-utf8 chars from string
     * uses WP function if 'iconv' is available to strip bad chars
     *
     * @see https://developer.wordpress.org/reference/functions/wp_check_invalid_utf8/
     */
    public static function utf8_clean( $value )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('value'),true), E_USER_NOTICE); }

        if (function_exists( 'iconv' )) {
            return wp_check_invalid_utf8( $value, true );
        }
        return preg_replace_callback(XSLT_Processor_Util::utf8_clean_regex, "XSLT_Processor_Util::utf8_clean_callback", $value);
    }

    /**
     * allow cntl-chars
        |[\xC2-\xDF][\x80-\xBF]            #   U+0080 -   U+07FF
     * disallow cntl-chars
        | \xC2[\xA0-\xBF]                  #   U+00A0 -   U+00BF
        |[\xC3-\xDF][\x80-\xBF]            #   U+00C0 -   U+07FF
     */
    const utf8_clean_regex = '/
        (\x9 | \xA | \xD                   #   U+0009, U+000A, U+000D
        |[\x20-\x7F]                       #   U+0000 -   U+007F
        |[\xC2-\xDF][\x80-\xBF]            #   U+0080 -   U+07FF
        | \xE0[\xA0-\xBF][\x80-\xBF]       #   U+0800 -   U+0FFF
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} #   U+1000 -   U+CFFF
        | \xED[\x80-\x9F][\x80-\xBF]       #   U+D000 -   U+D7FF
        | \xF0[\x90-\xBF][\x80-\xBF]{2}    #  U+10000 -  U+3FFFF
        |[\xF1-\xF3][\x80-\xBF]{3}         #  U+40000 -  U+FFFFF
        | \xF4[\x80-\x8F][\x80-\xBF]{2})   # U+100000 - U+10FFFF
        |(\xE0[\xA0-\xBF]                  #   U+0800 -   U+0FFF (invalid)
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]    #   U+1000 -   U+CFFF (invalid)
        | \xED[\x80-\x9F]                  #   U+D000 -   U+D7FF (invalid)
        | \xF0[\x90-\xBF][\x80-\xBF]?      #  U+10000 -  U+3FFFF (invalid)
        |[\xF1-\xF3][\x80-\xBF]{1,2}       #  U+40000 -  U+FFFFF (invalid)
        | \xF4[\x80-\x8F][\x80-\xBF]?)     # U+100000 - U+10FFFF (invalid)
        |(.)                               # invalid 1-byte
        /xs';
    public static function utf8_clean_callback( $matches )
    {
    /*  // default process
        if (isset($matches[2]) || isset($matches[3])) {
            return "\xEF\xBF\xBD";  // Unicode REPLACEMENT CHARACTER
        }
        return $matches[1];
    */
        // Invalid byte of the form 11xxxxxx.  Encode as 11000011 10xxxxxx.
        if (isset($matches[3])) {
            return '';
            //return "\xC3".chr(ord($matches[3])-64);
        }
        // Invalid byte of the form 10xxxxxx.  Encode as 11000010 10xxxxxx.
        if (isset($matches[2])) {
            return "\xC2".$matches[2];
        }
        // Valid byte sequence. Return unmodified.
        return $matches[1];
    }

} // end class XSLT_Processor_Util
