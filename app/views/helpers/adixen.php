<?php
class AdixenHelper extends Helper {

	var $helpers = Array (
		"Html",
		"Ajax",
		"Javascript"
	);

	/**
	 * @author FBOLLON
	 *
	 * Retourne la valeur d'un paramètre en fonction de la catégorie recherchée
	 *
	 * @param array $array Tableau de paramètre
	 * @param string $category Catégorie du paramètre recherché
	 * @param string $data Valeur à retourner
	 * @return string Valeur d'un paramètre
	 */
	function getParameterValue($array, $category, $data = 'name') {

		for ($i = 0; $i < sizeof($array); $i++) {
			if (!is_array($array[$i]))
			return false;

			foreach ($array[$i] as $key => $value) {
				if ($value == $category) {
					return $array[$i][$data];
				}
			}
		}
		return false;
	}

	/**
	 * @author FBOLLON
	 *
	 * Affiche les auditeurs assistants
	 *
	 * @param array $array Tableau de paramètre
	 * @return string Liste des auditeurs assistants
	 */
	function displayAssistantAuditors($array, $adixenPerson) {
		if (!is_array($array) && empty ($array))
		return false;

		$strAssistantAuditors = '';
		foreach ($array as $value) {
			$strAssistantAuditors .= $this->displayNameFromLdap(strtoupper($value['cn']), $adixenPerson) . ", ";

		}

		return substr($strAssistantAuditors, 0, -2);
	}

	/**
	 * @author FBOLLON
	 *
	 * Affiche les fichiers attachés en fonction de leur type
	 *
	 * @param array $array Tableau de paramètre
	 * @return string Une chaîne html
	 */
	function displayAttachedFilesByType($files) {

		$string = '';
		$tmpFiles = array ();

		for ($i = 0; $i < sizeof($files); $i++) {
			$tmpFiles[$files[$i]['Parameter'][0]['name']][] = $files[$i]['AttachedFile'];
		}

		asort($tmpFiles);

		foreach ($tmpFiles as $key => $value) {

			$string .= "<h4>" . $key . "</h4>\n";
			$string .= "<ul>";
			for ($j = 0; $j < sizeof($value); $j++) {
				$string .= "<li>&nbsp;" . $this->Html->link($value[$j]['name'], $value[$j]['url'], array('target' => '_blank')) . " &nbsp;".$this->Html->link($this->Html->image('b_drop.png', array (
				'alt' => 'Supprimer',
				'title' => 'Supprimer'
				)), 'deleteFile/' . $value[$j]['id'], null, 'Etes-vous certain de vouloir supprimer le fichier : ' . $value[$j]['name'] . '?', false)."</li>\n";
			}
			$string .= "</ul>";

		}
		return $string;
	}

	/**
	 * @author FBOLLON
	 *
	 * Affiche un lien vers l'historique des relances pour la tache
	 *
	 * @param integer $card_id
	 * @param integer $taskStatus
	 * @return string html
	 */
	function displayDunningHistoryLink($card_id, $taskStatus, $taskNumber) {
		if ($taskStatus == 1) {
			return $this->Html->link($this->Html->image('calendar.png', array (
			'alt' => 'Historique des relances',
			'title' => 'Historique des relances'
			)), '/dunnings/dunningHistory/' . $card_id . '&taskNumber=' . $taskNumber, null, null, false);
		} else {
			return null;
		}
	}

	/**
	 * @author FBOLLON
	 *
	 * Affiche une coche verte
	 *
	 * @param bool $checked
	 * @return string html
	 */
	function displayCheckedImg($checked = null, $date = null, $id = null, $visa = null, $validator = null, $tasksStatus = null) {

		$string = null;

		if ($checked == true) {
			$string = "&nbsp;" . $this->Html->image('checked.png', array (
			'alt' => 'Validé',
			'title' => 'Validé'
			));

			if ($date != null) {
				$string .= "<i>&nbsp;le&nbsp;" . $date . "</i>";
			}
		} else {

			// si l'utilisateur connecté est le validateur on rajoute un lien
			$user = $_SESSION['User'];
			if ((isset ($user['User']['cn'])) && (strtoupper($user['User']['cn']) == strtoupper($validator))) {

				$linK4SimpleVisa = $string = $this->Html->link('à valider', '/cards/addvisa/?id=' .
				$id . '&visa=' . $visa, null, 'Etes-vous certain de vouloir viser la fiche ?', false, false);

				$string = null;
				switch ($visa) {

					case 'ar_visa' :
						if (isset ($tasksStatus[1]['status']) && $tasksStatus[1]['status'] == 0)
						return $string = $linK4SimpleVisa;
						break;

					case 'pp_visa' :
						if (isset ($tasksStatus[2]['status']) && $tasksStatus[2]['status'] == 0)
						return $string = $linK4SimpleVisa;
						break;

					case 'qr_visa' :
						if (isset ($tasksStatus[3]['status']) && $tasksStatus[3]['status'] == 0)
						return $string = $linK4SimpleVisa;
						break;

					case 'cov_visa' :
						if (isset ($tasksStatus[4]['status']) && $tasksStatus[4]['status'] == 0)
						$string = $this->Ajax->link("à valider", "/cards/addvisacov/{$id}&visa={$visa}", array (
							"update" => "form_cov_visa",
							"loading" => "Element.show('loading_cov');",
							"complete" => "Element.hide('loading_cov');"
							), "Etes-vous certain de vouloir viser la fiche ?");
							return $string;
							break;

					case 'caev_visa' :
						if (isset ($tasksStatus[5]['status']) && $tasksStatus[5]['status'] == 0)
						$string = $this->Ajax->link("à valider", "/cards/addvisacaev/{$id}&visa={$visa}", array (
							"update" => "form_caev_visa",
							"loading" => "Element.show('loading_caev');",
							"complete" => "Element.hide('loading_caev');"
							), "Etes-vous certain de vouloir viser la fiche ?");
							return $string;
							break;

					default :
						return null;
						break;
				}

			} else {
				$string = null;
			}
		}
		return $string;

	}

