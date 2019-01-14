let UpdateProfileFormID = '#updateProfileForm';
let SuccessMessageAfterProfileUpdate = 'Your Profile is updated successfully !';
$( document ).ready( function() {

	// Update Profile of user.
	$( document ).on( 'submit', UpdateProfileFormID, function( e ) {
		e.preventDefault();
		UpdateProfile();
	} );

} );

/*
* Update user profile.
* */
let UpdateProfile = ()=>{
	$( UpdateProfileFormID ).ajaxSubmit( {
		success: function( data ) {
			if ( data=='OK' ) {
				Success( SuccessMessageAfterProfileUpdate, '.message' );
				location.reload();
			} else {
				Warning( data, '.message' )
			}
		},
		error: function( err, err2, err3 ) {
			alert( err.responseText );
			console.log( err.responseText );
			Warning( err.responseText, '.message' );
			console.log( err2 );
			console.log( err3 );
		}
	} );
};

