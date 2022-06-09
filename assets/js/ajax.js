var AJAX = function() {
	this.url		= '';
	this.method		= 'GET';
	this.parameters	= null;
	this.callbacks	= {
		progress	: null,
		success		: null,
		error		: null,
		complete	: null
	};
	this.send		= function() {
		var xhr = new XMLHttpRequest();

		xhr.callbacks = this.callbacks;

		if( this.callbacks.progress )
			xhr.upload.addEventListener( 'progress', this.callbacks.progress );

		if( this.callbacks.complete	||
		  	this.callbacks.success	||
		  	this.callbacks.error )
			xhr.addEventListener( 'readystatechange', function() {
				if( this.readyState == 4 ) {
					var response;
					var status 		= this.status;					
					var contentType = this.getResponseHeader( 'content-type' );
					
					if( contentType && typeof contentType !== 'undefined' ) {					
						if( contentType.indexOf( 'text/xml' ) != -1 )
							response = this.responseXML;						
						else if( contentType.indexOf( 'application/json' ) != -1 )
							response = JSON.parse( this.responseText );	
						else 
							response = this.responseText;
					} else
						response = this.responseText;									

					if( status == 200 ) {
						if( this.callbacks.success )
							this.callbacks.success( response );
					} else if( this.callbacks.error )
						this.callbacks.error( response, status );

					if( this.callbacks.complete )
						this.callbacks.complete( response, status );
				}
			} );

		xhr.withCredentials = true;

		xhr.open( this.method, this.url, true );

		var parameters;

		if ( this.parameters instanceof FormData ) {
			parameters = this.parameters;
		} else {
			xhr.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded' );

			parameters = '';

			for ( var i in this.parameters ) {
				parameters += ( parameters ? '&' : '' ) + encodeURIComponent( i ) + '=' + encodeURIComponent( this.parameters[ i ] );
			}
		}

		xhr.send( parameters );
	};
};
