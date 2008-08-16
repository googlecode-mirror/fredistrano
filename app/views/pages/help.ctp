<h1><?php __('Help') ?></h1>

<h3><?php __('Offline support') ?></h3>
<ul>
	<li><?php __('Documentation') ?> [ <?php echo $html->link('PDF-En','/files/'.__('Fredistrano-documentation-EN.pdf', true)); ?> ]</li>
</ul>

<h3><?php __('Online support') ?></h3>
<ul>
	<li><?php echo $html->link(__('Knoweldge base', true),'http://code.google.com/p/fredistrano/wiki'); ?> <?php __('with detailled installation instructions, FAQS and solutions to common problems') ?></li>
	<li><?php __('Share your experience and problems directly on the') ?> <?php echo $html->link('Fredistrano forum','http://www.fbollon.net/forum/25'); ?></li>
	<li><?php __('Report') ?> <?php echo $html->link(__('bugs and enhancements', true),'http://code.google.com/p/fredistrano/issues/list'); ?> <?php __('to the development team') ?></li>
</ul>