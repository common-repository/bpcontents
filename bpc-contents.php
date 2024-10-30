<?php
/*
Plugin Name: BP Contents
Plugin URI: http://wordpress.org/extend/plugins/bpcontents/
Description: BuddyPress Contents is a content aggregation and organization tool for BuddyPress. It implements Tags and hierarchical Categories for any type of content.
Author: Burt Adsit
Version: 1.0
Author http://buddypress.org/developers/burtadsit/profile/
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Site Wide Only: true
*/

if (!function_exists('bp_core_setup_globals')){
 return false;
}

define ( 'OCI_CONTENTS_IS_INSTALLED', 1 );
define ( 'OCI_CONTENTS_VERSION', '1.0' );
define ( 'OCI_CONTENTS_DB_VERSION', '1' );

/**
 * bpc constants
 *
 * Do not alter these. You'll break things.
 */
define('OCI_CATEGORY', 'category');
define('OCI_TAG', 'tag');
define('OCI_ITEM','item');
define('OCI_ROOT', 'root');
define('OCI_SITE_WIDE','sitewide');
define('OCI_TAXONOMY', 'taxonomy');
define('OCI_TERM', 'term');
define('OCI_PROFILE', 'profile');

// Base bpc component slug
if ( !defined( 'OCI_CONTENTS_SLUG' ) )
	define ( 'OCI_CONTENTS_SLUG', apply_filters( 'oci_contents_slug', 'contents' ) );

if ( !defined( 'OCI_SHOW_IN_NAV' ) )
	define ( 'OCI_SHOW_IN_NAV', true );

/**
 * Load the required bpc component files.
 * Do not load files if the individual bp component is not activated.
 */
require(WP_PLUGIN_DIR . '/bpcontents/bpc-taxonomy.php');
require(WP_PLUGIN_DIR . '/bpcontents/bpc-tags.php');
require(WP_PLUGIN_DIR . '/bpcontents/bpc-categories.php');
require(WP_PLUGIN_DIR . '/bpcontents/bpc-classes.php');
require(WP_PLUGIN_DIR . '/bpcontents/terms/bpc-terms-templatetags.php');

require(WP_PLUGIN_DIR . '/bpcontents/items/bpc-items.php');
require(WP_PLUGIN_DIR . '/bpcontents/items/bpc-items-templatetags.php');
require(WP_PLUGIN_DIR . '/bpcontents/items/bpc-items-widgets.php');

if (function_exists('bp_blogs_setup_globals')){
	include( WP_PLUGIN_DIR . '/bpcontents/blogs/bpc-blogs.php' );
}

if (function_exists('groups_setup_globals')){
	include( WP_PLUGIN_DIR . '/bpcontents/groups/bpc-groups.php' );
}

include( WP_PLUGIN_DIR . '/bpcontents/members/bpc-members.php' );

function oci_load_bpcontents_textdomain() {
	if ( file_exists( WP_PLUGIN_DIR . '/bpcontents/languages/bpcontents-' . get_locale() . '.mo' ) )
		load_textdomain( 'bpcontents', WP_PLUGIN_DIR . '/bpcontents/languages/bpcontents-' . get_locale() . '.mo' );
	}
add_action ( 'plugins_loaded','oci_load_bpcontents_textdomain', 9 );

function oci_is_active_blogs(){
	return function_exists('bp_blogs_setup_globals');
}

function oci_is_active_groups(){
	return function_exists('groups_setup_globals');
}

/**
 * oci_register_content_type()
 *
 * Each content type should register using this function to allow discovery of info about that type.
 * Example: 	oci_register_content_type(OCI_USER, 'user', 'BuddyPress Member');
 *
 * @global $oci Global component var
 * @param $slug Slug for the type. Used to lookup other information about a content type.
 * @param $id The slug may change but this can be used where a slug would be inappropriate.
 * @param $title The type pretty name for display purposes.
 */
function oci_register_content_type($slug, $id, $title){
	global $bp;

	$bp->contents->content_types->{$slug}->slug = $slug;
	$bp->contents->content_types->{$slug}->id = $id;
	$bp->contents->content_types->{$slug}->title = $title;

}

// Add bpc as a root component in bp
function oci_setup_root_component() {
	bp_core_add_root_component( OCI_CONTENTS_SLUG );
}
add_action( 'plugins_loaded', 'oci_setup_root_component', 1 );

/**
 * Creates the bpc global objects
 * 
 * @global $wpdb Wordpress global db object
 * @global $oci bpc global object exposed and created here
 * @global $ocitree bpc global tree db object
 * @global $bp Buddypress global object
 */
function oci_setup_globals() {
	global $wpdb, $bp, $wp_taxonomies;

	$bp->contents->items->table_name = $wpdb->base_prefix . 'bpc_items';
//	$bp->contents->item_meta->table_name = $wpdb->base_prefix . 'bpc_item_meta';
//	$bp->contents->term_meta->table_name = $wpdb->base_prefix . 'bpc_term_meta';

	$bp->contents->actions = array(OCI_TAG, OCI_CATEGORY);

	$bp->contents->image_base = WP_PLUGIN_URL . '/bpcontents/images';
	$bp->contents->slug = OCI_CONTENTS_SLUG;
	$bp->version_numbers->contents = OCI_CONTENTS_VERSION;

}
add_action( 'plugins_loaded', 'oci_setup_globals', 5);
add_action( 'admin_menu', 'oci_setup_globals', 1);

