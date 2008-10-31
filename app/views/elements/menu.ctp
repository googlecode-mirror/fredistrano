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
 * @subpackage		app.views.elements
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.elements
 */
?>
 <ul id="nav">
		<li class="<?php echo ($this->params['controller'] == 'home')?"active":"first";?>"><?php echo $html->link(__('Home', true),'/'); ?></li>
		<li class="<?php echo (in_array($this->params['controller'], array('projects','deploymentLogs')))?"active":"first";?>"><?php echo $html->link(__('Projects', true),'/projects'); ?></li>
		<li class="<?php echo (in_array($this->params['controller'], array('logs')))?"active":"first";?>"><?php echo $html->link(__('Logs', true),'/logs'); ?></li>
		<li class="<?php echo (in_array($this->params['controller'], array('administration','users','groups','control_objects','acl_management','aclManagement')))?"active":"first";?>"><?php echo $html->link(__('Administration', true),'/administration'); ?></li>
		<li class="<?php echo ($this->params['url']['url'] == 'pages/help')?"active":"first";?>"><?php echo $html->link(__('Help', true),'/pages/help'); ?></li>
</ul>


