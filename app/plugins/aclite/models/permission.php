<?php
class Permission extends AcliteAppModel {

	var $name = 'Permission';

	var $useTable = false;

	function add( $data = array ()) {
		$acl = AclManagement::getAcl();
		
		if (empty ($data))
			return false;

		if (!empty ($data['aco']) && !empty ($data['aro']) && !empty ($data['type'])) {
			$action = !empty ($data['action']) ? $data['action'] : '*';
			//echo "4. Add permission : ".$data['type']." - ".$data['aro']." - ".$data['aco']." - ".$action;
			return $acl->{$data['type']}($data['aro'], $data['aco'], $action);
		}

		return false;
	} // add

	function del( $id = null) {
		$acl = AclManagement::getAcl();
		
		if (empty ($id))
			return false;

		//echo "5. Delete permission : ".$id;
		return $acl->Aco->ArosAco->del($id);
	}// del

} // Permission
?>
