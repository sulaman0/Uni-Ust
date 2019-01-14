<?php if ( ! defined( 'BASEPATH' ) ) {
	exit( 'No direct script access allowed' );
}

class MY_Loader extends CI_Loader {

	public function __construct() {
		parent::__construct();
	}


	public function view( $template, $data = array(), $return = false ) {
		$CI =& get_instance();

		## Create Content Folder if not found.
		if ( ! is_dir( CONTENT_PATH ) ) {
			mkdir( CONTENT_PATH );
		}

		## Serve Login page if user is not logged in.
		$ignoreClass  = [
			'user',
		];
		$ignoreMethod = [
			'login',
			'index',
			'register'
		];

		if ( ( ! $CI->user_model->is_logged_in() ) && ( ! in_array( $CI->router->fetch_class(), $ignoreClass ) || ! in_array( $CI->router->fetch_method(), $ignoreMethod ) ) ) {
			redirect( base_url() );
		}

		try {
			$CI->twig->addGlobal( 'site_name', 'UST' );
			$CI->twig->addGlobal( 'assets_path', FCPATH . 'dist' . DIRECTORY_SEPARATOR );
			$CI->twig->addGlobal( 'class_name', $CI->router->fetch_class() );
			$CI->twig->addGlobal( 'method_name', $CI->router->fetch_method() );
			$CI->twig->addGlobal( 'get', $CI->input->get() );
			$CI->twig->addGlobal( 'post', $CI->input->post() );
			$CI->twig->addGlobal( 'project_list', $CI->project_model->projectList( 'mysql_cols=id,name' ) );
			$CI->twig->addGlobal( 'selected_Project', $CI->project_model->getSelectedProjectWhichIsInSession() );
			$CI->twig->addGlobal( 'current_user', $CI->user_model->get_current_user() );
			$CI->twig->addGlobal( 'task_types', TaskTypes );
			$CI->twig->addGlobal( 'task_status', TaskStatus );
			$CI->twig->addGlobal( 'users_list', $CI->user_model->get_users( 'mysql_cols=username,id' ) );
			$CI->twig->addGlobal( 'tasks_count', $CI->task_model->Counts() );
			$CI->twig->addGlobal( 'tasks_clicked_id', $CI->uri->segment( 3 ) );
			$CI->twig->add_function( [ 'time', 'phpversion', 'md5_file', 'number_format' ] );
			$output = $CI->twig->render( $template, $data );
		} catch ( Exception $e ) {
			show_error( htmlspecialchars_decode( $e->getMessage() ), 500, 'Twig Exception' );
		}

		// Return the output if the return value is TRUE.
		if ( $return === true ) {
			return $output;
		}

		// Otherwise append to output just like a view.
		$CI->output->append_output( $output );
	}
}