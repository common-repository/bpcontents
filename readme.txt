=== BuddyPress Contents ===
Contributors: burtadsit
Tags: buddypress, tags, categories, content, aggregation, organization
Requires at least: 2.7.1
Tested up to: 2.7.1
Stable tag: 1.0

== Description ==

BuddyPress Contents is a content aggregation and organization tool for BuddyPress. It implements Tags and hierarchical Categories for any type of content. Member, Group and Blog tags and categories are implemented first. 

Content Types

The three content types that bpc currently recognizes are Members, Groups and Blogs. Any type of content can be integrated into bpc however. bpc implements a content neutral 'Item' type that represents the various types of content within the tags and categories. 

Tags and Categories

bpc tags and categories are based on the Wordpress custom taxonomy system. The taxonomy system has been modified to support any content type and arbitrary tag and category target URLs.

The Site Admin manages the available categories for Members, Groups and Blogs. Individual users can then select where they fit within the category tree. Mulitple categories can be selected and the user can elect to remove themselves or add themselves to the available categories.

Tags are the free form 'folksonomy' discovery method for users. Individual users can create arbitrary tags that describe themselves. They can also tag themselves with existing tags to attach themselves to a particular group. The Site Admin can manage the tags that appear on their site. Deleting or modifing the tag name as they deem appropriate.

Groups and Blogs have the same tagging nd category capabilities as Members. The group or blog admin can create tags and select categories for their group or blog.

Member Tags and Categories

Each BuddyPress user can create profile tags that represent their interests. Visit My Account > Contents > Profile > Tags to create your tags. Enter a comma separated list of tags.

Select the categories that you'd like to be represented in by visiting My Account > Contents > Profile > Categories

Widgets for Member tags and categories are available.

Group Tags and Categories

Each BP Group can create individual tags to allow easier discovery. Any Group admin or moderator can create group tags and categories by going to the group home page Groups > My Group > Contents > Tags and Contents > Categories

Blog Tags and Categories

Each blog in a wpmu installation can be tagged through the blog creator's Blogs > My Blogs > Contents area.

Choose Contents > Select a Blog and then create tags and categories for your blog through Contents > Tags and Contents > Categories

Project Codename: 'Swiss Army'

== Installation ==

NOTE: After plugin installation, you MUST copy the templates folder to your bp active theme. See below.

1) unzip the bpcontents.zip file into /wp-contents/plugins/bpcontents

2) Copy the template directory /wp-content/plugins/bpcontents/bpcontents to your active member theme directory:

/wp-content/bp-themes/<current theme>/bpcontents

That directory should be at the same level as /<current theme>/groups and /<current theme>/profile etc..

2) activate the plugin site wide.

NOTE: You *must* activate this component AFTER bp has been activated.

Activating the plugin creates one new table in the wpmu database.

bpc_items

Have fun, be good. ~ Burt

== Configuration ==

bpc by default shows the member, group tags and categories in the member profile and the group home. This can be turned off. The member theme and home theme nav item 'Contents' can be turned off as well. You can toggle the display of these items in bp-custom.php.

Create a new function similar to the following in bp-custom.php:

function my_manage_bpc(){
	//remove_action('bp_nav_items', 'oci_bpc_nav_item');
	//remove_action('bp_custom_profile_sidebar_boxes', 'oci_the_member_profile_box');
	//remove_action('groups_sidebar_after', 'oci_the_group_profile_box');
}
add_action('plugins_loaded', 'my_manage_bpc');

Simply uncomment the display item you'd like to remove from the theme.

'bp_nav_items' - member and home theme 'Contents' nav item
'bp_custom_profile_sidebar_boxes' - member profile tags, categories display
'groups_sidebar_after' - group home tags, categories display

== Changelog ==  
  
= 1.0 =  
* Slipstreamed the .pot file into the 1.0 tag
* Initial release  
