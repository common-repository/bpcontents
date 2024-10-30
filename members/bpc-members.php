<?php

define('OCI_USER', 'user');

define('OCI_SITE_WIDE_USER_TAG_TAXONOMY', 't_' . OCI_SITE_WIDE . '_' . OCI_USER);
define('OCI_SITE_WIDE_USER_CATEGORY_TAXONOMY', 'c_' . OCI_SITE_WIDE . '_' . OCI_USER);

include( WP_PLUGIN_DIR . '/bpcontents/members/bpc-members-classes.php' );
include( WP_PLUGIN_DIR . '/bpcontents/members/bpc-members-templatetags.php' );
include( WP_PLUGIN_DIR . '/bpcontents/members/bpc-members-widgets.php' );

function oci_remove_data_for_member($user_id){
	$id = oci_get_member_item_id($user_id);
	$item = new OCI_Item($id);
	if ($item->id)
		$item->delete();
}
add_action( 'wpmu_delete_user', 'oci_remove_data_for_member', 1 );
add_action( 'delete_user', 'oci_remove_data_for_member', 1 );

function oci_members_register_default_taxonomies(){
	global $bp;
	oci_register_taxonomy(__('Member Tags', 'bpcontents'), OCI_SITE_WIDE, false, false, OCI_USER);
	oci_register_taxonomy(__('Member Categories', 'bpcontents'), OCI_SITE_WIDE, true, false, OCI_USER);
}
add_action('init', 'oci_members_register_default_taxonomies');

function oci_members_setup_globals(){
	oci_register_content_type(OCI_USER, 'user', apply_filters('oci_content_type_name',__('Member','bpcontents'), OCI_USER));
}
add_action( 'plugins_loaded', 'oci_members_setup_globals', 5 );
add_action( 'admin_menu', 'oci_members_setup_globals', 1 );

function oci_members_setup_nav() {
	global $bp;

	// are we in the member theme
	if ($bp->current_component == $bp->contents->slug){
		if ( bp_is_home() ) {
			$profile_link = $bp->loggedin_user->domain . $bp->contents->slug . '/' ;
			bp_core_add_subnav_item($bp->contents->slug, OCI_PROFILE , __( 'Profile', 'bpcontents' ),	$profile_link, 'oci_screen_profile_contents');

			if (is_site_admin())
				bp_core_add_subnav_item($bp->contents->slug, 'admin' , __( 'Site Admin', 'bpcontents' ),	$profile_link, 'oci_screen_profile_site_admin_settings');

			$bp->bp_options_title = __('My Contents', 'bpcontents');
		}
	}

}
add_action( 'wp', 'oci_members_setup_nav', 2 );
add_action( 'admin_menu', 'oci_members_setup_nav', 2 );

function oci_screen_profile_contents() {
	global $bp;
	if ( $bp->current_component == $bp->contents->slug && empty($bp->action_variables)) {
		$bp->is_directory = false;
		bp_core_load_template( 'bpcontents/profile/profile-tags' );
	}
}

