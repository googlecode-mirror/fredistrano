<div class="console">
<?php
	echo "console [executed in $took s] >";
	echo "<br>";
	echo "<pre>";
	print_r($output);
	echo "</pre>";
?> 
</div>

<script language="JavaScript" type="text/javascript">
  document.getElementById("DeploymentLogComment").value = "Revision exported <?php echo $revision;?>";
</script>