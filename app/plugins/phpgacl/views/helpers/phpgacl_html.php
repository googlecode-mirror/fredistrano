<?php
/**
 * Html Helper class file.
 *
 * Simplifies the construction of HTML elements.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage	sypad.plugins.phpgacl.views.helpers
 * @since			Sypad v 1.0
 */

loadHelper('html');

/**
 * Html Helper class for easy use of HTML widgets.
 *
 * HtmlHelper encloses all methods needed while working with HTML pages.
 *
 * @author		Mariano Iglesias
 * @package		sypad
 * @subpackage	sypad.plugins.phpgacl.views.helpers
 */
class PhpgaclHtmlHelper extends HtmlHelper 
{
	/**
	 * Get the tag for a checkbox which value should be appendable (ie: where the name ends with []).
	 * 
	 * @param string $fieldName	The field name that will hold each checkbox (it appends [] to it to make it an array)
	 * @param array $htmlAttributes	HTML attributes for the element.
	 * @param boolean $return	Wheter this method should return a value or output it.
	 * 
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 * 
	 * @access public
	 * @since 1.0
	 */
	function appendableCheckbox($fieldName, $htmlAttributes = array(), $return = false)
	{
		$fieldNameElements = explode('/', $fieldName);
		
		$html = sprintf($this->tags['checkbox'], $fieldNameElements[0], (isset($fieldNameElements[1]) ? $fieldNameElements[1] : $fieldNameElements[0]) . '[]', $this->_parseAttributes($htmlAttributes, null, '', ' '));
		$html = preg_replace('/name="([^"]*)\[\]\]/i', 'name="\\1][]', $html);

		return $this->output($html, $return);
	}
	
	/**
	 * Get a tree of checkboxes for a threaded list.
	 *
	 * @param string $field	Name attribute of the SELECT
	 * @param array $thread	The threaded array as return by Model->findAllThreaded()
	 * @param string $fieldChildren	The index where the children can be found
	 * @param string $fieldValue	The index where the value can be found
	 * @param string $fieldName	The index where the name can be found
	 * @param boolean $return	Whether this method should return a value
	 *
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 *
	 * @access public
	 * @since 1.0
	 */
	function generateThreadedCheckBoxes($field, &$thread, $fieldChildren = 'children', $fieldValue = 'value', $fieldName = 'name', $return = false)
	{
		$this->setFormTag($field);
		
		$html = $this->_threadedCheckboxElement($thread, $field, $fieldChildren, $fieldValue, $fieldName);
		
		return $this->output($html, $return);
	}
	
	/**
	 * Get a tree of links for a threaded list.
	 *
	 * @param array $model	Model returned by findAllThreaded()
	 * @param string $url	URL that will be generated (where the string ${VALUE} will be replaced with the value of the record)
	 * @param array $thread	The threaded array as return by Model->findAllThreaded()
	 * @param string $fieldChildren	The index where the children can be found
	 * @param string $fieldValue	The index where the value can be found
	 * @param string $fieldName	The index where the name can be found
	 * @param boolean $return	Whether this method should return a value
	 *
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 *
	 * @access public
	 * @since 1.0
	 */
	function generateThreadedLinks($model, $url, &$thread, $fieldChildren = 'children', $fieldValue = 'value', $fieldName = 'name', $return = false)
	{
		$html = $this->_threadedLinkElement($thread, $model, $url, $fieldChildren, $fieldValue, $fieldName);
		
		return $this->output($html, $return);
	}
	
	/**
	 * Get the SELECT tag for a threaded list.
	 *
	 * @param string $field	Name attribute of the SELECT
	 * @param array $thread	The threaded array as return by Model->findAllThreaded()
	 * @param mixed $selected	Selected option
	 * @param array $selectAttr	Array of HTML options for the opening SELECT element
	 * @param array $optionAttr	Array of HTML options for the enclosed OPTION elements
	 * @param boolean	$showEmpty If true, the empty select option is shown
	 * @param string $fieldChildren	The index where the children can be found
	 * @param string $fieldValue	The index where the value can be found
	 * @param string $fieldName	The index where the name can be found
	 * @param boolean $return	Whether this method should return a value
	 *
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 *
	 * @access public
	 * @since 1.0
	 */
	function generateThreadedList($field, &$thread, $selected = null, $selectAttr = array(), $optionAttr = null, $showEmpty = true, $fieldChildren = 'children', $fieldValue = 'id', $fieldName = 'name', $return = false)
	{
		list($model) = explode('/', $field);
		
		$options = $this->_threadedListElement($thread, $model, $fieldChildren, $fieldValue, $fieldName);
		
		// Remove escaped spaces
		
		$html = $this->selectTag($field, $options, $selected, $selectAttr, $optionAttr, $showEmpty, true);
		
		$html = str_replace('&amp;nbsp', '&nbsp', $html);
		
		return $this->output($html, $return);
	}
	
