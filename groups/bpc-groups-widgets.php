<?php

function oci_register_groups_widgets() {

	wp_register_sidebar_widget( 'oci-wcg', __( 'Group Tags', 'bpcontents' ), 'oci_widget_cloud_groups' );
	wp_register_widget_control( 'oci-wcg', __( 'Group Tags', 'bpcontents' ), 'oci_widget_cloud_groups_control' );
	wp_register_sidebar_widget( 'oci-wtg', __( 'Group Categories', 'bpcontents' ), 'oci_widget_categories_groups' );
	wp_register_widget_control( 'oci-wtg', __( 'Group Categories', 'bpcontents' ), 'oci_widget_categories_groups_control' );
}
add_action( 'plugins_loaded', 'oci_register_groups_widgets' );

function oci_widget_cloud_groups($args) {
	global $current_blog;

	$args = wp_parse_args( $args );

  extract($args);
	$options = get_blog_option( $current_blog->blog_id, 'oci_wcg' );

	echo $before_widget;
	echo $before_title;

	if ( $options['title'] ){
		echo $options['title'];
	}
	else
		echo $widget_name;

	echo $after_title;
	switch_to_blog(BP_ROOT_BLOG);
	oci_the_group_tag_cloud($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_cloud_groups_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wcg' );

	if ( $_POST['oci-wcg-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wcg-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wcg', $options );
	}

	$title = attribute_escape($options['title']);

?>
		<p><label for="oci-wcg-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wcg-title" name="oci-wcg-title" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<input type="hidden" id="oci-wcg-submit" name="oci-wcg-submit" value="1" />
<?php
}

function oci_widget_categories_groups($args = '') {
	global $current_blog;

	$args = wp_parse_args( $args );
  extract($args);

	$options = get_blog_option( $current_blog->blog_id, 'oci_wtb' );

	echo $before_widget;
	echo $before_title;

	if ( $options['title'] ){
		echo $options['title'];
	}
	else
		echo $widget_name;

	echo $after_title;
	switch_to_blog(BP_ROOT_BLOG);
	oci_the_group_category_list($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_categories_groups_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wtg' );

	if ( $_POST['oci-wtg-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wtg-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wtg', $options );
	}

	$title = attribute_escape($options['title']);
?>
		<p><label for="oci-wtg-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wtg-title" name="oci-wtg-title" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<input type="hidden" id="oci-wtg-submit" name="oci-wtg-submit" value="1" />
<?php
}

?>
