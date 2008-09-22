<?php
	if ( $log !== false) {
	?>
		<p><strong><?php __('Logs retreived from') ?> <?php e($project['Project']['name']); 
			if (isset($size)) { 
				printf(" [<em>%s</em> - %.3f Ko]", $logPath, ($size/1024));
			} ?></strong></p>

<div class="log_viewer">

		<div style="height: 300px; overflow: auto;">
			<?php //e(nl2br(htmlspecialchars(str_replace("<br />", "\n", $log)))); ?>
			<?php e(nl2br(htmlspecialchars($log))); ?>
		</div>	
	<?php 
	} else {
		__('Unable to retreive log');
		echo '<ul><li><i>'.$error.'</i></li></ul>';
	}
	?>
</div>
