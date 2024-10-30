<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_blog_contents_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	<?php if ( oci_has_selected_blog() ) : ?>
	<form action="<?php oci_the_blog_action(OCI_CATEGORY) ?>" method="post" id="oci-profile-my-contents-form" class="oci-profile-my-contents-form">

	<div id="oci-category-tree">
		<h2><?php _e('Select Your Blog Categories', 'bpcontents') ?></h2>
		<?php oci_the_blog_category_checklist() ?>
	</div>

	<p class="submit"><input type="submit" name="submit_save_blog_categories" id="submit" value="<?php _e( 'Save', 'bpcontents' ) ?>" /></p>

	<?php wp_nonce_field( 'oci_update_blog_categories' ) ?>
	</form>
	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'Please select a blog.', 'bpcontents' ) ?></p>
		</div>

	<?php endif; ?>
</div>

<?php get_footer() ?>