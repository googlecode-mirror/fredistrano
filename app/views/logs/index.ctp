<?php echo $form->create(null, array('url' => '/projects/edit/'.$html->value('Project/id'), 'method' => 'post', 'class' => 'f-wrap-1'));?>
<h3>Query parameters</h3>
<div>
	Select a project <?php echo $form->select('Search/project_id', $projects, $project_id); ?>
	<?php e($form->input('Search/pattern', array('label' => __('Enter a search pattern', true),'size' => '20', 'class' => 'f-name')));?>
	Reverse order : <?php echo  e($form->checkbox('Search/reverse', null, array('checked' => 'checked', 'class' => 'f-checkbox'))); ?>
	<?php e($form->input('Search/maxsize', array('label' => __('Max load size (in bytes)', true), 'size' => '20', 'class' => 'f-name')));?>
	<?php e($ajax->submit('Retreive',array( 'url' => '/logs/view', 'update' => 'logs'))); ?>
</div>
</fieldset>
<?php echo $form->end();?>

<h3>Log output</h3>
<div id="logs">Please select a project in the query section</div>
<br/><br/><br/><br/>