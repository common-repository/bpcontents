<?php

function oci_register_widgets() {
	wp_register_sidebar_widget( 'oci-wcs', __( 'All Content Tags', 'bpcontents' ), 'oci_widget_cloud_sitewide' );
	wp_register_widget_control( 'oci-wcs', __( 'All Content Tags', 'bpcontents' ), 'oci_widget_cloud_sitewide_control' );
}
add_action( 'plugins_loaded', 'oci_register_widgets' );

function oci_widget_cloud_sitewide($args) {
	global $current_blog;

	$defaults = array(
		'taxonomy' => OCI_DEFAULT_TAG_TAXONOMY, 'url' => OCI_DEFAULT_URL
		);

	$args = wp_parse_args( $args, $defaults );
  extract($args);

	$options = get_blog_option( $current_blog->blog_id, 'oci_wcs' );

	echo $before_widget;
	echo $before_title;

	if ( $options['title'] ){
		echo $options['title'];
	}
	else
		echo $widget_name;

	echo $after_title;
	switch_to_blog(BP_ROOT_BLOG);
	oci_the_tag_cloud($args);
	restore_current_blog();
	echo $after_widget;
}

function oci_widget_cloud_sitewide_control() {
	global $current_blog;

	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'oci_wcs' );

	if ( $_POST['oci-wcs-submit'] ) {
		$newoptions['title'] =  strip_tags( stripslashes($_POST['oci-wcs-title']));
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'oci_wcs', $options );
	}

	$title = attribute_escape($options['title']);
?>
		<p><label for="oci-wcs-title"> <?php _e('Title:', 'bpcontents'); ?>
		<input class="widefat" id="oci-wcs-title" name="oci-wcs-title" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<input type="hidden" id="oci-wcs-submit" name="oci-wcs-submit" value="1" />
<?php
}

?>
