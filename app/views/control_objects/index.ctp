<div class="controlObjects">
	<table class="table1">
		<thead>
			<tr>
				<th colspan="5"><?php __('Control object list'); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php
		$th = array (
		            $paginator->sort(__('Id', true), 'id'),
		            $paginator->sort(__('Name', true), 'name'),
					$paginator->sort(__('Parent', true), 'parent_id'),
		            'Actions'
		); // Generate the pagination sort links
		
		echo $html->tableHeaders($th); // Create the table headers with sort links if desired

		foreach ($data as $output) {
			$tr = array (
				$output['ControlObject']['id'],
				$output['ControlObject']['name'],
				$output['ParentControlObject']['name'],
				$html->link(
					$html->image('b_search.png', array('alt' => __('Display', true), 'title' => __('Display', true), 'class' => 'action')),
					'/control_objects/view/' . $output['ControlObject']['id'],
					null, false, false) .
				$html->link(
					$html->image('b_edit.png', array('alt' => __('Update', true), 'title' => __('Update', true), 'class' => 'action')),
					'/control_objects/edit/' . $output['ControlObject']['id'],
					null, false, false) .
				$html->link(
					$html->image('b_drop.png', array('alt' => __('Delete', true), 'title' => __('Delete', true), 'class' => 'action')),
					'/control_objects/delete/' . $output['ControlObject']['id'],
					null,
					__('Please confirm the deletion of control object :', true) . $output['ControlObject']['name'] . ' ?',
					false)
			);
			echo $html->tableCells($tr, array('class' => 'altRow', 
											'onmouseover' => "this.className='trOver'", 
											'onmouseout' => "this.className='table1'", 
											'onclick' => "document.location='".$this->base."/control_objects/view/" . $output['ControlObject']['id']."';"), 
										array('class' => 'evenRow', 
											'onmouseover' => "this.className='trOver'", 
											'onmouseout' => "this.className='table1'", 
											'onclick' => "document.location='".$this->base."/control_objects/view/" . $output['ControlObject']['id']."';")
									);
			
		}
		?>
		</tbody>
	</table>

	<? echo $this->renderElement('pagination'); // Render the pagination element ?>
</div>



