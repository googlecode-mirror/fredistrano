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