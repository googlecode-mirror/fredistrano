<?php
/* SVN FILE: $Id$ */
/**
 * Deprecated? Dans les pages "index", les données sont présentées sous forme de tableau
 * la dernière colonne de ce tableau contient des icônes d'actions
 * on ne veut pas de cette colonne à l'impression donc on lui attribue une classe spéciale
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.views.helpers
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Deprecated? Dans les pages "index", les données sont présentées sous forme de tableau
 * la dernière colonne de ce tableau contient des icônes d'actions
 * on ne veut pas de cette colonne à l'impression donc on lui attribue une classe spéciale
 *
 * @package		app
 * @subpackage	app.views.helpers
 */
class TableHelper extends Helper {

	var $helpers = array('Html');

	/**
	 * Returns a row of formatted and named TABLE headers.
	 *
	 * @param array $names		Array of tablenames.
	 * @param array $trOptions	HTML options for TR elements.
	 * @param array $thOptions	HTML options for TH elements.
	 * @param  boolean $return	Wheter this method should return a value
	 * @return string
	 * @access public
	 */
	function tableHeaders($names, $trOptions = null, $thOptions = null, $return = false) {
		$out = array();
		for ($i = 0; $i < count($names); $i++) {
			if ($i == count($names) - 1)
				$out[] = sprintf($this->tags['tableheader'], $this->Html->parseHtmlOptions($thOptions . 'class="aPasImprimer"'), $names[$i]);
			else 
				$out[] = sprintf($this->tags['tableheader'], $this->Html->parseHtmlOptions($thOptions), $names[$i]);
		}

		$data = sprintf($this->tags['tablerow'], $this->Html->parseHtmlOptions($trOptions), join(' ', $out));
		return $this->output($data, $return);
	}

	/**
	 * Returns a formatted string of table rows (TR's with TD's in them).
	 *
	 * @param array $data		Array of table data
	 * @param array $oddTrOptionsHTML options for odd TR elements
	 * @param array $evenTrOptionsHTML options for even TR elements
	 * @param  boolean $return	Wheter this method should return a value
	 * @return string	Formatted HTML
	 * @access public
	 */
	function tableCells($data, $oddTrOptions = null, $evenTrOptions = null, $return = false) {
		if (empty($data[0]) || !is_array($data[0])) {
			$data = array($data);
		}
		static $count = 0;

		foreach ($data as $line) {
			$count++;
			$cellsOut = array();

			for ($i = 0; $i < count($line); $i++) {
				if ($i == count($line) - 1)
					$cellsOut[] = sprintf($this->tags['tablecell'], $this->Html->parseHtmlOptions('class="aPasImprimer"'), $line[$i]);
				else
					$cellsOut[] = sprintf($this->tags['tablecell'], null, $line[$i]);
			}
			$options = $this->Html->parseHtmlOptions($count % 2 ? $oddTrOptions : $evenTrOptions);
			$out[] = sprintf($this->tags['tablerow'], $options, join(' ', $cellsOut));
		}
		return $this->output(join("\n", $out), $return);
	}

}
?>