	/**
	 * @author FBOLLON
	 *
	 * Affiche affiche le displayname d'une personne
	 *
	 * @param string $cn
	 * @param array $adixenPerson
	 * 				array( 'cn'=>'displayname' ) généré par $this->User->generateList(null, null, null, '{n}.User.cn', '{n}.User.displayname'));
	 * @return string displayname
	 */
	function displayNameFromLdap($cn, $adixenPerson) {
		if (empty($cn))
		return false;

		$tmp = explode(' ', trim(strtoupper($cn)));

		$return = '';
		foreach ($tmp as $value) {
			if (!empty ($adixenPerson[$value])) {
				$return .= $adixenPerson[$value].', ';
			} else {
				$return .= $value.', ';
			}
		}

		$return = substr($return,0,-2);

		return $return;
	}

	/**
	 * @author FBOLLON
	 *
	 * Affiche le niveau d'efficacité VEAC
	 *
	 * @param integer $level
	 * @return string niveau d'efficacité VEAC
	 */
	function displayCaevEfficiency($level) {
		switch ($level) {
			case 1 :
				return 'Oui';
				break;
			case 2 :
				return 'Moyennement';
				break;
			case 3 :
				return 'Non';
				break;

			default :
				return false;
				break;
		}
	}

	/**
	 * @author FBOLLON
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

	function displaySearchFilters($filters, $parameters, $controller) {

		// commun
		$str = '';
		if (isset($parameters)) {
			foreach ($filters['Parameter'] as $key => $value) {
				foreach ($parameters as $param) {
					if ($param['Parameter']['id'] == $value) {
						$str .= $key . ' = <strong>' . $param['Parameter']['name'] . '</strong><br />';
					}
				}
			}
		}

		// recherche d'audit
		if ($controller == 'Audits') {
			if(!empty($filters['Date']['planned_year']))
			$str .= 'Année (date prévue) = <strong>' . $filters['Date']['planned_year'] . '</strong><br />';
				
			if(!empty($filters['Audit']['main_auditor_id']) && !empty($filters['auditors'][$filters['Audit']['main_auditor_id']]))
			$str .= 'Auditeur principal = <strong>' . $filters['auditors'][$filters['Audit']['main_auditor_id']] .'</strong><br />';
		}


		// recherche de fiche
		if ($controller == 'Cards') {
 		if(!empty($filters['Date']['action_delay_year']))
			$str .= 'Année (délai action) = <strong>' . $filters['Date']['action_delay_year'] .'</strong><br />';

			if(!empty($filters['Card']['audit_id']))
			$str .= 'Audit = <strong>' . $filters['Card']['audit_id'] .'</strong><br />';

			if(!empty($filters['Card']['action_responsible']))
			$str .= 'Responsable de l\'action = <strong>' . $filters['Card']['action_responsible'] .'</strong><br />';

			if(!empty($filters['Audit']['main_auditor_id']) && !empty($filters['auditors'][$filters['Audit']['main_auditor_id']]))
			$str .= 'Responsable VMEP = <strong>' . $filters['auditors'][$filters['Audit']['main_auditor_id']] .'</strong><br />';
		}

		return $str;
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


	/**
	 * Retourne la date la plus élevée d'audit pour un auditeur.
	 *
	 * @param Array
	 *
	 * @return string
	 * @access public
	 */
	function getMaxAuditDate($aryAudits){

		foreach ($aryAudits as $key => $value){
			$tmpDate[] = strtotime($value['audit_date_time']);
		}


		if (!empty($tmpDate)) {
			rsort($tmpDate);
			return date  ( 'Y-m-d', $tmpDate[0]);
		}else{
			return null;
		}
	}

	function helpButton ($divId){
		return "<a href=\"#\" onclick=\"Element.toggle('$divId'); return false;\">".$this->Html->image('help.png', array (
			'alt' => 'Afficher/masquer l\'aide',
			'title' => 'Afficher/masquer l\'aide'
			))."</a>";
	}

	function displayHelp ($divId, $helpStr){
		return "<p class=\"error_message\" id=\"".$divId."\" style=\"display:none\">".$helpStr."</p>";
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

	function fromString($date_string) {
		if (is_integer($date_string) || is_numeric($date_string)) {
			return intval($date_string);
		} else {
			return strtotime($date_string);
		}
	}

	function truncateString($string = null, $max = 20){
		if ($string === null)
			return null;
			
		if (strlen($string) > $max){
			$string = "<span title=\"".addslashes($string)."\">".substr($string, 0, $max)."[...]</span>";
		}
		
		return ltrim($string);	
	}
}
?>