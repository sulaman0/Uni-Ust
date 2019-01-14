<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

Class Tasks extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model( [
			'task_model',
		] );
		$this->customFunctions->saveDataToJsFile();
	}

	public function index() {
		$this->load->view( 'index.html' );
	}

	/**
	 * Method is used to handle ajax request.
	 */
	public function ajax() {
		header( 'Content-Type: application/json' );
		$response = [];

		if ( $this->input->get( 'addTask' ) !== null ) {
			## Add project request.
			$response = $this->task_model->addEditTask( $this->input->post(), $_FILES );
		} else if ( $this->input->get( 'getTask' ) !== null ) {
			## Get full lists of tasks.
			$response = $this->task_model->getTaskList( $this->input->post() );
		} else if ( $this->input->get( 'singleTask' ) !== null ) {
			## Loading single task
			if ( $this->input->post()['conditions']['id'] <= $this->task_model->Counts()['total_tasks'] ) {
				$response = $this->task_model->getTaskList( $this->input->post() );
			} else {
				$_POST['conditions']['id'] = $this->task_model->Counts()['total_tasks'];
				$response                  = $this->task_model->getTaskList( $this->input->post() );
			}
		} else if ( $this->input->get( 'deleteTaskAttachment' ) !== null ) {
			$this->task_model->deleteTaskAttachment($this->input->post());
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