<?php
/* SVN FILE: $Id$ */
/**
 * Default template used for fredistrano
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.views.layout
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Default template used for fredistrano
 *
 * @package		app
 * @subpackage	app.views.layout
 */
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
Copyright: Daemon Pty Limited 2006, http://www.daemon.com.au
Community: Mollio http://www.mollio.org $
License: Released Under the "Common Public License 1.0",
http://www.opensource.org/licenses/cpl.php
License: Released Under the "Creative Commons License",
http://creativecommons.org/licenses/by/2.5/
License: Released Under the "GNU Creative Commons License",
http://creativecommons.org/licenses/GPL/2.0/
-->
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php echo $html->meta(
    'favicon.ico',
    'favicon.ico',
    array('type' => 'icon')
);?>
<title>Fredistrano</title>

<?php echo $html->css('main','stylesheet',array('media'=>'screen'),true) ?>
<?php echo $html->css('specific','stylesheet',array('media'=>'screen'),true) ?>
<?php echo $html->css('print','stylesheet',array('media'=>'print'),true) ?>
<!--[if lte IE 6]>
<?php echo $html->css('ie6_or_less','stylesheet',array('media'=>'screen'),true) ?>
<![endif]-->

<?php echo $javascript->link('common') ?>

<?php echo $javascript->link('scriptaculous/prototype') ?>
<?php echo $javascript->link('scriptaculous/scriptaculous.js?load=effects') ?>
<!--[if lt IE 7]>
<?php echo $javascript->link('pngfix') ?>
<![endif]-->

</head>

<body id="type-e">
<div id="wrap">

	<div id="header">
		<div id="site-name"><?php echo $html->link($html->image('logo1.png', 
												array(
													'alt' => __('Logo', true)
													)
												), 
									'/', 
									null,
									false,
									false);?>
		</div>
			<?php echo $this->renderElement('menu') ?>
		</div>

	<div id="content-wrap">
		<div id="utility">
			<?php
				if(isset($_SESSION['User'])){
					echo $this->renderElement('logout');
				}else{
					echo $this->renderElement('login'); 
				}
			?>

			<?php echo $this->renderElement('context_menu') ?>					
		</div>
		
		<div id="content">
			<?php 
			if ($session->check('Message.flash')):
				$session->flash();
			?>
				<script type="text/javascript">
					new Effect.Highlight('flashMessage', {startcolor:'#ffff99', endcolor:'#ffffff', duration: 3.0});
					Effect.Pulsate('flashMessage');
				</script>
			<?php endif; ?>

			<?php echo $content_for_layout;	?>
	
			<div id="footer">
				<p>&copy; 2007-2008 <a href="http://fbollon.net">fbollon.net</a> 
				<?php echo $html->link( 
									$html->image( 'cake.power.gif', array('alt' => 'cakephp', 'title' => 'cakephp', 'style' => 'float:right;')), 
									'http://cakephp.org/', null, false, false ); ?>
									
				</p>
			</div>
		</div>
	</div>
</div>
</body>
</html>