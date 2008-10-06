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
 <?php
if(isset($errorMessage)){
?>
	<div class="error">
		<?php __('The application is unable to continue.') ?><br/>
		<ul><li><em><?php echo $errorMessage;?></em></li></ul>
	</div>
<?php
}
?>

<div class="console">
<?php
	echo "<pre>";
	// print_r(htmlentities(str_replace("><",">\n<", $output)));
	print_r($output);
	echo "</pre>";
?> 
</div>