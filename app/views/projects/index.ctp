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
            $paginator->sort('name',__('Project name', true)),
            $paginator->sort('svn_url', __('SVN Url', true)),
            $paginator->sort('prd_path', __('Target', true))
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