<?php
/**
 * WP utils
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );


class XSLT_Processor_WP
{
    /**
     * sanitize html ???
     * @uses wp_kses( string $content, array[]|string $allowed_html, string[] $allowed_protocols = array() ): string
     */

    /**
     * convert titles/name to lowercase with dashes
     * eg, "The Title" => "the-title"
     * eg, "COOKING / Methods / Barbecue &amp; Grilling" => "cooking-methods-barbecue-grilling"
     *
     * ??? wp_kses( string $content, array[]|string $allowed_html, string[] $allowed_protocols = array() ): string
     *
     * @see wp.xsl, template name="wp-sanitize-title"
     * @uses sanitize_title( string $title, string $fallback_title = '', string $context = 'save' ): string
     */
    public static function getSanitizeTitle( $title )
    {
        $result = sanitize_title( $title );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('title','result'),true), E_USER_NOTICE); }
        return $result;
    }


    /**
     * call WP function shortcode_atts()
     * non-empty values starting with  '0|n|f' are set FALSE
     * remaining non-empty values are set TRUE
     *
     * @see https://developer.wordpress.org/reference/functions/shortcode_atts/
     */
    public static function getShortcodeBooleans( $pairs, $attrs, $shortcode = '' )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('pairs','attrs','shortcode'),true), E_USER_NOTICE); }
        $result = shortcode_atts( $pairs, $attrs, $shortcode );

        $no_vals = array( '0', 'n', 'f' );
        foreach ($result as $key => $val) {
            $char1 = substr(strtolower(strval($val)), 0, 1);
            if (!empty($val) && in_array($char1, $no_vals))
                { $val = false; }
            if (!empty($val) || in_array($key,$attrs))
                { $val = true; }
             $result[$key] = $val;
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('pairs','result'),true), E_USER_NOTICE); }
        return $result;
    }


    /**
     * get WP Post OBJECT, ARRAY_A, or ARRAY_N
     * @param string $post_name     the post's slug
     * @param array $post_type      select filter, eg array('page','xml')
     * @param constant $output      OBJECT, ARRAY_A, or ARRAY_N
     * @return object WP_Post, array, or false
     */
    public static function getPostByName( $post_name, $post_type = array(), $output = OBJECT )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_name','post_type','output'),true), E_USER_NOTICE); }

        global $wpdb;
        if (empty($post_type))
        {
            $sql = $wpdb->prepare(
                "SELECT ID
                FROM $wpdb->posts
                WHERE post_name = %s",
                $post_name
            );
        }
        else
        {
            if (!is_array($post_type)) { $post_type = array($post_type); }
            $post_types = implode(', ', array_fill(0, count($post_type), '%s'));
            $sql = $wpdb->prepare(
                "SELECT ID
                FROM $wpdb->posts
                WHERE post_name = %s
                AND post_type IN (".$post_types.")",
                array_merge( array($post_name), $post_type)
            );
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('sql'),true), E_USER_NOTICE); }

        $post_id = $wpdb->get_var( $sql );
        $result  = ($post_id) ? get_post( $post_id, $output ) : false;
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * get filtered content from post
     * if post_type=page, add <div> for XML
     * if post_type=page, extract stylesheet for XSL
     *
     * @param mixed $id             the post's id or slug
     * @param array $post_type      select filter, eg array('page','xml')
     * @return string
     */
    public static function getPostContent( $id, $post_type = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('id','post_type'),true), E_USER_NOTICE); }

        $post = (is_numeric($id))
            ? get_post($id)
            : self::getPostByName( $id, $post_type );
        if (!$post) { return false; }

        $post_content = self::filterPostContent( $post->post_content );
        if ($post->post_type == 'page')
        {
            $post_content = '<div class="page-content">'.$post_content.'</div>';

            if (!is_array($post_type)) { $post_type = array($post_type); }
            if (in_array('xsl', $post_type))
            {
                global $XSLT_Processor_XML;
                if (empty($XSLT_Processor_XML)) { $XSLT_Processor_XML = new XSLT_Processor_XML(); }

                $post_content = $XSLT_Processor_XML->decode_string( $post_content, '//xsl:stylesheet[1]', 'xml', '');
            }
        }
        return $post_content;
    }

    /**
     * filter raw content from post
     *
     * @param string $post_content  the post's content
     * @return string
     */
    public static function filterPostContent( $post_content = '' )
    {
        if (empty($post_content)) { return $post_content; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_content'),true), E_USER_NOTICE); }
        $post_content = apply_filters( 'the_content', $post_content );
        $post_content = str_replace( ']]>', ']]&gt;', $post_content );
        $post_content = XSLT_Processor_Util::utf8_clean( $post_content );
        //$post_content = XSLT_Processor_Util::removeXmlDeclaration( $post_content );
        return trim($post_content);
    }


} // end class XSLT_Processor_WP
