<hr>
<div class="pagination">
	<?php
	    if ($pagination->setPaging($paging)):
	    // $leftArrow = $html->image('nav/arrowleft.gif', Array('height' => 15));
	    // $rightArrow = $html->image('nav/arrowright.gif', Array('height' => 15));

		$leftArrow = "<strong>".LANG_PREVIOUS."</strong>";
	    $rightArrow = "<strong>".LANG_NEXT."</strong>";

	    $prev = $pagination->prevPage($leftArrow, false);
	    $prev = $prev?$prev:$leftArrow;
	    $next = $pagination->nextPage($rightArrow, false);
	    $next = $next?$next:$rightArrow;

	    $pages = $pagination->pageNumbers(' ');
	    
	    echo '<div class="pagin_nav">'.$prev.' '.$pages.' '.$next.'</div>';
	    echo '<div class="pagin_datas">' . $pagination->result('Results: ',' of ') . '&nbsp;&nbsp;&nbsp;&nbsp;'
	    	. LANG_NUMBERPERPAGE.' : ' . $pagination->resultsPerPage(NULL, ' ') . '</div>';
    endif;
	?>
</div>
