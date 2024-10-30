<?php

class OCI_Item_User extends OCI_Item{

	function oci_item_user($user = false){

		if ($user){

			$rec = $this->get_by_source_item_id($user->id, OCI_USER);
			if ($rec){
				$this->id = $rec->id;
				$this->date_created = strtotime($rec->date_created);
			}

			$this->populate($user);
		}
	}

	function populate($user){
		$this->item_type = OCI_USER;
		$this->item_id = $user->id;

		$this->item = $user;

		$this->item_link = $user->user_url;
		$this->item_date = null; // fairly meaningless, there are a few
		$this->item_title = $user->fullname;
		$this->item_description = '';
		$this->item_author = '';
		$this->item_avatar = apply_filters('oci_item_user_avatar', $user->avatar_thumb, $user->id);
	}

	function get($id = false){
		if (!$id)
			$id = $this->item_id;

		return new BP_Core_User($id);
	}

} // OCI_Item_User

?>
