<div class="user">
	<table class="table1">
		<thead>
			<tr>
				<th colspan="2">Détails d'un utilisateur</th>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<th colspan="2">
					<div class="tabletoplink">
						<ul>
							<li>
								<?php echo $html->link(
									$html->image('b_edit.png', array('alt' => 'Modifier', 'title' => 'Modifier', 'class' => 'action')) . 'Modifier',
									'/users/edit/' . $user['User']['id'], 
									null, false, false) ?>
							</li>
							<li>
								<?php echo $html->link(
									$html->image('key.png', array('alt' => 'Changer le mot de passe', 'title' => 'Changer le mot de passe', 'class' => 'action')) . 'Changer le mot de passe',
									'/users/change_password/' . $user['User']['id'],
									null, false, false) ?>
							</li>
							<li>
								<?php echo $html->link(
									$html->image('b_drop.png', array('alt' => 'Supprimer', 'title' => 'Supprimer', 'class' => 'action')) . 'Supprimer', 
									'/users/delete/' . $user['User']['id'], 
									null, 
									'Etes vous certain de vouloir supprimer : ' . $user['User']['login'] . ' ?',
									false) ?>
							</li>
						</ul>
					</div>
				</th>
			</tr>
			
			<tr>
				<th class="sub">Id</th>
				<td><?php echo $user['User']['id'] ?></td>
			</tr>
			
			<tr>
				<th class="sub">Login</th>
				<td><?php echo $user['User']['login'] ?></td>
			</tr>
			
			<tr>
				<th class="sub">Prénom</th>
				<td><?php echo $user['User']['first_name'] ?></td>
			</tr>
			
			<tr>
				<th class="sub">Nom</th>
				<td><?php echo $user['User']['last_name'] ?></td>
			</tr>
			
			<tr>
				<th class="sub">Email</th>
				<td><?php echo $user['User']['email'] ?></td>
			</tr>
			
			<tr>
				<th class="sub">Groupes associés</th>
				<td><?php echo $user['User']['groups'] ?></td>
			</tr>
		</tbody>
	</table>
</div>
