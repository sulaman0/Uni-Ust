let Success = ( Message, WherePut )=>{
	$html = '<div class="alert alert-info alert-dismissible">\n' +
	        '            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>\n' +
	        '            <h4><i class="icon fa fa-info"></i> Alert!</h4>\n' +
	        Message
	        + '        </div>';

	$( WherePut ).html( $html );
};

let Warning = ( Message, WherePut )=>{
	$html = '<div class="alert alert-danger alert-dismissible">\n' +
	        '            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>\n' +
	        '            <h4><i class="icon fa fa-ban"></i> Alert!</h4>\n' +
	        Message
	        + '        </div>';

	$( WherePut ).html( $html );
};