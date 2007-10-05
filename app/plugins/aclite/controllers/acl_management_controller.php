<?php
class AclManagementController extends AcliteAppController {

	var $name = 'AclManagement';

	function index(){}
	
	function deleteAclObjects($all=false) {
		$this->AclManagement->deleteAclObjects(null,$all);
		$this->Session->setFlash('Les tables ACL ont été vidées');
		$this->redirect('aclite/aclManagement');
	} // deleteAllAclObjects

	function importMasterData() {
		$this->AclManagement->importMasterData();
		$this->Session->setFlash('Import terminé');
		$this->redirect('aclite/aclManagement');
	} // importMasterData

	function listPermissions() {
		$this->set('aros', $this->AclManagement->getAclTree('Aro'));
		$this->set('acos', $this->AclManagement->getAclTree('Aco'));
		$this->set('permissions', $this->Acl->Aco->ArosAco->findAll());
		$this->set('edit', false);
	} // listPermissions

	function reloadAcls() {
		$this->AclManagement->reloadAcls();
		$this->Session->setFlash('Rechargment terminé');
		$this->redirect('aclite/aclManagement');
	} // reloadAcls

	function updatePermissions() {
		$this->set('edit', true);
		if (empty ($this->data)) {
			$this->_initializeLists();
			$this->set('permissions', $this->Acl->Aco->ArosAco->findAll());
		} else {
			if ($this->AclManagement->Permission->add($this->data['Permission'])) {
				$this->data = null;
			} else
				$this->Session->setFlash('Tous les éléments doivent avoir une valeur');

			$this->_initializeLists();
			$this->set('permissions', $this->Acl->Aco->ArosAco->findAll(null, null, "Aro.alias ASC"));
		}
	} // updatePermissions

	function deletePermission($id = null) {
		if ($id != null) {
			$this->AclManagement->Permission->del($id);
			$this->Session->setFlash('Permission supprimée');
		} else
			$this->Session->setFlash('Identifiant de la permission inconnu');

		$this->redirect('aclite/aclManagement/updatePermissions');
	} // deletePermission

	private function _initializeLists() {
		$this->set('aros', $this->Acl->Aro->generateList(null, 'Aro.alias ASC', null, '{n}.Aro.alias', '{n}.Aro.alias'));
		$this->set('acos', $this->Acl->Aco->generateList(null, 'Aco.alias ASC', null, '{n}.Aco.alias', '{n}.Aco.alias'));
		$this->set('types', array (
			'allow' => 'peut',
			'deny' => 'ne peut pas'
		));
		$this->set('actions', $this->AclManagement->actions);
	} //_initializeLists

} // EaclsController
?>