function oci_screen_profile_tags() {
	global $bp;

	if ( !($bp->current_component == $bp->contents->slug &&
		'profile' == $bp->current_action &&	OCI_TAG == $bp->action_variables[0]))
		return;


	if (!bp_is_home())
		return;

	$bp->is_directory = false;
	if ( isset( $_POST['submit_save_profile_tags'] )) {
		if ( !check_admin_referer( 'oci_update_member_tags' ) )
			return false;

		$item_id = oci_get_member_item_id($bp->loggedin_user->id);

		// update sitewide tags and member tags
		if ($item_id){
			oci_update_item_tags($item_id, $_POST['oci_member_tags'], OCI_DEFAULT_TAG_TAXONOMY);
			oci_update_item_tags($item_id, $_POST['oci_member_tags'], OCI_SITE_WIDE_USER_TAG_TAXONOMY);
		}

		$path = oci_get_the_profile_action(OCI_TAG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/profile/profile-tags' );
}
add_action( 'wp', 'oci_screen_profile_tags', 4 );

function oci_screen_profile_categories() {
	global $bp;

	if ( !($bp->current_component == $bp->contents->slug &&
		OCI_PROFILE == $bp->current_action && OCI_CATEGORY == $bp->action_variables[0]))
		return;

	if (!bp_is_home())
		return;

	$bp->is_directory = false;
	if ( isset( $_POST['submit_save_profile_categories'] )) {
		if ( !check_admin_referer( 'oci_update_member_categories' ) )
			return false;

		$item_id = oci_get_member_item_id($bp->loggedin_user->id);

		// update member categories
		if (isset($_POST['item_category'])){
			if ($item_id){
				oci_update_item_categories($item_id, $_POST['item_category'], OCI_SITE_WIDE_USER_CATEGORY_TAXONOMY);
			}
		} else {
			oci_update_item_categories($item_id, array(), OCI_SITE_WIDE_USER_CATEGORY_TAXONOMY);
		}

		$path = oci_get_the_profile_action(OCI_CATEGORY);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/profile/profile-categories' );
}
add_action( 'wp', 'oci_screen_profile_categories', 4 );

function oci_screen_profile_site_admin_settings() {
	global $bp;
	if ( $bp->current_component == $bp->contents->slug && !$bp->action_variables ) {
		bp_core_load_template( 'bpcontents/profile/admin-members' );
	}
}

function oci_screen_profile_site_admin_members(){
	global $bp;

	if ( !($bp->current_component == $bp->contents->slug && BP_MEMBERS_SLUG == $bp->action_variables[0] ))
		return;
	if (!bp_is_home() || !is_site_admin())
		return;

	if ( isset( $_POST['submit_add_member_categories'] )) {
		if ( !check_admin_referer( 'oci_new_member_categories' ) )
			return false;

		if ('-1' == $_POST['parent'] || !isset($_POST['parent'])){
			$parent_id = 0;
		}
		else
			$parent_id = $_POST['parent'];

		$categories = explode(',', $_POST['oci_new_member_categories']);

		oci_create_categories( $categories, OCI_SITE_WIDE_USER_CATEGORY_TAXONOMY, $parent_id);

		$path = oci_get_the_profile_action(BP_MEMBERS_SLUG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}
	else if ( isset( $_POST['submit_delete_member_categories'] )) {
		if ( !check_admin_referer( 'oci_delete_member_categories' ) )
			return false;

		if (isset($_POST['item_category'])){
			$term_ids = $_POST['item_category'];
			foreach ($term_ids as $id){
				oci_delete_category($id, OCI_SITE_WIDE_USER_CATEGORY_TAXONOMY);
			}
		}

		$path = oci_get_the_profile_action(BP_MEMBERS_SLUG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/profile/admin-members' );
}
add_action( 'wp', 'oci_screen_profile_site_admin_members', 4 );

function oci_screen_profile_site_admin_terms(){
	global $bp;

	if ( !($bp->current_component == $bp->contents->slug && OCI_TERM == $bp->action_variables[0] ))
		return;
	if (!bp_is_home() || !is_site_admin())
		return;

	if ( isset( $_POST['submit_dropdown_taxonomies_go'] )) {
		if ( !check_admin_referer( 'oci_term_maintenance' ) )
			return false;

		$taxonomy = $_POST['tax'];

		$path = oci_get_the_profile_action(OCI_TERM);
		$path .= '/?taxonomy=' . $taxonomy;
		bp_core_redirect($path);
	}

	if ( isset( $_POST['submit_delete_term_edit_fields'] )) {
		if ( !check_admin_referer( 'oci_term_maintenance' ) )
			return false;

		$taxonomy = $_POST['taxonomy'];
		$term_id = $_POST['term_id'];
		$slug = $_POST['slug'];

		if (is_taxonomy_hierarchical($taxonomy))
			oci_delete_category($term_id, $taxonomy);
		else
			wp_delete_term($term_id, $taxonomy);
			
		$path = oci_get_the_profile_action(OCI_TERM);
		$path .= '/?taxonomy=' . $taxonomy;
		bp_core_add_message(__('Term deleted.', 'bpcontents'));
		bp_core_redirect($path);
	}

	if ( isset( $_POST['submit_set_term_edit_fields'] )) {
		if ( !check_admin_referer( 'oci_term_maintenance' ) )
			return false;

		$taxonomy = $_POST['taxonomy'];
		$term_id = $_POST['term_id'];
		$slug = $_POST['slug'];

		$name = $_POST['oci_term_name'];
		$description = $_POST['oci_term_description'];
		if (isset($_POST['parent'])){
			$parent = $_POST['parent'];
			if ('-1' == $parent) // none
				$parent = 0;
		}
		else{
			$parent = 0;
		}

		$args = array('name' => $name, 'description' => $description, 'parent' => $parent);
		wp_update_term( $term_id, $taxonomy, $args );
		$path = oci_get_the_profile_action(OCI_TERM);
		$path .= '/?taxonomy=' . $taxonomy . '&term=' . $slug;
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/profile/admin-term-list' );
}
add_action( 'wp', 'oci_screen_profile_site_admin_terms', 4 );


?>