/**
 * oci_install()
 *
 * Creates the tables or updates them when site admin vistis the backend of wpmu.
 *
 * @global $wpdb wordpress global db object instance
 * @global $oci bpc global var instance
 * @global $ocitree bpc global mptt tree object
 */
function oci_install() {
	global $wpdb, $bp;

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";


	$sql[] = "CREATE TABLE {$bp->contents->items->table_name} (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					date_created datetime NOT NULL,
					item_type varchar(9) NOT NULL,
					item_id longtext NOT NULL,

					KEY date_created (date_created)

		 	   ) {$charset_collate};";
/*
	$sql[] .= "CREATE TABLE {$bp->contents->item_meta->table_name} (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					item_id bigint(20) NOT NULL,
					meta_key varchar(255) NOT NULL,
					meta_value longtext NOT NULL,

					KEY item_id (item_id),
					KEY item_id_meta_key (item_id, meta_key)

		 	   ) {$charset_collate};";

	$sql[] .= "CREATE TABLE {$bp->contents->term_meta->table_name} (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					taxonomy varchar(32) NOT NULL,
					term_id bigint(20) NOT NULL,
					item_id bigint(20) NOT NULL,
					meta_key varchar(255) NOT NULL,
					meta_value longtext NOT NULL,

					KEY taxonomy_term_id (taxonomy, term_id),
					KEY taxonomy_term_id_meta_key (taxonomy, term_id, meta_key),
					KEY taxonomy_term_id_item_id (taxonomy, term_id, item_id),
					KEY taxonomy_term_id_item_id_meta_key (taxonomy, term_id, item_id, meta_key)

		 	   ) {$charset_collate};";
*/
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	dbDelta($sql);

	update_site_option( 'bpc-contents-db-version', OCI_CONTENTS_DB_VERSION );
}

/**
 * oci_check_installed()
 *
 * Standard bp mechanism to check if the tables are installed or need upgrading.
 *
 * @global <type> $wpdb
 * @global <type> $oci
 * @global <type> $ocitree
 */
function oci_check_installed() {
	global $wpdb, $bp;

	if ( is_site_admin() ) {
		/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
		if ( ( false == $wpdb->get_var("show tables like '%{$bp->contents->items->table_name}%'") ) || ( get_site_option('bpc-contents-db-version') < OCI_CONTENTS_DB_VERSION )  )
			oci_install();
	}
}
add_action( 'admin_menu', 'oci_check_installed' );

/**
 * oci_add_structure_css()
 *
 * Queues the bpc CSS file.
 */
function oci_add_structure_css() {
	/* Enqueue the structure CSS file to give basic positional formatting for components */
	wp_enqueue_style( 'oci-contents-structure', WP_PLUGIN_URL . '/bpcontents/css/structure.css' );
}
add_action( 'bp_styles', 'oci_add_structure_css' );

/**
 * oci_setup_nav()
 *
 * Sets up the bpc nav items in member profile, group home and blog admin.
 *
 * @global $bp global bp object
 * @global $group_obj Used when in group home to id the group
 * @global $oci_blog_id Used when a blog is selected for contents admin by My Account > Blogs > Contents > Select a Blog
 */
function oci_setup_nav() {
	global $bp;
	
	bp_core_add_nav_item( __('Contents', 'bpcontents'), $bp->contents->slug);
	bp_core_add_nav_default( $bp->contents->slug, 'oci_screen_profile_contents', 'profile', false );

}
add_action( 'wp', 'oci_setup_nav', 2 );
add_action( 'admin_menu', 'oci_setup_nav', 2 );

function oci_force_buddypress_theme( $template ) {
	global $bp;

	if ( $bp->current_component == $bp->contents->slug && empty( $bp->current_action ) ) {
		$member_theme = get_site_option( 'active-member-theme' );

		if ( empty( $member_theme ) )
			$member_theme = 'bpmember';

		add_filter( 'theme_root', 'bp_core_filter_buddypress_theme_root' );
		add_filter( 'theme_root_uri', 'bp_core_filter_buddypress_theme_root_uri' );

		return $member_theme;
	} else {
		return $template;
	}
}
add_filter( 'template', 'oci_force_buddypress_theme', 1, 1 );

function oci_force_buddypress_stylesheet( $stylesheet ) {
	global $bp;

	if ( $bp->current_component == $bp->contents->slug && empty( $bp->current_action ) ) {
		$member_theme = get_site_option( 'active-member-theme' );

		if ( empty( $member_theme ) )
			$member_theme = 'bpmember';

		add_filter( 'theme_root', 'bp_core_filter_buddypress_theme_root' );
		add_filter( 'theme_root_uri', 'bp_core_filter_buddypress_theme_root_uri' );

		return $member_theme;
	} else {
		return $stylesheet;
	}
}
add_filter( 'stylesheet', 'oci_force_buddypress_stylesheet', 1, 1 );

?>