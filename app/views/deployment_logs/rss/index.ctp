<?php
	//e($rss->channel(null, array('title' => 'fbollon','link' => 'http://fbollon.net', 'description' => 'azeazeaz')));
	e($rss->items($logs, 'transformRSS'));

	function transformRSS($log) {
		return array(
			'title' 		=> date("Y/m/d - H:i:s", strtotime($log['DeploymentLog']['created'])).' - '.$log['Project']['name'],
			'link'  		=> array('action' => 'view', $log['DeploymentLog']['id']),
			'guid'  		=> array('action' => 'view', $log['DeploymentLog']['id']),
			'description' 	=> $log['DeploymentLog']['comment'],
			'author' 		=> $log['User']['login'],
			'pubDate'		=> $log['DeploymentLog']['created']
		);
	}
// 
// $created = strtotime($log['DeploymentLog']['created']);
// $newdate = date("Y/m/d",$created);
// $com = $log['DeploymentLog']['comment'];
// $com = $text->truncate($com, 30);
// if ($newdate != $lastdate) {
// 	$lastdate = $newdate;
// 	echo '<h3>'.$newdate.'</h3>';
// }
// $line = $html->link(date("H:i:s",$created),'/deploymentLogs/view/'.$log['DeploymentLog']['id']).' : deployment of <b>'.
// 	$log['Project']['name'].'</b> by <b>'. $log['User']['login'].'</b> [<i>'. $com .'</i>]';
// echo '<li>'.$line.'</li>';
?>
