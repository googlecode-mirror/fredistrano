<?php 
$action = $serverName . $appPath . "/users/login";
if ( env('HTTPS') || (!env('HTTPS') && (Configure::read('httpsenabled') == 0)) ){  
	$action = 'http://'. $action;
} else if (!env('HTTPS') && (Configure::read('httpsenabled') > 0)){
	$action = 'https://'. $action;
}
?>

<?php echo $form->create('User', array('url' => $action, 'method' => 'post', 'class' => 'f-wrap-1'));?>

    <fieldset>
	    <h6><?php __('Identification');?></h6>
	    <?php echo $form->input('login', array('label' => __('Username'), 'size' => 15, 'class' => 'f-name'));?>
	    <?php echo $form->password('password', array('label' => __('Password'), 'size' => 15, 'class' => 'f-name'));?>
	    <?php echo $form->submit(__('Login'), array('class' => 'f-submit')) ?>
	</fieldset>
<?php echo $form->end();?>