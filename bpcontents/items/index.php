<?php get_header() ?>

<div id="directory-main">
	
	<h3><?php _e( 'Item Listing', 'bpcontents' ) ?></h3>

	<div id="item-dir-list">
		<?php load_template( TEMPLATEPATH . '/bpcontents/items/items-loop.php' ) ?>
	</div>

	<div class="clear"></div>

	<?php do_action( 'oci_directory_items_content' ) ?>
		
</div>

<div id="directory-sidebar">
	<div id="oci-tag-cloud">
		<h3><?php _e('All Contents', 'bpcontents') ?></h3>
		<?php oci_the_tag_cloud() ?>
	</div>
	<div id="oci-tag-cloud">
		<h3><?php _e('Member Tags', 'bpcontents') ?></h3>
		<?php oci_the_member_tag_cloud() ?>
	</div>
	<div id="oci-category-tree">
		<h3><?php _e('Member Categories', 'bpcontents') ?></h3>
		<?php oci_the_member_category_list() ?>
	</div>

	<?php if (oci_is_active_blogs()) : ?>
	<div id="oci-tag-cloud">
		<h3><?php _e('Blog Tags', 'bpcontents') ?></h3>
		<?php oci_the_blog_tag_cloud() ?>
	</div>
	<div id="oci-category-tree">
		<h3><?php _e('Blog Categories', 'bpcontents') ?></h3>
		<?php oci_the_blog_category_list() ?>
	</div>
	<?php endif; ?>

	<?php if (oci_is_active_groups()) : ?>
	<div id="oci-tag-cloud">
		<h3><?php _e('Group Tags', 'bpcontents') ?></h3>
		<?php oci_the_group_tag_cloud() ?>
	</div>
	<div id="oci-category-tree">
		<h3><?php _e('Group Categories', 'bpcontents') ?></h3>
		<?php oci_the_group_category_list() ?>
	</div>
	<?php endif; ?>

	<?php do_action( 'oci_directory_items_sidebar' ) ?>

</div>

<?php get_footer() ?>
