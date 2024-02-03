<?php
/**
 * Custom Post Types
 *
 * usage:
 * require_once plugin_dir_path( __FILE__ ) . 'includes/post_type.php';
 * XSLT_Processor_Post_Type::init();
 *
 * @see https://developer.wordpress.org/reference/functions/get_post_type_labels/
 * @see https://developer.wordpress.org/reference/functions/get_post_type_capabilities/
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );

define( 'XSLT_POST_TYPE_XSL', 'xslt_xsl' );
define( 'XSLT_POST_TYPE_XML', 'xslt_xml' );

require_once(XSLT_PLUGIN_DIR.'includes/notice.php');


/**
 * XSLT_Processor_Post_Type
 * All class methods static, most hooked.
 */
class XSLT_Processor_Post_Type
{

    /**
     * init
     */
    public static function init()
    {
//if (WP_DEBUG) { trigger_error(__METHOD__, E_USER_NOTICE); }

        $options = get_option( XSLT_OPTS, array() );
        if (empty($options['post_type_xsl']) && empty($options['post_type_xml']) ) { return; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('options'),true), E_USER_NOTICE); }

        $post_type_params = array(
            'description'           => 'Custom post_type for XSLT Processor',
            'public'                => true,
            'exclude_from_search'   => true,
            'labels' => array(
                'name'                      => 'Pages',
                'singular_name'             => 'Page',
                'add_new_item'              => 'Add New Page',
                'edit_item'                 => 'Edit Page',
                'new_item'                  => 'New Page',
                'view_item'                 => 'View Page',
                'view_items'                => 'View Pages',
                'search_items'              => 'Search Pages',
                'not_found'                 => 'No pages found',
                'not_found_in_trash'        => 'No pages found in Trash',
                'all_items'                 => 'All Pages',
                //'archives'                  => 'Page Archives',
                //'attributes'                => 'Page Attributes',
                //'insert_into_item'          => 'Insert into page',
                //'uploaded_to_this_item'     => 'Uploaded to this page',
                'filter_items_list'         => 'Filter pages list',
                'items_list_navigation'     => 'Pages list navigation',
                'items_list'                => 'Pages list',
                'item_published'            => 'Page published.',
                'item_published_privately'  => 'Page published privately.',
                'item_reverted_to_draft'    => 'Page reverted to draft.',
                'item_trashed'              => 'Page trashed.',
                'item_scheduled'            => 'Page scheduled.',
                'item_updated'              => 'Page updated.',
                'item_link'                 => 'Page Link',
                'item_link_description'     => 'A link to a post.',
                ),
            'hierarchical'          => false,
            'show_in_rest'          => true,
            'menu_icon'             => 'dashicons-media-code',
            //'capability_type'       => array('xslt_doc','xslt_docs'),
            'supports'              => array(
                'title',
                'editor',
                //'comments',
                //'revisions',
                //'trackbacks',
                'author',
                //'excerpt',
                //'page-attributes',
                //'thumbnail',
                'custom-fields',
                //'post-formats',
                ),
            'has_archive'           => false,
            'template'              => array(
                array('core/html'),
                ),
            'template_lock'         => 'all',
            );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_type_params'),true), E_USER_NOTICE); }

        $taxonomy_params = array(
            'description'   => 'Custom taxonomy for XSLT Processor',
            'hierarchical'  => true,    // true for categories, false for tags
            'rewrite'       => array('slug' => 'xslt-taxonomy'),
            'labels' => array(
                'name'              => 'Categories',
                'singular_name'     => 'Category',
                'search_items'      => 'Search Categories',
                'all_items'         => 'All Categories',
                'parent_item'       => 'Parent Category',
                'parent_item_colon' => 'Parent Category:',
                'edit_item'         => 'Edit Category',
                'update_item'       => 'Update Category',
                'add_new_item'      => 'Add New Category',
                'new_item_name'     => 'New Category Name',
                'menu_name'         => 'Category',
                ),
            'public'                => true,    // dflt for publicly_queryable, show_ui, show_in_nav_menus
            'show_ui'               => true,    // show_tagcloud
            'show_in_quick_edit'    => true,    // meta_box_cb, meta_box_sanitize_cb
            'show_admin_column'     => true,
            'query_var'             => true,    // ?{taxonomy-name}={term-slug}

            'publicly_queryable'    => false,   // define ???
            'show_in_nav_menus'     => false,
            'show_in_rest'          => true,   // rest_base, rest_namespace, rest_controller_class
            //'default_term'          => 'Uncategorized',
            //'sort'                  => false,   // args
            );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('taxonomy_params'),true), E_USER_NOTICE); }

        if (!empty($options['post_type_xsl']))
        {
            self::register_xsl_post_type( $post_type_params );
            self::register_xsl_taxonomies( $taxonomy_params );
        }
        if (!empty($options['post_type_xml']))
        {
            self::register_xml_post_type( $post_type_params );
            self::register_xml_taxonomies( $taxonomy_params );
        }

        add_action( 'pre_get_posts', array('XSLT_Processor_Post_Type', 'update_taxonomy_query') );

        add_action( 'add_meta_boxes', array('XSLT_Processor_Post_Type', 'add_xslt_validation') );
        add_action( 'save_post', array('XSLT_Processor_Post_Type', 'update_xslt_validation'), 10, 3 );
        add_action( 'save_post', array('XSLT_Processor_Post_Type', 'xslt_validate'), 11, 3 );

        add_filter( 'the_content', array('XSLT_Processor_Post_Type', 'the_content_filter'), 20);
        add_filter( 'get_the_excerpt', array('XSLT_Processor_Post_Type', 'the_excerpt_filter'), 20, 2);
    }

    /**
     * register custom type for XSL
     */
    public static function register_xsl_post_type( $params )
    {
        $params['description']  = esc_html__( 'Custom XSL post_type for XSLT Processor', 'tenandtwo-xslt-processor' );
        $params['menu_icon']    = 'dashicons-media-code';

        $params['labels']['name']                   = esc_html__( 'XSL Stylesheets', 'tenandtwo-xslt-processor' );
        $params['labels']['singular_name']          = esc_html__( 'XSL Stylesheet', 'tenandtwo-xslt-processor' );
        $params['labels']['add_new_item']           = esc_html__( 'Add New XSL', 'tenandtwo-xslt-processor' );
        $params['labels']['edit_item']              = esc_html__( 'Edit XSL', 'tenandtwo-xslt-processor' );
        $params['labels']['new_item']               = esc_html__( 'New XSL', 'tenandtwo-xslt-processor' );
        $params['labels']['view_item']              = esc_html__( 'View XSL', 'tenandtwo-xslt-processor' );
        $params['labels']['view_items']             = esc_html__( 'View XSL Stylesheets', 'tenandtwo-xslt-processor' );
        $params['labels']['search_items']           = esc_html__( 'Search XSL Stylesheets', 'tenandtwo-xslt-processor' );
        $params['labels']['not_found']              = esc_html__( 'No XSL Stylesheets found', 'tenandtwo-xslt-processor' );
        $params['labels']['not_found_in_trash']     = esc_html__( 'No XSL Stylesheets found in Trash', 'tenandtwo-xslt-processor' );
        $params['labels']['all_items']              = esc_html__( 'All XSL Stylesheets', 'tenandtwo-xslt-processor' );
        $params['labels']['filter_items_list']      = esc_html__( 'Filter XSL list', 'tenandtwo-xslt-processor' );
        $params['labels']['items_list_navigation']  = esc_html__( 'XSL list navigation', 'tenandtwo-xslt-processor' );
        $params['labels']['items_list']             = esc_html__( 'XSL list', 'tenandtwo-xslt-processor' );
        $params['labels']['item_published']         = esc_html__( 'XSL published.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_published_privately'] = esc_html__( 'XSL published privately.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_reverted_to_draft'] = esc_html__( 'XSL reverted to draft.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_trashed']           = esc_html__( 'XSL trashed.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_scheduled']         = esc_html__( 'XSL scheduled.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_updated']           = esc_html__( 'XSL updated.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_link']              = esc_html__( 'XSL Link', 'tenandtwo-xslt-processor' );
        $params['labels']['item_link_description']  = esc_html__( 'A link to an XSL.', 'tenandtwo-xslt-processor' );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        register_post_type( XSLT_POST_TYPE_XSL, $params );
    }

    /**
     * register custom type for XML
     */
    public static function register_xml_post_type( $params )
    {
        $params['description']  = esc_html__( 'Custom XML post_type for XSLT Processor', 'tenandtwo-xslt-processor' );
        $params['menu_icon']    = 'dashicons-media-code';

        $params['labels']['name']                   = esc_html__( 'XML Documents', 'tenandtwo-xslt-processor' );
        $params['labels']['singular_name']          = esc_html__( 'XML Document', 'tenandtwo-xslt-processor' );
        $params['labels']['add_new_item']           = esc_html__( 'Add New XML', 'tenandtwo-xslt-processor' );
        $params['labels']['edit_item']              = esc_html__( 'Edit XML', 'tenandtwo-xslt-processor' );
        $params['labels']['new_item']               = esc_html__( 'New XML', 'tenandtwo-xslt-processor' );
        $params['labels']['view_item']              = esc_html__( 'View XML', 'tenandtwo-xslt-processor' );
        $params['labels']['view_items']             = esc_html__( 'View XML Documents', 'tenandtwo-xslt-processor' );
        $params['labels']['search_items']           = esc_html__( 'Search XML Documents', 'tenandtwo-xslt-processor' );
        $params['labels']['not_found']              = esc_html__( 'No XML Documents found', 'tenandtwo-xslt-processor' );
        $params['labels']['not_found_in_trash']     = esc_html__( 'No XML Documents found in Trash', 'tenandtwo-xslt-processor' );
        $params['labels']['all_items']              = esc_html__( 'All XML Documents', 'tenandtwo-xslt-processor' );
        $params['labels']['filter_items_list']      = esc_html__( 'Filter XML list', 'tenandtwo-xslt-processor' );
        $params['labels']['items_list_navigation']  = esc_html__( 'XML list navigation', 'tenandtwo-xslt-processor' );
        $params['labels']['items_list']             = esc_html__( 'XML list', 'tenandtwo-xslt-processor' );
        $params['labels']['item_published']         = esc_html__( 'XML published.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_published_privately'] = esc_html__( 'XML published privately.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_reverted_to_draft'] = esc_html__( 'XML reverted to draft.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_trashed']           = esc_html__( 'XML trashed.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_scheduled']         = esc_html__( 'XML scheduled.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_updated']           = esc_html__( 'XML updated.', 'tenandtwo-xslt-processor' );
        $params['labels']['item_link']              = esc_html__( 'XML Link', 'tenandtwo-xslt-processor' );
        $params['labels']['item_link_description']  = esc_html__( 'A link to an XML.', 'tenandtwo-xslt-processor' );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        register_post_type( XSLT_POST_TYPE_XML, $params );
    }

    /**
     * create categories/tags for XSL items
     */
    public static function register_xsl_taxonomies( $params )
    {
        $cat_params = $params;
        $cat_params['description']      = esc_html__( 'XSL Stylesheet Categories', 'tenandtwo-xslt-processor' );
        $cat_params['rewrite']          = array('slug' => 'xsl-category');
        $cat_params['hierarchical']     = true;
        $cat_params['labels']['name']               = esc_html__( 'XSL Categories', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['singular_name']      = esc_html__( 'XSL Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['search_items']       = esc_html__( 'Search XSL Categories', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['all_items']          = esc_html__( 'All XSL Categories', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['parent_item']        = esc_html__( 'Parent XSL Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['parent_item_colon']  = esc_html__( 'Parent XSL Category:', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['edit_item']          = esc_html__( 'Edit XSL Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['update_item']        = esc_html__( 'Update XSL Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['add_new_item']       = esc_html__( 'Add New XSL Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['new_item_name']      = esc_html__( 'New XSL Category Name', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['menu_name']          = esc_html__( 'XSL Category', 'tenandtwo-xslt-processor' );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('cat_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xsl-category', array(XSLT_POST_TYPE_XSL), $cat_params );
        register_taxonomy_for_object_type( 'xsl-category', XSLT_POST_TYPE_XSL );

        $tag_params = $params;
        $tag_params['description']      = esc_html__( 'XSL Stylesheet Tags', 'tenandtwo-xslt-processor' );
        $tag_params['rewrite']          = array('slug' => 'xsl-tag');
        $tag_params['hierarchical']     = false;
        $tag_params['labels']['name']               = esc_html__( 'XSL Tags', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['singular_name']      = esc_html__( 'XSL Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['search_items']       = esc_html__( 'Search XSL Tags', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['all_items']          = esc_html__( 'All XSL Tags', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['parent_item']        = esc_html__( 'Parent XSL Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['parent_item_colon']  = esc_html__( 'Parent XSL Tag:', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['edit_item']          = esc_html__( 'Edit XSL Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['update_item']        = esc_html__( 'Update XSL Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['add_new_item']       = esc_html__( 'Add New XSL Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['new_item_name']      = esc_html__( 'New XSL Tag Name', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['menu_name']          = esc_html__( 'XSL Tag', 'tenandtwo-xslt-processor' );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('tag_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xsl-tag', array(XSLT_POST_TYPE_XSL), $tag_params );
        register_taxonomy_for_object_type( 'xsl-tag', XSLT_POST_TYPE_XSL );

    }

    /**
     * create categories/tags for XML items
     */
    public static function register_xml_taxonomies( $params )
    {
        $cat_params = $params;
        $cat_params['description']      = esc_html__( 'XML Document Categories', 'tenandtwo-xslt-processor' );
        $cat_params['rewrite']          = array('slug' => 'xml-category');
        $cat_params['hierarchical']     = true;
        $cat_params['labels']['name']               = esc_html__( 'XML Categories', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['singular_name']      = esc_html__( 'XML Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['search_items']       = esc_html__( 'Search XML Categories', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['all_items']          = esc_html__( 'All XML Categories', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['parent_item']        = esc_html__( 'Parent XML Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['parent_item_colon']  = esc_html__( 'Parent XML Category:', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['edit_item']          = esc_html__( 'Edit XML Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['update_item']        = esc_html__( 'Update XML Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['add_new_item']       = esc_html__( 'Add New XML Category', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['new_item_name']      = esc_html__( 'New XML Category Name', 'tenandtwo-xslt-processor' );
        $cat_params['labels']['menu_name']          = esc_html__( 'XML Category', 'tenandtwo-xslt-processor' );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('cat_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xml-category', array(XSLT_POST_TYPE_XML), $cat_params );
        register_taxonomy_for_object_type( 'xml-category', XSLT_POST_TYPE_XML );

        $tag_params = $params;
        $tag_params['description']       = esc_html__( 'XML Document Tags', 'tenandtwo-xslt-processor' );
        $tag_params['rewrite']           = array('slug' => 'xml-tag');
        $tag_params['hierarchical']      = false;
        $tag_params['labels']['name']               = esc_html__( 'XML Tags', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['singular_name']      = esc_html__( 'XML Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['search_items']       = esc_html__( 'Search XML Tags', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['all_items']          = esc_html__( 'All XML Tags', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['parent_item']        = esc_html__( 'Parent XML Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['parent_item_colon']  = esc_html__( 'Parent XML Tag:', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['edit_item']          = esc_html__( 'Edit XML Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['update_item']        = esc_html__( 'Update XML Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['add_new_item']       = esc_html__( 'Add New XML Tag', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['new_item_name']      = esc_html__( 'New XML Tag Name', 'tenandtwo-xslt-processor' );
        $tag_params['labels']['menu_name']          = esc_html__( 'XML Tag', 'tenandtwo-xslt-processor' );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('tag_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xml-tag', array(XSLT_POST_TYPE_XML), $tag_params );
        register_taxonomy_for_object_type( 'xml-tag', XSLT_POST_TYPE_XML );

    }

    /**
     * set post_type = 'xslt_xsl' in taxonomy query for xsl-category, xsl-tag
     * set post_type = 'xslt_xml' in taxonomy query for xml-category, xml-tag
     */
    public static function update_taxonomy_query( $query )
    {
        if (is_admin() || !$query->is_main_query() || !$query->is_tax()) { return; }

        $xsl_category = $query->get( 'xsl-category', false );
        $xsl_tag      = $query->get( 'xsl-tag', false );
        if ($xsl_category || $xsl_tag) {
            $query->set('post_type', XSLT_POST_TYPE_XSL);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xsl_category','xsl_tag','query'),true), E_USER_NOTICE); }
        }

        $xml_category = $query->get( 'xml-category', false );
        $xml_tag      = $query->get( 'xml-tag', false );
        if ($xml_category || $xml_tag) {
            $query->set('post_type', XSLT_POST_TYPE_XML);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml_category','xml_tag','query'),true), E_USER_NOTICE); }
        }

    }


    /**
     * compose excerpt
     */
    public static function the_excerpt_filter( $post_excerpt, $post )
    {
        if (!in_array($post->post_type, array(XSLT_POST_TYPE_XSL,XSLT_POST_TYPE_XML)))
            { return $post_excerpt; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_excerpt','post'),true), E_USER_NOTICE); }

        $validation_warnings = get_post_meta( $post->ID, '_xslt_validation_warnings', true );
        $validation_errors   = get_post_meta( $post->ID, '_xslt_validation_errors', true );
        if ($validation_errors == -1)
            { return $post_excerpt; }

        $post_content = '';
        $limit = 100;
        if ($validation_warnings == 0 && $validation_errors == 0) {
            $shortcode = '[xslt_select_xml root="" xml="'.$post->ID.'" htmlentities /]';
            $post_content = XSLT_Processor_WP::filterPostContent($shortcode);
            $limit = 200;  // many htmlentities
        } else {
            $validation_message = get_post_meta( $post->ID, '_xslt_validation_message', true );
            if ($validation_warnings > 0)
                { $post_content .= $validation_warnings.' WARNING'.(($validation_warnings == 1) ? ' ' : 'S '); }
            if ($validation_errors > 0)
                { $post_content .= $validation_errors.' ERROR'.(($validation_errors == 1) ? ' ' : 'S '); }
            $post_content .= ': '.wp_kses($validation_message, 'post');
            $post_content = preg_replace('|\-+|', '--', $post_content);
        }
        $post_excerpt = esc_html( wp_html_excerpt( $post_content, $limit, '&hellip;' ) );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_excerpt','post'),true), E_USER_NOTICE); }
        return $post_excerpt;
    }

    /**
     * htmlentities($content) for front-end display
     */
    public static function the_content_filter( $content )
    {
        if (empty(get_post()) || !in_array(get_post()->post_type, array(XSLT_POST_TYPE_XSL,XSLT_POST_TYPE_XML)) )
            { return $content; }
        if ( !is_singular() || !in_the_loop() || !is_main_query() )
            { return $content; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('content'),true), E_USER_NOTICE); }
        //return '<pre style="font-size: medium">'.htmlentities($content).'</pre>';
        return '<pre style="font-size: medium">'.htmlentities($content, ENT_HTML5, 'UTF-8', false).'</pre>';
    }

    /**
     * add_meta_box( string $id, string $title, callable $callback, string|array|WP_Screen $screen = null, string $context = 'advanced', string $priority = 'default', array $callback_args = null )
     */
    public static function add_xslt_validation()
    {
        $xsl_xml_fields = array('_xslt_validation_warnings','_xslt_validation_errors','_xslt_validation_message');
        $xsl_fields     = array();
        $xml_fields     = array('_xslt_schema_type','_xslt_schema_value');
        $field_options  = array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
            );

        $post_types = array(XSLT_POST_TYPE_XSL,XSLT_POST_TYPE_XML);
        foreach( $post_types as $post_type ) {
            $flds = $xsl_xml_fields;
            if ($post_type == XSLT_POST_TYPE_XSL) { $flds = array_merge( $flds, $xsl_fields ); }
            if ($post_type == XSLT_POST_TYPE_XML) { $flds = array_merge( $flds, $xml_fields ); }

            foreach( $flds as $fieldname ) {
                register_post_meta(
                    $post_type,
                    $fieldname,
                    $field_options
                );
            }

            add_meta_box(
                '_xslt_validation_meta',
                esc_html(strtoupper($post_type)).' '.esc_html__( 'Validation', 'tenandtwo-xslt-processor' ),
                array('XSLT_Processor_Post_Type', 'display_xslt_validation'),
                $post_type,
                'normal'
            );
        }
    }

    /**
     * display meta box
     */
    public static function display_xslt_validation( $post )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post'),true), E_USER_NOTICE); }

        $xslt_validation_warnings = get_post_meta( $post->ID, '_xslt_validation_warnings', true );
        $xslt_validation_errors   = get_post_meta( $post->ID, '_xslt_validation_errors', true );
        $xslt_validation_message  = get_post_meta( $post->ID, '_xslt_validation_message', true );
        $xslt_schema_type  = get_post_meta( $post->ID, '_xslt_schema_type', true );
        $xslt_schema_value = get_post_meta( $post->ID, '_xslt_schema_value', true );

        $value_size = 28;
        echo '<div id="xslt_validation">';
        echo '<table width="100%">';

        if ($post->post_type == XSLT_POST_TYPE_XML)
        {
            echo '<tr><td>';
            echo '<label for="_xslt_schema_type"><strong>' . esc_html__( 'Validation Method', 'tenandtwo-xslt-processor' ) . ' :</strong></label>';
            echo '<br/>';
            echo '<select id="_xslt_schema_type" name="_xslt_schema_type">'
                . '<option value="none">'.esc_html__( 'Syntax Only', 'tenandtwo-xslt-processor' ).'</option>'
                . '<option value="dtd"' . (($xslt_schema_type == 'dtd') ? ' selected' : '') . '>DTD</option>'
                . '<option value="xsd"' . (($xslt_schema_type == 'xsd') ? ' selected' : '') . '>XSD</option>'
                . '<option value="rng"' . (($xslt_schema_type == 'rng') ? ' selected' : '') . '>RNG</option>'
                .'</select>';
            echo '</td></tr>';

            echo '<tr><td>';
            echo '<label for="_xslt_schema_value"><strong>XSD|RNG ' . esc_html__( 'Schema File', 'tenandtwo-xslt-processor' ) . ' :</strong></label>';
            echo '<br/>';
            echo '<input type="text" id="_xslt_schema_value" name="_xslt_schema_value"'
                . ' value="'.esc_attr($xslt_schema_value).'" size="'.esc_attr($value_size).'"'
                .'>';
            echo '</td></tr>';
        }

        $html = '<tr><td>';
        $html .= '<label><strong>' . esc_html__( 'Validation Results', 'tenandtwo-xslt-processor' ) . ' :</strong></label>';
        $html .= '</td></tr>';
        $html .= '<tr><td id="xslt_validation_message">';

        if ($xslt_validation_warnings > 0)
            { $html .= $xslt_validation_warnings.' '.(($xslt_validation_warnings == 1) ? esc_html__( 'Validation Warning', 'tenandtwo-xslt-processor' ) : esc_html__( 'Validation Warnings', 'tenandtwo-xslt-processor' )).' '; }
        if ($xslt_validation_errors > 0)
            { $html .= $xslt_validation_errors.' '.(($xslt_validation_errors == 1) ? esc_html__( 'Validation Error', 'tenandtwo-xslt-processor' ) : esc_html__( 'Validation Errors', 'tenandtwo-xslt-processor' )).' '; }
        if ($xslt_validation_warnings > 0 || $xslt_validation_errors > 0)
            { $html .= "<br/>\n"; }

        $html .= $xslt_validation_message;
        $html .= '</td></tr>';
        echo wp_kses($html, 'post');

        echo '</table>';
        echo '</div>';

        echo '<script language="javascript">';
        echo 'const editor = window.wp.data.dispatch("core/editor");'
            .' const savePost = editor.savePost;'
            .' editor.savePost = function (options) {'
            .' options = options || {};';
        echo ' return savePost(options).then(() => {'
            .' if (!options.isAutosave) {'
            //.' console.log("savePost");'
            .' el = document.querySelector( "#xslt_validation_message" );';
        echo " if (el) { el.innerHTML = '<center><a href=\"\">";
        esc_html_e( 'reload', 'tenandtwo-xslt-processor' );
        echo "</a></center>'; }";
        echo ' } }); }</script>';

     }

    /**
     * set post_meta '_xslt_schema_type'
     * set post_meta '_xslt_schema_value'
     */
    public static function update_xslt_validation( $post_id, $post, $update )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : _POST=".print_r($_POST,true), E_USER_NOTICE); }
        if (!in_array($post->post_type, array(XSLT_POST_TYPE_XSL,XSLT_POST_TYPE_XML)) ) { return; }

        $updated = false;
    	if (array_key_exists('_xslt_schema_type', $_POST ) )
    	{
    	    $schema_types = array('none','dtd','xsd','rng');
    	    $schema_type = sanitize_text_field(strtolower($_POST['_xslt_schema_type']));
            if (in_array($schema_type, $schema_types)) {
                update_post_meta( $post_id, '_xslt_schema_type', $schema_type );
//if (WP_DEBUG) { trigger_error(__METHOD__." : _POST=".print_r(compact('post_id','schema_type'),true), E_USER_NOTICE); }
                $updated = true;
            }
    	}
        if (array_key_exists('_xslt_schema_value', $_POST ) )
        {
            $schema_value = sanitize_text_field($_POST['_xslt_schema_value']);
            update_post_meta( $post_id, '_xslt_schema_value', $schema_value );
//if (WP_DEBUG) { trigger_error(__METHOD__." : _POST=".print_r(compact('post_id','schema_value'),true), E_USER_NOTICE); }
            $updated = true;
        }
        if ($updated) {
            update_post_meta( $post_id, '_xslt_validation_warnings', -1 );
            update_post_meta( $post_id, '_xslt_validation_errors',   -1 );
            update_post_meta( $post_id, '_xslt_validation_message',  '' );
        }
    }

    /**
     * set post_meta '_xslt_validation_warnings'
     * set post_meta '_xslt_validation_errors'
     * set post_meta '_xslt_validation_message'
     *
     * $schema_type  = 'none';
     *
     * $schema_type  = 'dtd';
     *  <!DOCTYPE sample SYSTEM "/var/www/html/wp-content/plugins/tenandtwo-xslt-processor/xsl/sample.dtd">
     *  <!DOCTYPE ONIXMessage SYSTEM "/var/www/html/wp-content/plugins/tenandtwo-xslt-processor/xsl/onix2/dtd_reference/onix-international.dtd">
     *
     * $schema_type  = 'xsd';
     * $schema_value = '/var/www/html/wp-content/plugins/tenandtwo-xslt-processor/xsl/onix2/xsd/ONIX_BookProduct_Release2.1_reference.xsd';
     *
     * $schema_type  = 'rng';
     * $schema_value = '/var/www/html/wp-content/plugins/tenandtwo-xslt-processor/xsl/onix2/xsd/ONIX_BookProduct_Release2.1_reference.xsd';
     *
     * @uses XSLT_Processor_WP::filterPostContent()
     * @uses XSLT_Processor_XSL::validateXSL()
     * @uses XSLT_Processor_XSL::validateXML()
     */
    public static function xslt_validate( $post_id, $post, $update )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','post','update'),true), E_USER_NOTICE); }
        if (!in_array($post->post_type, array(XSLT_POST_TYPE_XSL,XSLT_POST_TYPE_XML))) { return; }

        $validation = array('warnings' => -1, 'errors' => -1, 'message' => '');
        $post_content = XSLT_Processor_WP::filterPostContent( $post->post_content );

        if (!empty($post_content) && extension_loaded('tidy'))
        {
            $validation = XSLT_Processor_XML::tidy_validate( $post_content, 'xml' );
        }
        if (!empty($post_content) && $validation['errors'] < 1)
        {
            global $XSLT_Processor_XSL;
            if (empty($XSLT_Processor_XSL)) { $XSLT_Processor_XSL = new XSLT_Processor_XSL(); }

            if ($post->post_type == XSLT_POST_TYPE_XSL)
            {
                $params = array(
                    "xsl_type"  => 'string',
                    "xsl_value" => $post_content,
                );
                $validation = $XSLT_Processor_XSL->validateXSL( $params );

            } else {
                $schema_type  = get_post_meta( $post_id, '_xslt_schema_type', true );
                $schema_value = get_post_meta( $post_id, '_xslt_schema_value', true );
                $params = array(
                    "xml_type"  => 'string',
                    "xml_value" => $post_content,
                    "schema_type"  => $schema_type,
                    "schema_value" => $schema_value,
                );
                $validation = $XSLT_Processor_XSL->validateXML( $params );
            }
        }
        update_post_meta( $post_id, '_xslt_validation_warnings', $validation['warnings'] );
        update_post_meta( $post_id, '_xslt_validation_errors',   $validation['errors'] );
        update_post_meta( $post_id, '_xslt_validation_message',  $validation['message'] );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_id','validation'),true), E_USER_NOTICE); }
    }


}  // end XSLT_Processor_Post_Type
