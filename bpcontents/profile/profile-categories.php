<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_profile_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	<form action="<?php oci_the_profile_action(OCI_CATEGORY) ?>" method="post" id="oci-profile-my-contents-form" class="oci-profile-my-contents-form">

	<div id="oci-category-tree">
		<h2><?php _e('Select Your Member Categories', 'bpcontents') ?></h2>
		<?php oci_the_member_category_checklist() ?>
	</div>

	<p class="submit"><input type="submit" name="submit_save_profile_categories" id="submit" value="<?php _e( 'Save', 'bpcontents' ) ?>" /></p>

	<?php wp_nonce_field( 'oci_update_member_categories' ) ?>
	</form>

</div>

<?php get_footer() ?>