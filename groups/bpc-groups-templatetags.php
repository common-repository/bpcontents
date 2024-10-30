<?php

function oci_the_group_category_list($args = ''){
	$defaults = array('taxonomy' => OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY);
	$args = wp_parse_args($args, $defaults);
	echo oci_list_categories($args);
}

function oci_the_group_action($action = null){
	echo oci_get_the_group_action($action);
}
function oci_get_the_group_action($action = null){
	global $bp, $group_obj;

	if ($action)
		$action = '/' . $action;

	$path = bp_get_group_permalink( $group_obj ) . '/' . $bp->contents->slug .  '/' . OCI_PROFILE . $action;
	return apply_filters('oci_get_the_group_action', $path);
}

// current=0 for no selected cats
function oci_the_group_category_checklist($args = ''){
	global $group_obj;

	$defaults = array('taxonomy' => OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY, 'current' => true);
	$args = wp_parse_args($args, $defaults);
	extract($args);

	if ($current){
		$item_id = oci_get_group_item_id($group_obj->id);
	}

	echo oci_category_checklist( $taxonomy, $item_id);
}

function oci_the_group_tag_cloud($args = ''){
	$defaults = array('taxonomy' => OCI_SITE_WIDE_GROUP_TAG_TAXONOMY);
	$args = wp_parse_args($args, $defaults);

	echo oci_get_tag_cloud($args);
}

function oci_group_contents_header_tabs() {
	global $bp, $group_obj;
	$group_link = $bp->root_domain . '/' . $bp->groups->slug . '/' . $group_obj->slug . '/' . $bp->contents->slug
?>
	<li<?php if ( !isset($bp->action_variables[1]) || OCI_TAG == $bp->action_variables[1] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $group_link . '/' . OCI_PROFILE . '/' . OCI_TAG ?>"><?php _e( 'Tags', 'bpcontents' ) ?></a></li>
	<li<?php if ( OCI_CATEGORY == $bp->action_variables[1] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $group_link . '/' . OCI_PROFILE . '/' . OCI_CATEGORY ?>"><?php _e( 'Categories', 'bpcontents' ) ?></a></li>

<?php
	do_action( 'oci_group_contents_header_tabs' );
}

function oci_the_group_dropdown_categories($args = ''){

	$defaults = array(
		'hierarchical' => true,
		'hide_empty' => false,
		'name' => 'parent',
		'taxonomy' => OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY
	);
	$args = wp_parse_args($args, $defaults);

	echo oci_dropdown_categories($args);
}

function oci_the_group_tags_edit($taxonomy = false){
	echo oci_get_the_group_tags_edit($taxonomy);
}
function oci_get_the_group_tags_edit($taxonomy = false){
	global $group_obj;

	if (!$taxonomy)
		$taxonomy = OCI_SITE_WIDE_GROUP_TAG_TAXONOMY;
	$id = oci_get_group_item_id($group_obj->id);
	return oci_get_item_tags_edit($id, $taxonomy);
}

function oci_the_group_tags($args = ''){
	echo oci_get_the_group_tags($args);
}
function oci_get_the_group_tags($args = ''){
	global $group_obj;

	$defaults = array('taxonomy' => OCI_SITE_WIDE_GROUP_TAG_TAXONOMY, 'url' => OCI_DEFAULT_URL,
		'before' => '', 'sep' => ' ', 'after' => ''
	);
	$args = wp_parse_args($args, $defaults);
	extract($args);

	$item_id = oci_get_group_item_id($group_obj->id);
	return oci_the_term_list( $item_id, $taxonomy, $url, $before, $sep, $after);
}

function oci_the_group_categories($args = ''){
	echo oci_get_the_group_categories($args);
}
function oci_get_the_group_categories($args = ''){
	global $group_obj;

	$defaults = array('taxonomy' => OCI_SITE_WIDE_GROUP_CATEGORY_TAXONOMY, 'url' => OCI_DEFAULT_URL,
		'before' => '', 'sep' => ' ', 'after' => ''
	);
	$args = wp_parse_args($args, $defaults);
	extract($args);

	$item_id = oci_get_group_item_id($group_obj->id);
	return oci_the_term_list( $item_id, $taxonomy, $url, $before, $sep, $after);
}

function oci_the_group_profile_box(){
	?>
<div class="info-group">
	<h4><?php _e( 'Tags', 'bpcontents' ) ?></h4>
	<?php oci_the_group_tags() ?>
</div>
<div class="info-group">
	<h4><?php _e( 'Categories', 'bpcontents' ) ?></h4>
	<?php oci_the_group_categories() ?>
</div>

	<?php
}
add_action('groups_sidebar_after', 'oci_the_group_profile_box');


function oci_get_group_item_id($group_id){

	$group = new BP_Groups_Group($group_id);
	if (!$group->id)
		return false;

	$item = new OCI_Item_Group($group);
	if (!$item->id)
		$item->save();

	return $item->id;
}

?>
