<?php

/**
 * OCI_Item
 *
 * Instances of this class are the content neutral representations of all content in bpc.
 *
 * Derived classes such as OCI_Item User, OCI_Item_Group and OCI_Item_Blog convert from source content type format
 * to an instance of OCI_Item. Each item has enough information to get the original item back.
 *
 * Example:
 * User - $item_id for a bp user is their user id
 * Group - $item_id for a bp group is the group id
 *
 */
class OCI_Item{

	var $id; // record id in the bpc_items table
	var $date_created; // date the item was created, has nothing to do with the original content
	var $item_type; // blog post, forum topic, url, user - Note: use one of the defined types such as OCI_USER
	var $item_id; // unique identifier for the original content such as user id, blog post id

	function oci_item($id = false){

		if ($id){
			if (is_numeric($id))
				$rec = $this->get($id);
			
			if ($rec)
				$this->populate($rec);
		}
	}

	function populate($rec){
		$this->id = $rec->id;
		$this->date_created = strtotime($rec->date_created);
		$this->item_type = $rec->item_type;
		$this->item_id = maybe_unserialize($rec->item_id);
	}

	function update_categories($terms, $taxonomy, $id = false){
		if (!$id)
			$id = $this->id;

		if (!is_array($terms))
			$terms = array($terms);
			
		$terms = array_map('intval', $terms);
		$terms = array_unique($terms);
		return wp_set_object_terms($id, $terms, $taxonomy);
	}

	function update_tags($terms, $taxonomy, $id = false){
		if (!$id)
			$id = $this->id;

		if (is_string($terms)){
			$terms = explode(',', $terms);
		}

		return wp_set_object_terms($id, $terms, $taxonomy);
	}

	/**
	 * get_by_source_item_id()
	 *
	 * Gets an item by it's original source item id and item type.
	 *
	 * @param string $item_id original item id, serialized
	 * @param string $item_type original content type
	 * @return mixed false for error or no item | object record from items table
	 */
	function get_by_source_item_id($item_id, $item_type){
		global $wpdb, $bp;

		$item_id = maybe_serialize($item_id);
		$sql = $wpdb->prepare( "SELECT * FROM {$bp->contents->items->table_name} WHERE
			item_type = %s AND
			item_id = %s",
			$item_type,
			$item_id
		);

		$result = $wpdb->get_row($sql);

		if (!$result)
			return false;

		return $result;
	}

	function get($id){
		global $wpdb, $bp;

		$sql = $wpdb->prepare( "SELECT * FROM {$bp->contents->items->table_name} WHERE id = %d", $id );
		$result = $wpdb->get_row($sql);

		if (!$result)
			return false;

		return $result;
	}

	// get the item recs for all $terms in all $taxonomies, paginated
	function get_items_in_terms($terms, $taxonomies, $limit = null, $page = 1){
		global $wpdb, $bp;
		
		if ( !is_array( $terms) )
			$terms = array($terms);

		foreach ($terms as $slug){
			$term = get_term_by('slug', $slug, $taxonomies);
			if ($term)
				$_terms[] = $term->term_id;
		}

		$ids = get_objects_in_term( $_terms, $taxonomies);
		$ids = implode(', ', $ids);

		if ( $limit && $page )
			$pag_sql = $wpdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$total_sql = $wpdb->prepare("SELECT DISTINCT count(id) FROM {$bp->contents->items->table_name} WHERE id IN ({$ids}) ORDER BY date_created DESC");
		$total_items = $wpdb->get_var($total_sql);

		$items_sql = $wpdb->prepare("SELECT * FROM {$bp->contents->items->table_name} WHERE id IN ({$ids}) ORDER BY date_created DESC{$pag_sql}", $pag_sql);
		$paged_items = $wpdb->get_results($items_sql);

		if (empty($paged_items))
			return false;

		return array('items' => $paged_items, 'total' => $total_items);
	}

	/**
	 * save()
	 *
	 * Inserts new or updates item in items table
	 *
	 * $this->id should be null to cause an insert, else an update is done
	 * @return boolean success or failure
	 */
	function save(){
		global $wpdb, $bp;

		if ( $this->id ) {
			$sql = $wpdb->prepare(
				"UPDATE {$bp->contents->items->table_name} SET
					date_created = FROM_UNIXTIME(%d),
					item_type = %s,
					item_id = %s
				WHERE
					id = %d
				",
				$this->date_created,
				$this->item_type,
				maybe_serialize($this->item_id),
				$this->id
			);
		} else {
			$sql = $wpdb->prepare(
				"INSERT INTO {$bp->contents->items->table_name} (
					date_created,
					item_type,
					item_id
				) VALUES (
					 FROM_UNIXTIME(%d), %s, %s
				)",
				$this->date_created = time(),
				$this->item_type,
				maybe_serialize($this->item_id)
			);
		}

