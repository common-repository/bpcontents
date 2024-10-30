<?php

/**
 * Retrieves all category IDs.
 *
 * @since 2.0.0
 * @link http://codex.wordpress.org/Function_Reference/get_all_category_ids
 *
 * @return object List of all of the category IDs.
 */
function oci_get_all_category_ids($taxonomy) {
	if ( ! $cat_ids = wp_cache_get( 'all_category_ids', $taxonomy) ) {
		$cat_ids = get_terms( $taxonomy, 'fields=ids&get=all' );
		wp_cache_add( 'all_category_ids', $cat_ids, $taxonomy );
	}

	return $cat_ids;
}

/**
 * Retrieves category data given a category ID or category object.
 *
 * If you pass the $category parameter an object, which is assumed to be the
 * category row object retrieved the database. It will cache the category data.
 *
 * If you pass $category an integer of the category ID, then that category will
 * be retrieved from the database, if it isn't already cached, and pass it back.
 *
 * If you look at get_term(), then both types will be passed through several
 * filters and finally sanitized based on the $filter parameter value.
 *
 * The category will converted to maintain backwards compatibility.
 *
 * @since 1.5.1
 * @uses get_term() Used to get the category data from the taxonomy.
 *
 * @param int|object $category Category ID or Category row object
 * @param string $output Optional. Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional. Default is raw or no WordPress defined filter will applied.
 * @return mixed Category data in type defined by $output parameter.
 */
function &oci_get_category( $category, $taxonomy, $output = OBJECT, $filter = 'raw' ) {
	$category = get_term( $category, $taxonomy, $output, $filter );
	if ( is_wp_error( $category ) )
		return $category;

	return $category;
}

/**
 * Retrieve list of category objects.
 *
 * If you change the type to 'link' in the arguments, then the link categories
 * will be returned instead. Also all categories will be updated to be backwards
 * compatible with pre-2.3 plugins and themes.
 *
 * @since 2.1.0
 * @see get_terms() Type of arguments that can be changed.
 * @link http://codex.wordpress.org/Function_Reference/get_categories
 *
 * @param string|array $args Optional. Change the defaults retrieving categories.
 * @return array List of categories.
 */
function &oci_get_categories( $args = '' ) {

	$args = wp_parse_args( $args, $defaults );

	$taxonomy = $args['taxonomy'];
	if (empty($taxonomy))
		return;
	$categories = (array) get_terms( $taxonomy, $args );

	return $categories;
}

/**
 * Retrieve post categories.
 *
 * @since 0.71
 * @uses $post
 *
 * @param int $id Optional, default to current post ID. The post ID.
 * @return array
 */
function oci_get_the_category( $id, $taxonomy ) {

	$id = (int) $id;

	$categories = get_object_term_cache( $id, $taxonomy );
	if ( false === $categories ) {
		$categories = wp_get_object_terms( $id, $taxonomy );
		wp_cache_add($id, $categories, 'oci_category_relationships');
	}

	if ( !empty( $categories ) )
		usort( $categories, '_usort_terms_by_name' );
	else
		$categories = array();

	return $categories;
}

/**
 * Check if a category is an ancestor of another category.
 *
 * You can use either an id or the category object for both parameters. If you
 * use an integer the category will be retrieved.
 *
 * @since 2.1.0
 *
 * @param int|object $cat1 ID or object to check if this is the parent category.
 * @param int|object $cat2 The child category.
 * @return bool Whether $cat2 is child of $cat1
 */
function oci_cat_is_ancestor_of( $cat1, $cat2, $taxonomy ) {
	if ( is_int( $cat1 ) )
		$cat1 = &oci_get_category( $cat1, $taxonomy );
	if ( is_int( $cat2 ) )
		$cat2 = &oci_get_category( $cat2, $taxonomy );

	if ( !$cat1->term_id || !$cat2->parent )
		return false;

	if ( $cat2->parent == $cat1->term_id )
		return true;

	return oci_cat_is_ancestor_of( $cat1, oci_get_category( $cat2->parent, $taxonomy ), $taxonomy );
}

