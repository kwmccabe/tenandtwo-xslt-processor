<?php
/**
 * Sample callback function for XSLT
 *
 * add this file to tenandtwo-xslt-functions.php
 * ----
 *   require_once plugin_dir_path( __FILE__ ) . 'includes/sample-xslt-functions.php';
 * ----
 *
 * sample-xslt-functions.xsl
 * ----
 *   <?xml version="1.0" encoding="utf-8"?>
 *   <xsl:stylesheet version="1.0"
 *       xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 *       xmlns:php="http://php.net/xsl"
 *       exclude-result-prefixes="php"
 *       >
 *       <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
 *
 *       <xsl:param name="param1" />
 *       <xsl:param name="param2">default</xsl:param>
 *
 *       <xsl:template match="/">
 *
 *           <p>convert_uuencode: </p>
 *           <ul>
 *               <li>
 *     <xsl:value-of select="php:functionString('convert_uuencode', string($param1))" />
 *               </li>
 *           </ul>
 *
 *           <p>convert_uuencode(param2): </p>
 *           <ul>
 *               <li>
 *     <xsl:value-of select="php:functionString('convert_uuencode', string($param2))" />
 *               </li>
 *           </ul>
 *
 *           <p>xslt_function_sample(param1,param2): </p>
 *     <xsl:copy-of select="php:function('xslt_function_sample', string($param1), string($param2))" />
 *
 *       </xsl:template>
 *   </xsl:stylesheet>
 * ----
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */
defined( 'ABSPATH' ) or die( 'Not for browsing' );

/**
 * xslt_function_sample( $param1 = 'missing', $param2 = 'missing' ) : DomDocument
 */
function xslt_function_sample( $param1 = 'missing', $param2 = 'missing' )
{
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('param1','param2'),true), E_USER_NOTICE); }
    $xml = '<ul>' .
            '<li>param1: '.$param1.'</li>' .
            '<li>param2: '.$param2.'</li>' .
         '</ul>';

    $doc = new DomDocument('1.0', 'utf-8');
    $doc->loadXML( $xml );
    return $doc;
}

/**
 * append function name(s) to list of allowed callbacks
 */
global $XSLT_PLUGIN_PHP_FUNCTIONS;
if (empty($XSLT_PLUGIN_PHP_FUNCTIONS)) { $XSLT_PLUGIN_PHP_FUNCTIONS = array(); }
$XSLT_PLUGIN_PHP_FUNCTIONS[] = 'xslt_function_sample';  // custom
$XSLT_PLUGIN_PHP_FUNCTIONS[] = 'convert_uuencode';      // built-in
