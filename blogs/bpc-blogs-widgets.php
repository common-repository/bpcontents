<?php

function oci_register_blogs_widgets() {

	wp_register_sidebar_widget( 'oci-wcb', __( 'Blog Tags', 'bpcontents' ), 'oci_widget_cloud_blogs' );
	wp_register_widget_control( 'oci-wcb', __( 'Blog Tags', 'bpcontents' ), 'oci_widget_cloud_blogs_control' );
	wp_register_sidebar_widget( 'oci-wtb', __( 'Blog Categories', 'bpcontents' ), 'oci_widget_categories_blogs' );
	wp_register_widget_control( 'oci-wtb', __( 'Blog Categories', 'bpcontents' ), 'oci_widget_categories_blogs_control' );

}
add_action( 'plugins_loaded', 'oci_register_blogs_widgets' );

function oci_widget_cloud_blogs($args = '') {
	global $current_blog;

	$args = wp_parse_args( $args );
  extract($args);

	$options = get_blog_option( $current_blog->blog_id, 'oci_wcb' );

	echo $before_widget;
	echo $before_title;

	if ( $options['title'] ){
		echo $options['title'];
	}
	else
		echo $widget_name;

	echo $after_title;
	switch_to_blog(BP_ROOT_BLOG);
	oci_the_blog_tag_cloud($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_cloud_blogs_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wcb' );

	if ( $_POST['oci-wcb-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wcb-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wcb', $options );
	}

	$title = attribute_escape($options['title']);
?>
		<p><label for="oci-wcb-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wcb-title" name="oci-wcb-title" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<input type="hidden" id="oci-wcb-submit" name="oci-wcb-submit" value="1" />
<?php
}

function oci_widget_categories_blogs($args = '') {
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
	oci_the_blog_category_list($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_categories_blogs_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wtb' );

	if ( $_POST['oci-wtb-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wtb-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wtb', $options );
	}

	$title = attribute_escape($options['title']);
?>
		<p><label for="oci-wtb-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wtb-title" name="oci-wtb-title" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<input type="hidden" id="oci-wtb-submit" name="oci-wtb-submit" value="1" />
<?php
}

?>