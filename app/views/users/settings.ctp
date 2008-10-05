<h1><?php __('User settings') ?></h1>

<?php echo $html->link(__('Change password', true), '/users/change_password/'.$session->read('User.User.id'))?>
<hr />
<?php echo $form->create('Profile', array('method' => 'post', 'class' => 'f-wrap-1', 'url' => '/users/settings/'.$session->read('User.User.id')));?>

	<fieldset>
		<?php echo $form->input('lang', 
								array(
									'options' => $availableLanguages, 
									'selected' => $session->read('User.Profile.lang'),
									'class' => 'f-name', 
									'label' => '<b>'.__('Prefered language', true).'</b>'
									)
								); ?>
		<?php echo $form->input('rss_token', 
								array(
									'label' => '<b>'.__('Rss token', true).'</b>',
									'size' => '60', 
									'class' => 'f-name'
									)
								);?>	
		<?php echo $form->hidden('user_id', array('value' => $session->read('User.User.id'))) ?>											
		<?php echo $form->hidden('id') ?>											
		
		<div class="f-submit-wrap">
			<?php echo $form->submit(__('Save', true), array('class' => 'f-submit'));?>
		</div>
	</fieldset>
<?php echo $form->end();?>