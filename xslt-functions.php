<?php
/**
 * XSLT Functions
 *
 * usage:
 * require_once plugin_dir_path( __FILE__ ) . 'xslt-functions.php';
 *
 * @package           tenandtwo-plugins
 * @subpackage        xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */
defined( 'ABSPATH' ) or die( 'Not for browsing' );


/**
 * Global list of functions calls allowed within XSLT
 * passed to XSLTProcessor::registerPHPFunctions() in XSLT_Processor_XSL::getProcessor()
 *
 * Functions should return a scalar value (int, float, string, bool)
 *     <xsl:value-of select="php:functionString('fname', string($param))" />
 *
 * OR return a DomDocument(), as demonstrated in includes/functions_sample.php
 *     <xsl:copy-of select="php:function('fname', string($param))" />
 *
 * @see https://www.php.net/manual/en/xsltprocessor.registerphpfunctions.php
 */
global $XSLT_PLUGIN_PHP_FUNCTIONS;

$XSLT_PLUGIN_PHP_FUNCTIONS = array(
// date.xsl
    //'date',
// string.xsl
    'html_entity_decode',
    'mb_strtoupper','mb_strtolower',
    'nl2br',
    'str_replace',
    'trim','ltrim','rtrim',
    'urlencode',
// util.xsl
    'hash',
    //'trigger_error',
// wp.xsl
    'sanitize_title',
    );


/**
 * Sample extension
 *
 * built-in: convert_uuencode( string $string ): string
 * custom:   function_sample( string $param1, string $param2 ) : DomDocument
 */
if (is_readable(XSLT_PLUGIN_DIR.'includes/functions_sample.php')) {
    require_once(XSLT_PLUGIN_DIR.'includes/functions_sample.php');
}
