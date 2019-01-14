let RegistrationFormID = '#registerUserForm';
let AfterRegisterMessage = 'Account is create Successfully! System will redirect you to login page in a moment';

$( document ).ready( function() {
	// Focus first input.
	FocusUserNameField();
	// Submit form
	$( document ).on( 'submit', '#registerUserForm', function( e ) {
		e.preventDefault();
		Register();
	} )
} );

/*
* Login Functions.
* */
let Register = ()=>{
	$( RegistrationFormID ).ajaxSubmit( {
		success: function( data ) {
			if ( data=='OK' ) {
				// when Success comes.
				Success( AfterRegisterMessage, '.message' );

				// Reset Form
				$( '#registerUserForm' )[ 0 ].reset();
				// Redirect to login page.
				setTimeout( function() {
					window.location.href = site_url;
				}, 2000 );
			} else {
				// When database error comes
				Warning( data, '.message' );
			}

		},
		error: function( err, err2, err3 ) {
			// When ajax request fails
			Warning( err.responseText, '.message' );
			console.log( err.responseText );
			console.log( err );
			console.log( err2 );
			console.log( err3 );
		}
	} );
};

/*
* focus first input when page is loaded.
* */
let FocusUserNameField = ()=>{
	$( RegistrationFormID ).find( 'input' ).first().focus();
};