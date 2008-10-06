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
 * @subpackage		app.views.projects
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.projects
 */
 ?>
 <h1><?php __('Deployable projects') ?></h1>

<p><?php __('The list displays all the web projects configured in Fredistrano. Click on a project to deploy or manage it.') ?></p>

<div class="projects">
<table class="table1">
		<thead>
			<tr>
				<th colspan="7"><?php __('Project list');?></th>
			</tr>
		</thead>

<?php
//$pagination->setPaging($paging); // Initialize the pagination variables
$th = array (
            $paginator->sort(__('Project name', true),'name'),
            $paginator->sort(__('SVN Url', true), 'svn_url'),
            $paginator->sort(__('Target', true), 'prd_path')
); // Generate the pagination sort links
echo $html->tableHeaders($th); // Create the table headers with sort links if desired

foreach ($data as $output)
{
    $tr = array (
        $output['Project']['name'],
        $fbollon->truncateString( $output['Project']['svn_url'], 30 ),
        //$output['Project']['prd_url'],
        $fbollon->truncateString($output['Project']['prd_path'], 30 ) 
    );

    echo $html->tableCells($tr, array('class' => 'altRow', 'onmouseover' => "this.className='trOver'", 'onmouseout' => "this.className='table1'", 'onclick' => "document.location='".$this->base."/projects/view/".$output['Project']['id']."';"), array('class' => 'evenRow', 'onmouseover' => "this.className='trOver'", 'onmouseout' => "this.className='table1'", 'onclick' => "document.location='".$this->base."/projects/view/".$output['Project']['id']."';"));
}
?>
</table>
<? //echo $this->renderElement('pagination'); // Render the pagination element ?>
<?php echo $paginator->prev(); ?>
<?php echo $paginator->numbers(); ?>
<?php echo $paginator->next(); ?> 
</div>

<br /><br /><br />
