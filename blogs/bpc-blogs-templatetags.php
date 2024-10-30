<?php

function oci_the_blog_category_list($args = ''){
	$defaults = array('taxonomy' => OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY);
	$args = wp_parse_args($args, $defaults);
	echo oci_list_categories($args);
}

function oci_the_blog_dropdown_categories($args = ''){

	$defaults = array(
		'hierarchical' => true,
		'hide_empty' => false,
		'name' => 'parent',
		'taxonomy' => OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY
	);
	$args = wp_parse_args($args, $defaults);

	echo oci_dropdown_categories($args);
}

function oci_the_blog_tag_cloud($args = ''){
	$defaults = array('taxonomy' => OCI_SITE_WIDE_BLOG_TAG_TAXONOMY);
	$args = wp_parse_args($args, $defaults);

	echo oci_get_tag_cloud($args);
}

function oci_blog_contents_header_tabs() {
	global $bp;

		$blogs_link = $bp->loggedin_user->domain . $bp->blogs->slug . '/' . $bp->contents->slug;
		$blog_id = oci_get_blog_id_from_url();
		if ($blog_id)
			$blog_id_link = '/' . $blog_id;
?>
	<li<?php if ( !isset($bp->action_variables[0]) || 'select-blog' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $blogs_link . '/select-blog' ?>"><?php _e( 'Select A Blog', 'bpcontents' ) ?></a></li>
	<li<?php if ( isset($bp->action_variables[1]) && OCI_TAG == $bp->action_variables[1] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $blogs_link . '/' . OCI_PROFILE . '/' . OCI_TAG . $blog_id_link ?>"><?php _e( 'Tags', 'bpcontents' ) ?></a></li>
	<li<?php if ( isset($bp->action_variables[1]) && OCI_CATEGORY == $bp->action_variables[1] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $blogs_link . '/' . OCI_PROFILE . '/' . OCI_CATEGORY . $blog_id_link ?>"><?php _e( 'Categories', 'bpcontents' ) ?></a></li>

<?php
	do_action( 'blogs_header_tabs' );
}

function oci_has_selected_blog(){
	global $bp;

	$blog_id = oci_get_blog_id_from_url();
	
	return $blog_id;
}

function oci_get_blog_id_from_url(){
	global $bp;

	if (isset($bp->action_variables[2]) && is_numeric($bp->action_variables[2])){
		$blog_id = $bp->action_variables[2];
	}
	return $blog_id;
}

function oci_the_blog_action($action){
	echo oci_get_the_blog_action($action);
}
function oci_get_the_blog_action($action){
	global $bp;
	$blog_id = oci_get_blog_id_from_url();
	$path = $bp->loggedin_user->domain . $bp->blogs->slug . '/' . $bp->contents->slug . '/' . 
		OCI_PROFILE . '/' . $action . '/' . $blog_id;

	return apply_filters('oci_get_the_blog_action', $path);
}

function oci_the_blog_contents_link(){
	echo oci_get_the_blog_contents_link();
}
function oci_get_the_blog_contents_link(){
	global $bp, $blogs_template;

	return $bp->loggedin_user->domain . $bp->blogs->slug . '/' . $bp->contents->slug . '/' .
	OCI_PROFILE . '/' . OCI_TAG . '/' . $blogs_template->blog['id'];
}

function oci_the_blog_title(){
	echo oci_get_the_blog_title();
}
function oci_get_the_blog_title(){
	$blog_id = oci_get_blog_id_from_url();
	return get_blog_option($blog_id, 'blogname');
}

// current=0 for no selected cats
function oci_the_blog_category_checklist($args = ''){
	global $bp;

	$defaults = array('taxonomy' => OCI_SITE_WIDE_BLOG_CATEGORY_TAXONOMY, 'current' => true);
	$args = wp_parse_args($args, $defaults);
	extract($args);

	if ($current){
		$blog_id = oci_get_blog_id_from_url();
		$item_id = oci_get_blog_item_id($blog_id);
	}

	echo oci_category_checklist( $taxonomy, $item_id);
}

function oci_the_blog_tags_edit($taxonomy = false){
	echo oci_get_the_blog_tags_edit($taxonomy);
}
function oci_get_the_blog_tags_edit($taxonomy = false){

	if (!$taxonomy)
		$taxonomy = OCI_SITE_WIDE_BLOG_TAG_TAXONOMY;

	$blog_id = oci_get_blog_id_from_url();
	if (!$blog_id)
		return '';

	$id = oci_get_blog_item_id($blog_id);
	return oci_get_item_tags_edit($id, $taxonomy);
}

function oci_the_blog_tags($args = ''){
	echo oci_get_the_blog_tags($args);
}
function oci_get_the_blog_tags($args = ''){
	global $current_blog;

	$defaults = array('taxonomy' => OCI_SITE_WIDE_BLOG_TAG_TAXONOMY, 'url' => OCI_DEFAULT_URL,
		'before' => '', 'sep' => ' ', 'after' => ''
	);
	$args = wp_parse_args($args, $defaults);
	extract($args);

	$blog_id = oci_get_blog_id_from_url();
	if (!$blog_id)
		$blog_id = $current_blog->id;

	$id = oci_get_blog_item_id($id);
	return oci_the_term_list( $src_item->id, $taxonomy, $url, $before, $sep, $after);
}

function oci_get_blog_item_id($blog_id){

	$blog = get_blog_details($blog_id);
	if (!$blog->blog_id)
		return false;

	$item = new OCI_Item_Blog($blog);
	if (!$item->id)
		$item->save();

	return $item->id;
}

?>
