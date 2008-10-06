<?php
/* SVN FILE: $Id$ */
/**
 * Parent controller of Aclite
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
 * Parent controller of Aclite
 *
 * @package		aclite
 * @subpackage	aclite
 */
class AcliteAppController extends AppController {

	var $name = 'AcliteApp';

	var $helpers = array (
		'Html'
	);

	var $components = array (
		'Acl'
	);

	var $authGlobal = array (
		'AcliteApp' => array (
			'authorizations'
		)
	);

}
?>