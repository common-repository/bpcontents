<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_profile_site_admin_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>

	<form action="<?php oci_the_profile_action(BP_GROUPS_SLUG) ?>" method="post" id="oci-edit-tags-form" class="oci-edit-tags-form">
	<div id="oci-category-tree">
		<h2><?php _e('Group Categories', 'bpcontents') ?></h2>
		<?php oci_the_group_category_checklist("current=0") ?>
	</div>
	<p class="submit"><input type="submit" name="submit_delete_group_categories" id="submit" value="<?php _e( 'Delete', 'bpcontents' ) ?>" /></p>
	<?php wp_nonce_field( 'oci_delete_group_categories' ) ?>
	</form>

	<form action="<?php oci_the_profile_action(BP_GROUPS_SLUG) ?>" method="post" id="oci-edit-tags-form" class="oci-edit-tags-form">
	<div id="oci-edit-tags">
	<p>
		<label><?php _e( 'Parent Category', 'bpcontents' ) ?></label>
		<?php oci_the_group_dropdown_categories() ?>
	</p>
		<label><?php _e( 'Add group categories', 'bpcontents' ) ?></label>
		<input type="text" name="oci_new_group_categories" id="oci_new_group_categories" value="" />
	</div>
	<p class="submit"><input type="submit" name="submit_add_group_categories" id="submit" value="<?php _e( 'Add', 'bpcontents' ) ?>" /></p>
	<?php wp_nonce_field( 'oci_new_group_categories' ) ?>

	</form>

</div>

<?php get_footer() ?>