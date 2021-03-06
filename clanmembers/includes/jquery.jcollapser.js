/**
 * Small plugin to make toggling the visibility of certain parts of a page 
 * easier.
 * 
 * @package jCollapser
 * @author Peter Halasz <skinner@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3.0
 * @copyright (c) 2008-2009, Peter Halasz all rights reserved.
 */
(function($) {
	/**
	 * Sets up the functionality.
	 * 
	 * Options
	 * 
	 * 'container' : Sets the parent element which contains the one we want to 
	 *               collapse
	 *               
	 * 'target'    : The actual element that will collapse or expand
	 * 'state'     : The initial state we want it to be in
	 *               Valid values: collapsed, expanded
	 * 
	 * <sample>
	 * var options = {
	 *     container: '#container',
	 *     target:    '#collapse_element',
	 *     state:     'collapsed'
	 * }
	 * </sample>
	 * 
	 * @access public
	 * @return void
	 */
	$.fn.jcollapser = function(options) {
		
		var $this = $(this)[0];

		var settings = $.extend({}, $.fn.jcollapser.defaults, options);
		
		if(typeof(options.container) == 'undefined') {
			settings.container = "#" + $this.id;
		}
		
		$.fn.jcollapser.settings[$this.id] = settings; 
		
		$(settings.container + " > .jm-collapse").bind("click", {}, $.fn.jcollapser.toggleState);
		$(settings.container + " > .jm-expand").bind("click", {}, $.fn.jcollapser.toggleState);
		
		/* State from the cookie*/
		var $state
		
		if($state ==  'collapsed') {
			$(settings.container + ' > .jm-collapse').css("display","none");
			$(settings.container + ' > .jm-expand').css("display","block");        
			$(settings.target).hide();
		} else if($state == null) {
			/* 
			 * If we set the state at init time and no state in the cookie 
			 * then use the init setting. 
			 */
			if(settings.state != '' && settings.state == 'collapsed') {
				$(settings.container + ' > .jm-collapse').css("display","none");
				$(settings.container + ' > .jm-expand').css("display","block");        
				$(settings.target).hide();
			}
		}
	};
	
	$.fn.jcollapser.toggleState = function() {
		var $parent = $(this).parents().get(0).id;
		var settings = $(this).jcollapser.settings[$parent];
		
		if( $(settings.container + ' > .jm-collapse').css("display") == "none" ) {
			$(settings.container + ' > .jm-collapse').css("display","block");
			//$.cookie('jcollapser_' + settings.target, 'expanded', { path: '/', expires: 365 });
		} else {
			$(settings.container + ' > .jm-collapse').css("display","none");
		}
		
		if( $(settings.container + ' > .jm-expand').css("display") == "none" ) {
			$(settings.container + ' > .jm-expand').css("display","block");
			//$.cookie('jcollapser_' + settings.target, 'expanded', { path: '/', expires: 365 });
		} else {
			$(settings.container + ' > .jm-expand').css("display","none");
		}
		
		$(settings.target).slideToggle("slow");
	}
	
	$.fn.jcollapser.settings = {};
	
	$.fn.jcollapser.defaults = {
			container: '#example',
			target: '#data',
			state: ''
	};
})(jQuery);