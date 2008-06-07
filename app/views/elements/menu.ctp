<ul id="nav">
		<li class="<?php echo ($this->params['controller'] == 'home')?"active":"first";?>"><?php echo $html->link(__('Home', true),'/'); ?></li>
		<li class="<?php echo (in_array($this->params['controller'], array('projects','deploymentLogs')))?"active":"first";?>"><?php echo $html->link(__('Projects', true),'/projects'); ?></li>
		<li class="<?php echo (in_array($this->params['controller'], array('logs')))?"active":"first";?>"><?php echo $html->link(__('Logs', true),'/logs'); ?></li>
		<li class="<?php echo (in_array($this->params['controller'], array('administration','users','groups','control_objects','acl_management')))?"active":"first";?>"><?php echo $html->link(__('Administration', true),'/administration'); ?></li>
		<li class="<?php echo ($this->params['controller'] == 'pages')?"active":"first";?>"><?php echo $html->link(__('Help', true),'/pages/help'); ?></li>
</ul>


