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
 *
 * @package           tenandtwo-wp-plugins
 * @subpackage        tenandtwo-xslt-processor
 * @author            Ten & Two Systems
 * @copyright         2023 Ten & Two Systems
 */

defined( 'ABSPATH' ) or die( 'Not for browsing' );

define( 'POST_TYPE_XSL', 'xsl' );
define( 'POST_TYPE_XML', 'xml' );

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
        $params['description']  = __( 'Custom XSL post_type for XSLT Processor', XSLT_TEXT );
        $params['menu_icon']    = 'dashicons-media-code';

        $params['labels']['name']                   = __( 'XSL Stylesheets', XSLT_TEXT );
        $params['labels']['singular_name']          = __( 'XSL Stylesheet', XSLT_TEXT );
        $params['labels']['add_new_item']           = __( 'Add New XSL', XSLT_TEXT );
        $params['labels']['edit_item']              = __( 'Edit XSL', XSLT_TEXT );
        $params['labels']['new_item']               = __( 'New XSL', XSLT_TEXT );
        $params['labels']['view_item']              = __( 'View XSL', XSLT_TEXT );
        $params['labels']['view_items']             = __( 'View XSL Stylesheets', XSLT_TEXT );
        $params['labels']['search_items']           = __( 'Search XSL Stylesheets', XSLT_TEXT );
        $params['labels']['not_found']              = __( 'No XSL Stylesheets found', XSLT_TEXT );
        $params['labels']['not_found_in_trash']     = __( 'No XSL Stylesheets found in Trash', XSLT_TEXT );
        $params['labels']['all_items']              = __( 'All XSL Stylesheets', XSLT_TEXT );
        $params['labels']['filter_items_list']      = __( 'Filter XSL list', XSLT_TEXT );
        $params['labels']['items_list_navigation']  = __( 'XSL list navigation', XSLT_TEXT );
        $params['labels']['items_list']             = __( 'XSL list', XSLT_TEXT );
        $params['labels']['item_published']         = __( 'XSL published.', XSLT_TEXT );
        $params['labels']['item_published_privately'] = __( 'XSL published privately.', XSLT_TEXT );
        $params['labels']['item_reverted_to_draft'] = __( 'XSL reverted to draft.', XSLT_TEXT );
        $params['labels']['item_trashed']           = __( 'XSL trashed.', XSLT_TEXT );
        $params['labels']['item_scheduled']         = __( 'XSL scheduled.', XSLT_TEXT );
        $params['labels']['item_updated']           = __( 'XSL updated.', XSLT_TEXT );
        $params['labels']['item_link']              = __( 'XSL Link', XSLT_TEXT );
        $params['labels']['item_link_description']  = __( 'A link to an XSL.', XSLT_TEXT );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        register_post_type( POST_TYPE_XSL, $params );
    }

    /**
     * register custom type for XML
     */
    public static function register_xml_post_type( $params )
    {
        $params['description']  = __( 'Custom XML post_type for XSLT Processor', XSLT_TEXT );
        $params['menu_icon']    = 'dashicons-media-code';

        $params['labels']['name']                   = __( 'XML Documents', XSLT_TEXT );
        $params['labels']['singular_name']          = __( 'XML Document', XSLT_TEXT );
        $params['labels']['add_new_item']           = __( 'Add New XML', XSLT_TEXT );
        $params['labels']['edit_item']              = __( 'Edit XML', XSLT_TEXT );
        $params['labels']['new_item']               = __( 'New XML', XSLT_TEXT );
        $params['labels']['view_item']              = __( 'View XML', XSLT_TEXT );
        $params['labels']['view_items']             = __( 'View XML Documents', XSLT_TEXT );
        $params['labels']['search_items']           = __( 'Search XML Documents', XSLT_TEXT );
        $params['labels']['not_found']              = __( 'No XML Documents found', XSLT_TEXT );
        $params['labels']['not_found_in_trash']     = __( 'No XML Documents found in Trash', XSLT_TEXT );
        $params['labels']['all_items']              = __( 'All XML Documents', XSLT_TEXT );
        $params['labels']['filter_items_list']      = __( 'Filter XML list', XSLT_TEXT );
        $params['labels']['items_list_navigation']  = __( 'XML list navigation', XSLT_TEXT );
        $params['labels']['items_list']             = __( 'XML list', XSLT_TEXT );
        $params['labels']['item_published']         = __( 'XML published.', XSLT_TEXT );
        $params['labels']['item_published_privately'] = __( 'XML published privately.', XSLT_TEXT );
        $params['labels']['item_reverted_to_draft'] = __( 'XML reverted to draft.', XSLT_TEXT );
        $params['labels']['item_trashed']           = __( 'XML trashed.', XSLT_TEXT );
        $params['labels']['item_scheduled']         = __( 'XML scheduled.', XSLT_TEXT );
        $params['labels']['item_updated']           = __( 'XML updated.', XSLT_TEXT );
        $params['labels']['item_link']              = __( 'XML Link', XSLT_TEXT );
        $params['labels']['item_link_description']  = __( 'A link to an XML.', XSLT_TEXT );

//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('params'),true), E_USER_NOTICE); }
        register_post_type( POST_TYPE_XML, $params );
    }

    /**
     * create categories/tags for XSL items
     */
    public static function register_xsl_taxonomies( $params )
    {
        $cat_params = $params;
        $cat_params['description']      = __( 'XSL Stylesheet Categories', XSLT_TEXT );
        $cat_params['rewrite']          = array('slug' => 'xsl-category');
        $cat_params['hierarchical']     = true;
        $cat_params['labels']['name']               = __( 'XSL Categories', XSLT_TEXT );
        $cat_params['labels']['singular_name']      = __( 'XSL Category', XSLT_TEXT );
        $cat_params['labels']['search_items']       = __( 'Search XSL Categories', XSLT_TEXT );
        $cat_params['labels']['all_items']          = __( 'All XSL Categories', XSLT_TEXT );
        $cat_params['labels']['parent_item']        = __( 'Parent XSL Category', XSLT_TEXT );
        $cat_params['labels']['parent_item_colon']  = __( 'Parent XSL Category:', XSLT_TEXT );
        $cat_params['labels']['edit_item']          = __( 'Edit XSL Category', XSLT_TEXT );
        $cat_params['labels']['update_item']        = __( 'Update XSL Category', XSLT_TEXT );
        $cat_params['labels']['add_new_item']       = __( 'Add New XSL Category', XSLT_TEXT );
        $cat_params['labels']['new_item_name']      = __( 'New XSL Category Name', XSLT_TEXT );
        $cat_params['labels']['menu_name']          = __( 'XSL Category', XSLT_TEXT );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('cat_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xsl-category', array(POST_TYPE_XSL), $cat_params );
        register_taxonomy_for_object_type( 'xsl-category', POST_TYPE_XSL );

        $tag_params = $params;
        $tag_params['description']      = __( 'XSL Stylesheet Tags', XSLT_TEXT );
        $tag_params['rewrite']          = array('slug' => 'xsl-tag');
        $tag_params['hierarchical']     = false;
        $tag_params['labels']['name']               = __( 'XSL Tags', XSLT_TEXT );
        $tag_params['labels']['singular_name']      = __( 'XSL Tag', XSLT_TEXT );
        $tag_params['labels']['search_items']       = __( 'Search XSL Tags', XSLT_TEXT );
        $tag_params['labels']['all_items']          = __( 'All XSL Tags', XSLT_TEXT );
        $tag_params['labels']['parent_item']        = __( 'Parent XSL Tag', XSLT_TEXT );
        $tag_params['labels']['parent_item_colon']  = __( 'Parent XSL Tag:', XSLT_TEXT );
        $tag_params['labels']['edit_item']          = __( 'Edit XSL Tag', XSLT_TEXT );
        $tag_params['labels']['update_item']        = __( 'Update XSL Tag', XSLT_TEXT );
        $tag_params['labels']['add_new_item']       = __( 'Add New XSL Tag', XSLT_TEXT );
        $tag_params['labels']['new_item_name']      = __( 'New XSL Tag Name', XSLT_TEXT );
        $tag_params['labels']['menu_name']          = __( 'XSL Tag', XSLT_TEXT );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('tag_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xsl-tag', array(POST_TYPE_XSL), $tag_params );
        register_taxonomy_for_object_type( 'xsl-tag', POST_TYPE_XSL );

    }

    /**
     * create categories/tags for XML items
     */
    public static function register_xml_taxonomies( $params )
    {
        $cat_params = $params;
        $cat_params['description']      = __( 'XML Document Categories', XSLT_TEXT );
        $cat_params['rewrite']          = array('slug' => 'xml-category');
        $cat_params['hierarchical']     = true;
        $cat_params['labels']['name']               = __( 'XML Categories', XSLT_TEXT );
        $cat_params['labels']['singular_name']      = __( 'XML Category', XSLT_TEXT );
        $cat_params['labels']['search_items']       = __( 'Search XML Categories', XSLT_TEXT );
        $cat_params['labels']['all_items']          = __( 'All XML Categories', XSLT_TEXT );
        $cat_params['labels']['parent_item']        = __( 'Parent XML Category', XSLT_TEXT );
        $cat_params['labels']['parent_item_colon']  = __( 'Parent XML Category:', XSLT_TEXT );
        $cat_params['labels']['edit_item']          = __( 'Edit XML Category', XSLT_TEXT );
        $cat_params['labels']['update_item']        = __( 'Update XML Category', XSLT_TEXT );
        $cat_params['labels']['add_new_item']       = __( 'Add New XML Category', XSLT_TEXT );
        $cat_params['labels']['new_item_name']      = __( 'New XML Category Name', XSLT_TEXT );
        $cat_params['labels']['menu_name']          = __( 'XML Category', XSLT_TEXT );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('cat_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xml-category', array(POST_TYPE_XML), $cat_params );
        register_taxonomy_for_object_type( 'xml-category', POST_TYPE_XML );

        $tag_params = $params;
        $tag_params['description']       = __( 'XML Document Tags', XSLT_TEXT );
        $tag_params['rewrite']           = array('slug' => 'xml-tag');
        $tag_params['hierarchical']      = false;
        $tag_params['labels']['name']               = __( 'XML Tags', XSLT_TEXT );
        $tag_params['labels']['singular_name']      = __( 'XML Tag', XSLT_TEXT );
        $tag_params['labels']['search_items']       = __( 'Search XML Tags', XSLT_TEXT );
        $tag_params['labels']['all_items']          = __( 'All XML Tags', XSLT_TEXT );
        $tag_params['labels']['parent_item']        = __( 'Parent XML Tag', XSLT_TEXT );
        $tag_params['labels']['parent_item_colon']  = __( 'Parent XML Tag:', XSLT_TEXT );
        $tag_params['labels']['edit_item']          = __( 'Edit XML Tag', XSLT_TEXT );
        $tag_params['labels']['update_item']        = __( 'Update XML Tag', XSLT_TEXT );
        $tag_params['labels']['add_new_item']       = __( 'Add New XML Tag', XSLT_TEXT );
        $tag_params['labels']['new_item_name']      = __( 'New XML Tag Name', XSLT_TEXT );
        $tag_params['labels']['menu_name']          = __( 'XML Tag', XSLT_TEXT );
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('tag_params'),true), E_USER_NOTICE); }
        register_taxonomy( 'xml-tag', array(POST_TYPE_XML), $tag_params );
        register_taxonomy_for_object_type( 'xml-tag', POST_TYPE_XML );

    }

    /**
     * set post_type = 'xsl' in taxonomy query for xsl-category, xsl-tag
     * set post_type = 'xml' in taxonomy query for xml-category, xml-tag
     */
    public static function update_taxonomy_query( $query )
    {
        if (is_admin() || !$query->is_main_query() || !$query->is_tax()) { return; }

        $xsl_category = $query->get( 'xsl-category', false );
        $xsl_tag      = $query->get( 'xsl-tag', false );
        if ($xsl_category || $xsl_tag) {
            $query->set('post_type', POST_TYPE_XSL);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xsl_category','xsl_tag','query'),true), E_USER_NOTICE); }
        }

        $xml_category = $query->get( 'xml-category', false );
        $xml_tag      = $query->get( 'xml-tag', false );
        if ($xml_category || $xml_tag) {
            $query->set('post_type', POST_TYPE_XML);
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('xml_category','xml_tag','query'),true), E_USER_NOTICE); }
        }

    }


    /**
     * compose excerpt
     */
    public static function the_excerpt_filter( $post_excerpt, $post )
    {
        if (!in_array($post->post_type, array(POST_TYPE_XSL,POST_TYPE_XML)))
            { return $post_excerpt; }
//if (WP_DEBUG) { trigger_error(__METHOD__." : ".print_r(compact('post_excerpt','post'),true), E_USER_NOTICE); }

        $validation_warnings = get_post_meta( $post->ID, '_xslt_validation_warnings', true );
        $validation_errors   = get_post_meta( $post->ID, '_xslt_validation_errors', true );
        if ($validation_errors == -1)
            { return $post_excerpt; }

        $post_content = '';
        $limit = 100;
        if ($validation_warnings == 0 && $validation_errors == 0) {
            $shortcode = '[xml_select root="" xml="'.$post->ID.'" htmlentities /]';
            $post_content = XSLT_Processor_WP::filterPostContent($shortcode);
            $limit = 200;  // many htmlentities
        } else {
            $validation_message = get_post_meta( $post->ID, '_xslt_validation_message', true );
            if ($validation_warnings > 0)
                { $post_content .= $validation_warnings.' WARNING'.(($validation_warnings == 1) ? ' ' : 'S '); }
            if ($validation_errors > 0)
                { $post_content .= $validation_errors.' ERROR'.(($validation_errors == 1) ? ' ' : 'S '); }
            $post_content .= ': '.$validation_message;
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
        if (empty(get_post()) || !in_array(get_post()->post_type, array(POST_TYPE_XSL,POST_TYPE_XML)) )
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

        $post_types = array(POST_TYPE_XSL, POST_TYPE_XML);
        foreach( $post_types as $post_type ) {
            $flds = $xsl_xml_fields;
            if ($post_type == POST_TYPE_XSL) { $flds = array_merge($flds, $xsl_fields); }
            if ($post_type == POST_TYPE_XML) { $flds = array_merge($flds, $xml_fields); }

            foreach( $flds as $fieldname ) {
                register_post_meta(
                    $post_type,
                    $fieldname,
                    $field_options
                );
            }

            add_meta_box(
                '_xslt_validation_meta',
                strtoupper($post_type).' Validation',
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
        $html = '<div id="xslt_validation">';
        $html .= '<table width="100%">';

        if ($post->post_type == POST_TYPE_XML)
        {
            $html .= '<tr><td>';
            $html .= '<label for="_xslt_schema_type"><strong>' . __( 'Method', XSLT_TEXT ) . ' :</strong></label>';
            $html .= '<br/>';
            $html .= '<select id="_xslt_schema_type" name="_xslt_schema_type">'
                . '<option value="none">Syntax Only</option>'
                . '<option value="dtd"' . (($xslt_schema_type == 'dtd') ? ' selected' : '') . '>DTD</option>'
                . '<option value="xsd"' . (($xslt_schema_type == 'xsd') ? ' selected' : '') . '>XSD</option>'
                . '<option value="rng"' . (($xslt_schema_type == 'rng') ? ' selected' : '') . '>RNG</option>'
                .'</select>';
            $html .= '</td></tr>';

            $html .= '<tr><td>';
            $html .= '<label for="_xslt_schema_value"><strong>' . __( 'XSD|RNG File', XSLT_TEXT ) . ' :</strong></label>';
            $html .= '<br/>';
            $html .= '<input type="text" id="_xslt_schema_value" name="_xslt_schema_value"'
                . ' value="'.esc_attr($xslt_schema_value).'" size="'.$value_size.'"'
                .'>';
            $html .= '</td></tr>';
        }

        $html .= '<tr><td>';
        $html .= '<label><strong>' . __( 'Results', XSLT_TEXT ) . ' :</strong></label>';
        $html .= '</td></tr>';
        $html .= '<tr><td id="xslt_validation_message">';

        if ($xslt_validation_warnings > 0)
            { $html .= $xslt_validation_warnings.' Validation Warnings'.(($xslt_validation_warnings == 1) ? ' ' : 's '); }
        if ($xslt_validation_errors > 0)
            { $html .= $xslt_validation_errors.' Validation Error'.(($xslt_validation_errors == 1) ? ' ' : 's '); }
        if ($xslt_validation_warnings > 0 || $xslt_validation_errors > 0)
            { $html .= "<br/>\n"; }

        $html .= $xslt_validation_message;
        $html .= '</td></tr>';

        $html .= '</table>';
        $html .= '</div>';
        echo $html;

        $js = '
<script language="javascript">
const editor = window.wp.data.dispatch("core/editor");
const savePost = editor.savePost;

editor.savePost = function (options) {
    options = options || {};
    return savePost(options)
        .then(() => {
            if (!options.isAutosave) {
                //console.log("savePost");
                el = document.querySelector( "#xslt_validation_message" );
                if (el) { el.innerHTML = "<center><a href=\"\">'.__( 'reload', XSLT_TEXT ).'</a></center>"; }
            }
        });
}
</script>';
        echo $js;
     }

    /**
     * set post_meta '_xslt_schema_type'
     * set post_meta '_xslt_schema_value'
     */
    public static function update_xslt_validation( $post_id, $post, $update )
    {
//if (WP_DEBUG) { trigger_error(__METHOD__." : _POST=".print_r($_POST,true), E_USER_NOTICE); }
        if (!in_array($post->post_type, array(POST_TYPE_XSL,POST_TYPE_XML)) ) { return; }

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
        if (!in_array($post->post_type, array(POST_TYPE_XSL,POST_TYPE_XML))) { return; }

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

            if ($post->post_type == POST_TYPE_XSL)
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
