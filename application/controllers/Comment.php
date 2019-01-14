<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

Class Comment extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model( [
			'comment_model',
		] );
	}

	public function index() {
	}

	/**
	 * Method is used to handle ajax request.
	 */
	public function ajax() {
		header( 'Content-Type: application/json' );
		$response = [];

		if ( $this->input->get( 'addComment' ) !== null ) {
			## Add project request.
			$response = $this->comment_model->addComment( $this->input->post(), $_FILES );
		} else if ( $this->input->get( 'getComment' ) !== null ) {
			## Get full lists of tasks.
			$response = $this->task_model->getComment( $this->input->post() );
		}
		echo json_encode( $response );
		exit();
	}

	/**
	 * Show single task with detail option
	 */
	public function taskDetail() {
		$this->load->view( 'tasks/single_details.html' );
	}
}