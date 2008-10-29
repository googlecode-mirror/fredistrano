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
 * @package			app
 * @subpackage		app.models
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.models
 */
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
	            'rule' => array('minLength', 4)
	        )
	    )
	);
	
	var $hasAndBelongsToMany = array (
		'Group' => array (
			'className' => 'Group',
			'joinTable' => 'groups_users',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'group_id'
		)
	);
	
	var $hasOne = array (
		'Profile' => array ('className' => 'Profile',
			'foreignKey' => 'user_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => ''
			)
	);
	

	function beforeSave() {
		// si y a un champ password : add() et change_password() mais pas edit()
		if (isset ($this->data[$this->name]['password'])){
			$this->data[$this->name]['password'] = md5($this->data[$this->name]['password']);
		}
		
		// if (empty($this->data['Profile']['rss_token'])) {
		// 	$this->data['Profile']['rss_token'] = sha1( rand(0,10000) + time());
		// }

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
	
	/**
	 * authentification par webservice + mysql
	 * pour changer le type d'authentification voir le fichier app/confg/config.php
	 */
	function authenticate($user, $passwd) {
		
		if (Configure::read('Security.authenticationType') === 0){
			// Accept all
			return true;
			
		} else if (Configure::read('Security.authenticationType') === 1) {
			// Custom authentification 
			App::import('Vendor', 'authentication');
			return CustomAuthentication::authenticate($user, $passwd);

		} else if (Configure::read('Security.authenticationType') === 2) {
			// MySQL authentification 
			$user = $this->findByLogin($user); // requete cachée
			return (!empty ($user['User']['password']) and ($user['User']['password'] == md5($passwd)));
			
		} else {
			return false;
			
		}
	} // authenticate
	
		
}
?>