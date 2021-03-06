/** Scitent JavaScript Namespace
 * Handy Utilities and Helpers for Scitent Plugins
 *
 */
'use strict';
if(!scitent) { var scitent = {}; }
if(!scitent.utils) { scitent.utils = {}; }

/*********************************
 * Things to run after dom loads
 */
 // activate remodal popups when user opens a course in a new tab
jQuery( document ).ready( function() {
 	jQuery('.scitent-refresh').click( function(e){
 		location.reload();
 	});
 });

/*********************************
 * Scitent definition
 */
scitent = jQuery.extend({}, scitent, {
});