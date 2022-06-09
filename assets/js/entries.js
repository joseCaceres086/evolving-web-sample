/**
 * This file handles the retrieval of form entries.
 *
 * Uses '/ajax.js';
 */

var formEntries = {
	getEntryPage      : function( pageNumber ) {
		var ajax            = new AJAX();
		var endpoint        = document.getElementById( 'evolving-web-endpoint' );
		var getEntriesNonce = document.getElementById( 'evolving_web_get_entries_nonce' );

		ajax.url        = endpoint.value;
		ajax.method     = 'POST';
		ajax.parameters = new FormData();
		ajax.parameters.append( 'action', 'evolving_web_get_entries' ); //this corresponds to wp_action: wp_ajax_evolving_web_get_entries.
		ajax.parameters.append( 'evolving_web_get_entries_nonce', getEntriesNonce.value );

		ajax.parameters.append( 'page-number', pageNumber );

		ajax.callbacks.success = function( response ) {
			var shortcodecontent = document.getElementById( 'evolving-web-form-entries' );
			shortcodecontent.innerHTML = response;
		};

		ajax.send();
	},

	getSingleEntry      : function( entryId ) {
		var singleAjax          = new AJAX();
		var singleEndpoint      = document.getElementById( 'evolving-web-endpoint' );
		var getSingleEntryNonce = document.getElementById( 'evolving_web_get_single_entry_nonce' );

		singleAjax.url        = singleEndpoint.value;
		singleAjax.method     = 'POST';
		singleAjax.parameters = new FormData();
		singleAjax.parameters.append( 'action', 'evolving_web_get_single_entry' ); //this corresponds to wp_action: wp_ajax_evolving_web_get_single_entry.
		singleAjax.parameters.append( 'evolving_web_get_single_entry_nonce', getSingleEntryNonce.value );

		singleAjax.parameters.append( 'entry-id', entryId );

		singleAjax.callbacks.success = function( response ) {
			var fullEntryContainer = document.getElementById( 'full-entry-result' );
			fullEntryContainer.innerHTML = response;
			fullEntryContainer.classList.add( 'displayed' );
		};

		singleAjax.send();
	},

	init : function() {
		var detailsbuttons = document.getElementsByClassName( 'show-details-button' );

		for ( var i = 0; i < detailsbuttons.length; i++ ) {
			detailsbuttons[ i ].addEventListener(
				'click', function( e ) {
					formEntries.getSingleEntry( this.dataset.entryId );
				}
			);
		};

		var paginationbuttons = document.getElementsByClassName( 'pagination-button' );

		for ( var i = 0; i < paginationbuttons.length; i++ ) {
			if ( ! paginationbuttons[ i ].dataset.pageNumber ) {
				paginationbuttons[ i ].style.opacity = '0.5';
				paginationbuttons[ i ].disabled = true;
			}

			paginationbuttons[ i ].addEventListener(
				'click', function( e ) {
					formEntries.getEntryPage( this.dataset.pageNumber );
				}
			);
		};
	}
};

var formEntriesInit = function() {
	formEntries.init();

	var mutationObserver = new MutationObserver(
		function() {
			formEntries.init();
		}
	);

	mutationObserver.observe( document.body, { childList: true, subtree: true } );
};

if ( 'complete' === document.readyState ) {
	formEntriesInit();
} else {
	document.addEventListener( 'DOMContentLoaded', formEntriesInit );
}
