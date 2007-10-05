<?php
class User extends AppModel {

	var $name = 'User';

	var $validate = array (
		'login' => array (
			array (VALID_NOT_EMPTY, 'Veuillez saisir un login.'),
			array (array('isUnique', array('login')), 'Ce login est déjà utilisé.')
		)
	);

	var $hasAndBelongsToMany = array (
		'Group' => array (
			'className' => 'Group',
			'joinTable' => 'groups_users',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'unique' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		
	);

	function beforeSave() {
		// si y a un champ password : add() et change_password() mais pas edit()
		if (isset ($this->data[$this->name]['password']))
			$this->data[$this->name]['password'] = md5($this->data[$this->name]['password']);
		return parent :: beforeSave();
	}
	
	/**
	 * Vérifie si un utilisateur existe dans la base 
	 * @param String $login Utilisateur à vérifier
	 * @return boolean 
	 */
	function isValid($login = null) {
		$tmp = $this->findByLogin($login);
		return !empty($tmp);
	}// isValid
	
	function _format(& $user) {
		$groups = null;
		if (!empty ($user['Group']))
			foreach ($user['Group'] as $group)
				$groups .= $group['name'] . ' ; ';
		$user['User']['groups'] = $groups;
		unset ($user['Group']);
	}

	function _formatAll(& $users) {
		foreach ($users as & $user)
			$this->_format($user);
	}
	
}
?>