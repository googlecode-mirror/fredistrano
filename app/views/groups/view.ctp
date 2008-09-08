<div class="group">
<table class="table1">
	<thead>
		<tr>
			<th colspan="3"><?php __('Group details') ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<th colspan="2">
			<div class="tabletoplink">
			<ul>
				<li><?php echo $html->link(
										$html->image('b_edit.png', 
													array('alt' => __('Edit', true), 
														'title' => __('Edit', true), 
														'class' => 'action')) . __('Edit', true),
										'/groups/edit/' . $group['Group']['id'], 
										null, false, false) ?>
				</li>
				<li><?php echo $html->link(
										$html->image('b_drop.png', 
													array('alt' => __('Delete', true), 
														'title' => __('Delete', true), 
														'class' => 'action')) . __('Delete', true),
										'/groups/delete/' . $group['Group']['id'], 
										null,
										__('Please confirm the deletion of group :', true) . $group['Group']['name'] . ' ?',
										false) ?>
				</li>
			</ul>
			</div>
			</th>
		</tr>

		<tr>
			<th class="sub"><?php __('Id') ?></th>
			<td><?php echo $group['Group']['id'] ?></td>
		</tr>

		<tr>
			<th class="sub"><?php __('Name') ?></th>
			<td><?php echo $group['Group']['name'] ?></td>
		</tr>

		<tr>
			<th class="sub"><?php __('Parent') ?></th>
			<td><?php echo $html->link($group['ParentGroup']['name'], 
										'/groups/view/' . $group['ParentGroup']['id'], 
										null, false, false) ?>
			</td>
		</tr>
	</tbody>
</table>





<!-- Liste des utilisateurs associés au groupe -->

<h2><?php __('Group members') ?></h2>

<table class="table1">
	<thead>
		<tr>
			<th colspan="7"><?php __('Group members list') ?></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<th colspan="2">
			<div class="tabletoplink">
			<ul>
				<li><?php echo $html->link(
									$html->image('b_edit.png', 
										array('alt' => __('Edit', true), 
											'title' => __('Edit', true), 
											'class' => 'action')
											) . __('Manage group members', true),
									'/groups/affectUsers/' . $group['Group']['id'], 
									null, false, false) ?>
				</li>
			</ul>
			</div>
			</th>
		</tr>
		<?php
		if (isset($group['User'])) {
			
			foreach ($group['User'] as $value) {
				$tr = array (
					$html->link($value['login'],
					'/users/view/'.$value['id']),
					$value['login']
					);
				echo $html->tableCells($tr,array('class'=>'altRow'),array('class'=>'evenRow'));
			}
		}
		?>
	</tbody>
</table>
</div>
