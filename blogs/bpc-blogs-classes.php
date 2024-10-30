<?php

class OCI_Item_Blog extends OCI_Item{

	function oci_item_blog($blog = false){
		if ($blog){
			$rec = $this->get_by_source_item_id($blog->blog_id, OCI_BLOG);
			if ($rec){
				$this->id = $rec->id;
				$this->date_created = strtotime($rec->date_created);
			}

			$this->populate($blog);
		}
	}

	function populate($blog){
		global $bp;
		$this->item_id = $blog->blog_id;
		$this->item_type = OCI_BLOG;

		$this->item = $blog;

		$this->item_link = get_blog_option($blog->blog_id, 'siteurl');
		$this->item_title = get_blog_option($blog->blog_id, 'blogname');
		$this->item_description = get_blog_option($blog->blog_id, 'blogdescription');
		$this->item_author = null;
		$this->item_avatar = apply_filters( 'bp_get_blogs_blog_avatar_' . $blog->blog_id,'<img src="http://www.gravatar.com/avatar/' .
		md5( $blog->blog_id . '.blogs@' . $bp->root_domain ) .
		'?d=identicon&amp;s=50" class="avatar blog-avatar" alt="' . __( 'Blog Avatar', 'bpcontents' ) . '" />', $blog->blog_id);

	}

	function get($id = false){
		if (!$id)
			$id = $this->item_id;

		return get_blog_details($id);
	}


} // OCI_Item_Blog

?>
