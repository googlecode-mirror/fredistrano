// CUSTOM DELICIOUS -- modified ------------------------------------------------------------------
String.prototype.escRegExp = function(){ return this.replace(/[\\$*+?()=!|,{}\[\]\.^]/g,'\\$&') }
String.prototype.unescHtml = function(){ var i,t=this; for(i in e) t=t.replace(new RegExp(i,'g'),e[i]); return t }
function Suggestions() { this.length=1; this.picked=0; this[0] = 'dummy' }
var suggestions = new Suggestions()
var values=[], valueSearch=[], lastEdit=[]
var h={}, selected={}, currentValue=[], e={'&lt;':'<','&gt;':'>','&amp;':'&','&quot;':'"'}

function init_completion(completionObj) { 
	var t, j, k, obj; 
	
	obj=makeDiv('suggestionsDiv');
	addClass( obj, 'suggestion');
	$id('f-jcomplete').appendChild(obj);
	
	completionObj.each( function(item){
		var tmp, obj;
		
		tmp = h[item['obsField']] = [];

		obj=makeDiv('suggestDiv_'+item['obsField']);
		$id('suggestionsDiv').appendChild(obj);
		
		tmp['observedField'] = $id(item['obsField']);
		tmp['suggestionDiv'] = obj;
		if(!item['max']){
			tmp['max'] = null;
		}else{
			tmp['max'] = item['max'];
		}
		tmp['type'] = item['type'];
		
		if (!valueSearch[tmp['type']]) {
			valueSearch[tmp['type']] = '';
			for (t in values[tmp['type']])
				valueSearch[tmp['type']] += t + ' ';
		}
	});
    
    document.onkeyup = keyup;
	
	for(j in h) {
		h[j]['observedField'].onkeydown = keydown;
		h[j]['observedField'].onfocus = dropdownFocus;
		
		addClass(h[j]['suggestionDiv'], 'popup');
		inviso = document.createElement('div');
		inviso.style.top = inviso.style.left = 0;
		inviso.style.position = 'absolute'; 
		inviso.style.visibility = 'hidden';
	
		h[j]['observedField'].parentNode.appendChild(inviso);
	}
}

var valuesFocused = false;
var tagHeight = 0;

function dropdownBlur() { valuesFocused = false; hideSuggestions() }
function dropdownFocus() { valuesFocused = true }
function makeDiv(id) { var obj=document.createElement('div'); obj.id=id; return obj }

