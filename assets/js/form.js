/**
 * This file handles the submission of Forms.
 *
 * Uses '/ajax.js';
 */

var EvolvingWebSampleForm = {
	form : null,

	messageElement: null,

	init : function() {
		this.messageElement = this.form.getElementsByClassName( 'evolving-web-response-message' )[0];

		this.form.dataset.isEvolvingWebSampleFormListening = true;

		this.form.addEventListener(
			'submit', function( e ) {
				e.preventDefault();

				this.submit();
			}.bind( this )
		);
	},

	submit : function() {
		this.messageElement.innerText = '';
		this.messageElement.className = 'evolving-web-response-message';

		var ajax = new AJAX();

		ajax.url        = this.form.action;
		ajax.method     = this.form.method;
		ajax.parameters = new FormData( this.form );
		ajax.parameters.append( 'action', 'evolving_web_submit_form' ); //this corresponds to wp_action: wp_ajax_evolving_web_submit_form.

		ajax.callbacks.success = function( response ) {
			if ( response.success ) {
				this.form.className           = 'evolving-web-ajax-form submission-success';
				this.messageElement.innerText = response.data.message;
				this.messageElement.className = 'evolving-web-response-message success';
			} else {
				this.enable( true );
				this.messageElement.innerText = response.data.message;
				this.form.className           = 'evolving-web-ajax-form submission-error';
				this.messageElement.className = 'evolving-web-response-message error';
			}
		}.bind( this );

		ajax.callbacks.error = function( response ) {
			this.enable( true );
			this.messageElement.innerText = response.data.message;
			this.form.className           = 'evolving-web-ajax-form submission-error';
		}.bind( this );

		/**
		 * When we start sending we disable the form elements and add a class
		 * for styling purposes.
		 */
		this.enable( false );
		this.form.className = 'evolving-web-ajax-form evolving-web-sending';

		document.dispatchEvent( new Event( 'EvolvingWebSampleFormSubmissionStart' ) );

		ajax.send();
	},

	enable : function( enable ) {
		for ( var i in this.form.elements ) {
			this.form.elements[ i ].disabled = ! enable;
		}
	},
};

var EvolvingWebSampleFormsInit = function() {
	var formElements = document.getElementsByClassName( 'evolving-web-ajax-form' );
	var forms = {};

	for ( let i = 0; i < formElements.length; i++ ) {
		forms.i = Object.create( EvolvingWebSampleForm );
		forms.i.form = formElements[ i ];
		forms.i.init();
	}
};

if ( 'complete' === document.readyState ) {
	EvolvingWebSampleFormsInit();
} else {
	document.addEventListener( 'DOMContentLoaded', EvolvingWebSampleFormsInit );
}
