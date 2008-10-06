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
 <h1><?php __('About') ?></h1>
<p><em><?php __('Version') ?> <?php echo F_VERSION.' - '.F_RELEASEDATE; ?></em></p>

<?php __('Check for news and updates about Fredistrano on its') ?> <?php echo $html->link('homepage','http://code.google.com/p/fredistrano'); ?>	

<h3><?php __('Developpement team') ?></h3>
<ul>
	<li><a href="http://www.fbollon.net/about">fbollon</a> - Project leader</li>
	<li><a href="http://www.fbollon.net/user/3">Dia</a> - Developement, beta test</li>
	<li><a href="http://www.fbollon.net/user/2">euphrate_ylb</a> - Developement</li>
</ul>

<h3><?php __('How to contribute') ?></h3>
<p><?php __('If you are interested by fredistrano and willing to contribute to this project, you can help us to test newly implemented feature or join the development team. Contact us by email for further details.'); ?></p>

<h3><?php __('Acknowledgments') ?></h3>
<?php __('And finally, thank you, fredistrano users, for using this application to deploy your source code.') ?>