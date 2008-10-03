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