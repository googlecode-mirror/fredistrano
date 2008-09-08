<div class="users">
	<table class="table1">
		<thead>
			<tr>
				<th colspan="7"><?php __('User list'); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php		
				$th = array (
				            $paginator->sort(__('Id', true), 'id'),
				            $paginator->sort(__('Login', true), 'login'),
							$paginator->sort(__('First name', true), 'first_name'),
							$paginator->sort(__('Last name', true), 'last_name'),
							$paginator->sort(__('Email', true), 'email'),
							// __('Groups', true),
				            // 'Actions'
				); 
				echo $html->tableHeaders($th); 
				
				foreach ($data as $output)
				{
				    $tr = array (
				        $output['User']['id'],
				        $output['User']['login'],
				        $output['User']['first_name'],
						$output['User']['last_name'],
						$output['User']['email'],
						// $html->link(
						// 							$html->image('b_search.png', array('alt' => 'Afficher', 'title' => 'Afficher', 'class' => 'action')),
						// 							'/users/view/' . $output['User']['id'],
						// 							null, false, false) .
						// 						$html->link(
						// 							$html->image('b_edit.png', array('alt' => 'Modifier', 'title' => 'Modifier', 'class' => 'action')),
						// 							'/users/edit/' . $output['User']['id'],
						// 							null, false, false) .
						// 						$html->link(
						// 							$html->image('b_drop.png', array('alt' => 'Supprimer', 'title' => 'Supprimer', 'class' => 'action')), 
						// 							'/users/delete/' . $output['User']['id'], 
						// 							null, 
						// 							__('Please confirm the deletion of user :', true) . $output['User']['login'] . ' ?',
						// 							false)
				    );

					echo $html->tableCells($tr, array('class' => 'altRow', 
													'onmouseover' => "this.className='trOver'", 
													'onmouseout' => "this.className='table1'", 
													'onclick' => "document.location='".$this->base."/users/view/" . $output['User']['id']."';"), 
												array('class' => 'evenRow', 
													'onmouseover' => "this.className='trOver'", 
													'onmouseout' => "this.className='table1'", 
													'onclick' => "document.location='".$this->base."/users/view/" . $output['User']['id']."';")
											);



				}
		?>
		</tbody>
	</table>
	<? echo $this->renderElement('pagination'); ?>
</div>
<?php
// debug($data);
?>