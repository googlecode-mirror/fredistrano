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
 * @subpackage		app.views.logs
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.logs
 */
 ?>
 <?php debug($logs); ?>

<?php echo $form->input('Search/logPath', array(
									'options' => $logs, 
									'label' => '<b>' . __('Log file', true). '</b>',
									'selected' => $this->params['pass'][1],
									'class' => 'f-name',
									));?>