function makeValue(parent, id, value, js, post, display) {
	parent.appendChild(document.createTextNode(' '))
	var obj = document.createElement('a')
	if (display) obj.style.display = display
	obj.className = 'suggestionValue'
	obj.setAttribute('href','javascript:'+js+'(\''+id+'\',\''+value.replace(/"/g,'\\"')+'\')')
	var text = value
	if(post) text += post
	obj.appendChild(document.createTextNode(text))
	if(values[value] < 2) obj.style.color = '#66f'
	else if(values[value] == 2) obj.style.color = '#44f'
	parent.appendChild(obj)
	if (tagHeight == 0) tagHeight = obj.offsetHeight
	return obj
}

function complete(id, value) { 
	var valueArray=h[id]['observedField'].value.split(' ');
	if(typeof value == 'undefined') value = suggestions[suggestions.picked].innerHTML.replace(/ \(.+\)$/, '').unescHtml(); // tab complete rather than click complete
	valueArray[currentValue[id].index] = value;
	var text = valueArray.join(' ');
	h[id]['observedField'].value = (text.substr(-1,1) == ' ' ? text : text + ' ' );
	hideSuggestions(id);
	focusTo(h[id]['observedField']);
	//$id(h[id]['observedField']).focus();
}

// focus the caret to end of a form input (+ optionally select some text)
var range=0 //ie
function focusTo(obj, selectFrom) {
	if (typeof selectFrom == 'undefined') selectFrom = obj.value.length
	if(obj.createTextRange){ //ie + opera
		//if (range == 0) range = obj.createTextRange()
		//range.moveEnd("character",obj.value.length)
		//range.moveStart("character",selectFrom)
		//setTimeout('range.select()', 10)
	} else if (obj.setSelectionRange){ //ff
		obj.select()
		obj.setSelectionRange(selectFrom,obj.value.length)
	} else { //safari :(
	 obj.blur()
	 obj.focus()
}}

function hideSuggestions(id) {
	h[id]['suggestionDiv'].style.visibility='hidden'
}
function showSuggestions(id) {
	suggest(0);
	var pos = 0, valz = h[id]['observedField'].value.split(' '), s = h[id]['suggestionDiv'], t = h[id]['observedField']
	
	if (valz[valz.length-1] == '')
		valz.splice(valz.length-1);
	
	if((h[id]['max'] != null)&&(valz.length>h[id]['max'])){
		alert('nombre de choix possible : '+h[id]['max']);
		complete(id, '');
		return false;
	}
	
	// Content
	for(var i=0; i<currentValue[id].index; i++) { pos += valz[i].length+1 };
	var text = h[id]['observedField'].value.substr(0,pos);
	var esc = {'<':'[','>':']',' ':'&nbsp;'};
	for(var i in esc) text=text.replace(new RegExp(i,'g'), esc[i]);
	inviso.innerHTML = text;
	
	// Size
	s.style.top = getY(h[id]['observedField']) + h[id]['observedField'].offsetHeight - 1 + 'px';
	s.style.height = 'auto';
	s.style.overflow = 'visible';
	s.style.width = s.scrollWidth + 'px';

	if( s.offsetHeight > 200) {
		s.style.height =  '200px'
		s.style.overflow = 'auto'
		s.scrollTop = 0
		if(s.clientWidth < s.scrollWidth) s.style.width = s.scrollWidth + 30 + 'px'
	} 
		
	// Position
	s.style.left = getX(t) + inviso.offsetWidth + 'px' // put dropdown right below current typed tag
	h[id]['suggestionDiv'].style.visibility='visible'
}

function scrollDropdown(id) {
	var amt = Math.ceil((Math.ceil(h[id]['suggestionDiv'].offsetHeight - tagHeight) / tagHeight) / 2 )
	var scrollTo = (suggestions.picked * tagHeight) - (amt * tagHeight)
	h[id]['suggestionDiv'].scrollTop = (scrollTo < 0) ? 0 : scrollTo
}

function updateSuggestions(id) {
	if(!getcurrentValue(id) || !currentValue[id].text || !valuesFocused) { hideSuggestions(id); return false }
	
	while (h[id]['suggestionDiv'].hasChildNodes()) h[id]['suggestionDiv'].removeChild(h[id]['suggestionDiv'].firstChild)
	delete suggestions; suggestions = new Suggestions();

	h[id]['suggestionDiv'].innerHTML = "<!--[if IE]> <iframe src=\"javascript:'<html></html>'\"></iframe> <![endif]-->";
	
	var valueArray = h[id]['observedField'].value.toLowerCase().split(' '), txt=currentValue[id].text.escRegExp(), valueHash={}, t
	valueArray.each( function(value){
		valueHash[value] = true
	});
	
	var search = valueSearch[h[id]['type']].match(new RegExp(("(?:^| )("+txt+"[^ ]+)"), "gi"))
	if(search){
		for (i=0; i<search.length; i++) {
			tl = search[i].trim()
			if(valueHash[tl.toLowerCase()])  continue // do not suggest already typed tag
			var text = values[h[id]['type']][tl] ? ' ('+values[h[id]['type']][tl]+')' : ''
			suggestions[suggestions.length] = makeValue(h[id]['suggestionDiv'], id, tl, 'complete', text, 'block')
			suggestions.length++
		}
	}
	
	if (suggestions.length > 1) showSuggestions(id)
	else hideSuggestions(id)
}

function suggest(index) {
	if(suggestions.length == 1) index = 0
	if(suggestions[suggestions.picked].className) rmClass(suggestions[suggestions.picked], 'selected')
	addClass(suggestions[suggestions.picked = index], 'selected')
}

function getcurrentValue(id) {	
	if(h[id]['observedField'].value == lastEdit[id]) return true // no edit
	if(h[id]['observedField'] == '') return false
	
	var valueArray=h[id]['observedField'].value.toLowerCase().split(' '), oldArray=lastEdit[id].toLowerCase().split(' '), currentValues = [], matched=false, t,o

	for (t=0; t<valueArray.length; t++) {
		
		for (o=0; o<oldArray.length; o++) {
			if(typeof oldArray[o] == 'undefined') { oldArray.splice(o,1); break }
			if(valueArray[t] == oldArray[o]) { matched = true; oldArray.splice(o,1); break;}
		}
		if(!matched) currentValues[currentValues.length] = t
		matched=false
	}
	// more than one word changed... abort
	if(currentValues.length > 1) { hideSuggestions(id); return false }
	currentValue[id] = { text:valueArray[currentValues[0]], index:currentValues[0] }
	return true
}

function prevent(e) {
	if (window.event) window.event.returnValue = false
	else e.preventDefault()
}

function keydown(e) { e=e||window.event
	if (e.target) targ = e.target;
	else if (e.srcElement) targ = e.srcElement;
	
	var completionObj = h[targ.id]
	
	switch(e.keyCode){
		case 40:
			if(completionObj['suggestionDiv'].style.visibility == 'visible') {
				suggest((suggestions.picked + 1) % suggestions.length)
				scrollDropdown(targ.id)
			}
			prevent(e)
			break
		case 38:
			if(completionObj['suggestionDiv'].style.visibility == 'visible') {
				suggest(suggestions.picked == 0 ? suggestions.length - 1 : suggestions.picked - 1)
				scrollDropdown(targ.id)
			}
			prevent(e)
			break
		case 9:
			if(completionObj['suggestionDiv'].style.visibility == 'visible') prevent(e)
			break
		case 13:
			if(completionObj['suggestionDiv'].style.visibility == 'visible' && suggestions.picked > 0) prevent(e)
			break
		default: lastEdit[targ.id] = completionObj['observedField'].value
	}

}

function keyup(e) { e=e||window.event
	if (e.target) targ = e.target;
	else if (e.srcElement) targ = e.srcElement;
	
	var completionObj = h[targ.id]
	
	switch(e.keyCode){
		case 38: case 40:
			prevent(e)
			break
		case 9:
			if(completionObj['suggestionDiv'].style.visibility == 'visible') {
				if (suggestions.picked == 0) suggest(1)
				complete(targ.id)
				prevent(e)
			}
			break
		case 13:
			if(completionObj['suggestionDiv'].style.visibility == 'visible' && suggestions.picked > 0) {
				complete(targ.id)
				prevent(e)
			}
			break
		case 35: //end
		case 36: //home
		case 39: //right
		case 37: //left
		case 32: //space
			hideSuggestions(targ.id)
			break
		default: if (h[targ.id]) updateSuggestions(targ.id)
}}

// LEGACY DELICIOUS -- untouched ------------------------------------------------------------------
String.prototype.trim = function(){ return this.replace(/^\s+|\s+$/g,'') }
//String.prototype.unescHtml = function(){ var i,e={'&lt;':'<','&gt;':'>','&amp;':'&','&quot;':'"'},t=this; for(i in e) t=t.replace(new RegExp(i,'g'),e[i]); return t }

function loadValues(type, t) { values[type] = t }

// get previous/next non-text node
function previousElement(o) {
	if(o.previousSibling) { while (o.previousSibling.nodeType != 1) o = o.previousSibling; return o.previousSibling }
	else return false
}
function nextElement(o) {
	if(o.nextSibling) { while (o.nextSibling.nodeType != 1) o = o.nextSibling; return o.nextSibling }
	else return false
}

// styling functions
function isA(o,klass){ if(!o.className) return false; return new RegExp('\\b'+klass+'\\b').test(o.className) }
function addClass(o,klass){ if(!isA(o,klass)) o.className += ' ' + klass }
function rmClass(o,klass){ o.className = o.className.replace(new RegExp('\\s*\\b'+klass+'\\b'),'') }
function swapClass(o,klass,klass2){ var swap = isA(o,klass) ? [klass,klass2] : [klass2,klass]; rmClass(o,swap[0]); addClass(o,swap[1]) }
function getStyle(o,s) {
	if (document.defaultView && document.defaultView.getComputedStyle) return document.defaultView.getComputedStyle(o,null).getPropertyValue(s)
	else if (o.currentStyle) { return o.currentStyle[s.replace(/-([^-])/g, function(a,b){return b.toUpperCase()})] }
}
// shorter names for grabbing stuff
function $id(id){ return document.getElementById(id) }
// get elements by class name, eg $c('post', document, 'li')
function $c(c,o,t) { o=o||document;
	if (!o.length) o = [o]
	else if(o.length == 1 && !o[0]) o = [o] // opera, you're weird
	var elements = []
	for(var i = 0, e; e = o[i]; i++) {
		if(e.getElementsByTagName) {
			var children = e.getElementsByTagName(t || '*')
			for (var j = 0, child; child = children[j]; j++) if(isA(child,c)) elements.push(child)
	}}
	return elements
}

function extend(dest, src) {
	for (var p in src) dest[p] = src[p]
	return dest
}

// get mouse pointer position
function pointerX(e) { return e.pageX || (e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft)) }
function pointerY(e) { return e.pageY || (e.clientY + (document.documentElement.scrollTop || document.body.scrollTop)) }

