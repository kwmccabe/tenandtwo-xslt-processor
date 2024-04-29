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
     * do_shortcode [xslt_transform_xml/]
     *
     * ## USAGE
     *
     *   wp xslt transform_xml
     *     --xsl='{file|url|id|slug}'
     *     --xml='{file|url|id|slug}'
     *     --cache='{minutes, if xsl|xml={url}}'
     *     --tidy='{yes|html}' or tidy or --tidy='xml'
     *     --{myparam}='{myvalue}'
     *     --outfile='{filepath}'
     *     --htmlentities
     *
     * ## EXAMPLES
     *
     *   wp --allow-root xslt transform_xml --xsl='sample-xsl' --xml='sample-xml' --testparam='HERE' --outfile='__WP_CONTENT_DIR__/uploads/cli-outfile.txt'
     *   wp --allow-root xslt transform_xml --xsl='sample-xsl' --xml='sample-xml' --testparam='HERE' > ./cli-outfile.txt
     *
     * @when after_wp_load
     */
    public function transform_xml( $args, $assoc_args ) {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('args','assoc_args'),true), E_USER_NOTICE); }

        if (in_array('tidy',$args))             { $assoc_args['tidy'] = 'yes'; }
        if (in_array('htmlentities',$args))     { $assoc_args['htmlentities'] = 'yes'; }

        $attrs   = $assoc_args;
        $content = $assoc_args['content'] ?? '';
        $result  = XSLT_Processor_Shortcode::xslt_transform_xml( $attrs, $content );
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content','result'),true), E_USER_NOTICE); }

        WP_CLI::line( print_r($result,true) );
    }


    /**
     * do_shortcode [xslt_select_xml/]
     *
     * ## USAGE
     *
     *   wp xslt select_xml
     *     --xml='{file|url|id|slug}'
     *     --cache='{minutes, if xml={url}}'
     *     --select='{xpath}'
     *     --root='{nodename|empty}'
     *
     *     --tidy='{yes|html}' or tidy or --tidy='xml'
     *     --strip-namespaces='{no|yes}' or strip-namespaces
     *     --strip-declaration='{yes|no}'
     *
     *     --format='{xml|json}'
     *     --htmlentities
     *
     * ## EXAMPLES
     *
     *   wp --allow-root xslt select_xml --xml='sample-xml' --select='//list'
     *   wp --allow-root xslt select_xml --xml='sample-xml' --select='//list' > ./cli-outfile.txt
     *
     * @when after_wp_load
     */
    public function select_xml( $args, $assoc_args ) {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('args','assoc_args'),true), E_USER_NOTICE); }

        if (in_array('strip-namespaces',$args)) { $assoc_args['strip-namespaces'] = 'yes'; }
        if (in_array('tidy',$args))             { $assoc_args['tidy'] = 'yes'; }
        if (in_array('htmlentities',$args))     { $assoc_args['htmlentities'] = 'yes'; }

        $attrs   = $assoc_args;
        $content = $assoc_args['content'] ?? '';
        $result  = XSLT_Processor_Shortcode::xslt_select_xml( $attrs, $content );
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content','result'),true), E_USER_NOTICE); }

        WP_CLI::line( print_r($result,true) );
    }


    /**
     * do_shortcode [xslt_select_csv/]
     *
     * ## USAGE
     *
     *   wp xslt select_csv
     *     --csv='{file|url}'
     *     --cache='{minutes, if csv={url}}'
     *
     *     --separator=','
     *     --enclosure='\"'
     *     --escape='\\'
     *
     *     --key_row='{row number for column labels}'
     *     --col='{return column number(s), letter(s), or label(s)}'
     *     --key_col='{col number, letter, or label for key matching}'
     *     --key='{value(s) for key_col matching}'
     *     --row='{return row number(s)}'
     *     --class='{css classname(s) for result <table>}'
     *
     *     --htmlentities
     *
     * ## EXAMPLES
     *
     *   wp --allow-root xslt select_csv --csv='case-study-gsheets/Sheet1.csv'
     *   wp --allow-root xslt select_csv --csv="case-study-gsheets/Sheet1.csv" --row="1" --key_row="1" --key_col="ID" --key="1004,1005"
     *   wp --allow-root xslt transform_xml --xsl='csv-pivot-xsl' --key_row='1' --content='[xslt_select_csv csv="case-study-gsheets/Sheet1.csv" row="1" key_row="1" key_col="ID" key="1004,1005" /]'
     *
     * @when after_wp_load
     */
    public function select_csv( $args, $assoc_args ) {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('args','assoc_args'),true), E_USER_NOTICE); }

        if (in_array('htmlentities',$args))     { $assoc_args['htmlentities'] = 'yes'; }

        $attrs   = $assoc_args;
        $content = $assoc_args['content'] ?? '';
        $result  = XSLT_Processor_Shortcode::xslt_select_csv( $attrs, $content );
if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('attrs','content','result'),true), E_USER_NOTICE); }

        WP_CLI::line( print_r($result,true) );
    }


} // end class XSLT_Processor_CLI



    /**
     * ## OPTIONS
     *
     * [--xsl=<value>]
     * : XSL stylesheet ={file|url|id|slug}
	 *
     * [--xml=<string>]
     * : XML document ={file|url|id|slug}
	 *
     * [--cache=<integer>]
     * : if xsl|xml={url} ={minutes}
	 *
     * [--tidy=<type>]
     * : pre-filter input XML ={html|xml}
	 *
     * [--outfile=<value>]
     * : save results as ={filepath}
	 *
     * [--htmlentities]
     * : post-filter result
	 *
     * [--{myparam}=<myvalue>]
     * : stylesheet parameter =<myvalue>
	 *
     */
