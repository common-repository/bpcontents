<?php

define('OCI_DEFAULT_TAG_TAXONOMY', 't_' . OCI_SITE_WIDE);
define('OCI_DEFAULT_URL', bp_core_get_root_domain() . '/' . OCI_CONTENTS_SLUG . '/');

function oci_get_taxonomies(){
	global $bp;
	return $bp->contents->taxonomy;
}

/**
 * Creates the default contexts for tags and categories.
 *
 * @global $bp
 */
function oci_register_default_taxonomies(){
	global $bp;
	// $label, $domain, $hierarchical, $instance, $type
	oci_register_taxonomy(__('Site Wide Tags', 'bpcontents'), OCI_SITE_WIDE);
}
add_action('init', 'oci_register_default_taxonomies');

// returns a string x_xxxxxxxxx_999999999_xxxxxxxxx 1_9_9_9 for taxonomy name, 32 max for tax name
//                 domain ^  instance ^ type ^
function oci_get_taxonomy_name($domain, $hier, $instance = null , $type = null){
	if (empty($domain))
		return '';

	if ($instance && !is_numeric($instance))
		return '';

	$instance = sprintf("%d",$instance);
	if (strlen($instance) > 9) // 999,999,999 without commas
		return '';

	if ($type)
		$type = sanitize_title_with_dashes(substr(trim($type),0,9));
	if (strlen($type) > 9)
		return '';

	$domain = sanitize_title_with_dashes(substr(trim($domain),0,9));
	if (strlen($domain) > 9)
		return '';

	$name = $hier ? 'c_' : 't_';
	$name .= $domain;
	if ($instance)
		$name .= '_' . $instance;
	if ($type)
		$name .= '_' . $type;

	return $name;
}

// x_xxxxxxxxx_999999999_xxxxxxxxx >> array('domain' => xxx ...)
function oci_parse_taxonomy_name($name){

	$n = explode('_', $name);

	$r = array('hierarchical' => 't' == $n[0] ? false : true); // (?)_xxxxxxxxx_999999999_xxxxxxxxx
	$r['domain'] = $n[1]; // x_(?????????)_999999999_xxxxxxxxx

	if (OCI_SITE_WIDE == $n[1]){ // x_(?????????)_xxxxxxxxx
		if (isset($n[2]))
			$r['taxonomy'] = $n[2]; // x_xxxxxxxxx_(?????????)

		return $r;
	}

	if (isset($n[2])){ // x_xxxxxxxxx_(?????????)_xxxxxxxxx
		if (is_numeric($n[2])){
			$r['instance'] = $n[2];
			if (isset($n[3])) // x_xxxxxxxxx_999999999_(?????????)
				$r['taxonomy'] = $n[3];

			return $r;
		}
		else{ // x_xxxxxxxxx_(?????????)
			$r['taxonomy'] = $n[2];

			return $r;
		}

	}
}

function oci_register_taxonomy($label, $domain, $hierarchical = false, $instance = null, $type = null){
	global $bp;

	if ($name = oci_get_taxonomy_name($domain, $hierarchical, $instance, $type)){
		$label = apply_filters('oci_register_taxonomy_label', $label, $name, $domain, $hierarchical, $instance, $type);
		// if tax exists just return the name
		if (!get_taxonomy($name)){
			register_taxonomy( $name, OCI_ITEM, array('hierarchical' => $hierarchical,
				'update_count_callback' => 'oci_update_item_term_count', 'label' => $label, 'query_var' => false, 'rewrite' => false) );
		}
		// record the bpc taxonomy details for later use
		$bp->contents->taxonomy->{$name}->label = $label;
		$bp->contents->taxonomy->{$name}->domain = $domain;
		$bp->contents->taxonomy->{$name}->hierarchical = $hierarchical;
		$bp->contents->taxonomy->{$name}->instance = $instance;
		$bp->contents->taxonomy->{$name}->type = $type;
		$bp->contents->taxonomy->{$name}->name = $name;
		return $name;
	}
	else
		return false;
}

?>
