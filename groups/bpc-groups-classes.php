<?php

class OCI_Item_Group extends OCI_Item{

	function oci_item_group($group = false){
		if ($group){
			$rec = $this->get_by_source_item_id($group->id, OCI_GROUP);
			if ($rec){
				$this->id = $rec->id;
				$this->date_created = strtotime($rec->date_created);
			}

			$this->populate($group);
		}
	}

	function populate($group){
		global $bp;

		$this->item_type = OCI_GROUP;
		$this->item_id = $group->id;

		$this->item = $group;

		$this->item_link = $bp->root_domain . '/' . $bp->groups->slug . '/' . $group->slug;
		$this->item_date = $group->date_created;
		$this->item_title = $group->name;
		$this->item_description = $group->description;
		$this->item_author = $group->creator_id;
		$this->item_avatar = '<img src="' . attribute_escape( $group->avatar_thumb ) . '" class="avatar" alt="' . attribute_escape( $group->name ) . '" />';
	}

	function get($id = false){
		if (!$id)
			$id = $this->item_id;

		return new BP_Groups_Group($id);
	}

} // OCI_Item_Group

?>
