<?php
/* SVN FILE: $Id$ */
/**
 * 
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.views.control_objects
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.control_objects
 */
 ?>
 <div class="controlObject">
	<table class="table1">
		<thead>
			<tr>
				<th colspan="2"><?php __('Object control detail') ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th colspan="2">
					<div class="tabletoplink">
						<ul>
							<li>
								<?php echo $html->link(
									$html->image('b_edit.png', array('alt' => __('Update', true), 'title' => __('Update', true), 'class' => 'action')) . __('Update', true),
									'/control_objects/edit/' . $controlObject['ControlObject']['id'],
									null, false, false) ?>
							</li>
							<li>
								<?php echo $html->link(
									$html->image('b_drop.png', array('alt' => __('Delete', true), 'title' => __('Delete', true), 'class' => 'action')) . __('Delete', true),
									'/control_objects/delete/' . $controlObject['ControlObject']['id'],
									null,
									__('Please confirm the deletion of control object :', true) . $controlObject['ControlObject']['name'] . ' ?',
									false) ?>
							</li>
						</ul>
					</div>
				</th>
			</tr>

			<tr>
				<th class="sub">Id</th>
				<td><?php echo $controlObject['ControlObject']['id']?></td>
			</tr>
			<tr>
				<th class="sub">Nom</th>
				<td><?php echo $controlObject['ControlObject']['name']?></td>
			</tr>
			<tr>
				<th class="sub">Parent</th>
				<td><?php echo $html->link($controlObject['ParentControlObject']['name'], '/control_objects/view/' . $controlObject['ParentControlObject']['id'], null, false, false) ?></td>
			</tr>
	</tbody>
	</table>
</div>
