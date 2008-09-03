<?php
	e($rss->items($logs, 'transformRSS'));

	function transformRSS($log) {
		return array(
			'title' 		=> $log['Project']['name'],
			'link'  		=> array('action' => 'view', $log['DeploymentLog']['id']),
			'guid'  		=> array('action' => 'view', $log['DeploymentLog']['id']),
			'description' 	=> __('Comment', true) . " : " .$log['DeploymentLog']['comment'] ." [by ". $log['User']['login'] . "]",
			'author' 		=> $log['User']['login'],
			'pubDate'		=> $log['DeploymentLog']['created']
		);
	}

?>
