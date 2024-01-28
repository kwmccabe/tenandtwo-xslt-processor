<?php
/**
 * WP-CLI commands
 *
 * see https://make.wordpress.org/cli/
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );


class XSLT_Processor_CLI
{
    /**
     * init : register self as WP_CLI command
     */
    public static function init()
    {
        if (!(defined( 'WP_CLI' ) && WP_CLI)) { return; }
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }
        WP_CLI::add_command( 'xslt', 'XSLT_Processor_CLI' );
    }


    /**
     * do_shortcode [xslt_transform/]
     *
     * ## USAGE
     *
     *   wp xslt transform
     *     --xsl='{file|url|id|slug}'
     *     --xml='{file|url|id|slug}'
     *     --cache={minutes, if xsl|xml={url}}
     *     --tidy='{yes|html}' or tidy or --tidy='xml'
     *     --{myparam}='{myvalue}'
     *     --outfile='{filepath}'
     *     --htmlentities='yes' or htmlentities
     *
     * ## EXAMPLES
     *
     *   wp --allow-root xslt transform --xsl='sample-xsl' --xml='sample-xml' --testparam='HERE' --outfile='__WP_CONTENT_DIR__/uploads/cli-outfile.txt'
     *
     * @when after_wp_load
     */
    function transform( $args, $assoc_args ) {
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('args','assoc_args'),true), E_USER_NOTICE); }

        if (in_array('tidy',$args))             { $assoc_args['tidy'] = 'yes'; }
        if (in_array('htmlentities',$args))     { $assoc_args['htmlentities'] = 'yes'; }

        $attrs   = $assoc_args;
        $content = $assoc_args['content'] ?? '';
        $result  = XSLT_Processor_Shortcode::xslt_transform( $attrs, $content );

        WP_CLI::success('');
        if (WP_DEBUG) { WP_CLI::line( __METHOD__.' : '.print_r($attrs,true) ); }
        WP_CLI::line( print_r($result,true) );
    }


    /**
     * do_shortcode [xslt_select_xml/]
     *
     * ## USAGE
     *
     *   wp xslt select_xml
     *     --xml='{file|url|id|slug}'
     *     --cache={minutes, if xml={url}}
     *     --select='{xpath}'
     *     --root='{nodename|empty}'
     *
     *     --tidy=yes|html or tidy or --tidy=xml
     *     --strip-namespaces=yes or strip-namespaces
     *     --strip-declaration=yes|no
     *
     *     --format='{xml|json}'
     *     --htmlentities=yes or htmlentities
     *
     * ## EXAMPLES
     *
     *   wp --allow-root xslt select_xml --xml='sample-xml' --select='//list'
     *
     * @when after_wp_load
     */
    function select_xml( $args, $assoc_args ) {
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('args','assoc_args'),true), E_USER_NOTICE); }

        if (in_array('strip-namespaces',$args)) { $assoc_args['strip-namespaces'] = 'yes'; }
        if (in_array('tidy',$args))             { $assoc_args['tidy'] = 'yes'; }
        if (in_array('htmlentities',$args))     { $assoc_args['htmlentities'] = 'yes'; }

        $attrs   = $assoc_args;
        $content = $assoc_args['content'] ?? '';
        $result  = XSLT_Processor_Shortcode::xslt_select_xml( $attrs, $content );

        WP_CLI::success('');
        if (WP_DEBUG) { WP_CLI::line( __METHOD__.' : '.print_r($attrs,true) ); }
        WP_CLI::line( print_r($result,true) );
    }


    /**
     * do_shortcode [xslt_select_csv/]
     *
     * ## USAGE
     *
     *   wp xslt select_csv
     *     --csv='{file|url}'
     *     --cache={minutes, if csv={url}}
     *
     *     --separator=","
     *     --enclosure="\""
     *     --escape="\\"
     *
     *     --key_row="{row number for column labels}"
     *     --col="{return column number(s), letter(s), or label(s)}"
     *     --key_col="{col number, letter, or label for key matching}"
     *     --key="{value(s) for key_col matching}"
     *     --row="{return row number(s)}"
     *     --class="{css classname(s) for result <table>}"
     *
     *     --htmlentities=yes or htmlentities
     *
     * ## EXAMPLES
     *
     *   wp --allow-root xslt select_csv --csv='case-study-gsheets/Sheet1.csv'
     *   wp --allow-root xslt select_csv --csv="case-study-gsheets/Sheet1.csv" --row="1" --key_row="1" --key_col="ID" --key="1004,1005"
     *
     *   wp --allow-root xslt transform --xsl='csv-pivot-xsl' --key_row='1' --content='[xslt_select_csv csv="case-study-gsheets/Sheet1.csv" row="1" key_row="1" key_col="ID" key="1004,1005" /]'     *
     * @when after_wp_load
     */
    function select_csv( $args, $assoc_args ) {
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('args','assoc_args'),true), E_USER_NOTICE); }

        if (in_array('htmlentities',$args))     { $assoc_args['htmlentities'] = 'yes'; }

        $attrs   = $assoc_args;
        $content = $assoc_args['content'] ?? '';
        $result  = XSLT_Processor_Shortcode::xslt_select_csv( $attrs, $content );

        WP_CLI::success('');
        if (WP_DEBUG) { WP_CLI::line( __METHOD__.' : '.print_r($attrs,true) ); }
        WP_CLI::line( print_r($result,true) );
    }


} // end class XSLT_Processor_CLI



    /**
     * ## OPTIONS
     *
     * [--xsl=<xsl>]
     * : xsl stylesheet
     * ---
     * default: default.xsl
     * ---
     *
     * [--xml=<xml>]
     * : xml document
     * ---
     * default: default.xml
     * ---
     */
