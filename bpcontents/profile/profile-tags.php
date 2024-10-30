<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_profile_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	<form action="<?php oci_the_profile_action(OCI_TAG) ?>" method="post" id="oci-profile-my-contents-form" class="oci-profile-my-contents-form">

	<div id="oci-sitewide-tag-cloud">
		<h2><?php _e('Sitewide Tags', 'bpcontents') ?></h2>
		<?php oci_the_tag_cloud() ?>
	</div>
	<div id="oci-tag-cloud">
		<h2><?php _e('Sitewide Member Tags', 'bpcontents') ?></h2>
		<?php oci_the_member_tag_cloud() ?>
	</div>

	<div id="oci-edit-tags">
		<h2><?php _e('Your Member Tags', 'bpcontents') ?></h2>
		<input type="text" name="oci_member_tags" id="oci_member_tags" value="<?php oci_the_member_tags_edit() ?>" />
		<label><?php _e( 'Edit your member tags. Enter a comma separated list of tag names.', 'bpcontents' ) ?></label>
		<p class="submit"><input type="submit" name="submit_save_profile_tags" id="submit" value="<?php _e( 'Save', 'bpcontents' ) ?>" /></p>
	</div>

	<?php wp_nonce_field( 'oci_update_member_tags' ) ?>
	</form>

</div>

<?php get_footer() ?>