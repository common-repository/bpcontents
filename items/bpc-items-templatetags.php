<?php

class OCI_Items_Template {
	var $current_item = -1;
	var $item_count;
	var $items;
	var $item;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_item_count;

	function oci_items_template( $taxonomies, $per_page, $max ) {
		global $bp;

		$this->pag_page = isset( $_GET['ipage'] ) ? intval( $_GET['ipage'] ) : 1;
		$this->pag_num = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : $per_page;

		$taxonomies = oci_get_query_var(OCI_TAXONOMY);
		$terms = oci_get_query_var(OCI_TERM);
		$this->taxonomies = $taxonomies;
		$this->terms = $terms;
		
		if ($terms && $taxonomies)
			$this->items = OCI_Item::get_items_in_terms($terms, $taxonomies, $this->pag_num, $this->pag_page);

		if ( !$max || $max >= (int)$this->items['total'] )
			$this->total_item_count = (int)$this->items['total'];
		else
			$this->total_item_count = (int)$max;

		$this->items = $this->items['items'];

		if ( $max ) {
			if ( $max >= count($this->items) )
				$this->item_count = count($this->items);
			else
				$this->item_count = (int)$max;
		} else {
			$this->item_count = count($this->items);
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( 'ipage', '%#%' ),
			'format' => '',
			'total' => ceil( (int) $this->total_item_count / (int) $this->pag_num ), // tot num pages possible
			'current' => (int) $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));

	}

	function has_items() {
		if ( $this->item_count )
			return true;

		return false;
	}

	function next_item() {
		$this->current_item++;
		$this->item = $this->items[$this->current_item];
		
		return $this->item;
	}

	function rewind_items() {
		$this->current_item = -1;
		if ( $this->item_count > 0 ) {
			$this->item = $this->items[0];
		}
	}

	function items() {
		if ( $this->current_item + 1 < $this->item_count ) {
			return true;
		} elseif ( $this->current_item + 1 == $this->item_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_item() {
		global $item, $bp;

		$this->in_the_loop = true;
		$this->item = $this->next_item();
		
		$item_id = $this->item->item_id;
		$item_type = $this->item->item_type;
		$id = $this->item->id;

		if ( !$this->item = wp_cache_get( 'oci_item_' . $id, 'oci' ) ) {
			$this->item = OCI_Item::get_source_object($item_id, $item_type);
			wp_cache_set( 'oci_item_' . $id, $this->item, 'oci' );
		}

		if ( 0 == $this->current_item ) // loop has just started
			do_action('loop_start');
	}
}

function oci_rewind_items() {
	global $oci_items_template;

	return $oci_items_template->rewind_items();
}

function oci_has_items( $args = '' ) {
	global $bp, $oci_items_template;

	$defaults = array(
		'taxonomy' => OCI_DEFAULT_TAG_TAXONOMY,
		'per_page' => 10,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( $max ) {
		if ( $per_page > $max )
			$per_page = $max;
	}

	$oci_items_template = new OCI_Items_Template( $taxonomy, $per_page, $max );

	return $oci_items_template->has_items();
}

function oci_the_item() {
	global $oci_items_template;
	return $oci_items_template->the_item();
}

function oci_the_items() {
	global $oci_items_template;
	return $oci_items_template->items();
}

function oci_items_pagination() {
	echo oci_get_items_pagination();
}
	function oci_get_items_pagination() {
		global $oci_items_template;

		return apply_filters( 'oci_get_items_pagination', $oci_items_template->pag_links );
	}

function oci_the_items_pagination_count(){
	echo oci_get_the_items_pagination_count();
}
function oci_get_the_items_pagination_count() {
	global $bp, $oci_items_template;

	$from_num = intval( ( $oci_items_template->pag_page - 1 ) * $oci_items_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $oci_items_template->pag_num - 1 ) > $oci_items_template->total_item_count ) ? $oci_items_template->total_item_count : $from_num + ( $oci_items_template->pag_num - 1) ;
	return sprintf( __( 'Viewing item %d to %d (of %d items)', 'bpcontents' ), $from_num, $to_num, $oci_items_template->total_item_count );
}

function oci_the_items_term_and_taxonomy(){
	echo oci_get_the_items_term_and_taxonomy();
}
function oci_get_the_items_term_and_taxonomy() {
	global $bp, $oci_items_template;

	$pretty_tax = apply_filters('oci_get_the_items_term_and_taxonomy_taxonomy' , $bp->contents->taxonomy->{$oci_items_template->taxonomies}->label);
	$term = apply_filters('oci_get_the_items_term_and_taxonomy_taxonomy_term' , get_term_by('slug', $oci_items_template->terms, $oci_items_template->taxonomies));
	return apply_filters('oci_get_the_items_term_and_taxonomy_taxonomy' , 
		sprintf( __(' for %s in %s', 'bpcontents'), $term->name, $pretty_tax));
}

function oci_bpc_nav_item(){
	?>
			<li<?php if ( bp_is_page( OCI_CONTENTS_SLUG ) ) {?> class="selected"<?php } ?>><a href="<?php echo bp_core_get_root_domain() ?>/<?php echo OCI_CONTENTS_SLUG ?>" title="<?php _e( 'Contents', 'bpcontents' ) ?>"><?php _e( 'Contents', 'bpcontents' ) ?></a></li>
	<?php
}
add_action('bp_nav_items', 'oci_bpc_nav_item');

/*
 * Template functions
 */

function oci_the_item_link($id = false){
	echo oci_get_the_item_link($id);
}
function oci_get_the_item_link($id = false){
	global $oci_items_template;

	if ($id){
		$src_item = oci_source_get_item($id);
		$link = $src_item->item_link;
	} else{
		$link = $oci_items_template->item->item_link;
	}
	return apply_filters('oci_get_the_item_link', $link);
}

function oci_the_item_title($id = false){
	echo oci_get_the_item_title($id);
}
function oci_get_the_item_title($id = false){
	global $oci_items_template;

	if ($id){
		$src_item = oci_source_get_item($id);
		$title = $src_item->item_title;
	} else{
		$title = $oci_items_template->item->item_title;
	}
	return apply_filters('oci_get_the_item_title', $title);
}

function oci_the_item_type($id = false){
	echo oci_get_the_item_type($id);
}
function oci_get_the_item_type($id = false){
	global $oci_items_template, $bp;

	if ($id){
		$src_item = oci_source_get_item($id);
		$type = $bp->contents->content_types->{$src_item->item_type}->title;
	} else{
		$type = $bp->contents->content_types->{$oci_items_template->item->item_type}->title;
	}
	return apply_filters('oci_get_the_item_type', $type);
}

// items template fn
function oci_the_item_tags($id = false, $args = ''){
	echo oci_get_the_item_tags($id, $args);
}
function oci_get_the_item_tags($id = false, $args = ''){
	global $bp, $oci_items_template;

	$defaults = array('taxonomy' => OCI_DEFAULT_TAG_TAXONOMY, 'url' => OCI_DEFAULT_URL,
		'before' => '',
		'sep' => ' ', 'after' => ''
	);
	$args = wp_parse_args($args, $defaults);
	extract($args);

	$before = apply_filters('oci_the_item_tags_before',
		$bp->contents->taxonomy->{$taxonomy}->label . ': ', $taxonomy);
	if ($id)
		$item = oci_source_get_item($id);
	else
		$item = $oci_items_template->item;

	return oci_the_term_list( $item->id, $taxonomy, $url, $before, $sep, $after);
}

function oci_the_item_description($id = false){
	echo oci_get_the_item_description($id);
}
function oci_get_the_item_description($id = false){
	global $oci_items_template;

	if ($id){
		$src_item = oci_source_get_item($id);
		$desc = $src_item->item_description;
	} else{
		$desc = $oci_items_template->item->item_description;
	}
	return apply_filters('oci_get_the_item_description', $desc);
}

function oci_the_item_author($id = false){
	echo oci_get_the_item_author($id);
}
function oci_get_the_item_author($id = false){
	global $oci_items_template;

	if ($id){
		$src_item = oci_source_get_item($id);
		$author = $src_item->item_author;
	} else{
		$author = $oci_items_template->item->item_author;
	}
	return apply_filters('oci_get_the_item_author', $author);
}

function oci_the_item_avatar($id = false){
	echo oci_get_the_item_avatar($id);
}
function oci_get_the_item_avatar($id = false){
	global $oci_items_template;

	if ($id){
		$src_item = oci_source_get_item($id);
		$av = $src_item->item_avatar;
	} else{
		$av = $oci_items_template->item->item_avatar;
	}
	return apply_filters('oci_get_the_item_avatar', $av);
}

/**
 * oci_objects_get_properties()
 *
 * Utility function to return the values of an object's properties.
 * Passed an array of objects, it will return an array of the property for each object's $prop_name.
 *
 * Example: passed an array of term objs the call oci_objects_get_properties($tags, 'name') will return an
 * array of 'name' properties for all the $tags objects.
 *
 * @param array $arr_of_objs array of stdClass objects
 * @param string $prop_name the property of the objects to return
 * @return array $props array of property values for the property $prop_name for each obj
 */
function oci_objects_get_properties($arr_of_objs, $prop_name){
	$props = array();
	foreach((array)$arr_of_objs as $obj){
		$props[] = $obj->{$prop_name};
	}
	return $props;
}

/**
 * oci_get_query_var()
 * 
 * If a query var of the type http://mysite.org/members/?name=thisvalue&othername=thatvalue exists and the
 * $var == 'name' exists, returns the value 'thisvalue'
 * 
 * @param string $var query var to return
 * @return string value of the $var argument
 */
function oci_get_query_var($var){
	if (isset($_GET[$var]))
		return $_GET[$var];
	else
		return '';
}

function oci_no_items_message(){
	global $oci_items_template;

	if (!$oci_items_template->has_items() && '' == oci_get_query_var('taxonomy')){
		$no_items = apply_filters('oci_no_items_no_query', __('Select a tag or category.', 'bpcontents'));
	}
	else{
		$no_items = apply_filters('oci_no_items_empty_query', __('No items found', 'bpcontents') . oci_get_the_items_term_and_taxonomy());
	}
	echo $no_items;
}

?>
