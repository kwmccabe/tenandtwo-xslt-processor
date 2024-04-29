<?php
/**
 * Util
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
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
     * @return float    : current microtime with milliseconds
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
     * @return string   : XML
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
	 * @param array|string $input   : paths to check
     * @return array                : array of valid realpaths
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
        foreach( $result as $key => $path )
        {
            $result[$key] = realpath($path);
            if (empty($path) || empty($result[$key]))
                { unset($result[$key]); }
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('input','result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * get array of local files under path
     * OR an XML list/fragment if format="xml":
     *     <file basename="{filename}" bytes="{filesize}">{filepath}</file>
     * final search is restricted to ABSPATH or search_paths
     *
     * @param string $path          : local directory path
     * @param string $match         : preg_match for full filepath
     * @param string $levels        : recursive
     * @param array $search_paths   :
     * @param array $format         : php (dflt) | xml
     * @return mixed                : array or XML with valid realpaths
     */
    public static function getFileListingLocal( $path, $match = '.xml$', $levels = 1, $search_paths = array(), $format = 'php' )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('path','match','levels','search_paths','format'),true), E_USER_NOTICE); }

        if (strpos($path,'__') !== false)
        {
            $search  = array('__WP_HOME_DIR__', '__WP_CONTENT_DIR__', '__XSLT_PLUGIN_DIR__');
            $replace = array( ABSPATH,           WP_CONTENT_DIR,       XSLT_PLUGIN_DIR);
            $path = str_replace($search, $replace, $path);
        }
        if (!empty($search_paths) && !is_array($search_paths))
            { $search_paths = array($search_paths); }

        $dir_path = realpath($path);
        $valid_path = $dir_path && is_dir($dir_path) && is_readable($dir_path);
        if ($valid_path && strpos($dir_path,ABSPATH) === false)
        {
            $valid_path = false;
            foreach( $search_paths as $search_path )
            {
                if (strpos($dir_path,$search_path) === false)
                    { continue; }
                $valid_path = true;
                break;
            }
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('path','dir_path','valid_path'),true), E_USER_NOTICE); }

        $result = array();
        if ($valid_path)
        {
//if (WP_DEBUG) { trigger_error(__METHOD__." : OPENDIR( $dir_path )", E_USER_NOTICE); }
            if ($dir = opendir($dir_path))
            {
                while (($file = readdir($dir)) !== false) {
                    $fullpath = rtrim($dir_path,'/').'/'.$file;

                    if ('.' === $file[0] || !is_readable($fullpath))
                        { continue; }
                    if (is_dir($fullpath) && $levels > 1)
                    {
                        $subresult = self::getFileListingLocal( $fullpath, $match, $levels-1 );
                        if (is_array($subresult))
                            { $result = array_merge( $subresult, $result ); }
                        continue;
                    }
                    if (!pathinfo($file, PATHINFO_EXTENSION))
                        { continue; }
                    if ($match && !preg_match('/'.$match.'/i', $fullpath))
                        { continue; }

                    $result[] = $fullpath;
                }
                closedir($dir);
            }
        }
        else
        {
            foreach( $search_paths as $search_path )
            {
                if (empty($search_path)) { continue; }
                $fullpath = rtrim($search_path,'/').'/'.$path;

                if (!($fullpath && is_dir($fullpath) && is_readable($fullpath)))
                    { continue; }

                $subresult = self::getFileListingLocal( $fullpath, $match, $levels );
                if (is_array($subresult))
                    { $result = array_merge( $subresult, $result ); }
            }
        }
        if (!empty($result))
        {
            $result = array_unique( $result, SORT_STRING );
            sort( $result, SORT_STRING );
        }

        if ($format != 'xml')
        {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('path','match','levels','result'),true), E_USER_NOTICE); }
            return $result;
        }

        $rv = '';
        foreach ($result as $file) {
            $basename = pathinfo($file,  PATHINFO_BASENAME);
            $bytes    = filesize($file);
            $rv .= '<file'
                . ' basename="'.$basename.'"'
                . ' bytes="'.$bytes.'"'
                .'>';
            $rv .= html_entity_decode($file,ENT_XML1,"UTF-8");
            $rv .= '</file>';
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('path','match','levels','rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * check local file exists
     * __WP_HOME_DIR__, __WP_CONTENT_DIR__ and __XSLT_PLUGIN_DIR__ automatically replaced
     *
     * @see file.xsl, template name="file-exists-local"
     * @param string $file          : local path passed to realpath()
     * @param array $search_paths   :
     * @return string               : realpath or empty
     */
    public static function getFileExistsLocal( $file, $search_paths = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('file','search_paths'),true), E_USER_NOTICE); }

        $file = trim($file);
        if (strpos($file,'__') !== false)
        {
            $search  = array('__WP_HOME_DIR__', '__WP_CONTENT_DIR__', '__XSLT_PLUGIN_DIR__');
            $replace = array( ABSPATH,           WP_CONTENT_DIR,       XSLT_PLUGIN_DIR);
            $file = str_replace($search, $replace, $file);
        }

        if (strpos($file,'/') === 0 && is_file($file))
        {
            return realpath( $file ) ? realpath( $file ) : $file;
        }

        if (!is_array($search_paths)) { $search_paths = array($search_paths); }
        foreach( $search_paths as $path )
        {
            if (empty($path)) { continue; }
            $fullpath = rtrim($path,'/').'/'.$file;
            if (is_file($fullpath))
            {
                return realpath( $fullpath ) ? realpath( $fullpath ) : $fullpath;
            }
        }
        return '';
    }

    /**
     * retrieve local file body
     *
     * @param string $file              : local path passed to file_get_contents()
     * @param integer $cache_minutes    : cache_minutes for wp transient
     * @return string                   : page body
     */
    public static function getLocalFile( $file, $cache_minutes = 1 )
    {
        if (is_file($file) && is_readable($file))
        {
            return file_get_contents( $file );
        }
        return '';
    }


    /**
     * check remote file exists
     *
     * @see file.xsl, template name="file-exists-remote"
     * @param string $url   : remote path passed to cURL
     * @return string       : url or empty
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
     * @param string $url               : remote path passed to wp_remote_get()
     * @param integer $cache_minutes    : >0 for set_transient(), else delete_transient()
     * @return string                   : response body
     */
    public static function getRemoteFile( $url, $cache_minutes = 1 )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','cache_minutes'),true), E_USER_NOTICE); }

        $cache_params = array( 'method' => 'md5', 'data' => $url );
        $cache_key = self::getHash( $cache_params );
        $body = get_transient( $cache_key );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','cache_key','body'),true), E_USER_NOTICE); }

        if ($body !== false)
        {
            if ($cache_minutes > 0)
            {
if (WP_DEBUG) { trigger_error(__METHOD__." : CACHE GET : $cache_key", E_USER_NOTICE); }
                return $body;
            }
            else
            {
if (WP_DEBUG) { trigger_error(__METHOD__." : CACHE DELETE : $cache_key", E_USER_NOTICE); }
                delete_transient( $cache_key );
            }
        }

        $request_args = array(
            'timeout' => 30     // pref ???
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
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('url','response_code','body'),true), E_USER_NOTICE); }

        if (0 < $cache_minutes && $response_code < 400)
        {
if (WP_DEBUG) { trigger_error(__METHOD__." : CACHE SET : $cache_key", E_USER_NOTICE); }
            set_transient( $cache_key, $body, 60 * $cache_minutes );
        }
        return $body;
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
     * remove non-utf8 chars from file
     * @param string $infile    : local filepath
     * @param string $outfile   : local filepath, dflt = $infile
     * @return string           : $outfile | false
     */
    public static function utf8_clean_file( $infile, $outfile = '' )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('infile','outfile'),true), E_USER_NOTICE); }
        if (!is_file($infile))
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
     * @see https://developer.wordpress.org/reference/functions/wp_check_invalid_utf8/
     *
     * @param string $value : string to clean
     * @return string       :
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
