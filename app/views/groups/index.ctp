<div class="groups">
	<table class="table1">
		<thead>
			<tr>
				<th colspan="7"><?php __('Group list'); ?></th>
			</tr>
		</thead>
		
		<tbody>
		<?php		
				$th = array (
				            $paginator->sort(__('Id', true), 'id'),
				            $paginator->sort(__('Name', true), 'name'),
							$paginator->sort(__('Parent', true), 'parent_id'),
				            // 'Actions'
				); // Generate the pagination sort links
				echo $html->tableHeaders($th); // Create the table headers with sort links if desired
				
				foreach ($data as $output)
				{
				    $tr = array (
				        $output['Group']['id'],
				        $output['Group']['name'],
				        $output['ParentGroup']['name'],
						// $html->link(
						// 	$html->image('b_search.png', array('alt' => 'Afficher', 'title' => 'Afficher', 'class' => 'action')),
						// 	'/groups/view/' . $output['Group']['id'],
						// 	null, false, false) .
						// $html->link(
						// 	$html->image('b_edit.png', array('alt' => 'Modifier', 'title' => 'Modifier', 'class' => 'action')),
						// 	'/groups/edit/' . $output['Group']['id'],
						// 	null, false, false) .
						// $html->link(
						// 	$html->image('b_drop.png', array('alt' => 'Supprimer', 'title' => 'Supprimer', 'class' => 'action')), 
						// 	'/groups/delete/' . $output['Group']['id'], 
						// 	null, 
						// 	__('Please confirm the deletion of group :', true) . $output['Group']['name'] . ' ?',
						// 	false)
				    );
				    //echo $html->tableCells($tr,array('class'=>'altRow'),array('class'=>'evenRow'));
				
					echo $html->tableCells($tr, array('class' => 'altRow', 
													'onmouseover' => "this.className='trOver'", 
													'onmouseout' => "this.className='table1'", 
													'onclick' => "document.location='".$this->base."/groups/view/" . $output['Group']['id']."';"), 
												array('class' => 'evenRow', 
													'onmouseover' => "this.className='trOver'", 
													'onmouseout' => "this.className='table1'", 
													'onclick' => "document.location='".$this->base."/groups/view/" . $output['Group']['id']."';")
											);
				
				
				
				}
		?>
		</tbody>
	</table>
	<? echo $this->renderElement('pagination'); ?>
</div>