/**
 * Sanitizes category data based on context.
 *
 * @since 2.3.0
 * @uses sanitize_term() See this function for what context are supported.
 *
 * @param object|array $category Category data
 * @param string $context Optional. Default is 'display'.
 * @return object|array Same type as $category with sanitized data for safe use.
 */
function oci_sanitize_category( $category, $taxonomy, $context = 'display' ) {
	return sanitize_term( $category, $taxonomy, $context );
}

/**
 * Sanitizes data in single category key field.
 *
 * @since 2.3.0
 * @uses sanitize_term_field() See function for more details.
 *
 * @param string $field Category key to sanitize
 * @param mixed $value Category value to sanitize
 * @param int $cat_id Category ID
 * @param string $context What filter to use, 'raw', 'display', etc.
 * @return mixed Same type as $value after $value has been sanitized.
 */
function oci_sanitize_category_field( $field, $value, $cat_id, $taxonomy, $context ) {
	return sanitize_term_field( $field, $value, $cat_id, $taxonomy, $context );
}

/**
 * Remove the category cache data based on ID.
 *
 * @since 2.1.0
 * @uses clean_term_cache() Clears the cache for the category based on ID
 *
 * @param int $id Category ID
 */
function oci_clean_category_cache( $id, $taxonomy ) {
	clean_term_cache( $id, $taxonomy );
}

/**
 * Retrieve category children list separated before and after the term IDs.
 *
 * @since 1.2.0
 *
 * @param int $id Category ID to retrieve children.
 * @param string $before Optional. Prepend before category term ID.
 * @param string $after Optional, default is empty string. Append after category term ID.
 * @param array $visited Optional. Category Term IDs that have already been added.
 * @return string
 */
function oci_get_category_children( $id, $taxonomy, $before = '/', $after = '', $visited = array() ) {
	if ( 0 == $id )
		return '';

	$chain = '';
	/** TODO: consult hierarchy */
	$cat_ids = oci_get_all_category_ids($taxonomy);
	foreach ( (array) $cat_ids as $cat_id ) {
		if ( $cat_id == $id )
			continue;

		$category = oci_get_category( $cat_id, $taxonomy );
		if ( is_wp_error( $category ) )
			return $category;
		if ( $category->parent == $id && !in_array( $category->term_id, $visited ) ) {
			$visited[] = $category->term_id;
			$chain .= $before.$category->term_id.$after;
			$chain .= oci_get_category_children( $category->term_id, $taxonomy, $before, $after );
		}
	}
	return $chain;
}

/**
 * Retrieve category link URL.
 *
 * @since 1.0.0
 * @uses apply_filters() Calls 'category_link' filter on category link and category ID.
 *
 * @param int $category_id Category ID.
 * @return string
 */
function oci_get_category_link( $category_id, $taxonomy, $url = '' ) {
	global $wp_rewrite, $bp;
//	$catlink = $wp_rewrite->get_category_permastruct();

	if ( empty( $catlink ) ) {
		// allow url override for term links
		if (!$url)
			$file = $bp->root_domain . '/' . OCI_CONTENTS_SLUG . '/';
		else
			$file = $url;

		$category = &oci_get_category( $category_id, $taxonomy );
		if ( is_wp_error( $category ) )
			return $category;

		$catlink = "$file?taxonomy=$taxonomy&term=$category->slug";
	} else {
		$category = &get_category( $category_id );
		if ( is_wp_error( $category ) )
			return $category;
		$category_nicename = $category->slug;

		if ( $category->parent == $category_id ) // recursive recursion
			$category->parent = 0;
		elseif ($category->parent != 0 )
			$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;

		$catlink = str_replace( '%category%', $category_nicename, $catlink );
		$catlink = get_option( 'home' ) . user_trailingslashit( $catlink, 'category' );
	}
	return apply_filters( 'oci_category_link', $catlink, $category_id, $taxonomy );
}

/**
 * Retrieve category parents with separator.
 *
 * @since 1.2.0
 *
 * @param int $id Category ID.
 * @param bool $link Optional, default is false. Whether to format with link.
 * @param string $separator Optional, default is '/'. How to separate categories.
 * @param bool $nicename Optional, default is false. Whether to use nice name for display.
 * @param array $visited Optional. Already linked to categories to prevent duplicates.
 * @return string
 */
