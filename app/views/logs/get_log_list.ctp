<?php debug($logs); ?>

<?php echo $form->input('Search/logPath', array(
									'options' => $logs, 
									'label' => '<b>' . __('Log file', true). '</b>',
									'selected' => $this->params['pass'][1],
									'class' => 'f-name',
									));?>