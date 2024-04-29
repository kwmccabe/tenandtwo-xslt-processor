<?php
/**
 * XSL
 *
 * usage:
 * global $XSLT_Processor_XSL;
 * if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }
 * $params = array(
 *     "xsl_type"  => "file",
 *     "xsl_value" => "/path/to/transform.xsl",
 *     "xml_type"  => "file",
 *     "xml_value" => "/path/to/input.xml",
 *     "outfile"   => "/path/to/output.txt"
 *     "some_param" => "some value"
 * );
 * $result = $XSLT_Processor_XSL->transform( $params );
 *
 * @see https://www.php.net/manual/en/book.xsl.php
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );

/**
 * include XSL->PHP callback class
 */
//require_once(plugin_dir_path( __FILE__ ).'includes/callback.php');

/**
 * component class provides XSLT functionality
 * see http://php.net/xsl
 */
class XSLT_Processor_XSL
{

    /**
     * class properties
     */
    private $pool;

    /**
     * Run XSLTProcessor->transformToXML or XSLTProcessor->transformToURI
     *
     * @uses XSLT_Processor_XSL::getProcessor()
     * @uses XSLT_Processor_XSL::getXMLError()
     * @uses XSLT_Processor_XSL::releaseProcessor()
     * @uses XSLT_Processor_Util::getMicrotime()
     * @uses XSLT_Processor_XML::strip_declaration
     *
     * @param array $params
     * - params['xsl_type']  : (string) "file" | "string"
     * - params['xsl_value'] : (string) filepath | xsl
     * - params['xml_type']  : (string) "file" | "string"
     * - params['xml_value'] : (string) filepath | xml
     * - params['outfile']   : (string) optional filepath for results
     * - params['some_param']: additional params passed to stylesheet
     *
     * @return string          transform result
     */
    public function transform( $params )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }

        $rv = null;
        $types = array('string','file');
        $xsl_type  = (in_array($params['xsl_type'], $types)) ? $params['xsl_type']  : "string";
        $xsl_value = (!empty($params['xsl_value'])) ? $params['xsl_value'] : null;
        $xml_type  = (in_array($params['xml_type'], $types)) ? $params['xml_type']  : "string";
        $xml_value = (!empty($params['xml_value'])) ? $params['xml_value'] : null;
        $outfile   = (!empty($params['outfile']))   ? $params['outfile']  : null;

        if ($xsl_type == "file" && !is_file($xsl_value))
        {
            $err = 'ERROR : XSL file ('.$xsl_value.') not found';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }
        if ($xml_type == "file" && !is_file($xml_value))
        {
            $err = 'ERROR : XML file ('.$xml_value.') not found';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        // handle errors via getXMLError()
        libxml_use_internal_errors(true);

        // XML DOM object
        $xml = new DomDocument('1.0', 'utf-8');
        ($xml_type == 'string') ? $xml->loadXML($xml_value) : $xml->load($xml_value);
        $errors = libxml_get_errors();
        if (!empty($errors))
        {
            $err = "ERROR : DomDocument->load(xml): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error, $xml_type, $xml_value); }
            libxml_clear_errors();
            trigger_error(__METHOD__." : ".print_r(compact('err','xml_value'),true), E_USER_NOTICE);
            return $err;
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : xml loaded =\n".print_r($xml->saveXML(),true), E_USER_NOTICE); }

        // XSL DOM object
        $xsl = new DomDocument('1.0', 'utf-8');
        ($xsl_type == 'string') ? $xsl->loadXML($xsl_value) : $xsl->load($xsl_value);
        $errors = libxml_get_errors();
        if (!empty($errors))
        {
            $err = "ERROR : DomDocument->load(xsl): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error, $xsl_type, $xsl_value); }
            libxml_clear_errors();
            trigger_error(__METHOD__." : ".print_r(compact('err','xsl_value'),true), E_USER_NOTICE);
            return $err;
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : xsl loaded =\n".print_r($xsl->saveXML(),true), E_USER_NOTICE); }

        // XSLTProcessor from pool
        $idx = $this->getProcessor();
        $xsltproc = $this->pool[$idx]['xslt'];

        // load stylesheet
        $imported = $xsltproc->importStyleSheet($xsl);
        $errors = libxml_get_errors();
        if (!$imported || !empty($errors))
        {
            $err = "ERROR : XSLTProcessor->importStyleSheet(xsl): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error); }
            libxml_clear_errors();
            $this->releaseProcessor( $idx );
            trigger_error(__METHOD__." : ".print_r(compact('err','xsl_value'),true), E_USER_NOTICE);
            return $err;
        }

        // set additional stylesheet params
        $exclude_params = array(
            "xsl_type", "xsl_value", "xml_type", "xml_value" //, "outfile"
            );
        $release_params = array();
        foreach( $params as $key => $val )
        {
            if (in_array($key,$exclude_params))  { continue; }
            if (is_null($val) || is_array($val)) { continue; }
            $xsltproc->setParameter("", $key, strval($val));
            $release_params[$key] = $val;
//if (WP_DEBUG) { trigger_error(__METHOD__." : stylesheet param '$key' = '$val'", E_USER_NOTICE); }
        }

        // run transform
        if (WP_DEBUG) { $starttime = XSLT_Processor_Util::getMicrotime(); }
        if (!empty($outfile))
        {
            $bytes = $xsltproc->transformToURI($xml,$outfile);
            $rv = $outfile;
        }
        else
        {
            $rv = $xsltproc->transformToXML($xml);
            $rv = XSLT_Processor_XML::strip_declaration( $rv );
        }
        if (WP_DEBUG) { $stoptime = XSLT_Processor_Util::getMicrotime(); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }

        // check errors
        $errors = libxml_get_errors();
        if (!empty($errors))
        {
            $err = "ERROR : XSLTProcessor->transform(xml): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error); }
            libxml_clear_errors();
            $this->releaseProcessor( $idx, $release_params );

            $err .= "<br/>xml_value:<pre>\n".htmlentities($xml_value)."</pre>";
            $err .= "<br/>xsl_value:<pre>\n".htmlentities($xsl_value)."</pre>";
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }
        $this->releaseProcessor( $idx, $release_params );

        if (WP_DEBUG && !empty($outfile))
        {
            $msg = __METHOD__." : ";
            $msg .= "\n- XML ("
                . $xml_type
                . ', ' . size_format( ($xml_type == "file") ? filesize($xml_value) : strlen($xml_value) )
                . ") " . ( ($xml_type == "file") ? "'$xml_value'" : '' );
            $msg .= "\n- XSL ("
                . $xsl_type
                . ', ' . size_format( ($xsl_type == "file") ? filesize($xsl_value) : strlen($xsl_value) )
                . ") " . ( ($xsl_type == "file") ? "'$xsl_value'" : '' );
            if (!empty($outfile))
            {
                $msg .= "\n- FILE $bytes bytes written to '$outfile'";
            }
            $msg .= "\n- TIME " . sprintf("%.4f",($stoptime - $starttime)) . " seconds \n";
            trigger_error( $msg, E_USER_NOTICE );
        }

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('rv'),true), E_USER_NOTICE); }
        return $rv;
    }

    /**
     * Validate XSL syntax
     *
     * @uses XSLT_Processor_XSL::getProcessor()
     * @uses XSLT_Processor_XSL::getXMLError()
     * @uses XSLT_Processor_XSL::releaseProcessor()
     *
     * @param array $params
     * - params['xsl_type']  : (string) "file" | "string"
     * - params['xsl_value'] : (string) filepath | xsl
     *
     * @return array          validation warnings (int) errors (int) and message (str)
     */
    public function validateXSL( $params )
    {
        $result = array(
            'warnings' => 0,    // always 0
            'errors'   => 0,
            'message'  => '',
            );
        $types = array('string','file');
        $xsl_type  = (in_array($params['xsl_type'], $types)) ? $params['xsl_type']  : "string";
        $xsl_value = (!empty($params['xsl_value'])) ? $params['xsl_value'] : null;
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xsl_type','xsl_value'),true), E_USER_NOTICE); }

        if ($xsl_type == "file" && !is_file($xsl_value))
        {
            $err = 'ERROR : XSL file ('.$xsl_value.') not found';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return array('errors' => 1, 'message' => $err);
        }

        $rv  = "XSL Syntax OK <br/>\n";
        $rv .= size_format( ($xsl_type == "file") ? filesize($xsl_value) : strlen($xsl_value) );

        // handle errors via getXMLError()
        libxml_use_internal_errors(true);

        // XSL DOM object
        $xsl = new DomDocument('1.0', 'utf-8');
        ($xsl_type == 'string') ? $xsl->loadXML($xsl_value) : $xsl->load($xsl_value);
        $errors = libxml_get_errors();
        if (!empty($errors))
        {
            $err = "ERROR : DomDocument->load(xsl): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error, $xsl_type, $xsl_value); }
            $result = array_merge( $result, array('errors' => count($errors), 'message' => $err) );
            libxml_clear_errors();
            trigger_error(__METHOD__." : ".print_r(compact('err','xsl_value'),true), E_USER_NOTICE);
            return $result;
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : xsl loaded =\n".print_r($xsl->saveXML(),true), E_USER_NOTICE); }

        // get processor
        $idx = $this->getProcessor();
        $xsltproc = $this->pool[$idx]['xslt'];

        // load stylesheet
        $imported = $xsltproc->importStyleSheet($xsl);
        $errors = libxml_get_errors();
        if (!$imported || !empty($errors))
        {
            $err = "ERROR : XSLTProcessor->importStyleSheet(xsl): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error); }
            $result = array_merge( $result, array('errors' => count($errors), 'message' => $err) );
            libxml_clear_errors();
            $this->releaseProcessor( $idx );
            trigger_error(__METHOD__." : ".print_r(compact('err','xsl_value'),true), E_USER_NOTICE);
            return $result;
        }

        $result['message'] = "<p>".$rv."</p>\n";
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result','xsl_value'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * Validate XML syntax
     *
     * $schema_type = 'dtd'; $schema_value = '<!DOCTYPE rootNode SYSTEM "/path/to/validation.dtd">';
     * $schema_type = 'xsd'; $schema_value = '/path/to/validation.xsd';
     * $schema_type = 'rbg'; $schema_value = '/path/to/validation.rng';
     *
     * @uses XSLT_Processor_XSL::getProcessor()
     * @uses XSLT_Processor_XSL::getXMLError()
     * @uses XSLT_Processor_XSL::releaseProcessor()
     *
     * @param array $params
     * - params['xml_type']  : (string) "file" | "string"
     * - params['xml_value'] : (string) filepath | xml
     * - params['schema_type']  : (string) "none" | "dtd" | "xsd" | "rng"
     * - params['schema_value'] : (string) filepath
     *
     * @return array          validation warnings (int) errors (int) and message (str)
     */
    public function validateXML( $params )
    {
        $result = array(
            'warnings' => 0,    // always 0
            'errors'   => 0,
            'message'  => '',
            );
        $xml_types = array('string','file');
        $xml_type  = (in_array($params['xml_type'], $xml_types)) ? $params['xml_type']  : "string";
        $xml_value = (!empty($params['xml_value'])) ? $params['xml_value'] : null;
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml_type','xml_value'),true), E_USER_NOTICE); }

        $schema_types = array('dtd','xsd','rng');
        $schema_type  = (in_array($params['schema_type'], $schema_types)) ? $params['schema_type']  : 'none';
        $schema_value = (!empty($params['schema_value'])) ? $params['schema_value'] : null;
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('schema_type','schema_value'),true), E_USER_NOTICE); }

        if ($xml_type == "file" && !is_file($xml_value))
        {
            $err = 'ERROR : XML file ('.$xml_value.') not found';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return array('errors' => 1, 'message' => $err);
        }

        if (in_array($schema_type, array('xsd','rng')) && !empty($schema_value) && !is_file($schema_value))
        {
            $err = 'ERROR : '.strtoupper($schema_type).' schema file ('.$schema_value.') not found';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return array('errors' => 1, 'message' => $err);
        }

        $rv  = "XML Syntax OK <br/>\n";
        $rv .= size_format( ($xml_type == "file") ? filesize($xml_value) : strlen($xml_value) );
        $err = "";

        // handle errors via getXMLError()
        libxml_use_internal_errors(true);

        // XML DOM object
        $xml = new DomDocument('1.0', 'utf-8');
        if ($schema_type == 'dtd') {
            $err .= "DomDocument->validate() : VALID";  // DTD defined in xml_value
            $xml->validateOnParse = true;
        }
        ($xml_type == 'string') ? $xml->loadXML($xml_value) : $xml->load($xml_value);
        $errors = libxml_get_errors();
        if (!empty($errors))
        {
            $err = "ERROR : DomDocument->load(xml): ";
            foreach( $errors as $error )
                { $err .= $this->getXMLError($error, $xml_type, $xml_value); }
            $result = array_merge( $result, array('errors' => count($errors), 'message' => $err) );
            libxml_clear_errors();
            trigger_error(__METHOD__." : ".print_r(compact('err','xml_value'),true), E_USER_NOTICE);
            return $result;
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : xml loaded =\n".print_r($xml->saveXML(),true), E_USER_NOTICE); }

        $valid = false;
        if (!empty($schema_value))
        {
            //if (in_array($schema_type, array('none','dtd'))) {}
            if ($schema_type == 'xsd')
            {
                $err .= "DomDocument->schemaValidate( XSD ) : ";
                $valid = $xml->schemaValidate( $schema_value );
            }
            if ($schema_type == 'rng')
            {
                $err .= "DomDocument->relaxNGValidate( RNG ) : ";
                $valid = $xml->relaxNGValidate( $schema_value );
            }
            if ($valid) { $err .= "VALID"; }

            $errors = libxml_get_errors();
            if (!empty($errors)) {
                foreach( $errors as $error )
                    { $err .= $this->getXMLError($error, $xml_type, $xml_value); }
                $result['errors'] = count($errors);
                libxml_clear_errors();
            }
        }

        $result['message'] = "<p>".$rv."</p>\n";
        if ($err) { $result['message'] .= "<p>".$err."</p>\n"; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('result','xml_value'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * Replaces default handling of transform errors
     * after calling libxml_use_internal_errors(true)
     *
     * @param array $error       array of LibXMLError objects returned by libxml_get_errors()
     * @param string $doc_type   xml_type or xsl_type
     * @param string $doc_value  xml_value or xsl_value
     * @return string            error info
     */
    protected function getXMLError($error, $doc_type = null, $doc_value = null)
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact("error","doc_type","doc_value"),true), E_USER_NOTICE); }
        $newline = "<br/>\n";
        $doc_array = array();
        if ($doc_type == 'string')
        {
            $doc_array = explode("\n", $doc_value);
        }
        if ($doc_type == 'file')
        {
            $doc = @fopen($doc_value, "r");
            if (!is_resource($doc))
            {
                $err = 'ERROR : fopen \'' . $doc_value . '\' failed';
                trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
                return $err;
            }

            $cnt = 0;
            while (!feof($doc))
            {
                $line = fgets($doc);
                if ($cnt == $error->line - 1)
                    { $doc_array[$error->line - 1] = $line; break; }
                $cnt++;
            }

            if (is_resource($doc) && !@fclose($doc))
            {
                $err = 'ERROR : fclose \'' . $doc_value . '\' failed';
                trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
                return $err;
            }
        }

        $rv = $newline . str_repeat('-',40);
        if (!empty($doc_array[$error->line - 1]))
        {
            $rv .= $newline . htmlentities($doc_array[$error->line - 1]);
            $rv .= $newline . str_repeat('-', $error->column) . "^";
        }

        switch ($error->level)
        {
            case LIBXML_ERR_WARNING:
                $rv .= $newline."Warning " . $error->code . ": ";
                break;
            case LIBXML_ERR_ERROR:
                $rv .= $newline."Error " . $error->code . ": ";
                break;
            case LIBXML_ERR_FATAL:
                $rv .= $newline."Fatal Error " . $error->code . ": ";
                break;
        }

        $rv .= trim($error->message) .
            $newline."Line: " . $error->line .
            $newline."Column: " . $error->column;
        if ($error->file)
            { $rv .= $newline."File: " . $error->file; }
        $rv .= $newline . str_repeat('-',40) . $newline;

        return $rv;
    }

    /**
     * Return key for XSLTProcessor in pool.
     * Returns new, or unlocked if available.
     *
     * @return integer     key for processor pool
     */
    private function getProcessor()
    {

        $idx = -1;
        if (empty($this->pool)) { $this->pool = array(); }

        reset($this->pool);
        foreach( $this->pool as $idx => $val )
        {
            if (!$this->pool[$idx]['locked'])
            {
                $this->pool[$idx]['locked'] = true;
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('idx'),true), E_USER_NOTICE); }
                return $idx;
            }
        }

        $idx = count($this->pool);
        $this->pool[] = array(
            "locked" => true
            , "xslt" => new XSLTProcessor()
            , "exslt" => false
            );

        if (!is_object($this->pool[$idx]['xslt']))
        {
            $err = 'ERROR : missing object this->pool['.$idx.'][\'xslt\']';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        global $XSLT_PLUGIN_PHP_FUNCTIONS;
        $allowed = array('XSLT_Callback');                                          // required
        $allowed = array_merge( $XSLT_PLUGIN_PHP_FUNCTIONS ?? array(), $allowed );  // optional

        $this->pool[$idx]['xslt']->registerPHPFunctions($allowed);
        $this->pool[$idx]['exslt'] = $this->pool[$idx]['xslt']->hasExsltSupport();

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('idx'),true), E_USER_NOTICE); }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r($this->pool[$idx],true), E_USER_NOTICE); }
        return $idx;
    }

    /**
     * Unlock specific XSLTProcessor in pool
     *
     * @param integer $idx key for processor pool
     * @return bool            true for success ; false for error
     */
    private function releaseProcessor( $idx, $release_params = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('idx','release_params'),true), E_USER_NOTICE); }

        if (!$this->pool[$idx]['locked'])
        {
            $err = 'ERROR : this->pool['.$idx.'] missing or not locked';
            trigger_error(__METHOD__." : ".print_r(compact('err'),true), E_USER_NOTICE);
            return $err;
        }

        foreach( $release_params as $key => $val )
            { $this->pool[$idx]['xslt']->removeParameter("", $key); }
        $this->pool[$idx]['locked'] = false;
        return;
    }

    /**
     * Unset all XSLTProcessors in pool
     */
    public function freeProcessors()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : count(pool) = ".count($this->pool)." )", E_USER_NOTICE); }

        reset($this->pool);
        foreach( $this->pool as $idx => $val )
            { unset($this->pool[$idx]['xslt']); }
        $this->pool = array();
    }

} // end class XSLT_Processor_XSL
