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
 * @subpackage		app.views.pages
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.pages
 */
 ?>

<h1><?php __('Help') ?></h1>

<?php __('Find on this page help information about Fredistrano.') ?>

<h3><?php __('Offline support') ?></h3>
<ul>
	<li><?php __('Documentation') ?> 
		<ul>
			<li>[ <?php echo $html->link('PDF-En', '/files/Fredistrano-documentation-EN.pdf'); ?> ]</li>
			<li>[ <?php echo $html->link('PDF-Fr', '/files/Fredistrano-documentation-FR.pdf'); ?> ]</li>
		</ul>
	</li>
</ul>

<h3><?php __('Online support') ?></h3>
<ul>
	<li><?php echo $html->link(__('Knoweldge base', true),'http://code.google.com/p/fredistrano/wiki'); ?> <?php __('with detailled installation instructions, FAQS and solutions to common problems') ?></li>
	<li><?php __('Share your experience and problems directly on the') ?> <?php echo $html->link('Fredistrano forum','http://groups.google.com/group/fredistrano-discuss'); ?></li>
	<li><?php __('Report') ?> <?php echo $html->link(__('bugs and enhancements', true),'http://code.google.com/p/fredistrano/issues/list'); ?> <?php __('to the development team') ?></li>
</ul>