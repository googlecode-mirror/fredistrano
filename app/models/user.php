<?php
class User extends AppModel {
	var $name = 'User';

	var $validate = array (
		'username' => array (
			array (VALID_NOT_EMPTY, 'Veuillez saisir un login.'),
			array ('isUsernameUnique', 'Ce login existe déjà.')
		),
		'password' => array (
			array (VALID_NOT_EMPTY, 'Veuillez saisir un mot de passe.')
		),
		'first_name' => array (
			array (VALID_NOT_EMPTY, 'Veuillez saisir un prénom.')
		),
		'last_name' => array (
			array (VALID_NOT_EMPTY, 'Veuillez saisir un nom.')
		)
		,
		'email' => array (
			array (VALID_EMAIL_OPTIONAL, 'L\'adresse e-mail est invalide.')
		)
	);
	
	var $hasMany = array (
		'DeploymentLog' => array (
			'className' => 'DeploymentLog',
			'order'     => 'DeploymentLog.date DESC',
			'limit'     => '5',
			'foreignKey' => 'user_id',
			'dependent' => false
		)
	);

	// basée sur http://cakebaker.wordpress.com/2006/02/06/yet-another-data-validation-approach/
	// rajouté par mes soins une condition pour ne pas tester le username lors d'une modification
	function isUsernameUnique() {
		if ( $this->id == null ) //Adding a user
        {
            return (!$this->hasAny( array( 'User.username' => $this->data[$this->name]['username'] ) ));
        }
        else //Editing a user
        {
            return (!$this->hasAny( array( 'User.username' => $this->data[$this->name]['username'], 'User.id' => '!='.$this->data[$this->name]['id'] ) ) );
        }
	}

	function beforeSave() {
		// si y a un champ password : add() et change_password() mais pas edit()
		if (isset ($this->data[$this->name]['password'])) {
			$this->data[$this->name]['password'] = md5($this->data[$this->name]['password']);
		}
		return parent::beforeSave();
	}
}

?>