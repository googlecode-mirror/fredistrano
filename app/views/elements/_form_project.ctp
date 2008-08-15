<?php echo $form->input('Project/name', 
						array(
							'label' => '<b>'.__('Project name', true).'<span class="req">*</span></b>',
							'size' => '60', 
							'class' => 'f-name'
							)
						);?>
<?php echo $form->input('Project/svn_url', 
						array(
							'label' => '<b>'.__('SVN Url', true).'<span class="req">*</span></b>', 
							'size' => '60', 
							'class' => 'f-name'
							)
						);?>
<?php echo $form->input('Project/prd_path', 
						array(
							'label' => '<b>'.__('Application absolute path', true).'<span class="req">*</span></b>',
							'size' => '60', 
							'class' => 'f-name'
							)
						);?>
<?php echo $form->input('Project/prd_url', 
						array(
							'label' => '<b>'.__('Application Url', true).'<span class="req">*</span></b>', 
							'size' => '60', 
							'class' => 'f-name'
							)
						);?>
<?php echo $form->input('Project/log_path', 
						array(
							'label' => '<b>'.__('Log pathes (;)', true).'</b>', 
							'cols' => '80', 
							'class' => 'f-name',
							'type' => 'textarea'
							)
						);?>
