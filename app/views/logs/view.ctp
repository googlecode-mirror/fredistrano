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
 <?php	
	if ( $log !== false) {
	?>
		<p><strong><?php __('Logs retreived from') ?> <?php e($project['Project']['name']); 
			if (isset($size)) { 
				printf(" [<em>%s</em> - %.3f Ko]", $logPath, ($size/1024));
			} ?></strong></p>

<div class="log_viewer">

		<div style="height: 300px; overflow: auto;">
			<?php e($log); ?>
		</div>	
	<?php 
	} else {
		__('Unable to retreive log');
		echo '<ul><li><i>'.$error.'</i></li></ul>';
	}
	?>
</div>