function oci_get_category_parents( $id, $taxonomy, $url = '', $link = false, $separator = '/', $nicename = false, $visited = array() ) {
	$chain = '';
	$parent = &oci_get_category( $id, $taxonomy );
	if ( is_wp_error( $parent ) )
		return $parent;

	if ( $nicename )
		$name = $parent->slug;
	else
		$name = $parent->cat_name;

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain .= oci_get_category_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
	}

	if ( $link )
		$chain .= '<a href="' . oci_get_category_link( $parent->term_id, $taxonomy, $url ) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $parent->cat_name ) . '">'.$name.'</a>' . $separator;
	else
		$chain .= $name.$separator;
	return $chain;
}

/**
 * Retrieve category list in either HTML list or custom format.
 *
 * @since 1.5.1
 *
 * @param string $separator Optional, default is empty string. Separator for between the categories.
 * @param string $parents Optional. How to display the parents.
 * @param int $post_id Optional. Post ID to retrieve categories.
 * @return string
 */
function oci_get_the_category_list( $id, $taxonomy, $url, $separator = '', $parents='' ) {
	global $wp_rewrite;
	$categories = oci_get_the_category( $id, $taxonomy );
	if ( empty( $categories ) )
		return apply_filters( 'oci_the_category', __( 'Uncategorized', 'bpcontents' ), $separator, $parents );

	$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

	$thelist = '';
	if ( '' == $separator ) {
		$thelist .= '<ul class="post-categories">';
		foreach ( $categories as $category ) {
			$thelist .= "\n\t<li>";
			switch ( strtolower( $parents ) ) {
				case 'multiple':
					if ( $category->parent )
						$thelist .= oci_get_category_parents( $category->parent, $taxonomy, $url, true, $separator );
					$thelist .= '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url ) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $category->name ) . '" ' . $rel . '>' . $category->name.'</a></li>';
					break;
				case 'single':
					$thelist .= '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $category->name ) . '" ' . $rel . '>';
					if ( $category->parent )
						$thelist .= oci_get_category_parents( $category->parent, $taxonomy, $url, false, $separator );
					$thelist .= $category->name.'</a></li>';
					break;
				case '':
				default:
					$thelist .= '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url ) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $category->name ) . '" ' . $rel . '>' . $category->cat_name.'</a></li>';
			}
		}
		$thelist .= '</ul>';
	} else {
		$i = 0;
		foreach ( $categories as $category ) {
			if ( 0 < $i )
				$thelist .= $separator . ' ';
			switch ( strtolower( $parents ) ) {
				case 'multiple':
					if ( $category->parent )
						$thelist .= oci_get_category_parents( $category->parent, true, $taxonomy, $url, $separator );
					$thelist .= '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url ) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $category->name ) . '" ' . $rel . '>' . $category->cat_name.'</a>';
					break;
				case 'single':
					$thelist .= '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url ) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $category->name ) . '" ' . $rel . '>';
					if ( $category->parent )
						$thelist .= oci_get_category_parents( $category->parent, $taxonomy, $url, false, $separator );
					$thelist .= "$category->cat_name</a>";
					break;
				case '':
				default:
					$thelist .= '<a href="' . oci_get_category_link( $category->term_id, $taxonomy, $url ) . '" title="' . sprintf( __( "View all posts in %s", 'bpcontents' ), $category->name ) . '" ' . $rel . '>' . $category->name.'</a>';
			}
			++$i;
		}
	}
	return apply_filters( 'oci_the_category', $thelist, $separator, $parents );
}

