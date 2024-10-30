<?php

class OCI_Terms_Template {
	var $current_item = -1;
	var $item_count;
	var $items;
	var $item;
	
	var $taxonomy;
	var $url;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_item_count;

	function oci_terms_template( $taxonomy, $url, $per_page, $max ) {
		global $bp;

		$this->pag_page = isset( $_GET['ipage'] ) ? intval( $_GET['ipage'] ) : 1;
		$this->pag_num = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : $per_page;

		$tax = oci_get_query_var(OCI_TAXONOMY);
		if ($tax)
			$this->taxonomy = $tax;
		else
			$this->taxonomy = $taxonomy;

		$this->url = $url;
		// pagination args for get_terms()
		$args = array('offset' => $this->page_page, 'number' => $this->pag_num, 'hide_empty' => 0);
		if ($this->taxonomy)
			$this->items = (array) get_terms($this->taxonomy, $args);

		if ( !$max || $max >= count($this->items) )
			$this->total_item_count = count($this->items);
		else
			$this->total_item_count = (int)$max;

		// paginate the results
//		$this->items = array_slice( (array)$this->items, intval( ( $this->pag_page - 1 ) * $this->pag_num), intval( $this->pag_num ) );

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
		
		$this->item = $this->items[$this->current_item];

		if ( 0 == $this->current_item ) // loop has just started
			do_action('loop_start');
	}
}

function oci_rewind_terms() {
	global $oci_terms_template;

	return $oci_terms_template->rewind_items();
}

function oci_has_terms( $args = '' ) {
	global $bp, $oci_terms_template;

	$defaults = array(
		'taxonomy' => OCI_DEFAULT_TAG_TAXONOMY,
		'url' => oci_get_the_profile_action(OCI_TERM),
		'per_page' => 10,
		'max' => false
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	if ( $max ) {
		if ( $per_page > $max )
			$per_page = $max;
	}

	$oci_terms_template = new OCI_Terms_Template( $taxonomy, $url, $per_page, $max );

	return $oci_terms_template->has_items();
}

function oci_the_term() {
	global $oci_terms_template;
	return $oci_terms_template->the_item();
}

function oci_the_terms() {
	global $oci_terms_template;
	return $oci_terms_template->items();
}

function oci_terms_pagination() {
	echo oci_get_items_pagination();
}
	function oci_get_terms_pagination() {
		global $oci_terms_template;

		return apply_filters( 'oci_get_items_pagination', $oci_terms_template->pag_links );
	}

function oci_the_terms_pagination_count(){
	echo oci_get_the_terms_pagination_count();
}
function oci_get_the_terms_pagination_count() {
	global $bp, $oci_terms_template;

	$from_num = intval( ( $oci_terms_template->pag_page - 1 ) * $oci_terms_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $oci_terms_template->pag_num - 1 ) > $oci_terms_template->total_item_count ) ? $oci_terms_template->total_item_count : $from_num + ( $oci_terms_template->pag_num - 1);
}

/*
 * Template functions
 */

/**
 * Generates a permalink for a taxonomy term archive.
 *
 * @since 2.5.0
 *
 * @param object|int|string $term
 * @param string $taxonomy
 * @return string HTML link to taxonomy term archive
 */
function oci_get_term_link( $term, $taxonomy, $url = '' ) {
	global $wp_rewrite, $bp;

	if ( !is_object($term) ) {
		if ( is_int($term) ) {
			$term = &get_term($term, $taxonomy);
		} else {
			$term = &get_term_by('slug', $term, $taxonomy);
		}
	}
	if ( is_wp_error( $term ) )
		return $term;

	$termlink = $wp_rewrite->get_extra_permastruct($taxonomy);

	$slug = $term->slug;

	if ( empty($termlink) ) {
		// allow url override for term links
		if (!$url)
			$file = $bp->root_domain . '/' . OCI_CONTENTS_SLUG . '/';
		else
			$file = $url . '/';

		$t = get_taxonomy($taxonomy);
		if ( $t->query_var )
			$termlink = "$file?$t->query_var=$slug";
		else
			$termlink = "$file?taxonomy=$taxonomy&term=$slug";
	} else {
		$termlink = str_replace("%$taxonomy%", $slug, $termlink);
		$termlink = get_option('home') . user_trailingslashit($termlink, 'category');
	}
	return apply_filters('term_link', $termlink, $term, $taxonomy);
}

