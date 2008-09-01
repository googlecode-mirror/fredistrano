<?php
class Profile extends AppModel {

	var $name = 'Profile';

	var $belongsTo = array(
			'User' => array('className' => 'User',
								'foreignKey' => 'user_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);

	function beforeSave() {
		
		if (empty($this->data['Profile']['rss_token'])) {
			$this->data['Profile']['rss_token'] = sha1( rand(0,10000) + time());
		}

		if (empty($this->data['Profile']['lang'])) {
			$this->data['Profile']['lang'] = Configure::read('Fredistrano.language');
		}
		

		return parent :: beforeSave();
	}
	

}
?>