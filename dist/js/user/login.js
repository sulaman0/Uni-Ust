let LoginFormID = '#loginForm';
let SuccessMessageAfterLogin = 'Successfully Login !';
$( document ).ready( function() {
	FocusUserNameField();
	$( document ).on( 'submit', '#loginForm', function( e ) {
		e.preventDefault();
		Login();
	} );
} );

/*
* Login Functions.
* */
let Login = ()=>{
	$( LoginFormID ).ajaxSubmit( {
		success: function( data ) {
			if ( data=='OK' ) {
				Success( SuccessMessageAfterLogin, '.message' );
				setTimeout( function() {
					window.location.href = dashbordPageUrl;
				}, 1000 );
			} else {
				Warning( data, '.message' )
			}
		},
		error: function( err, err2, err3 ) {
			console.log( err.responseText );
			Warning( err.responseText, '.message' );
			console.log( err2 );
			console.log( err3 );
		}
	} );
};

/*
* focus first input when page is loaded.
* */
let FocusUserNameField = ()=>{
	$( LoginFormID ).find( 'input' ).first().focus();
};