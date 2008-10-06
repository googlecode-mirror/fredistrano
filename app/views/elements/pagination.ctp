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
 <hr />
<div class="pagination">
		<div id="spinner" style="display:none;float:right;margin:0 10px;">
			<?php echo $html->image('loading_orange.gif'); ?>
		</div>
		<?php
		$paginator->options(array('update' => 'content', 'indicator' => 'spinner'));
   		echo $paginator->prev(__('« Previous ', true), null, null, array('class' => 'disabled'));
	   	echo $paginator->numbers(array('separator' => ' '));
	   	echo $paginator->next(__(' Next »', true), null, null, array('class' => 'disabled'));
		echo '<br /><p><em>';
		echo $paginator->counter(array(
									'format' => __('Page %page% of %pages%, showing %current% records out of 
									%count% total', true)
									));
		echo '</em></p>';
		//exemple d'affichage possible :							
		//'format' => 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%'
	   ?>
	
</div>
