<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php oci_blog_contents_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<h2><?php bp_word_or_name( __( "Select Blog", 'bpcontents' ), __( "%s's Blogs", 'bpcontents' ) ) ?></h2>
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>

	<?php if ( bp_has_blogs() ) : ?>
		<ul id="blog-list" class="item-list">
		<?php while ( bp_blogs() ) : bp_the_blog(); ?>
			<li>
				<h4><a href="<?php oci_the_blog_contents_link() ?>"><?php bp_blog_title() ?></a></h4>
				<p><?php bp_blog_description() ?></p>
			</li>
		<?php endwhile; ?>
		</ul>
	<?php else: ?>

		<div id="message" class="info">
			<p><?php bp_word_or_name( __( "You haven't created any blogs yet.", 'bpcontents' ), __( "%s hasn't created any public blogs yet.", 'bpcontents' ) ) ?> <?php bp_create_blog_link() ?> </p>
		</div>

	<?php endif;?>

</div>

<?php get_footer() ?>