let TaskAddFromID = '#addTaskForm';
let VueObjLoadTaskVueRendering = {};
$( document ).ready( function() {
	// Add task in database.
	$( document ).on( 'click', '.saveTaskFormButton', function() {
		AddTaskInDB();
	} );

	// Redirect to other page
	$( document ).on( 'click', '.tasksTrs', function() {
		let TaskId = $( this ).attr( 'data-ProjectID' );
		window.location.href = SingleTaskUrl + TaskId;
	} );

	if ( Controller=='user' ) {
		// Load Task
		LoadTasks();
	}
} );

// Add task in database.
let AddTaskInDB = ()=>{
	$( TaskAddFromID ).ajaxSubmit( {
		success: function( data ) {
			console.log( data )
		},
		error: function( err, err2, err3 ) {
			alert( err.resposneText );
		}
	} );
};

/*
* Load task,get it form database.
* */
let LoadTasks = ( afterEdit )=>{
	let dataSending = {
		conditions: {
			project_id: SelectedProjectID,
		},
	};
	$.ajax( {
		method: 'post',
		url: taskAjaxUrl + 'getTask',
		data: dataSending,
		success: function( data ) {
			if ( afterEdit ) {
				VueObjLoadTaskVueRendering.tasks = data;
			} else {
				LoadTaskVueRendering( data );
			}
		},
		error: function( err, err2, err3 ) {
			alert( err.responseText );
		}
	} );
};

/*
* Load View
* */
let LoadTaskVueRendering = ( p_data )=>{
	VueObjLoadTaskVueRendering = new Vue( {
		el: '#taskListSection',
		delimiters: [ '${', '}' ],
		data: {
			tasks: p_data,
		}
	} );

};