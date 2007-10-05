<?php
class AclManagement extends AcliteAppModel {

	var $name = 'AclManagement';

	var $useTable = false;

	var $mapping = array (
		'Group' => array (
			'model' => 'Group',
			'parentAlias' => 'ParentGroup',
			'alias' => 'name'
		),
		'User' => array (
			'model' => 'User',
			'id' => 'id',
			'alias' => 'login'
		),
		'ControlObject' => array (
			'model' => 'ControlObject',
			'parentAlias' => 'ParentControlObject',
			'id' => 'id',
			'alias' => 'name'
		)
	);

	var $actions = array (
		'*'=>'*',
		'read'=>'read',
		'create'=>'create',
		'update'=>'update',
		'delete'=>'delete'
	);

	var $gPrefix = 'group.';
	
	var $uPrefix = '';

	var $Permission = null;
	
	function __construct() {
		parent::__construct();	
		
		$this->Permission = new Permission();
	}// __construct
	
	function & getAcl() {
		static $acl = null;

		if (!isset ($acl) || !$acl) {
			$acl =  new AclComponent();
		}
		return $acl;
	}// getAcl
	
	function importMasterData($data = null) {
		$acl = AclManagement::getAcl();
		
		$list = $this->mapping;
		if (!empty($data) && (in_array($data,array_keys($list))))
			$list = array($data => $list[$data]);
			
		foreach ($list as $key => $value) {
			$modelName = $value['model'];
			loadModel($modelName);
			$model = new $modelName ();
			
			// load data
			$aros = $acl->Aro->generateList(null, null, null, null, '{n}.Aro.alias');
			if (empty($aros))
				$aros = array();
				
			switch ($key) {
				case 'Group' :
					$groups = $model->findAll(null,null,null,null,null,0);
					if (empty ($groups))
						break;

					foreach ($groups as $group) {
						//echo "1.1. Add group : ".$group[$value['model']][$value['alias']]."<br/>";
						if (!in_array($this->gPrefix.$group[$value['model']][$value['alias']],$aros))
							$acl->Aro->create(0, null, $this->gPrefix . $group[$value['model']][$value['alias']]);
					}

					foreach ($groups as $group) {
						if (!empty ($value['parentAlias']) && !empty ($group[$value['parentAlias']]) && !empty ($group[$value['parentAlias']][$value['alias']])) {
							$parent = $group[$value['parentAlias']][$value['alias']];
							//echo "1.2. Add group parent : ".$parent."-".$group[$value['model']][$value['alias']]."<br/>";
							$acl->Aro->setParent($this->gPrefix . $parent, $this->gPrefix . $group[$value['model']][$value['alias']]);
						}
					}
					break;

				case 'User' :	
					$users = $model->findAll();
					if (empty ($users))
						break;

					foreach ($users as $user) {
						//echo "2.1. Add User : ".$user[$value['model']][$value['id']]."-".$user[$value['model']][$value['alias']]."<br/>";
						if (!in_array($user[$value['model']][$value['alias']],$aros))
							$acl->Aro->create($user[$value['model']][$value['id']], null, $this->uPrefix . $user[$value['model']][$value['alias']]);
					}

					foreach ($users as $user) {
						if (!empty ($user[$this->mapping['Group']['model']])) {
							$mainGroup = $user[$this->mapping['Group']['model']][0][$this->mapping['Group']['alias']];
							//echo "2.2. Add main group : ".$mainGroup."-".$user['User'][$value['alias']]."<br/>";
							$acl->Aro->setParent($this->gPrefix . $mainGroup, $this->uPrefix . $user['User'][$value['alias']]);
						}
					}
					break;

				case 'ControlObject' :			
					$controlObjects = $model->findAll(null,null,null,null,null,0);
					$acos = $acl->Aco->generateList(null, null, null, null, '{n}.Aco.alias');
					if (empty($acos))
							$acos = array();
				
					if (empty ($controlObjects))
						break;

					foreach ($controlObjects as $controlObject) {
						//echo "3.1. Add acos : ".$controlObject[$value['model']][$value['id']]."-".$controlObject[$value['model']][$value['alias']]."<br/>";
						if (!in_array($controlObject[$value['model']][$value['alias']],$acos))
							$acl->Aco->create($controlObject[$value['model']][$value['id']], null, $controlObject[$value['model']][$value['alias']]);
					}
					
					foreach ($controlObjects as $controlObject) {
						if (!empty ($value['parentAlias']) && !empty ($controlObject[$value['parentAlias']]) && !empty ($controlObject[$value['parentAlias']][$value['alias']])) {
							$parent = $controlObject[$value['parentAlias']][$value['alias']];
							//echo "3.2. Add acos parent : ".$parent."-".$controlObject[$value['model']][$value['alias']]."<br/>";
							$acl->Aco->setParent($parent, $controlObject[$value['model']][$value['alias']]);
						}
					}
					break;

				default :
					break;
			}
		}
	} // importMasterData

	function getAclTree ($type='Aro') {
		$acl = AclManagement::getAcl();
		$tmp = $acl->{$type}->findAll(null,null,null,null,null,0);
		
		if (empty($tmp))
			return false;
		
		return $this->_listParents($tmp, $type);
	}// createRequesterTree
	
	
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
			$this->importMasterData('User');
		}
	} // reloadAcls
	
	function setMapping($mapping = array ()) {
		if (!empty ($mapping))
			$this->mapping = $mapping;
	}// setMasterData

} // AclInitialization
?>
