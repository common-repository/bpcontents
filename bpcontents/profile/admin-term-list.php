<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_profile_site_admin_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	<form action="<?php oci_the_profile_action(OCI_TERM) ?>" method="post" id="oci-edit-terms-form" class="oci-edit-terms-form">

	<?php oci_set_term_edit_fields() ?>

	<div id="oci-edit-term">
		<div class="term">
		<span class="term_name"><?php _e( 'Term Id', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><?php oci_the_term_id() ?></span>
		</div>
		<div class="term">
		<span class="term_name"><?php _e( 'Taxonomy', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><?php oci_the_term_taxonomy() ?></span>
		</div>
		<div class="term">
		<span class="term_name"><?php _e( 'Item Count', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><?php oci_the_term_count() ?></span>
		</div>
		<div class="term">
		<span class="term_name"><?php _e( 'Slug', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><?php oci_the_term_slug() ?></span>
		</div>
		<div class="term">
		<span class="term_name"><?php _e( 'Parent Category', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><?php oci_the_term_dropdown_categories() ?></span>
		</div>
		<div class="term">
		<span class="term_name"><?php _e( 'Name', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><input type="text" name="oci_term_name" id="oci_term_name" value="<?php oci_the_term_name() ?>" /></span>
		</div>
		<div class="term">
		<span class="term_name"><?php _e( 'Description', 'bpcontents' ) ?>:</span><br/>
		<span class="term_value"><textarea rows="6" name="oci_term_description" id="oci_term_description"><?php oci_the_term_description(false) ?></textarea></span>
		</div>

		<input type="hidden" name="term_id" id="term_id" value="<?php oci_the_term_id() ?>" />
		<input type="hidden" name="slug" id="slug" value="<?php oci_the_term_slug() ?>" />
		<input type="hidden" name="taxonomy" id="taxonomy" value="<?php echo oci_get_the_term_taxonomy() ?>" />

		<input type="submit" class="save" name="submit_set_term_edit_fields" id="submit" value="<?php _e( 'Save', 'bpcontents' ) ?>" />
		<input type="submit" class="delete" name="submit_delete_term_edit_fields" id="submit" value="<?php _e( 'Delete', 'bpcontents' ) ?>" />

	</div>

	<div id="oci-terms-list">

		<?php if ( oci_has_terms('per_page=10') ) : ?>
			<div id="oci-dropdown-taxonomies">
				<?php oci_the_dropdown_taxonomies() ?>
				<input type="submit" name="submit_dropdown_taxonomies_go" id="submit" value="<?php _e( 'Go', 'bpcontents' ) ?>" />
			</div>

			<div class="pag-count" id="terms-list-count">
				<?php oci_the_terms_pagination_count() ?>
			</div>

			<div class="pagination-links" id="terms-list-pag">
				<?php oci_terms_pagination() ?>
			</div>

			<table id="oci-terms-table" class="widefat" cellspacing="0">

				<thead>
					<tr>
						<th scope="col"><?php _e('Id','bpcontents') ?></th>
						<th scope="col"><?php _e('Term','bpcontents') ?></th>
						<th scope="col"><?php _e('Slug','bpcontents') ?></th>
						<th scope="col"><?php _e('Count','bpcontents') ?></th>
						<th scope="col"><?php _e('Parent','bpcontents') ?></th>
					</tr>
				</thead>

				<tbody>
				<?php while ( oci_the_terms() ) : oci_the_term(); ?>
					<tr>
						<td class="term-id"><?php oci_the_term_id() ?></td>
						<td class="term-link"><a href="<?php oci_the_term_link() ?>"><?php oci_the_term_name() ?></a></td>
						<td class="term-slug"><?php oci_the_term_slug() ?></td>
						<td class="term-count"><?php oci_the_term_count() ?></td>
						<td class="term-parent"><?php oci_the_term_parent() ?></td>
					</tr>
					<tr>
						<td colspan="5" class="term-description"><?php oci_the_term_description() ?></td>
					</tr>
				<?php endwhile; ?>

				</tbody>

			</table>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php oci_no_terms_message() ?></p>
			</div>

		<?php endif; ?>
		</div>
		
	<div class="clear"></div>
	<?php wp_nonce_field( 'oci_term_maintenance' ) ?>

	<?php do_action( 'oci_directory_terms_content' ) ?>
	</form>
		
</div>

<?php get_footer() ?>
