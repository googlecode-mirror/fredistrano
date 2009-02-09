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
class Group extends AppModel {

	var $name = 'Group';
		
	var $validate = array(
	    'name' => array(
	        'rule1' => array(
	            'rule' => 'alphaNumeric',
	            'required' => true
	        ),
	        'rule2' => array(
	            'rule' => 'isUnique'
	        )
	    )
	);

	var $hasAndBelongsToMany = array (
		'User' => array (
			'className' => 'User',
			'joinTable' => 'groups_users',
			'foreignKey' => 'group_id',
			'associationForeignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'unique' => '',
			// 'finderQuery' => 'SELECT user_id AS id FROM groups_users AS User WHERE group_id = {$__cakeID__$}',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	var $belongsTo = array (
		'ParentGroup' => array (
			'className' => 'Group',
			'foreignKey' => 'parent_id'
		)		
	);
	
	function getUsers($id = null) {
    	if (is_null($id)) {
			return null;
		}
			
		$sql = "SELECT `user_id` FROM `groups_users` WHERE `group_id` LIKE '$id'";	
		$res = $this->query($sql);	
		
		$tmp = array();
		$size = count($res);
		for ($i = 0; $i < $size; $i++) {
			$tmp[$i]['User']['id'] = $res[$i]['groups_users']['user_id'];
		}
		
		return $tmp;
	}// getUsers

	function updateMembership($data = null){
		if (is_null($data)) {
			return false;
		}
		
		$this->id = $data['Group']['id'];
		$this->data = $data;	

		if ($this->deleteMembership()) {
			return $this->_addMembership();
		}
	}// updateMembership

	function deleteMembership($id=null){
		if (is_null($id)) {
			$id = $this->id;
		}

		$sql = "DELETE FROM `groups_users` WHERE `group_id` = " . $id;
		return (is_array($this->query($sql)));
	}// deleteMembership
    
	private function _addMembership(){
		if (isset($this->data['User']) && is_array($this->data['User'])) {
			
			$sql = "INSERT INTO `groups_users` ( `user_id` , `group_id` ) VALUES ";
			
			$size = count($this->data['User']);
					for ($i=0; $i<$size; $i++){
						$tmpSql[] = "('".$this->data['User'][$i]."','".$this->id."')";
					}
			
			$sql .= implode(',',$tmpSql);		
			return (is_array($this->query($sql)));
		}
		return true;
	}// _addMembership
	
}// Group
?>