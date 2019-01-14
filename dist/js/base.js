$( document ).ready( function() {
	/*
	* ====================== Project Section
	* */

	// When Click on the project then store it in the session so we server task according to projects.
	$( document ).on( 'click', 'ul.projectMainNavUl li', function( e ) {
		e.preventDefault();
		let ProjectID = $( this ).attr( 'data-ProjectID' );
		let Href = $( this ).find( 'a' ).attr( 'href' );

		if ( Href=='#' ) {
			SaveSelectedProjectInSession( ProjectID );
		} else {
			window.location.href = projectController;
		}
		// Make Project Active.
		$( 'ul.projectMainNavUl li' ).removeClass( 'active' );
		$( this ).addClass( 'active' );

		// Also make project listed active
		if ( Controller=='projects' ) {
			$( 'table#projectListSection tr' ).removeClass( 'active' );
			$( 'table#projectListSection' ).find( 'tr[data-ProjectID=' + ProjectID + ']' ).addClass( 'active' );
		} else if ( Controller=='user' ) {
			//LoadTasks( true );
		}
	} );

	/*
	*
	* ====================== Initialize Plugins
	*
	* */

	//Select2
	$( '.select2' ).select2();

} );
/*
* Save selected Project in session then display task according to project
* */
let SaveSelectedProjectInSession = ( ProjectID )=>{
	$.ajax( {
		method: 'post',
		url: projectAjaxUrl + 'saveSelectedProjectInSession',
		data: 'id=' + ProjectID,
		success: function( data ) {
		},
		error: function( err, err1, err2 ) {
			Warning( '.message', err.responseText );
		}
	} );
};