/**
 * Display or retrieve the HTML dropdown list of categories.
 *
 * The list of arguments is below:
 *     'show_option_all' (string) - Text to display for showing all categories.
 *     'show_option_none' (string) - Text to display for showing no categories.
 *     'orderby' (string) default is 'ID' - What column to use for ordering the
 * categories.
 *     'order' (string) default is 'ASC' - What direction to order categories.
 *     'show_last_update' (bool|int) default is 0 - See {@link get_categories()}
 *     'show_count' (bool|int) default is 0 - Whether to show how many posts are
 * in the category.
 *     'hide_empty' (bool|int) default is 1 - Whether to hide categories that
 * don't have any posts attached to them.
 *     'child_of' (int) default is 0 - See {@link get_categories()}.
 *     'exclude' (string) - See {@link get_categories()}.
 *     'echo' (bool|int) default is 1 - Whether to display or retrieve content.
 *     'depth' (int) - The max depth.
 *     'tab_index' (int) - Tab index for select element.
 *     'name' (string) - The name attribute value for selected element.
 *     'class' (string) - The class attribute value for selected element.
 *     'selected' (int) - Which category ID is selected.
 *
 * The 'hierarchical' argument, which is disabled by default, will override the
 * depth argument, unless it is true. When the argument is false, it will
 * display all of the categories. When it is enabled it will use the value in
 * the 'depth' argument.
 *
 * @since 2.1.0
 *
 * @param string|array $args Optional. Override default arguments.
 * @return string HTML content only if 'echo' argument is 0.
 */
function oci_dropdown_categories( $args = '' ) {
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '----' . __('None', 'bpcontents') . '----',
		'orderby' => 'ID', 'order' => 'ASC',
		'show_last_update' => 0, 'show_count' => 0,
		'hide_empty' => 1, 'child_of' => 0,
		'exclude' => '', 
		'selected' => 0, 'hierarchical' => 0,
		'name' => 'cat', 'class' => 'postform',
		'depth' => 0, 'tab_index' => 0,
		'taxonomy' => ''
	);

	$r = wp_parse_args( $args, $defaults );
	$r['include_last_update_time'] = $r['show_last_update'];
	extract( $r );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";

	$categories = oci_get_categories( $r );

	$output = '';
	if ( ! empty( $categories ) ) {
		$output = "<select name='$name' id='$name' class='$class' $tab_index_attribute>\n";

		if ( $show_option_all ) {
			$show_option_all = apply_filters( 'list_cats', $show_option_all );
			$selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
			$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
		}

		if ( $show_option_none ) {
			$show_option_none = apply_filters( 'list_cats', $show_option_none );
			$selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
			$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
		}

		if ( $hierarchical )
			$depth = $r['depth'];  // Walk the full depth.
		else
			$depth = -1; // Flat.

		$output .= walk_category_dropdown_tree( $categories, $depth, $r );
		$output .= "</select>\n";
	}

	$output = apply_filters( 'oci_dropdown_cats', $output );

	return $output;
}

function oci_the_dropdown_categories($args = ''){
	echo oci_dropdown_categories($args);
}

function oci_the_category_list($args = ''){
	echo oci_list_categories($args);
}

/**
 * Display or retrieve the HTML list of categories.
 *
 * The list of arguments is below:
 *     'show_option_all' (string) - Text to display for showing all categories.
 *     'orderby' (string) default is 'ID' - What column to use for ordering the
 * categories.
 *     'order' (string) default is 'ASC' - What direction to order categories.
 *     'show_last_update' (bool|int) default is 0 - See {@link
 * walk_category_dropdown_tree()}
 *     'show_count' (bool|int) default is 0 - Whether to show how many posts are
 * in the category.
 *     'hide_empty' (bool|int) default is 1 - Whether to hide categories that
 * don't have any posts attached to them.
 *     'use_desc_for_title' (bool|int) default is 1 - Whether to use the
 * description instead of the category title.
 *     'feed' - See {@link get_categories()}.
 *     'feed_type' - See {@link get_categories()}.
 *     'feed_image' - See {@link get_categories()}.
 *     'child_of' (int) default is 0 - See {@link get_categories()}.
 *     'exclude' (string) - See {@link get_categories()}.
 *     'exclude_tree' (string) - See {@link get_categories()}.
 *     'echo' (bool|int) default is 1 - Whether to display or retrieve content.
 *     'current_category' (int) - See {@link get_categories()}.
 *     'hierarchical' (bool) - See {@link get_categories()}.
 *     'title_li' (string) - See {@link get_categories()}.
 *     'depth' (int) - The max depth.
 *
 * @since 2.1.0
 *
 * @param string|array $args Optional. Override default arguments.
 * @return string HTML content only if 'echo' argument is 0.
 */