	/**
	 * Get the CHECKBOX tag for a threaded list.
	 *
	 * @param array $thread	The threaded array as return by Model->findAllThreaded()
	 * @param string $field	The field name
	 * @param string $fieldChildren	The index where the children can be found
	 * @param string $fieldValue	The index where the value can be found
	 * @param string $fieldName	The index where the name can be found
	 * @param int $level	Depth level
	 *
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _threadedCheckboxElement(&$thread, $field, $fieldChildren = 'children', $fieldValue = 'id', $fieldName = 'name', $level = 0)
	{
		$html = '';
		
		foreach ($thread as $key => $element) 
		{
			$htmlAttributes = array();
			
			$htmlAttributes['class'] = 'checkbox';
			$htmlAttributes['value'] = $element[$this->model][$fieldValue];
			
			if (!empty($this->data) && isset($this->data[$this->model]) && isset($this->data[$this->model][$this->field]) && in_array($element[$this->model][$fieldValue], $this->data[$this->model][$this->field]))
			{
				$htmlAttributes['checked'] = true;
			}
			
			$html .= '<div class="phpgacl-checkbox-element">' . "\n";
			$html .= $this->appendableCheckbox($field, $htmlAttributes, true) . ' ' . $element[$this->model][$fieldName] . "\n";
			$html .= '</div>' . "\n";
			
			if (count($element['children']) > 0)
			{
				$html .= '<div class="phpgacl-checkbox-element-indent">' . "\n";
				$html .= $this->_threadedCheckboxElement($element['children'], $field, $fieldChildren, $fieldValue, $fieldName, $level + 1);
				$html .= '</div>' . "\n";
			}
		}
		
		return $html;
	}
	
	/**
	 * Get the HREF tag for a threaded list.
	 *
	 * @param array $thread	The threaded array as return by Model->findAllThreaded()
	 * @param array $model	Model returned by findAllThreaded()
	 * @param string $url	URL that will be generated (where the string ${VALUE} will be replaced with the value of the record)
	 * @param string $fieldChildren	The index where the children can be found
	 * @param string $fieldValue	The index where the value can be found
	 * @param string $fieldName	The index where the name can be found
	 * @param int $level	Depth level
	 *
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _threadedLinkElement(&$thread, $model, $url, $fieldChildren = 'children', $fieldValue = 'id', $fieldName = 'name', $level = 0)
	{
		$html = '';
		
		$html .= '<ul>' . "\n";
		
		foreach ($thread as $key => $element) 
		{
			$destinationUri = str_replace('{$VALUE}', $element[$model][$fieldValue], $url);
			
			$html .= '<li>' . $this->link($element[$model][$fieldName], $destinationUri);
			
			if (count($element['children']) > 0)
			{
				$html .= $this->_threadedLinkElement($element['children'], $model, $url, $fieldChildren, $fieldValue, $fieldName, $level + 1);
			}
			
			$html .= '</li>' . "\n";
		}
		
		$html .= '</ul>' . "\n";
		
		return $html;
	}
	
	/**
	 * Get the SELECT tag for a threaded list.
	 *
	 * @param array $thread	The threaded array as return by Model->findAllThreaded()
	 * @param string $model	The name of the model
	 * @param string $fieldChildren	The index where the children can be found
	 * @param string $fieldValue	The index where the value can be found
	 * @param string $fieldName	The index where the name can be found
	 * @param int $level	Depth level
	 * @param string $space	Space character to use
	 *
	 * @return mixed	Either string or boolean value, depends on AUTO_OUTPUT and $return.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _threadedListElement(&$thread, $model, $fieldChildren = 'children', $fieldValue = 'id', $fieldName = 'name', $level = 0, $space = '&nbsp;') 
	{
		$elements = array();
		
		foreach ($thread as $key => $element) 
		{
			$elements['' . $element[$model][$fieldValue]] = str_repeat($space, $level * 3) . $element[$model][$fieldName];
			
			if (count($element['children']) > 0)
			{
				$children = $this->_threadedListElement($element['children'], $model, $fieldChildren, $fieldValue, $fieldName, $level + 1);
				
				// Can't use am() since it rearranges numeric indexes
				
				foreach($children as $childrenKey => $childrenElement)
				{
					$elements[$childrenKey] = $childrenElement;
				}
			}
		}
		
		return $elements; 
	}
}

?>