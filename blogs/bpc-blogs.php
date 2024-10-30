<?php

define('OCI_BLOG', 'blog');

define('OCI_SITE_WIDE_BLOG_TAG_TAXONOMY', 't_' . OCI_SITE_WIDE . '_' . OCI_BLOG);
define('OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY', 'c_' . OCI_SITE_WIDE . '_' . OCI_BLOG);

include( WP_PLUGIN_DIR . '/bpcontents/blogs/bpc-blogs-classes.php' );
include( WP_PLUGIN_DIR . '/bpcontents/blogs/bpc-blogs-templatetags.php' );
include( WP_PLUGIN_DIR . '/bpcontents/blogs/bpc-blogs-widgets.php' );

function oci_remove_data_for_blog( $blog_id ) {
	$id = oci_get_blog_item_id($blog_id);
	$item = new OCI_Item($id);
	if ($item->id)
		$item->delete();
}
add_action( 'bp_blogs_remove_data_for_blog', 'oci_remove_data_for_blog', 1 );

function oci_blogs_register_default_taxonomies(){
	global $bp;
	oci_register_taxonomy(__('Blog Tags', 'bpcontents'), OCI_SITE_WIDE, false, false, OCI_BLOG);
	oci_register_taxonomy(__('Blog Categories', 'bpcontents'), OCI_SITE_WIDE, true, false, OCI_BLOG);
}
add_action('init', 'oci_blogs_register_default_taxonomies');

function oci_blogs_setup_globals(){
	oci_register_content_type(OCI_BLOG, 'blog', apply_filters('oci_content_type_name',__('Blog','bpcontents'), OCI_BLOG));
}
add_action( 'plugins_loaded', 'oci_blogs_setup_globals', 5 );
add_action( 'admin_menu', 'oci_blogs_setup_globals', 1 );

function oci_blogs_setup_nav() {
	global $bp;

	if ($bp->current_component == $bp->blogs->slug){

		$blogs_link = $bp->loggedin_user->domain . $bp->blogs->slug . '/';

		/* Add the subnav items to the blogs nav item */
		bp_core_add_subnav_item( $bp->blogs->slug, $bp->contents->slug , __('Contents', 'bpcontents'), $blogs_link, 'oci_screen_blog_contents' );
	}

}
add_action( 'wp', 'oci_blogs_setup_nav', 2 );
add_action( 'admin_menu', 'oci_blogs_setup_nav', 2 );

function oci_screen_profile_site_admin_blogs(){
	global $bp;

	if ( !($bp->current_component == $bp->contents->slug && BP_BLOGS_SLUG == $bp->action_variables[0] ))
		return;

	if (!bp_is_home() || !is_site_admin())
		return;

	if ( isset( $_POST['submit_add_blog_categories'] )) {
		if ( !check_admin_referer( 'oci_new_blog_categories' ) )
			return false;

		if ('-1' == $_POST['parent'] || !isset($_POST['parent'])){
			$parent_id = 0;
		}
		else
			$parent_id = $_POST['parent'];

		$categories = explode(',', $_POST['oci_new_blog_categories']);
		oci_create_categories( $categories, OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY, $parent_id);

		$path = oci_get_the_profile_action(BP_BLOGS_SLUG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}
	else if ( isset( $_POST['submit_delete_blog_categories'] )) {
		if ( !check_admin_referer( 'oci_delete_blog_categories' ) )
			return false;

		if (isset($_POST['item_category'])){
			$term_ids = $_POST['item_category'];
			foreach ($term_ids as $id){
				oci_delete_category($id, OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY);
			}
		}

		$path = oci_get_the_profile_action(BP_BLOGS_SLUG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/profile/admin-blogs' );

}
add_action( 'wp', 'oci_screen_profile_site_admin_blogs', 4 );

function oci_screen_blog_contents() {
	global $bp;

	if ( $bp->current_action == $bp->contents->slug && empty($bp->action_variables) ) {
			bp_core_load_template( 'bpcontents/blogs/select-blog' );
	}
}

function oci_screen_blog_select() {
	global $bp;
//var_dump($bp->current_component, $bp->current_action, $bp->action_variables);

	if ($bp->current_component == $bp->blogs->slug && $bp->current_action == $bp->contents->slug &&
		'select-blog' == $bp->action_variables[0]) {
			bp_core_load_template( 'bpcontents/blogs/select-blog' );
	}

}
add_action( 'wp', 'oci_screen_blog_select', 4 );

function oci_screen_blog_categories() {
	global $bp;

	if ( !($bp->current_component == $bp->blogs->slug && $bp->current_action == $bp->contents->slug	&&
		OCI_CATEGORY == $bp->action_variables[1]))
		return;

	if ( isset( $_POST['submit_save_blog_categories'] )) {
		if ( !check_admin_referer( 'oci_update_blog_categories' ) )
			return false;

		$blog_id = oci_get_blog_id_from_url();
		$item_id = oci_get_blog_item_id($blog_id);

		if (isset($_POST['item_category'])){
			if ($item_id){
				oci_update_item_categories($item_id, $_POST['item_category'], OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY);
			}
		} else {
			oci_update_item_categories($item_id, array(), OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY);
		}

		$path = oci_get_the_blog_action(OCI_CATEGORY);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/blogs/blog-categories' );
}
add_action( 'wp', 'oci_screen_blog_categories', 4 );

function oci_screen_blog_tags() {
	global $bp;

	if ( !($bp->current_component == $bp->blogs->slug && $bp->current_action == $bp->contents->slug	&&
		OCI_TAG == $bp->action_variables[1]))
		return;

	if ( isset( $_POST['submit_save_blog_tags'] )) {
		if (!check_admin_referer('oci_update_blog_tags'))
			return false;
			
		$blog_id = oci_get_blog_id_from_url();
		$item_id = oci_get_blog_item_id($blog_id);

		if ($item_id){
			oci_update_item_tags($item_id, $_POST['oci_blog_tags'], OCI_DEFAULT_TAG_TAXONOMY);
			oci_update_item_tags($item_id, $_POST['oci_blog_tags'], OCI_SITE_WIDE_BLOG_TAG_TAXONOMY);
		}
		$path = oci_get_the_blog_action(OCI_TAG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/blogs/blog-tags' );
}
add_action( 'wp', 'oci_screen_blog_tags', 4 );

?>