function oci_list_categories( $args = '' ) {
	$defaults = array(
		'show_option_all' => '', 'orderby' => 'name',
		'order' => 'ASC', 'show_last_update' => 0,
		'style' => 'list', 'show_count' => 0,
		'hide_empty' => 1, 'use_desc_for_title' => 1,
		'child_of' => 0, 'feed' => '', 'feed_type' => '',
		'feed_image' => '', 'exclude' => '', 'exclude_tree' => '', 'current_category' => 0,
		'hierarchical' => true, 'title_li' => '', //__( 'Categories' ),
		'depth' => 0, 'taxonomy' => '', 'url' => OCI_DEFAULT_URL
	);

	$r = wp_parse_args( $args, $defaults );

	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	if ( isset( $r['show_date'] ) ) {
		$r['include_last_update_time'] = $r['show_date'];
	}

	if ( true == $r['hierarchical'] ) {
		$r['exclude_tree'] = $r['exclude'];
		$r['exclude'] = '';
	}

	extract( $r );

	$categories = oci_get_categories( $r );

	$output = '';
	if ( $title_li && 'list' == $style )
			$output = '<li class="categories">' . $r['title_li'] . '<ul>';

	if ( empty( $categories ) ) {
		if ( 'list' == $style )
			$output .= '<li>' . __( "No categories", 'bpcontents' ) . '</li>';
		else
			$output .= __( "No categories", 'bpcontents' );
	} else {
		global $wp_query;

		if( !empty( $show_option_all ) )
			if ( 'list' == $style )
				$output .= '<li><a href="' . $url . '">' . $show_option_all . '</a></li>';
			else
				$output .= '<a href="' . $url . '">' . $show_option_all . '</a>';

		if ( empty( $r['current_category'] ) && is_category() )
			$r['current_category'] = $wp_query->get_queried_object_id();

		if ( $hierarchical )
			$depth = $r['depth'];
		else
			$depth = -1; // Flat.

		$output .= oci_walk_category_tree( $categories, $depth, $r );
	}

	if ( $title_li && 'list' == $style )
		$output .= '</ul></li>';

	$output = apply_filters( 'oci_wp_list_categories', $output );


	return $output;
}

/**
 * Retrieve HTML list content for category list.
 *
 * @uses Walker_Category to create HTML list content.
 * @since 2.1.0
 * @see Walker_Category::walk() for parameters and return description.
 */
