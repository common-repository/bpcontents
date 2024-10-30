<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_blog_contents_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	<?php if ( oci_has_selected_blog() ) : ?>
	<form action="<?php oci_the_blog_action(OCI_TAG) ?>" method="post" id="oci-profile-my-contents-form" class="oci-profile-my-contents-form">

	<div id="oci-sitewide-tag-cloud">
		<h2><?php _e('Sitewide Tags', 'bpcontents') ?></h2>
		<?php oci_the_tag_cloud() ?>
	</div>
	<div id="oci-tag-cloud">
		<h2><?php _e('Sitewide Blog Tags', 'bpcontents') ?></h2>
		<?php oci_the_blog_tag_cloud() ?>
	</div>

	<div id="oci-edit-tags">
		<h2><?php echo oci_get_the_blog_title() . " " . __('Blog Tags', 'bpcontents') ?></h2>
		<input type="text" name="oci_blog_tags" id="oci_blog_tags" value="<?php oci_the_blog_tags_edit() ?>" />
		<label><?php _e( 'Edit your group tags. Enter a comma separated list of tag names.', 'bpcontents' ) ?></label>
		<p class="submit"><input type="submit" name="submit_save_blog_tags" id="submit" value="<?php _e( 'Save', 'bpcontents' ) ?>" /></p>
	</div>

	<?php wp_nonce_field( 'oci_update_blog_tags' ) ?>
	</form>
	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'Please select a blog.', 'bpcontents' ) ?></p>
		</div>

	<?php endif; ?>
</div>

<?php get_footer() ?>