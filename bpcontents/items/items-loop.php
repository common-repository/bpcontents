<?php if ( oci_has_items('per_page=10') ) : ?>

	<div class="pag-count" id="items-dir-count">
		<?php oci_the_items_pagination_count(); oci_the_items_term_and_taxonomy(); ?>
	</div>

	<div class="pagination-links" id="items-dir-pag">
		<?php oci_items_pagination() ?>
	</div>

	<ul id="items-list" class="item-list">
	<?php while ( oci_the_items() ) : oci_the_item(); ?>

		<li>
			<div class="item-avatar">
				<a href="<?php oci_the_item_link() ?>"><?php oci_the_item_avatar() ?></a>
			</div>

			<div class="item">
				<div class="item-title"><a href="<?php oci_the_item_link() ?>"><?php oci_the_item_title() ?></a></div>
				<div class="item-meta type"><?php oci_the_item_type() ?></div>
				<div class="item-meta description"><?php oci_the_item_description() ?></div>
				<div class="item-meta tags"><?php oci_the_item_tags() ?></div>
				<?php do_action( 'oci_directory_items_content' ) ?>
			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>
	</ul>		
			
<?php else: ?>

	<div id="message" class="info">
		<p><?php oci_no_items_message() ?></p>
	</div>

<?php endif; ?>
