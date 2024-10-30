<?php

define('OCI_GROUP', 'group');

define('OCI_SITE_WIDE_GROUP_TAG_TAXONOMY', 't_' . OCI_SITE_WIDE . '_' . OCI_GROUP);
define('OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY', 'c_' . OCI_SITE_WIDE . '_' . OCI_GROUP);

include( WP_PLUGIN_DIR . '/bpcontents/groups/bpc-groups-classes.php' );
include( WP_PLUGIN_DIR . '/bpcontents/groups/bpc-groups-templatetags.php' );
include( WP_PLUGIN_DIR . '/bpcontents/groups/bpc-groups-widgets.php' );

function oci_remove_data_for_group( $group_id ) {
	$id = oci_get_group_item_id($group_id);
	$item = new OCI_Item($id);
	if ($item->id)
		$item->delete();
}
add_action( 'groups_delete_group', 'oci_remove_data_for_group', 1 );

function oci_groups_register_default_taxonomies(){
	global $bp;
	oci_register_taxonomy(__('Group Tags', 'bpcontents'), OCI_SITE_WIDE, false, false, OCI_GROUP);
	oci_register_taxonomy(__('Group Categories', 'bpcontents'), OCI_SITE_WIDE, true, false, OCI_GROUP);
}
add_action('init', 'oci_groups_register_default_taxonomies');

function oci_groups_setup_globals(){
	oci_register_content_type(OCI_GROUP, 'group', apply_filters('oci_content_type_name', __('Group','bpcontents'), OCI_GROUP));
}
add_action( 'plugins_loaded', 'oci_groups_setup_globals', 5 );
add_action( 'admin_menu', 'oci_groups_setup_globals', 1 );

function oci_groups_setup_nav() {
	global $bp, $group_obj;

	if ($bp->current_component == $bp->groups->slug && $bp->is_single_item){

		$group_link = $bp->root_domain . '/' . $bp->groups->slug . '/' . $group_obj->slug . '/';

		if ( $bp->is_item_mod || $bp->is_item_admin )
			bp_core_add_subnav_item($bp->groups->slug, $bp->contents->slug , __( 'Contents', 'bpcontents' ),	$group_link, 'oci_screen_group_contents');
	}

}
add_action( 'wp', 'oci_groups_setup_nav', 2 );
add_action( 'admin_menu', 'oci_groups_setup_nav', 2 );

function oci_screen_profile_site_admin_groups(){
	global $bp;

	if ( !($bp->current_component == $bp->contents->slug && BP_GROUPS_SLUG == $bp->action_variables[0] ))
		return;

		if (!bp_is_home() || !is_site_admin())
			return;

		if ( isset( $_POST['submit_add_group_categories'] )) {
			if ( !check_admin_referer( 'oci_new_group_categories' ) )
				return false;

			if ('-1' == $_POST['parent'] || !isset($_POST['parent'])){
				$parent_id = 0;
			}
			else
				$parent_id = $_POST['parent'];

			$categories = explode(',', $_POST['oci_new_group_categories']);
			oci_create_categories( $categories, OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY, $parent_id);

			$path = oci_get_the_profile_action(BP_GROUPS_SLUG);
			bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
			bp_core_redirect($path);
		}
		else if ( isset( $_POST['submit_delete_group_categories'] )) {
			if ( !check_admin_referer( 'oci_delete_group_categories' ) )
				return false;

			if (isset($_POST['item_category'])){
				$term_ids = $_POST['item_category'];
				foreach ($term_ids as $id){
					oci_delete_category($id, OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY);
				}
			}

			$path = oci_get_the_profile_action(BP_GROUPS_SLUG);
			bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
			bp_core_redirect($path);
		}

		bp_core_load_template( 'bpcontents/profile/admin-groups' );
}
add_action( 'wp', 'oci_screen_profile_site_admin_groups', 4 );

function oci_screen_group_contents() {
	global $bp;
	if ( $bp->current_component == $bp->groups->slug && empty($bp->action_variables) ) {
		$bp->is_directory = false;
		bp_core_load_template( 'bpcontents/groups/group-tags' );
	}
}

function oci_screen_group_categories() {
	global $bp, $group_obj;

	if ( !($bp->current_component == $bp->groups->slug &&
		$bp->contents->slug == $bp->current_action &&	OCI_CATEGORY == $bp->action_variables[1]))
		return;

	if ( isset( $_POST['submit_save_group_categories'] )) {
		if ( !check_admin_referer( 'oci_update_group_categories' ) )
			return false;

		$item_id = oci_get_group_item_id($group_obj->id);

		if (isset($_POST['item_category'])){
			if ($item_id){
				oci_update_item_categories($item_id, $_POST['item_category'], OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY);
			}
		} else {
			oci_update_item_categories($item_id, array(), OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY);
		}

		$path = oci_get_the_group_action(OCI_CATEGORY);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/groups/group-categories' );
}
add_action( 'wp', 'oci_screen_group_categories', 4 );

function oci_screen_group_tags() {
	global $group_obj, $bp;

	if ( !($bp->current_component == $bp->groups->slug &&
		$bp->contents->slug == $bp->current_action &&	OCI_TAG == $bp->action_variables[1]))
		return;

	if ( isset( $_POST['submit_save_group_tags'] )) {
		if ( !check_admin_referer( 'oci_update_group_tags' ) )
			return false;

		$item_id = oci_get_group_item_id($group_obj->id);

		if ($item_id){
			oci_update_item_tags($item_id, $_POST['oci_group_tags'], OCI_DEFAULT_TAG_TAXONOMY);
			oci_update_item_tags($item_id, $_POST['oci_group_tags'], OCI_SITE_WIDE_GROUP_TAG_TAXONOMY);
		}
		$path = oci_get_the_group_action(OCI_TAG);
		bp_core_add_message(__('Your settings have been successfully saved.', 'bpcontents'));
		bp_core_redirect($path);
	}

	bp_core_load_template( 'bpcontents/groups/group-tags' );
}
add_action( 'wp', 'oci_screen_group_tags', 4 );

?>
