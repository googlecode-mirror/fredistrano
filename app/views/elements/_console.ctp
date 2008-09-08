<?php
if(isset($errorMessage)){
?>
	<div class="error">
		<?php __('The application is unable to continue.') ?><br/>
		<ul><li><em><?php echo $errorMessage;?></em></li></ul>
	</div>
<?php
}
?>

<div class="console">
<?php
	echo "<pre>";
	print_r(htmlentities(str_replace("><",">\n<", $output)));
	echo "</pre>";
?> 
</div>