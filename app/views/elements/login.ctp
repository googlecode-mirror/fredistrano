<?php 
$action = $serverName . $appPath . "/users/login";
if ( env('HTTPS') || (!env('HTTPS') && (Configure::read('Security.https') == 0)) ){  
	$action = 'http://'. $action;
} else if (!env('HTTPS') && (Configure::read('Security.https') > 0)){
	$action = 'https://'. $action;
}
?>

<?php echo $form->create('User', array('url' => $action, 'method' => 'post', 'class' => 'f-wrap-1'));?>

    <fieldset>
	    <h6><?php __('Identification');?></h6>
	    <?php echo $form->input('login', array('label' => __('Username', true), 'size' => 15, 'class' => 'f-name'));?>
	    <?php echo $form->password('password', array('label' => __('Password', false), 'size' => 15, 'class' => 'f-name'));?>
	    <?php echo $form->submit('login', array('class' => 'f-submit', 'label' => __('Login', true))) ?>
	</fieldset>
<?php echo $form->end();?>