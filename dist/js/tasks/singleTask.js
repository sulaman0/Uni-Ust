var VueObj = {};
var SingleTaskDetails = {};
let EditTaskFormID = '#EditTaskForm';
let CommentFormID = '#addCommentForm';
$( document ).ready( function() {

	// Load single task detail
	if ( TaskClicked<=TotalTask ) {
		LoadSingleTaskDetails( TaskClicked );
	} else {
		TaskClicked = TaskClicked - 1;
		LoadSingleTaskDetails( TaskClicked );
	}

	// Single task click event.
	$( document ).on( 'click', '.editSingleTask', function() {
		let TaskID = $( this ).attr( 'data-taskID' );
		$( '#addProjectModelDiv' ).modal( 'show' );

		// set value of fields.
		$( EditTaskFormID ).find( 'input[name=id]' ).attr( 'value', TaskID );
		$( EditTaskFormID ).find( 'select[name=project_id]' ).val( SingleTaskDetails.res[ 0 ].project_id );
		$( EditTaskFormID ).find( 'select[name=type]' ).val( SingleTaskDetails.res[ 0 ].type );
		$( EditTaskFormID ).find( 'select[name=status]' ).val( SingleTaskDetails.res[ 0 ].status );
		$( EditTaskFormID ).find( 'input[name=name]' ).val( SingleTaskDetails.res[ 0 ].name );
		$( 'h4.modal-title' ).html( 'Edit ' + SingleTaskDetails.res[ 0 ].name );
		$( EditTaskFormID ).find( 'textarea[name=description]' ).val( SingleTaskDetails.res[ 0 ].description );
		let UserAr = SingleTaskDetails.res[ 0 ].assign_to.split( ',' );
		$( '#select2AddTask' ).select2( "val", UserAr );
	} );
	// Edit Single task and open model and show all data in it.
	$( document ).on( 'click', '.editSingleTaskFormSUbmit', function() {
		EditTaskInDB()
	} );
	// Delete attachment
	$( document ).on( 'click', '.deleteAttachment', function() {
		let attachmentRefrence = $( this ).attr( 'data-ItemRefrence' );
		DeleteRefrence( attachmentRefrence );
		$( this ).prev( 'a' ).remove();
		$( this ).remove();
	} );
	// Save comment in database.
	$( document ).on( 'submit', 'form#addCommentForm', function( e ) {
		e.preventDefault();
		SaveCommentInDataBase();
	} );
	$( document ).on( 'click', '.submitCommentForm', function( e ) {
		e.preventDefault();
		SaveCommentInDataBase();
	} );
	// Open upload file diloag
	$( document ).on( 'click', '.uploadFileForComment', function() {
		$( CommentFormID ).find( 'input[name=commentFile]' ).click();
	} )

} );

/*
* Load Task full detail with picture
* */
let LoadSingleTaskDetails = ( TaskID, afterEdit )=>{
	let dataSending = {
		getAttachment: TaskID,
		conditions: {
			id: TaskID,
		},
	};
	$.ajax( {
		method: 'post',
		data: dataSending,
		url: taskAjaxUrl + 'singleTask',
		success: function( data ) {
			SingleTaskDetails = data;
			if ( afterEdit ) {
				VueObj.t_detail = data;
			} else {
				RenderingHtml( data );
			}
		},
		error: function( err, err2, err3 ) {
			alert( err.responseText );
		}
	} );
};

/*
* Rendering Html
* */
let RenderingHtml = ( data )=>{
	VueObj = new Vue( {
		el: '#taskDetailDiv',
		delimiters: [ '${', '}' ],
		data: {
			t_detail: data,
			CurrentUserProfile: StaffUsers[ CurrentUser ].profile_url,
		},
		computed: {},
		methods: {
			StaffList: function( record, profile = false ) {
				if ( StaffUsers.hasOwnProperty( record ) ) {
					if ( profile ) {
						return StaffUsers[ record ].profile_url;
					} else {
						return StaffUsers[ record ].username;
					}

				} else {
					return '--No Staff User--';
				}
			},
			BaseName: function( str ) {
				if ( str!='' && str!=null ) {
					var base = new String( str ).substring( str.lastIndexOf( '\\' ) + 1 );
					if ( base.lastIndexOf( "." )!= -1 ) {
						base = base.substring( 0, base.lastIndexOf( "." ) );
					}
					return base;
				}
			},
			ProjectName: function( ProjectID ) {
				if ( ProjectList.hasOwnProperty( ProjectID ) ) {
					return ProjectList[ ProjectID ].name;
				} else {
					return '--No Project Selected--';
				}
			},
			ReadAbleTime: function( time ) {
				var t = time.split( /[- :]/ );
				var dtt = new Date( t[ 0 ], t[ 1 ] - 1, t[ 2 ], t[ 3 ], t[ 4 ], t[ 5 ] );
				dtt = dtt.toString()
				dtt = dtt.substr( 0, 15 );
				return dtt;
			}
		}
	} );
};

// Add task in database.
let EditTaskInDB = ()=>{
	$( EditTaskFormID ).ajaxSubmit( {
		success: function( data ) {
			if ( data.status=='OK' ) {
				VueObj.t_detail = data.data;
				$( '#addProjectModelDiv' ).modal( 'hide' );
			} else {
			}
		},
		error: function( err, err2, err3 ) {
			alert( err.resposneText );
		}
	} );
};

/*
*  Delete attachments.
* */
let DeleteRefrence = ( Ref )=>{
	$.ajax( {
		method: 'post',
		data: 'ref=' + Ref,
		url: taskAjaxUrl + 'deleteTaskAttachment',
		success: function( data ) {
		}, error: function( err, err2, err3 ) {
			alert( err.responseText );
		}
	} );
};

let SaveCommentInDataBase = ()=>{
	$( CommentFormID ).ajaxSubmit( {
		success: function( data ) {
			if ( data=='OK' ) {
				LoadSingleTaskDetails( TaskClicked, true );
				$( CommentFormID )[ 0 ].reset()
			}
		},
		error: function( err, err2, err3 ) {
			alert( err.responseText );
		}
	} );
};