<h1><?php __('Help') ?></h1>
<p><em><?php __('Version') ?> <?php echo _VERSION.' - '. _RELEASEDATE; ?> - </em></p>

<?php __('Check for news and updates about Fredistrano on its') ?> <?php echo $html->link('homepage','http://code.google.com/p/fredistrano'); ?>	
	
<h3><?php __('Support') ?></h3>
<ul>
	<li><?php __('Documentation') ?> [ <?php echo $html->link('PDF-En','/files/'.__('Fredistrano-documentation-EN.pdf', true)); ?> ]</li>
	<li><?php echo $html->link(__('Knoweldge base', true),'http://code.google.com/p/fredistrano/wiki'); ?> <?php __('with detailled installation instructions, FAQS and solutions to common problems') ?></li>
	<li><?php __('Share your experience and problems directly on the') ?> <?php echo $html->link('Fredistrano forum','http://www.fbollon.net/forum/25'); ?></li>
	<li><?php __('Report') ?> <?php echo $html->link(__('bugs and enhancements', true),'http://code.google.com/p/fredistrano/issues/list'); ?> <?php __('to the development team') ?></li>
</ul>	

<h3><?php __('Developpement team') ?></h3>
<ul>
	<li><a href="http://www.fbollon.net/about">fbollon</a> - Project leader</li>
	<li><a href="http://www.fbollon.net/user/3">Dia</a> - Developement, beta test</li>
	<li><a href="http://www.fbollon.net/user/2">euphrate_ylb</a> - Developement</li>
</ul>

<h3><?php __('Acknowledgments') ?></h3>
<?php __('And finally, thank you, fredistrano users, for using this application to deploy your source code.') ?> 	