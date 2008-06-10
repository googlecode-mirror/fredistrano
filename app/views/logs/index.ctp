<?php echo $form->create(null, array('url' => '/projects/edit/'.$html->value('Project/id'), 'method' => 'post', 'class' => 'f-wrap-1'));?>
<fieldset>
	<h3>Query parameters</h3>
	<div>
		
		<?php echo $form->label('Search/project_id', '<b>'.__('Project name', true).'</b>') ?>
		<?php echo $form->select('Search/project_id', $projects, $project_id, array('label' => '<b>'.__('Select a project', true).'</b>','class' => 'f-name'));?>
		<?php e($form->input('Search/pattern', array('label' => '<b>'.__('Enter a search pattern', true).'</b>','size' => '20', 'class' => 'f-name')));?>
		<?php echo $form->label('Search/reverse', '<b>'.__('Reverse order', true).'</b>') ?>
		<?php echo  e($form->checkbox('Search/reverse', null, array('checked' => 'checked', 'class' => 'f-checkbox'))); ?>
		<?php e($form->input('Search/maxsize', array('label' =>'<b>'. __('Max load size (in bytes)', true).'</b>', 'size' => '20', 'class' => 'f-name', 'value' => Configure::read('Log.maxSize'))));?>
		<?php e($ajax->submit('Retreive',array( 'url' => '/logs/view', 'update' => 'logs', 'class' => 'f-submit'))); ?>
	</div>
</fieldset>
<?php echo $form->end();?>

<h3>Log output</h3>
<div id="logs">Please select a project in the query section</div>
<br/><br/><br/><br/>