		if ( false === $wpdb->query($sql) )
		return false;

		if ( !$this->id ) {
			$this->id = $wpdb->insert_id;
		}

		return true;
	}

	/**
	 * Deletes this item from the bpc_items table. Also deletes all taxonomy/term/item relationships
	 *
	 */
	function delete($id = false){
		global $wpdb, $bp;

		if (!$id)
			$id = $this->id;

		// get the tax that are associated with items
		$obj->post_type = OCI_ITEM;
		$taxs = get_object_taxonomies($obj);

		// delete the item from the taxonomies
		wp_delete_object_term_relationships($id, $taxs);

		// delete the item
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->contents->items->table_name} WHERE id = %d", $id));
	}

	// returns a live instance of the original, derived item, fully populated
	function get_source_object($item_id = false, $type = false){
		if (!$item_id && !$type){
			$item_id = $this->item_id;
			$type = $this->item_type;
		}

		$source_class = "OCI_Item_" . $type;
		if (class_exists($source_class)){
			$source_obj = new $source_class;
			$rec = $source_obj->get($item_id);
			$item = OCI_Item::get_by_source_item_id($item_id, $type);
			$source_obj->id = $item->id;
			$source_obj->date_created = strtotime($item->date_created);
			$source_obj->populate($rec);
		}

		return $source_obj;
	}

} // class OCI_Item

/**
 * Create HTML list of categories.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class OCI_Walker_Category extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'category';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * @see Walker::start_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for tab indentation.
	 * @param array $args Will only append content if style argument value is 'list'.
	 */
	function start_lvl(&$output, $depth, $args) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for tab indentation.
	 * @param array $args Will only append content if style argument value is 'list'.
	 */
	function end_lvl(&$output, $depth, $args) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category in reference to parents.
	 * @param array $args
	 */
	function start_el(&$output, $category, $depth, $args) {
		extract($args);

		$cat_name = attribute_escape( $category->name);
		$cat_name = apply_filters( 'list_cats', $cat_name, $category );
		$link = '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url ) . '" ';
		if ( $use_desc_for_title == 0 || empty($category->description) )
			$link .= 'title="' . sprintf(__( 'View all items filed under %s', 'bpcontents' ), $cat_name) . '"';
		else
			$link .= 'title="' . attribute_escape( apply_filters( 'oci_category_description', $category->description, $category, $taxonomy )) . '"';
		$link .= '>';
		$link .= $cat_name . '</a>';

		if ( (! empty($feed_image)) || (! empty($feed)) ) {
			$link .= ' ';

			if ( empty($feed_image) )
				$link .= '(';

			$link .= '<a href="' . get_category_feed_link($category->term_id, $feed_type) . '"';

			if ( empty($feed) )
				$alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s', 'bpcontents' ), $cat_name ) . '"';
			else {
				$title = ' title="' . $feed . '"';
				$alt = ' alt="' . $feed . '"';
				$name = $feed;
				$link .= $title;
			}

			$link .= '>';

			if ( empty($feed_image) )
				$link .= $name;
			else
				$link .= "<img src='$feed_image'$alt$title" . ' />';
			$link .= '</a>';
			if ( empty($feed_image) )
				$link .= ')';
		}

		if ( isset($show_count) && $show_count )
			$link .= ' (' . intval($category->count) . ')';

		if ( isset($show_date) && $show_date ) {
			$link .= ' ' . gmdate('Y-m-d', $category->last_update_timestamp);
		}

		if ( isset($current_category) && $current_category )
			$_current_category = oci_get_category( $current_category, $taxonomy );

		if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-'.$category->term_id;
			if ( isset($current_category) && $current_category && ($category->term_id == $current_category) )
				$class .=  ' current-cat';
			elseif ( isset($_current_category) && $_current_category && ($category->term_id == $_current_category->parent) )
				$class .=  ' current-cat-parent';
			$output .=  ' class="'.$class.'"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}

	/**
	 * @see Walker::end_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Not used.
	 * @param int $depth Depth of category. Not used.
	 * @param array $args Only uses 'list' for whether should append to output.
	 */
	function end_el(&$output, $page, $depth, $args) {
		if ( 'list' != $args['style'] )
			return;

		$output .= "</li>\n";
	}

}

class OCI_Walker_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $category, $depth, $args) {
		extract($args);

		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='category-$category->term_id'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="item_category[]" id="in-category-' . $category->term_id . '"' . (in_array( $category->term_id, $selected_cats ) ? ' checked="checked"' : "" ) . '/> ' . wp_specialchars( apply_filters('the_category', $category->name )) . '</label>';
	}

	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}
}

?>
