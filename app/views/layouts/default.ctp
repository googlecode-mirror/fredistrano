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
		<div id="site-name">Fredistrano</div>
			<?php echo $this->renderElement('menu') ?>
		</div>

	<div id="content-wrap">

		<div id="logo">
		<?php echo $html->link( $html->image( 'logo.png', array('alt' => 'fbollon.net', 'title' => 'fbollon.net')), 'http://www.fbollon.net', null, false, false ); ?>
		</div>
		<!--<div id="flag">
        <?php echo $html->link( $html->image( 'fr.gif', array('alt' => 'french', 'title' => 'french')), '/home/switchLanguage/fr-FR', null, false, false ); ?>
        <?php echo $html->link( $html->image( 'en.gif', array('alt' => 'english', 'title' => 'english')), '/home/switchLanguage/en-US', null, false, false ); ?>
		</div>-->
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
			<p>&copy; 2007 <a href="http://fbollon.net">fbollon.net</a> </p>
			</div>
		</div>
	</div>
</div>
</body>
</html>