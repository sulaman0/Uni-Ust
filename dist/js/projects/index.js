let ProjectProgress = {
	'Working': 0,
	'Complete': 1,
};
let ProjectAddEditFormId = '#addEditProjectForm';
let EditProjectModel = '#EditProjectForm';
let SuccessMessageOnProjectAdd = 'Project Added Successfully!';
let VueObject = {};
$( document ).ready( function() {

	// Show add project model
	$( document ).on( 'click', '.addProjectButton', function() {
		$( '#addProjectModelDiv' ).modal( 'show' );
	} );

	// Submit form
	$( document ).on( 'click', '.saveProjectFormButton', function() {
		AddEditProject();
	} );
	$( document ).on( 'click', '.EditProjectFormButton', function() {
		AddEditProject( EditProjectModel );
	} );

	// Delete project
	$( document ).on( 'click', '.deleteProject', function() {
		let ProjectID = $( this ).closest( 'tr' ).attr( 'data-ProjectID' );
		DeleteProject( ProjectID );
	} );

	// Edit Project
	$( document ).on( 'click', '.editProject', function() {
		let ProjectID = $( this ).closest( 'tr' ).attr( 'data-ProjectID' );
		FetchingValueThenShowInEditModel( ProjectID );

	} );

	// Loading Projects.
	LoadProjects();
} );

/*
* Send add or edit project request.
* */
let AddEditProject = ( FormID = ProjectAddEditFormId )=>{
	$( FormID ).ajaxSubmit( {
		success: function( data ) {
			if ( data=='OK' ) {
				if ( FormID==EditProjectModel ) {
					LoadProjects( true );
					$( '#EditProjectModelDiv' ).modal( 'hide' );
				} else {
					$( '#addProjectModelDiv' ).modal( 'hide' );
					Success( SuccessMessageOnProjectAdd, '.message' );
				}
			}
		},
		error: function( err, err2, err3 ) {
			alert( err.resposneText );
			Warning( err.responseText, '.message' );
		}
	} );
};

/*
* Load Projects
* */
let LoadProjects = ( afterEdit )=>{
	$.ajax( {
		method: 'post',
		url: projectAjaxUrl + 'getProjectList',
		success: function( data ) {
			if ( afterEdit ) {
				VueObject.projects = data;
			} else {
				LoadProjectVueRendering( data );
			}
		},
		error: function( err, err1, err2 ) {
			Warning( '.message', err.responseText );
		}
	} );
};

let LoadProjectVueRendering = ( p_data )=>{
	VueObject = new Vue( {
		el: '#projectListSection',
		delimiters: [ '${', '}' ],
		data: {
			projects: p_data,
		}
	} );

};

let DeleteProject = ( ProjectID )=>{
	$.ajax( {
		method: 'post',
		url: projectAjaxUrl + 'deleteProject',
		data: 'id=' + ProjectID,
		success: function( data ) {
			console.log( data );
			alert( data );
			if ( data=='OK' ) {
				RemoveProjectTr();
			}
		},
		error: function( err, err1, err2 ) {
			Warning( '.message', err.responseText );
		}
	} );
};

let RemoveProjectTr = ( ProjectID )=>{
	$( '#projectListSection' ).find( 'tr[data-ProjectID=' + ProjectID + ']' ).remove();
};

let FetchingValueThenShowInEditModel = ( ProjectID )=>{
	let TriggeredTR = $( '#projectListSection' ).find( 'tr[data-ProjectID=' + ProjectID + ']' );
	let ReplaceMentObj = {
		name: $( TriggeredTR ).find( 'td.name' ).html(),
		description: $( TriggeredTR ).find( 'td.description' ).html(),
		status: $( TriggeredTR ).find( 'td.status span' ).html(),
		id: ProjectID,
	};

	for ( x in ReplaceMentObj ) {
		if ( x=='description' ) {
			$( '#EditProjectModelDiv' ).find( 'form' + EditProjectModel + ' textarea[name=' + x + ']' ).html( ReplaceMentObj[ x ].trim() );
		} else if ( x=='status' ) {
			$( '#EditProjectModelDiv' ).find( 'form' + EditProjectModel + ' select[name=' + x + ']' ).val( ProjectProgress[ ReplaceMentObj[ x ].trim() ] );
		} else {
			$( '#EditProjectModelDiv' ).find( 'form' + EditProjectModel + ' input[name=' + x + ']' ).attr( 'value', ReplaceMentObj[ x ].trim() );
		}
	}

	$( '#EditProjectModelDiv' ).modal( 'show' );
};