function oci_walk_category_tree() {
	$args = func_get_args();
	// the user's options are the third parameter
	if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
		$walker = new OCI_Walker_Category;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $cat_name
 * @return unknown
 */
function oci_category_exists($cat_name, $taxonomy) {
	$id = is_term($cat_name, $taxonomy);
	if ( is_array($id) )
		$id = $id['term_id'];
	return $id;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $cat_name
 * @param unknown_type $parent
 * @return unknown
 */
function oci_create_category( $cat_name, $taxonomy, $parent = 0 ) {
	if ( $id = oci_category_exists($cat_name, $taxonomy) )
		return $id;

	return oci_insert_category( array('cat_name' => $cat_name, 'category_parent' => $parent), $taxonomy );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $categories
 * @param unknown_type $post_id
 * @return unknown
 */
function oci_create_categories($categories, $taxonomy, $parent = 0, $id = '') {
	$cat_ids = array ();
	foreach ($categories as $category) {
		if ($id = oci_category_exists($category, $taxonomy))
			$cat_ids[] = $id;
		else
			if ($id = oci_create_category($category, $taxonomy, $parent))
				$cat_ids[] = $id;
	}

	if ($id)
		oci_update_item_categories(id, $cat_ids, $taxonomy);

	return $cat_ids;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $catarr
 * @param unknown_type $wp_error
 * @return unknown
 */
function oci_insert_category($catarr, $taxonomy, $wp_error = false) {
	$cat_defaults = array('cat_ID' => 0, 'cat_name' => '', 'category_description' => '', 'category_nicename' => '', 'category_parent' => '');
	$catarr = wp_parse_args($catarr, $cat_defaults);
	extract($catarr, EXTR_SKIP);

	if ( trim( $cat_name ) == '' ) {
		if ( ! $wp_error )
			return 0;
		else
			return new WP_Error( 'cat_name', __('You did not enter a category name.', 'bpcontents') );
	}

	$cat_ID = (int) $cat_ID;

	// Are we updating or creating?
	if ( !empty ($cat_ID) )
		$update = true;
	else
		$update = false;

	$name = $cat_name;
	$description = $category_description;
	$slug = $category_nicename;
	$parent = $category_parent;

	$parent = (int) $parent;
	if ( $parent < 0 )
		$parent = 0;

	if ( empty($parent) || !oci_category_exists( $parent, $taxonomy ) || ($cat_ID && oci_cat_is_ancestor_of($cat_ID, $parent, $taxonomy) ) )
		$parent = 0;

	$args = compact('name', 'slug', 'parent', 'description');

	if ( $update )
		$cat_ID = wp_update_term($cat_ID, $taxonomy, $args);
	else
		$cat_ID = wp_insert_term($cat_name, $taxonomy, $args);

	if ( is_wp_error($cat_ID) ) {
		if ( $wp_error )
			return $cat_ID;
		else
			return 0;
	}

	return $cat_ID['term_id'];
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $catarr
 * @return unknown
 */
function oci_update_category($catarr, $taxonomy) {
	$cat_ID = (int) $catarr['cat_ID'];

	if ( isset($catarr['category_parent']) && ($cat_ID == $catarr['category_parent']) )
		return false;

	// First, get all of the original fields
	$category = oci_get_category($cat_ID, $taxonomy, ARRAY_A);

	// Escape data pulled from DB.
	$category = add_magic_quotes($category);

	// Merge old and new fields with new fields overwriting old ones.
	$catarr = array_merge($category, $catarr);

	return oci_insert_category($catarr, $taxonomy);
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $post_id
 * @param unknown_type $descendants_and_self
 * @param unknown_type $selected_cats
 * @param unknown_type $popular_cats
 */
function oci_category_checklist( $taxonomy, $id = 0, $descendants_and_self = 0, $selected_cats = false, $popular_cats = false, $walker = null ) {
	if ( empty($walker) || !is_a($walker, 'Walker') )
		$walker = new OCI_Walker_Category_Checklist;

	$descendants_and_self = (int) $descendants_and_self;
	$id = (int) $id;
	
	$args = array();

	if ( is_array( $selected_cats ) )
		$args['selected_cats'] = $selected_cats;
	elseif ( $id )
		$args['selected_cats'] = oci_get_item_categories($id, $taxonomy);
	else
		$args['selected_cats'] = array();

	if ( is_array( $popular_cats ) )
		$args['popular_cats'] = $popular_cats;
	else
		$args['popular_cats'] = get_terms( $taxonomy, array( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

	if ( $descendants_and_self ) {
		$categories = oci_get_categories( "child_of=$descendants_and_self&hierarchical=0&hide_empty=0&taxonomy=" . $taxonomy );
		$self = oci_get_category( $descendants_and_self, $taxonomy );
		array_unshift( $categories, $self );
	} else {
		$categories = oci_get_categories('get=all&taxonomy=' . $taxonomy);
	}

/*
	// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
	$checked_categories = array();
	for ( $i = 0; isset($categories[$i]); $i++ ) {
		if ( in_array($categories[$i]->term_id, $args['selected_cats']) ) {
			$checked_categories[] = $categories[$i];
			unset($categories[$i]);
		}
	}

	// Put checked cats on top
	echo call_user_func_array(array(&$walker, 'walk'), array($checked_categories, 0, $args));
 * 
 */
	// Then the rest of them
	echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 *
 * @param unknown_type $cat_ID
 * @return unknown
 */
function oci_delete_category($term_id, $taxonomy) {

	$default = get_option('default_category');

	// Don't delete the default cat
	if ( $term_id == $default )
		return 0;

	return wp_delete_term($term_id, $taxonomy, array('default' => $default));
}

?>
