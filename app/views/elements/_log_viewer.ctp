<?php
if ( $log !== false) {
?>
	<p><?php __('Logs retreived from') ?> <?php e($project['Project']['name']); 
		if (isset($size)) { 
			printf(" [<em>%s</em> - %.3f Ko]", $logPath, ($size/1024));
		} ?></p>
	<div style="height: 300px; overflow: auto;">
		<?php e($log); ?>
	</div>	
<?php 
} else {
	__('Unable to retreive log');
	echo '<ul><li><i>'.$error.'</i></li></ul>';
}
?>