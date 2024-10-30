<?php

/**
 * Display tag cloud.
 *
 * The text size is set by the 'smallest' and 'largest' arguments, which will
 * use the 'unit' argument value for the CSS text size unit. The 'format'
 * argument can be 'flat' (default), 'list', or 'array'. The flat value for the
 * 'format' argument will separate tags with spaces. The list value for the
 * 'format' argument will format the tags in a UL HTML list. The array value for
 * the 'format' argument will return in PHP array type format.
 *
 * The 'orderby' argument will accept 'name' or 'count' and defaults to 'name'.
 * The 'order' is the direction to sort, defaults to 'ASC' and can be 'DESC'.
 *
 * The 'number' argument is how many tags to return. By default, the limit will
 * be to return the top 45 tags in the tag cloud list.
 *
 * The 'topic_count_text_callback' argument is a function, which, given the count
 * of the posts  with that tag, returns a text for the tooltip of the tag link.
 *
 * The 'exclude' and 'include' arguments are used for the {@link get_tags()}
 * function. Only one should be used, because only one will be used and the
 * other ignored, if they are both set.
 *
 * @since 2.3.0
 *
 * @param array|string $args Optional. Override default arguments.
 * @return array Generated tag cloud, only if no failures and 'array' is set for the 'format' argument.
 */
function oci_the_tag_cloud($args = ''){
	echo oci_get_tag_cloud($args);
}
function oci_get_tag_cloud( $args = '' ) {
	global $bp;

	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
		'exclude' => '', 'include' => '', 'link' => 'view',
		'taxonomy' => OCI_DEFAULT_TAG_TAXONOMY, 'url' => OCI_DEFAULT_URL, 'hide_empty' => false
	);
	$args = wp_parse_args( $args, $defaults );

	// woah. $args['taxonomy'] can be an array of tax's. hmmm
	$tags = get_terms( $args['taxonomy'], array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) ) ); // Always query top tags

	if ( empty( $tags ) )
		return;

	foreach ( $tags as $key => $tag ) {
		if ( 'edit' == $args['link'] )
			$link = oci_get_edit_tag_link( $tag->term_id, $args['taxonomy'] );
		else
			$link = oci_get_term_link( intval($tag->term_id), $args['taxonomy'], $args['url'] );
		if ( is_wp_error( $link ) )
			return false;

		$tags[ $key ]->link = $link;
		$tags[ $key ]->id = $tag->term_id;
	}
	
	$return = wp_generate_tag_cloud( $tags, $args ); // Here's where those top tags get sorted according to $args

	$return = apply_filters( 'oci_tag_cloud', $return, $args );

	return $return;
}

/**
 * Retrieve edit tag link.
 *
 * @since 2.7.0
 *
 * @param int $tag_id Tag ID
 * @return string
 */
function oci_get_edit_tag_link( $tag_id, $taxonomy ) {
	$tag = get_term($tag_id, $taxonomy);

	if ( !current_user_can('manage_categories') )
		return;

	$location = admin_url('edit-tags.php?action=edit&amp;tag_ID=') . $tag->term_id;
	return apply_filters( 'get_edit_tag_link', $location );
}

?>
