<div class="user">
	<table class="table1">
		<thead>
			<tr>
				<th colspan="2"><?php __('User details') ?></th>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<th colspan="2">
					<div class="tabletoplink">
						<ul>
							<li>
								<?php echo $html->link(
									$html->image('b_edit.png', array('alt' => __('Edit', true), 'title' => __('Edit', true), 'class' => 'action')) . __('Edit', true),
									'/users/edit/' . $user['User']['id'], 
									null, false, false) ?>
							</li>
<!--							<li>
								<?php echo $html->link(
									$html->image('users.png', array('alt' => __('manage groups for the current user', true), 'title' => __('manage groups for the current user', true), 'class' => 'action')) . __('Group management', true),
									'/groups/affectUsers/' . $user['User']['login'],
									null, false, false) ?>
							</li>
-->
							<li>
								<?php echo $html->link(
									$html->image('key.png', array('alt' => __('Change password', true), 'title' => __('Change password', true), 'class' => 'action')) . __('Change password', true),
									'/users/change_password/' . $user['User']['id'],
									null, false, false) ?>
							</li>
							<li>
								<?php echo $html->link(
									$html->image('b_drop.png', array('alt' => __('Delete', true), 'title' => __('Delete', true), 'class' => 'action')) . __('Delete', true), 
									'/users/delete/' . $user['User']['id'], 
									null, 
									__('Are you sure you want to delete', true) .' : ' . $user['User']['login'] . ' ?',
									false) ?>
							</li>
						</ul>
					</div>
				</th>
			</tr>
			
			<tr>
				<th class="sub"><?php __('Id') ?></th>
				<td><?php echo $user['User']['id'] ?></td>
			</tr>
			
			<tr>
				<th class="sub"><?php __('Login') ?></th>
				<td><?php echo $user['User']['login'] ?></td>
			</tr>
			
			<tr>
				<th class="sub"><?php __('First name') ?></th>
				<td><?php echo $user['User']['first_name'] ?></td>
			</tr>
			
			<tr>
				<th class="sub"><?php __('Last name') ?></th>
				<td><?php echo $user['User']['last_name'] ?></td>
			</tr>
			
			<tr>
				<th class="sub"><?php __('Email') ?></th>
				<td><?php echo $user['User']['email'] ?></td>
			</tr>
		</tbody>
	</table>
</div>

<?php
if (!empty($user)){
	e('<h3>'.__('Group membership for user : ', true).$user['User']['login'].' '.$html->link('['.__('Edit', true).']', '/groups/index').'</h3>');
	
	if (!empty($user['Group'])){
		e('<ul>');
			foreach ($user['Group'] as $key => $value) {
				e('<li>'.$value['name'].' - '.$html->link('['.__('Edit', true).']','/groups/affectUsers/'.$value['id']).'</li>');
			}//foreach
		e('</ul>');
	}
}
?>