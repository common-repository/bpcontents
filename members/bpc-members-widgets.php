<?php

function oci_register_members_widgets() {

	wp_register_sidebar_widget( 'oci-wcm', __( 'Member Tags', 'bpcontents' ), 'oci_widget_cloud_members' );
	wp_register_widget_control( 'oci-wcm', __( 'Member Tags', 'bpcontents' ), 'oci_widget_cloud_members_control' );
	wp_register_sidebar_widget( 'oci-wtm', __( 'Member Categories', 'bpcontents' ), 'oci_widget_categories_members' );
	wp_register_widget_control( 'oci-wtm', __( 'Member Categories', 'bpcontents' ), 'oci_widget_categories_members_control' );
}
add_action( 'plugins_loaded', 'oci_register_members_widgets' );

function oci_widget_cloud_members($args = '') {
	global $current_blog;

	$args = wp_parse_args( $args );
  extract($args);

	$options = get_blog_option( $current_blog->blog_id, 'oci_wcm' );

	echo $before_widget;
	echo $before_title;

	if ( $options['title'] ){
		echo $options['title'];
	}
	else
		echo $widget_name;

	echo $after_title;
	switch_to_blog(BP_ROOT_BLOG);
	oci_the_member_tag_cloud($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_cloud_members_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wcm' );

	if ( $_POST['oci-wcm-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wcm-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wcm', $options );
	}

	$title = attribute_escape($options['title']);

?>
		<p><label for="oci-wcm-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wcm-title" name="oci-wcm-title" type="text" value="<?php echo $title; ?>" />
		</label></p>
		<input type="hidden" id="oci-wcm-submit" name="oci-wcm-submit" value="1" />
<?php
}

function oci_widget_categories_members($args = '') {
	global $current_blog;

	$args = wp_parse_args( $args );
  extract($args);

	$options = get_blog_option( $current_blog->blog_id, 'oci_wtm' );

	echo $before_widget;
	echo $before_title;

	if ( $options['title'] ){
		echo $options['title'];
	}
	else
		echo $widget_name;

	echo $after_title;
	switch_to_blog(BP_ROOT_BLOG);
	oci_the_member_category_list($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_categories_members_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wtm' );

	if ( $_POST['oci-wtm-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wtm-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wtm', $options );
	}

	$title = attribute_escape($options['title']);
?>
		<p><label for="oci-wtm-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wtm-title" name="oci-wtm-title" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<input type="hidden" id="oci-wtm-submit" name="oci-wtm-submit" value="1" />
<?php
}

?>
