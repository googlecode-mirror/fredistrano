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
 * @subpackage		app.views.deployment_logs.rss
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.deployment_logs.rss
 */
 ?>
 <?php
    e($rss->items($logs, 'transformRSS'));

    function transformRSS($log) {
        return array(
            'title'         => $log['Project']['name'],
            'link'          => array('action' => 'view', $log['DeploymentLog']['id']),
            'guid'          => array('action' => 'view', $log['DeploymentLog']['id']),
            'description'     => __('Comment', true) . " : " .$log['DeploymentLog']['comment'] ." [by ". $log['User']['login'] . "]",
            'author'         => $log['User']['email'].' ('.$log['User']['first_name'].' '.$log['User']['last_name'].')',
            'pubDate'        => $log['DeploymentLog']['created']
        );
    }
?>