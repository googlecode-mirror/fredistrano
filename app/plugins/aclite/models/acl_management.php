<?php
class AclManagement extends AcliteAppModel {

	var $name = 'AclManagement';

	var $useTable = false;

	var $mapping = array (
		'Group' => array (
			'model' 		=> 'Group',
			'parentAlias' 	=> 'ParentGroup',
			'alias' 		=> 'name'
		),
		'ControlObject' => array (
			'model' 		=> 'ControlObject',
			'parentAlias' 	=> 'ParentControlObject',
			'alias' 		=> 'name'
		)
	);

	var $actions = array (
		'*'			=> '*',
		'read'		=> 'read',
		'create'	=> 'create',
		'update'	=> 'update',
		'delete'	=> 'delete'
	);

	var $gPrefix = 'group.';
	
	function importMasterData($data = null) {
		$acl = ClassRegistry::init('AclComponent');
		
		$list = $this->mapping;
		if (!empty($data) && (in_array($data,array_keys($list))))
			$list = array($data => $list[$data]);
			
		foreach ($list as $key => $value) {
			$modelName = $value['model'];
			App::import('Model', $modelName);
			$model = new $modelName ();
			
			switch ($key) {
				case 'Group' :
					$aros = $acl->Aro->find('list', array( 'fields' => array('Aro.alias'), 'recursive' => 0));
					if (empty($aros)) {
						$aros = array();
					}
					$groups = $model->find('all');
					if (empty ($groups)) {
							break;			
					}
					
					foreach ($groups as $group) {
						if (!in_array($this->gPrefix.$group[$value['model']][$value['alias']],$aros)) {
							$acl->Aro->create();
							$data = array(
								'Aro' => array(
									'alias' 		=> $this->gPrefix.$group[$value['model']][$value['alias']],
									'foreign_key' 	=> $group[$value['model']]['id'],
									'model' 		=> $value['model']
								)
							);
							$acl->Aro->save($data);
						}
					}

					foreach ($groups as $group) {
						if (!empty ($value['parentAlias']) && !empty ($group[$value['parentAlias']]) && !empty ($group[$value['parentAlias']][$value['alias']])) {
							$parentId = $group[$value['parentAlias']]['id'];
							$data = array(
								'Aro' => array(
									'id' 		=> $group[$value['model']]['id'],
									'parent_id' => $parentId 
								)
							);
							if (!$acl->Aro->save($data)) {
								echo 'Critical Error with Aros';
								exit;	
							}
						}
					}
					break;

				case 'ControlObject' :			
					$acos = $acl->Aco->find('list', array( 'fields' => array('Aco.alias'), 'recursive' => 0));
					if (empty($acos)) {
						$acos = array();
					}
					$controlObjects = $model->find('all');
					if (empty ($controlObjects)) {
						break;
					}
					
					foreach ($controlObjects as $controlObject) {
						if (!in_array($controlObject[$value['model']][$value['alias']],$acos)) {
							$acl->Aco->create();
							$data = array(
								'Aco' => array(
									'alias' 		=> $controlObject[$value['model']][$value['alias']],
									'foreign_key' 	=> $controlObject[$value['model']]['id'],
									'model' 		=> $value['model']
								)
							);
							$acl->Aco->save($data);
						}
					}
					
					foreach ($controlObjects as $controlObject) {
						if (!empty ($value['parentAlias']) && !empty ($controlObject[$value['parentAlias']]) && !empty ($controlObject[$value['parentAlias']][$value['alias']])) {
							$parentId = $controlObject[$value['parentAlias']]['id'];
							$data = array(
								'Aco' => array(
									'id' 		=> $controlObject[$value['model']]['id'],
									'parent_id' => $parentId 
								)
							);
							if (!$acl->Aco->save($data)) {
								echo 'Critical Error with Acos';
								exit;	
							}
						}
					}
					break;
				default :
					break;
			}
		}
	} // importMasterData

	function getAclTree ($type='Aro') {
		$acl = ClassRegistry::init('AclComponent');
		
		$tmp = $acl->{$type}->find('all', array('recursive' => 0));
		
		if (empty($tmp)) {
			return false;
		}
		
		return $this->_listParents($tmp, $type);
	}// createRequesterTree
	
	// TODO A: depreated since parent_id
	function _listParents ( $list = array(), $type = 'Aro') {
		if (empty($list))
			return false;
			
		$roots = array();
		$relations = array();
		foreach ($list as $element) {
			$parent = $this->_getParent($element, $list, $type);
		
			if (empty($parent)) 
				$roots[] = $element[$type]['alias'];
			else 
				$relations[$element[$type]['alias']]  = $parent[$type]['alias'];
		}	
		
		$result = array();
		$tmp=$this->_convertParentListToTree($roots, $relations, $result);

		return $tmp;
	}// _recursiveParentSearch
	
	// TODO A: depreated since parent_id
	function _getParent($element, $candidates, $type = 'Aro') {
		foreach ( $candidates as $candidate) {
			if ($element[$type]['lft'] > $candidate[$type]['lft'] && $element[$type]['rght'] < $candidate[$type]['rght'])
					$parents[] = $candidate;
		}
		
		$result = null;
		if (empty($parents))
			return $result;
			
		$maxDist = 0;
		foreach ($parents as $parent) {
			$dist = 1/($parent[$type]['rght'] - $parent[$type]['lft']);
			if ($dist > $maxDist)
				$result = $parent;
		}
			
		return $result;
	}// _getParent
	
	// TODO A: depreated since parent_id
	function _convertParentListToTree ($roots, $relations, &$result = array()) {
		$str = "<ul style=\"margin: 0;\">"; // margin 0 pour surcharger la déclaration du fichier CSS
		foreach ($roots as $root) {
			$children = array();
			$str .= "<li>".$root;
			foreach( $relations as $child => $parent ) {
				if ($root===$parent)
					$children[] = $child;
			}

			if (!empty($children)) {
				$result[$root] = array();
				$str .= $this->_convertParentListToTree($children, $relations, $result[$root]);
			} else 
				$result[] = $root;
			$str .= "</li>";
		}
		$str .= "</ul>";
		return $str;
	}// _convertParentListToTree

	function deleteAclObjects($type = null, $permissions = false) {
		if (empty($type)) {
			$this->query("TRUNCATE TABLE acos");
			$this->query("TRUNCATE TABLE aros");
		} else if ($type == 'Aro'|| $type == 'Aco') {
			$this->query('TRUNCATE TABLE '.strtolower($type).'s');
		} 
		
		if ($permissions) 
			$this->query("TRUNCATE TABLE aros_acos");
	} // deleteAclObjects

	function reloadAcls($type=null) {
		if ($type == null) {
			$this->deleteAclObjects();
			$this->importMasterData();
		} else if ($type == 'Aco') {
			$this->deleteAclObjects($type);
			$this->importMasterData('ControlObject');
		} else if ($type == 'Aro') {
			$this->deleteAclObjects($type);
			$this->importMasterData('Group');
		}
	} // reloadAcls
	
	function setMapping($mapping = array ()) {
		if (!empty ($mapping)) {
			$this->mapping = $mapping;
		}
	}// setMasterData

} // AclInitialization
?>
