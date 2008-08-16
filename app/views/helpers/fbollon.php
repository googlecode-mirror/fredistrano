<?php
class FbollonHelper extends Helper {

	var $helpers = Array (
		"Html",
		"Ajax",
		"Javascript"
	);


	/**
	 *
	 * Affiche oui/non
	 *
	 * @param integer $value
	 * @return string niveau d'efficacité VEAC
	 */
	function displayOuiNon($value) {
		switch ($value) {
			case 1 :
				return 'Oui';
				break;

			default :
				return 'Non';
				break;
		}
	}



	/**
	 * Returns a DATE elements.
	 *
	 * @param string $tagName Prefix name for the SELECT element
	 * @param string $selected Option which is selected.
	 * @param string $withTime bool Option to display a time field.
	 * @param string $showEmpty bool
	 * @return string The HTML formatted OPTION element
	 * @access public
	 */
	function enhancedDateTimeOptionTag($tagName, $selected = null, $withTime = false, $showEmpty = false) {

		list($model, $field) = explode("/", $tagName);

		if (empty($selected)) {
			$selected = $this->Html->tagValue($tagName);
		}

		if (!empty($selected)) {
			$tmp = explode(' ', $selected);
			$date = $tmp[0];
			if ($withTime)
			$time = $tmp[1];
		} else {
		 $date = $showEmpty?'':date('Y-m-d');
		 $time = $showEmpty?'':date('H:i:s');
		}

		$res = '<input name="data['.$model.']['.$field.'_date]" size="10" class="f-name" onfocus="this.select();lcs(this)" onclick="event.cancelBubble=true;this.select();lcs(this)" value="'.$date.'" id="AuditAuditDateTime" type="text">';

		if ($withTime) {
			$res .= ' <input name="data['.$model.']['.$field.'_time]" size="8" class="f-name" value="'.$time.'" id="AuditAuditDateTime" type="text">(hh:mm:ss)';
		}

		return $res;
	}

	/**
	 * Retourne un élément HTML d'initialisation du javascript de multi-completion .
	 *
	 * @param Array $obsField
	 * 		['sugDiv'] = l'id du div ou sera affiché la liste des valeurs
	 * 		['type'] = le nom du tableau de données généré par /jcomplete/typeLoader/get? qui alimentera la liste
	 * 		['max'](optionnel) = le nom maximum de choix
	 *
	 * @return string The HTML code
	 * @access public
	 */
	function init_multi_completion($obsField){
		$str = $this->Javascript->link('delicious_multi-completion.js');

		$str .= "\n".'<script language="JavaScript" type="text/javascript">';
		$str .= "\n".'onloads.push( "init_completion( [';

		$tmp = array();
		$list = array();
		foreach ($obsField as $key => $value ){
			$max = (!empty($value['max']))?",'max':".$value['max']:'';
			$tmp[] = "{'obsField':'".$key."', 'sugDiv':'".$value['sugDiv']."','type':'".$value['type']."'".$max."}";
			$list[] = $value['type'];
		}

		$str .= implode(',', $tmp). '] )" );';
		$str .= "\n</script>";
		$str .= "\n".'<script src="'.$this->base.'/jcomplete/typeLoader/get?'.implode('&amp;',array_unique($list)).'"></script>';

		return $str;
	}


	function helpButton ($divId){
		return "<a href=\"#\" onclick=\"Element.toggle('$divId'); return false;\">".$this->Html->image('help.png', array (
			'alt' => 'Afficher/masquer l\'aide',
			'title' => 'Afficher/masquer l\'aide',
			'style' => 'float:right;margin:15px 0px'
			))."</a>";
	}

	function displayHelp ($divId, $helpStr){
		return "<p class=\"error_message\" id=\"".$divId."\" style=\"display:none;float:right;margin:15px 0px\">".$helpStr."</p>";
	}

	
	function nice($date_string = null, $short = false, $return = false) {
		if ($date_string != null) {
			$date = $this->fromString($date_string);
			if ($short)
				$ret = date("D, M jS Y", $date);
				else
				$ret = date("D, M jS Y, H:i", $date);
		} else {
			$ret = null;
		}

		return $this->output($ret, $return);
	}


	function truncateString($string = null, $max = 20){
		if ($string === null)
			return null;
			
		if (strlen($string) > $max){
			$string = "<span title=\"".addslashes($string)."\">".substr($string, 0, $max)."[...]</span>";
		}
		
		return ltrim($string);	
	}
	
	function logsLinks($string, $projectId){
		$ret = '';
		$aryLinks = explode("\n", $string);
		if (!empty($aryLinks[0])) {
			foreach ($aryLinks as $key => $value) {
				$ret .= trim($value).'&nbsp;'.$this->Html->link($this->Html->image('edit-find.png') , 
							'/logs/index/'.$projectId.'/'.$key, 
							null,
							false,
							false) . "<br />";
			}
		}
		return substr($ret, 0, -6);
	}
	
	
}
?>