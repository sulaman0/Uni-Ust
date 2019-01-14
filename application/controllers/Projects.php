<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

Class Projects extends CI_Controller {

	public function __construct() {
		parent::__construct();
		## Load models
		$this->load->model( [
			'project_model',
		] );
	}

	/**
	 * Serve project view.
	 */
	public function index() {
		## Loading view.
		$this->load->view( 'projects/index.html' );
	}

	/**
	 * Method is used to handle ajax request.
	 */
	public function ajax() {
		header( 'Content-Type: application/json' );

		if ( $this->input->get( 'addEditProject' ) !== null ) {
			## Add project request.
			$this->customFunctions->debug( $this->input->post(), false );
			$response = $this->project_model->addEditProject( $this->input->post() );
		} else if ( $this->input->get( 'getProjectList' ) !== null ) {
			## Show list of projects
			$response = $this->project_model->projectList( $this->input->post() );
		} else if ( $this->input->get( 'deleteProject' ) !== null ) {
			## Delete Project
			$response = $this->project_model->deleteProject( $this->input->post() );
		} else if ( $this->input->get( 'saveSelectedProjectInSession' ) !== null ) {
			## Delete Project
			$response = $this->project_model->saveSelectedProjectInSession( $this->input->post() );
		}
		echo json_encode( $response );
		exit();
	}
}