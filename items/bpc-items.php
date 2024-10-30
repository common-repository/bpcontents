<?php

function oci_screen_items_directory() {
	global $bp;

	if ( $bp->current_component == $bp->contents->slug && empty( $bp->current_action ) ) {
		$bp->is_directory = true;

		bp_core_load_template( apply_filters( 'oci_screen_items_directory', 'bpcontents/items/index' ) );
	}
}
add_action( 'wp', 'oci_screen_items_directory', 2 );

// item id, taxonomy
function oci_get_item_tags_edit($id, $taxonomy){

	$terms = get_the_terms($id, $taxonomy);

	if (!$terms)
		return false;

	$term_names = oci_objects_get_properties($terms, 'name');
	$term_names = join(', ', $term_names);
	return $term_names;
}

function oci_update_item_categories($item_id, $terms, $taxonomy){
	$item = new OCI_Item($item_id);
	if (!$item->id)
		return false;

	$item->update_categories($terms, $taxonomy);
}

function oci_update_item_tags($item_id, $terms, $taxonomy){
	$item = new OCI_Item($item_id);
	if (!$item->id)
		return false;

	$item->update_tags($terms, $taxonomy);
}

// returns a live instance of the original, derived item, fully populated
function oci_source_get_item($id){
	$item = new OCI_Item($id);
	if ($item->id){
		$src_item = $item->get_source_object();
	}
	// instance of the source item derived class
	return $src_item;
}

function oci_get_item($id){
	$item = new OCI_Item($id);
}
// returns an arbitrary source item property from a fully populated derived item
function oci_get_item_property($property, $id = false){
	global $oci_items_template;

	if ($id)
		$src_item = oci_source_get_item($id);
	else
		$src_item = $oci_items_template->item->item;

	return $src_item->item->{$property};
}

function oci_get_item_tags( $id, $taxonomy, $args = array() ) {
	$id = (int) $id;

	$defaults = array('fields' => 'ids');
	$args = wp_parse_args( $args, $defaults );

	$tags = wp_get_object_terms($id, $taxonomy, $args);
	return $tags;
}

function oci_update_item_term_count( $terms ) {
	global $wpdb, $bp;

	foreach ( (array) $terms as $term ) {
		$count = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships,
			{$bp->contents->items->table_name}
			WHERE {$bp->contents->items->table_name}.id = $wpdb->term_relationships.object_id AND term_taxonomy_id = %d", $term ) );
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
	}
}

function oci_get_item_categories( $id, $taxonomy, $args = array() ) {
	$id = (int) $id;

	$defaults = array('fields' => 'ids');
	$args = wp_parse_args( $args, $defaults );

	$cats = wp_get_object_terms($id, $taxonomy, $args);
	return $cats;
}

?>
