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
        foreach( $result as $key => $val ) {
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
     *
     * @param mixed $post_id        the post's id or slug
     * @param array $post_type      select filter, eg array('page','xslt_xml')
     * @param constant $output      OBJECT, ARRAY_A, or ARRAY_N
     * @return object WP_Post, array, or false
     */
    public static function getPostItem( $post_id, $post_type = array(), $output = OBJECT )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','post_type','output'),true), E_USER_NOTICE); }

        // by post_id (ignore post_type)
        if (is_numeric($post_id))
        {
            return get_post( $post_id, $output );
        }

        // by post_name
        global $wpdb;
        if (empty($post_type))
        {
            $sql = $wpdb->prepare(
                "SELECT ID
                FROM $wpdb->posts
                WHERE post_name = %s",
                $post_id
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
                array_merge( array($post_id), $post_type )
            );
        }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('sql'),true), E_USER_NOTICE); }

        $post_id = $wpdb->get_var( $sql );
        $result  = ($post_id) ? get_post( $post_id, $output ) : false;
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','result'),true), E_USER_NOTICE); }
        return $result;
    }

    /**
     * get all meta fields for post
     * if meta value is a single-element array, convert to scalar
     *
     * @param mixed $post_id        WP_Post object, post id, or post slug
     * @param array $post_type      select filter, eg array('page','xslt_xml')
     * @return array
     */
    public static function getPostMeta( $post_id, $post_type = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','post_type'),true), E_USER_NOTICE); }

        $meta = null;
        if (is_object($post_id) && get_class($post_id) == 'WP_Post') {
            $meta = get_post_meta( $post_id->ID );
        } elseif (is_numeric($post_id)) {
            $meta = get_post_meta( $post_id );
        } else {
            $post = self::getPostItem( $post_id, $post_type );
            if ($post) { $meta = get_post_meta( $post->ID ); }
        }
        if (!$meta) { return false; }

        $result = array();
        foreach($meta as $key => $val) {
            $result[$key] = (is_array($val) && count($val) == 1) ? $val[0] : $val;
        }
        return $result;
    }

    /**
     * get filtered content from post
     * if post_type=page, add <div> for XML
     * if post_type=page, extract stylesheet for XSL
     *
     * @param mixed $post_id        WP_Post object, post id, or post slug
     * @param array $post_type      select filter, eg array('page','xslt_xml')
     * @return string
     */
    public static function getPostContent( $post_id, $post_type = array() )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','post_type'),true), E_USER_NOTICE); }

        $post = (is_object($post_id) && get_class($post_id) == 'WP_Post')
            ? $post_id
            : self::getPostItem( $post_id, $post_type );
        if (!$post) { return false; }

        $post_content = self::filterPostContent( $post->post_content );
        if ($post->post_type == 'page')
        {
            $post_content = '<div class="page-content">'.$post_content.'</div>';

            if (!is_array($post_type)) { $post_type = array($post_type); }
            if (in_array(XSLT_POST_TYPE__XSL, $post_type))
            {
                $post_content = XSLT_Processor_XML::decode_string( $post_content, '//xsl:stylesheet[1]', 'xml', '');
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
        //$post_content = XSLT_Processor_XML::strip_declaration( $post_content );
        return trim($post_content);
    }


} // end class XSLT_Processor_WP