// get window size
function windowHeight() { return self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight || 0 }
function windowWidth() { return self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth || 0 }

// get pixel position of an object
function getY(o){ var y = 0
	if (o.offsetParent) while (o.offsetParent) { if (o.offsetParent.id && o.offsetParent.id != 'f-jcomplete') break; y += o.offsetTop; o = o.offsetParent }
	return y
}
function getX(o){ var x = 0
	if (o.offsetParent) while (o.offsetParent) { if (o.offsetParent.id && o.offsetParent.id != 'f-jcomplete') break; x += o.offsetLeft; o = o.offsetParent }
	return x
}

// form stuff
function getRadioValue(o) {
	for(var i = 0, r; r = o[i]; i++) if (r.checked && r.value) return r.value
	return false
}

// todo: make less crap
function resizeToText(o, text, margin) {
	margin = margin || 0
	var c = $id(o.id + '-copy')
	if (!c) { makeResizeThing(o); c = $id(o.id + '-copy') }
	var esc = {'<':'[','>':']',' ':'&nbsp;'}
	for(var i in esc) text=text.replace(new RegExp(i,'g'), esc[i])
	c.innerHTML = text
	o.style.width = c.offsetWidth + margin + 'px'
}
function makeResizeThing(src) {
	var o = document.createElement('div')
	o.style.position = 'absolute'; o.style.top = o.style.left = 0
	o.style.visibility = 'hidden'
	o.style.fontSize = getStyle(src, 'font-size')
	o.style.fontFamily = getStyle(src, 'font-family')
	o.id = src.id + '-copy'
	src.parentNode.appendChild(o)
}

// event functions
function falseFunc(){ return false }
function addLoadEvent(f) { var old = window.onload
	if (typeof old != 'function') window.onload = f
	else { window.onload = function() { old(); f() }}
}

function mailer(oName,oDomain) {
 email="mailto:" + oName + "@" + oDomain;
 window.location=email;
}

// the following two functions ganked from prototype (see http://prototype.conio.net)
// (c) 2005 Sam Stephenson
var Class = {
	create: function() {
		return function() { this.initialize.apply(this, arguments) }
}}
Function.prototype.bind = function(o) {
	var __method = this
	return function() { return __method.apply(o, arguments) }
}