function oci_the_dropdown_taxonomies($args = ''){
	global $oci_terms_template;

	// allow template override if it is set
	$args['taxonomy'] = $args['selected'] = $oci_terms_template->taxonomy;

	echo oci_get_dropdown_taxonomies($args);
}
function oci_get_dropdown_taxonomies( $args = '' ) {
	$defaults = array(
		'name' => 'tax', 'class' => '',
		'tab_index' => 0,
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";

	$taxonomies = oci_get_taxonomies();

	$output = '';
	if ( ! empty( $taxonomies ) ) {
		$output = "<select name='$name' id='$name' class='$class' $tab_index_attribute>\n";

		foreach ($taxonomies as $tax) {
			$output .= "\t<option value=\"".$tax->name."\"";
			if ( $tax->name == $selected )
				$output .= ' selected="selected"';
			$output .= '>';
			$output .= $tax->label;
			$output .= "</option>\n";
		}

		$output .= "</select>\n";
	}

	$output = apply_filters( 'oci_dropdown_taxs', $output );

	return $output;
}

/**
 * Retrieve terms as a list with specified format.
 *
 * @since 2.5.0
 *
 * @param int $id Term ID.
 * @param string $taxonomy Taxonomy name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return string
 */
function oci_the_term_list($id, $taxonomy, $url, $before, $sep, $after){
	echo oci_get_the_term_list($id, $taxonomy, $url, $before, $sep, $after);
}
function oci_get_the_term_list( $id, $taxonomy, $url, $before, $sep, $after) {
	$terms = get_the_terms( $id, $taxonomy );

	if ( is_wp_error( $terms ) )
		return $terms;

	if ( empty( $terms ) )
		return false;

	foreach ( $terms as $term ) {
		$link = oci_get_term_link( $term, $taxonomy, $url );
		if ( is_wp_error( $link ) )
			return $link;
		$term_links[] = '<a href="' . $link . '" rel="tag">' . $term->name . '</a>';
	}

	$term_links = apply_filters( "term_links-$taxonomy", $term_links );

	return $before . join( $sep, $term_links ) . $after;
}

// get the term, if any, to edit and set the template $this->item to reflect that
// can only be called after the term list loop is complete
function oci_set_term_edit_fields(){
	global $oci_terms_template;
	
	$term = oci_get_query_var(OCI_TERM);
	$taxonomy = oci_get_query_var(OCI_TAXONOMY);
	if ($term && $taxonomy)
		$oci_terms_template->item = get_term_by('slug', $term, $taxonomy);
	else{
		$oci_terms_template->item = null;
	}

}

function oci_the_term_link(){
	echo oci_get_the_term_link();
}
function oci_get_the_term_link(){
	global $oci_terms_template;

	$link = oci_get_term_link($oci_terms_template->item, $oci_terms_template->taxonomy, $oci_terms_template->url);

	return apply_filters('oci_get_the_term_link', $link);
}

function oci_the_term_slug(){
	echo oci_get_the_term_slug();
}
function oci_get_the_term_slug(){
	global $oci_terms_template;

	return $oci_terms_template->item->slug;
}

function oci_the_term_id(){
	echo oci_get_the_term_id();
}
function oci_get_the_term_id(){
	global $oci_terms_template;

	return $oci_terms_template->item->term_id;
}

function oci_the_term_taxonomy(){
	global $bp;
	
	$tax = oci_get_the_term_taxonomy();
	if ($tax)
		echo $bp->contents->taxonomy->{$tax}->label;
}
function oci_get_the_term_taxonomy(){
	global $oci_terms_template;

	return $oci_terms_template->item->taxonomy;
}

function oci_the_term_count(){
	echo oci_get_the_term_count();
}
function oci_get_the_term_count(){
	global $oci_terms_template;

	return $oci_terms_template->item->count;
}

function oci_the_term_parent(){
	echo oci_get_the_term_parent();
}
function oci_get_the_term_parent(){
	global $oci_terms_template;

	return $oci_terms_template->item->parent;
}

function oci_the_term_name(){
	echo oci_get_the_term_name();
}
function oci_get_the_term_name(){
	global $oci_terms_template;

	$name = $oci_terms_template->item->name;

	return apply_filters('oci_get_the_term_title', $name);
}

function oci_the_term_type(){
	echo oci_get_the_term_type();
}
function oci_get_the_term_type(){
	global $oci_terms_template, $bp;

	$tax = get_taxonomy($oci_terms_template->taxonomy);
	if (!$tax)
		return '';
		
	if (!$tax->hierarchical)
		return apply_filters('oci_get_the_term_type', __('Tag', 'bpcontents'), $tax);
	else
		return apply_filters('oci_get_the_term_type', __('Category', 'bpcontents'), $tax);
}

function oci_the_term_dropdown_categories($args = ''){
	global $oci_terms_template;

	if (!is_taxonomy_hierarchical($oci_terms_template->item->taxonomy)){
		echo __('No Parent', 'bpcontents');
		return;
	}
		
	$defaults = array(
		'hierarchical' => true,
		'hide_empty' => false,
		'name' => 'parent',
		'taxonomy' => $oci_terms_template->item->taxonomy
	);
	$args = wp_parse_args($args, $defaults);
	$args['selected'] = $oci_terms_template->item->parent;
	echo oci_dropdown_categories($args);
}

function oci_the_term_description($excerpt = true, $words = 20){
	if ($excerpt)
		echo bp_create_excerpt(oci_get_the_term_description(), $words);
	else
		echo oci_get_the_term_description();
}
function oci_get_the_term_description(){
	global $oci_terms_template;

	$desc = $oci_terms_template->item->description;

	return apply_filters('oci_get_the_term_description', $desc);
}


function oci_no_terms_message(){
	global $oci_terms_template;

	if (!$oci_terms_template->has_items() && '' == oci_get_query_var('taxonomy')){
		$no_items = apply_filters('oci_no_terms_no_query', __('Select a taxonomy.', 'bpcontents'));
	}
	else{
		$no_items = apply_filters('oci_no_terms_empty_query', __('No terms found', 'bpcontents'));
	}
	echo $no_items;
}


?>
