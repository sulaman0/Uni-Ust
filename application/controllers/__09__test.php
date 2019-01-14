<?php

defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );


class __09__test extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model( [
			'task_model',
		] );

		## task model test
		$ar = [
			'id'          => '0',
			'name'        => 't_1',
			'project_id'  => '11',
			'type'        => 'bug',
			'description' => 'this is task one and project 1',
			'assign_to'   => '1,3,09,15,938,039,89,90',
		];
		echo '<pre>';
		print_r( $this->task_model->addEditTask( $ar ) );
	}


	public function index() {
	}

}