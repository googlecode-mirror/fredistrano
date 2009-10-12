<?php
/* SVN FILE: $Id$ */
/**
 * YUI helper designed by fbollon
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
 * Custom helper designed by fbollon
 *
 * @package		app
 * @subpackage	app.views.helpers
 */
uses('view/helpers/Form');
class YuiHelper extends FormHelper {
	
	/**
	 * solve issue of z-index under IE
	 *
	 */
	private static $zindex = 9000;
	
	/**
	 * autocomplete field 
	 *
	 * @param string $fieldname 	'User.field 
	 * @param string $DSUrl 		Data source url array('controller' => 'users', 'action' => 'FilialeList')
	 * @param string $options 
	 *
	 *	Example :
	 *		echo $yui->autoComplete(
	 *			'User.filialemngt', 
	 *			array(
	 *				'url' 		=> '/users/FilialeList',
	 *				'fields' 	=> array('id', 'name'),
	 *				''name'		=> 'YAHOO.fbc.projects'
	 *				), 
	 *			array(
	 *				'label'		=> __('Subsidiary management', true), 
	 *				'error' 	=> __("The hierarchical manager is required", true),
	 *				'selected' 	=> $this->data['User']['filialemngt'],
	 *				'multiple'	=> false,
	 *				'redirectTo' => $this->base."/projects/view/",
	 *				)
	 *			);	 
	 * @return string
	 * @author Frédéric BOLLON
	 */
	function autoComplete($fieldname, $datasource, $options=null) {
		?>
		<style type='text/css'>   	 	 
		.yui-skin-sam .yui-ac-container { width:20em;left:0px;}
		.yui-ac .yui-button {vertical-align:middle;}
		.yui-ac .yui-button button {background: url(http://developer.yahoo.com/yui/examples/autocomplete/assets/img/ac-arrow-rt.png) center center no-repeat }
		.yui-ac .open .yui-button button {background: url(http://developer.yahoo.com/yui/examples/autocomplete/assets/img/ac-arrow-dn.png) center center no-repeat}
		</style>
		<?php
		
		$tmp = explode('.',$fieldname);
		$model = $tmp[0];
		$field = $tmp[1];
		$DSUrl = $datasource['url'];
		$DStype = (isset($datasource['type']))?$datasource['type']:'local';
		
		$divId = 'div'.$field;
		$inputId = $divId.'input';
		$requireHidden = (count($datasource['fields']) > 1);
		if ($requireHidden) {
			$hiddenId = $divId.'inputHidden';
		}
		$containerId = $divId.'inputContainer';
		$selected = (isset($options['selected']))?$options['selected']:null;
		$label = (isset($options['label']))?$options['label']:$field;
		$error = (isset($options['error']))?$options['error']:false;
		?>
		<div class="yui-skin-sam">
			<label for="<?php e($inputId) ?>"><?php e($label) ?></label>
			<div id="<?php e($divId) ?>" style="width:21em;padding-bottom:2em;z-index:<?php e(self::$zindex) ?>">
				
				<?php self::$zindex--;?>
				
				<?php 
				if ($requireHidden) {
					e("<input id='$inputId' type='text' value='$selected' style=' position:static;width:20em; vertical-align:middle;'/>");
				} else {
					e("<input id='$inputId' type='text' value='$selected' name='data[$model][$field]' style=' position:static;width:20em; vertical-align:middle;'/>");
				}
				?>
				<span id='toggleB'></span>
				<div id="<?php e($containerId) ?>"></div>
			</div>
			
			<?php
			if ($requireHidden) {
				echo $this->input($model.'.'.$field, 
					array(
						'type' 	=> 'hidden',
						'id'	=> $hiddenId,
						'error' => $error
					)
				);
			}
			
			$divOptions = array(
				'class'		=>	'input',
				'tag'		=>	'div'
			);
			
			$errMsg = '';
			if ($error !== false) {
				$errMsg = $this->error($field, $error);
				if ($errMsg) {
					$divOptions = $this->addClass($divOptions, 'error');
				}
			}

			if (isset($divOptions) && isset($divOptions['tag']) && !is_null($errMsg)) {
				$tag = $divOptions['tag'];
				unset($divOptions['tag']);
				echo $this->Html->tag($tag, $errMsg, $divOptions);
			}
			?>
		</div>

		<script type="text/javascript" src="<?php e(Router::url($DSUrl)) ?>"></script>
		<script type="text/javascript">
		YAHOO.fbc.jsLibs = YAHOO.fbc.jsLibs.concat(['autocomplete','datasource','animation','button']);
		
		YAHOO.fbc.loaderReady.subscribe( function () {
		    <?php 
			if ($DStype != 'local') {
				trigger_error("Type de datasource $DStype non supporté.");
				return;
			} else {
				// Use a LocalDataSource
				e("var oDS = new YAHOO.util.LocalDataSource(".$datasource['name'].");");
			}
		 	?>
			
			oDS.responseSchema = {fields : ['<?php e(implode("','", $datasource['fields'])) ?>']};

			// Instantiate the AutoComplete
			var oConfigs = {
				prehighlightClassName: "yui-ac-prehighlight",
				useShadow: true,
				queryDelay: 0,
				minQueryLength: 0,
				animVert: .01,
				typeAhead: true
			}
			var oAC = new YAHOO.widget.AutoComplete("<?php e($inputId) ?>", "<?php e($containerId) ?>", oDS, oConfigs);
			oAC.alwaysShowContainer = false;
			oAC.minQueryLength = 0; // Can be 0, which will return all results
			oAC.maxResultsDisplayed = 100; // Show more results, scrolling is enabled via CSS
			<?php 
			if (isset($options['multiple']) && $options['multiple']) {
					e('oAC.delimChar = [" "];');
			} 
			?>    
			// Enable comma and semi-colon delimiters
			oAC.autoHighlight = false; // Auto-highlighting interferes with adding new tags
			oAC.useShadow = true;
			oAC.useIFrame = true;
			
			// Keeps container centered
			oAC.doBeforeExpandContainer = function(oTextbox, oContainer, sQuery, aResults) {
			var pos = YAHOO.util.Dom.getXY(oTextbox);
			pos[1] += YAHOO.util.Dom.get(oTextbox).offsetHeight + 2;
			YAHOO.util.Dom.setXY(oContainer,pos);
				return true;
			};
			
			<?php
			if ($requireHidden) {
			?>
				// Define an event handler to populate a hidden form field
				// when an item gets selected
				var myHiddenField = YAHOO.util.Dom.get("<?php e($hiddenId) ?>");
				var updateHiddenInput = function(sType, aArgs) {
					var myAC = aArgs[0]; 			// reference back to the AC instance
					var elLI = aArgs[1]; 			// reference to the selected LI element
					var oData = aArgs[2][1]; 		// object literal of selected item's result data

					// update hidden form field with the selected item's ID
					/*
						TODO Ne fonctionne pas avec un autocomplete multiple
					*/
					myHiddenField.value = oData;
				};
				
				<?php
				if ($options['redirectTo']) {
				?>
					var redirectTo = function(sType, aArgs) {
						var myAC = aArgs[0]; 			// reference back to the AC instance
						var elLI = aArgs[1]; 			// reference to the selected LI element
						var oData = aArgs[2][1]; 		// object literal of selected item's result data
						document.location = "<?php e($options['redirectTo']) ?>"+oData;
					};
					oAC.itemSelectEvent.subscribe(redirectTo);
				<?php
				} else{
				?>
				oAC.itemSelectEvent.subscribe(updateHiddenInput);
				<?php
				}
				?>
			<?php
			}
			?>
			
			var bToggler = YAHOO.util.Dom.get("toggleB");
			var oPushButtonB = new YAHOO.widget.Button({container:bToggler});
		    var toggleB = function(e) {
		        //YAHOO.util.Event.stopEvent(e);
		        if(!YAHOO.util.Dom.hasClass(bToggler, "open")) {
		            YAHOO.util.Dom.addClass(bToggler, "open")
		        }
			
		        // Is open
		        if(oAC.isContainerOpen()) {
		            oAC.collapseContainer();
		        }
		        // Is closed
		        else {
		            oAC.getInputEl().focus(); // Needed to keep widget active
		            setTimeout(function() { // For IE
		                oAC.sendQuery("");
		            },0);
		        }
		    }
		    oPushButtonB.on("click", toggleB);
		    oAC.containerCollapseEvent.subscribe(function(){YAHOO.util.Dom.removeClass(bToggler, "open")});
			
			return {
				oDS: oDS,
				oAC: oAC
			};
		}, this);
		</script>
		<?php
	}// autoComplete

}// YuiHelper
?>