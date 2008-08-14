<?php
class User extends AppModel {

	var $name = 'User';

	var $validate = array(
	    'login' => array(
	        'rule1' => array(
	            'rule' => 'alphaNumeric',
	            'required' => true
	        ),
	        'rule2' => array(
	            'rule' => 'isUnique'
	        )
	    ),
	    'password' => array(
	        'rule1' => array(
	            'rule' => 'alphaNumeric',
	            'required' => true
	        ),
	        'rule2' => array(
	            'rule' => array('minLength', 6)
	        )
	    ),
		 'email' => array(
		    'rule' => array('email', true),
		  	// 'message' => 'Please supply a valid email address.'
		    )
	);

	// var $displayField = 'login';
	
	var $hasAndBelongsToMany = array (
		'Group' => array (
			'className' => 'Group',
			'joinTable' => 'groups_users',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'group_id'
		)
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