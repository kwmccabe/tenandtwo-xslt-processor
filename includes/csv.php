<?php
/**
 * CSV
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );


/**
 * Decodes CSV strings and files as XML
 */
class XSLT_Processor_CSV
{

    /**
     * decode CSV file resource as XML
     *
     * fgetcsv( resource $stream, ?int $length = null, string $separator = ",", string $enclosure = "\"", string $escape = "\\" ): array|false
     *
     * <table rows="1" cols="2">
     *   <tr row="1" cols="2">
     *     <td row="1" col="1">{data}</td>
     *     <td row="1" col="2">{data}</td>
     *   </tr>
     * </table>
     *
     * @param string $csv       : data
     * @param string $path      : xpath expression
     * @param string $root      : 'RESULT' (dflt) | nodename
     * @param array $attributes : key="val" added to root node
     * @return string
     */
    private static function decode_csv( $fp, $separator = ",", $enclosure = "\"", $escape = "\\" )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('fp','separator','enclosure','escape'),true), E_USER_NOTICE); }

        if (empty($fp))
        {
            $err = "Missing input file resource";
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        // skip bom, if present
        $bom = "\xef\xbb\xbf";
        if (fgets($fp, 4) !== $bom) { rewind($fp); }
        // fix newlines
        $auto = (ini_get('auto_detect_line_endings') || version_compare(PHP_VERSION, '8.1.0') >= 0);
        if (!$auto) { ini_set('auto_detect_line_endings',TRUE); }

        // get lines
        $lines = array();
        $cols = 0;
        while(!feof($fp) && ($line = fgetcsv( $fp, 10000, $separator, $enclosure, $escape )) !== false)
        {
            if (empty($line)) { continue; }
            if (count($line) == 1 && empty($line[0])) { continue; }  // skip empty ???
            if ($cols < count($line)) { $cols = count($line); }
            $lines[] = $line;
        }

        if (!$auto) { ini_set('auto_detect_line_endings',FALSE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('fp','lines'),true), E_USER_NOTICE); }

        // convert lines to xml
        $result = '<table rows="'.count($lines).'" cols="'.$cols.'">';
        if (WP_DEBUG) { $result .= "\n"; }
        foreach( $lines as $rownum => $row )
        {
            $result .= '<tr row="'.($rownum+1).'" cols="'.count($row).'">';
            foreach( $row as $i => $col ) {
                $result .= '<td row="'.($rownum+1).'" col="'.($i + 1).'">';
                $result .= htmlentities($col, ENT_NOQUOTES|ENT_XML1, 'UTF-8', false);
                $result .= '</td>';
            }
            $result .= '</tr>';
            if (WP_DEBUG) { $result .= "\n"; }
        }
        $result .= '</table>';

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('fp','lines','result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * decode CSV string as XML
     *
     * @param string $csv       : data
     *
     * @param array $read_params
     * - separator  : string, dflt ","  (use "\t" for .tsv data)
     * - enclosure  : string, dflt "\""
     * - escape     : string, dflt "\\"
     *
     * @param array $write_params
     * - key_row    : int, dflt 0
     * - col        : int|AZ|string if key_row, dflt 0
     * - key_col    : int|AZ|string if key_row, dflt 0
     * - key        : int/string, dflt ''
     * - row        : int, dflt 0
     * - class      : string, dflt ''
     *
     * @return XML string
     */
    public static function decode_string( $csv, $read_params = array(), $write_params = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('csv','read_params','write_params'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        if (empty($csv))
        {
            $err = "Missing input csv value";
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        $read_params = array_merge(array(
            'separator' => ",",
            'enclosure' => "\"",
            'escape'    => "\\",
            ), $read_params);
        $write_params = array_merge(array(
            'key_row'   => 0,
            'col'       => 0,
            'key_col'   => 0,
            'key'       => '',
            'row'       => 0,
            'class'     => 'table',
            ), $write_params);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('read_params','write_params'),true), E_USER_NOTICE); }

        if (WP_DEBUG) { $starttime = XSLT_Processor_Util::getMicrotime(); }
        $fp = fopen('data://text/plain,' . $csv, 'r');
        $xml = self::decode_csv( $fp, $read_params['separator'], $read_params['enclosure'], $read_params['escape'] );
        fclose($fp);
        if (WP_DEBUG) { $stoptime = XSLT_Processor_Util::getMicrotime(); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }

        if (WP_DEBUG && $starttime && $stoptime) {
            $msg = __METHOD__." : ";
            $msg .= "\n- CSV (string, " . size_format( strlen($csv) ) . ") ";
            $msg .= "\n- TIME " . sprintf("%.4f",($stoptime - $starttime)) . " seconds \n";
            trigger_error( $msg, E_USER_NOTICE );
        }

        $transform = array(
            'xml_type'    => 'string'
            , 'xml_value' => $xml
            , 'xsl_type'  => 'file'
            , 'xsl_value' => XSLT_PLUGIN_DIR.'xsl/csv-select.xsl'
            // stylehseet params
            , 'key_row' => $write_params['key_row']
            , 'col'     => $write_params['col']
            , 'key_col' => $write_params['key_col']
            , 'key'     => $write_params['key']
            , 'row'     => $write_params['row']
            , 'class'   => $write_params['class']
            );
        $result = $XSLT_Processor_XSL->transform( $transform );
        if (empty($result)) { $result = '<RESULT/>'; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('transform'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * decode CSV file as XML
     *
     * @param string $csv       : local filepath
     *
     * @param array $read_params
     * - separator  : string, dflt ","  (use "\t" for .tsv files)
     * - enclosure  : string, dflt "\""
     * - escape     : string, dflt "\\"
     *
     * @param array $write_params
     * - key_row    : int, dflt 0
     * - col        : int|AZ|string if key_row, dflt 0
     * - key_col    : int|AZ|string if key_row, dflt 0
     * - key        : int/string, dflt ''
     * - row        : int, dflt 0
     * - class      : string, dflt ''
     *
     * @return XML string
     */
    public static function decode_file( $csv, $read_params = array(), $write_params = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('csv','read_params','write_params'),true), E_USER_NOTICE); }

        global $XSLT_Processor_XSL;
        if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

        if (!is_file($csv))
        {
            $err = "Missing input csv file '".$csv."'";
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        $read_params = array_merge(array(
            'separator' => ",",
            'enclosure' => "\"",
            'escape'    => "\\",
            ), $read_params);
        $write_params = array_merge(array(
            'key_row'   => 0,
            'col'       => 0,
            'key_col'   => 0,
            'key'       => '',
            'row'       => 0,
            'class'     => 'table',
            ), $write_params);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('read_params','write_params'),true), E_USER_NOTICE); }

        if (WP_DEBUG) { $starttime = XSLT_Processor_Util::getMicrotime(); }
        $fp = fopen($csv, 'r');
        $xml = self::decode_csv( $fp, $read_params['separator'], $read_params['enclosure'], $read_params['escape'] );
        fclose($fp);
        if (WP_DEBUG) { $stoptime = XSLT_Processor_Util::getMicrotime(); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml'),true), E_USER_NOTICE); }

        if (WP_DEBUG && $starttime && $stoptime) {
            $msg = __METHOD__." : ";
            $msg .= "\n- CSV (file, " . size_format( filesize($csv) ) . ") '$csv'";
            $msg .= "\n- TIME " . sprintf("%.4f",($stoptime - $starttime)) . " seconds \n";
            trigger_error( $msg, E_USER_NOTICE );
        }

        $transform = array(
            'xml_type'    => 'string'
            , 'xml_value' => $xml
            , 'xsl_type'  => 'file'
            , 'xsl_value' => XSLT_PLUGIN_DIR.'xsl/csv-select.xsl'
            // stylehseet params
            , 'key_row' => $write_params['key_row']
            , 'col'     => $write_params['col']
            , 'key_col' => $write_params['key_col']
            , 'key'     => $write_params['key']
            , 'row'     => $write_params['row']
            , 'class'   => $write_params['class']
            );
        $result = $XSLT_Processor_XSL->transform( $transform );
        if (empty($result)) { $result = '<RESULT/>'; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('transform'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result'),true), E_USER_NOTICE); }
        return $result;
    }


}	// end class XSLT_Processor_CSV
