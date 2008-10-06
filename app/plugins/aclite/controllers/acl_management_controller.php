<?php
/* SVN FILE: $Id$ */
/**
 * 
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			aclite
 * @subpackage		aclite.controllers
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		aclite
 * @subpackage	aclite.controllers
 */
class AclManagementController extends AcliteAppController {

	var $name = 'AclManagement';

	function index(){
	}
	
	function deleteAclObjects($all=false) {
		$this->AclManagement->deleteAclObjects(null,$all);
		$this->Session->setFlash('Les tables ACL ont été vidées');
		$this->redirect('aclite/aclManagement');
		exit;
	} // deleteAllAclObjects

	function importMasterData() {
		$this->AclManagement->importMasterData();
		$this->Session->setFlash('Import terminé');
		$this->redirect('aclite/aclManagement');
		exit;
	} // importMasterData

	function listPermissions() {
		$this->set('aros', $this->AclManagement->getAclTree('Aro'));
		$this->set('acos', $this->AclManagement->getAclTree('Aco'));
		$this->set('permissions', $this->Acl->Aco->Permission->find('all'));
		$this->set('edit', false);
	} // listPermissions

	function reloadAcls() {
		$this->AclManagement->reloadAcls();
		$this->Session->setFlash('Rechargment terminé');
		$this->redirect('aclite/aclManagement');
		exit;
	} // reloadAcls

	function updatePermissions() {
		$this->set('edit', true);
		if (empty ($this->data)) {
			$this->_initializeLists();
			$this->set('permissions', $this->Acl->Aco->Permission->find('all'));
		} else {
			$permission = $this->data['Permission'];
 			if (!empty ($permission['aco']) && !empty ($permission['aro']) && !empty ($permission['type'])) {
				$action = !empty ($permission['action']) ? $permission['action'] : '*';
				if ($this->Acl->{$permission['type']}($permission['aro'], $permission['aco'], $action)) {
					$this->data = null;
				} else {
					$this->Session->setFlash('Impossible de sauvegarder');
				}	
			} else {
				$this->Session->setFlash('Tous les éléments doivent avoir une valeur');
			}

			$this->_initializeLists();
			$this->set('permissions', $this->Acl->Aco->Permission->find('all',array('order' => "Aro.alias ASC")));
		}
	} // updatePermissions

	function deletePermission($id = null) {
		if ($id != null) {
			$this->Acl->Aco->Permission->del($id);
			
			$this->Session->setFlash('Permission supprimée');
		} else
			$this->Session->setFlash('Identifiant de la permission inconnu');

		$this->redirect('updatePermissions');
		exit;
	} // deletePermission

	private function _initializeLists() {
		$this->set('aros', $this->Acl->Aro->find('list', array('fields' => array('Aro.alias', 'Aro.alias'))));
		$this->set('acos', $this->Acl->Aco->find('list', array('fields' => array('Aco.alias', 'Aco.alias'))));
		$this->set('types', array (
			'allow' => 'peut',
			'deny' => 'ne peut pas'
		));
		$this->set('actions', $this->AclManagement->actions);
	} //_initializeLists

} // EaclsController
?>
