<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

##$this->customFunctions->debug(CONTENT_PATH_USER,false);
class Project_model extends CI_Model {

	const table = 'projects';

	public function __construct() {
		parent::__construct();
	}

	/**
	 * @param $args
	 * Method is used to add a project in database.
	 *
	 * 0 Mean project is in working state.
	 * 1 Mean project is done.
	 *
	 *
	 * @return string
	 */
	public function addEditProject( $args ) {
		$default = [
			'name'        => '',
			'description' => '',
			'status'      => 0,
			'id'          => 0,
			'added_by'    => $this->user_model->get_current_user_id(),
			'datetime'    => $this->customFunctions->mysql_datetime(),
		];
		$args    = $this->customFunctions->set_args( $args, $default );
		if ( empty( $args['name'] ) ) {
			return 'Project name should not be empty';
		}

		if ( empty( $args['description'] ) ) {
			return 'Project description should not be empty';
		}

		$project_id = $args['id'];
		unset( $args['id'] );
		if ( empty( $project_id ) ) {

			$this->customFunctions->debug( $args, false );

			## Insert new record in database.
			$response = $this->db->insert( self::table, $args );

		} else {
			## update record in database.
			$response = $this->db->where( 'id', $project_id )->update( self::table, $args );
		}
		if ( $response ) {
			return 'OK';
		} else {
			return $this->db->error();
		}
	}

	/**
	 * @param string $args
	 * Method is used to Get projects list.
	 *
	 * @return mixed
	 */
	public function projectList( $args = '' ) {
		$default = [
			'mysql_cols' => '*',
		];
		$args    = $this->customFunctions->set_args( $args, $default );
		$query   = $this->db->select( $args['mysql_cols'] )->from( self::table )->get();
		$res     = $query->result();

		return $res;
	}

	/**
	 * @param string $args
	 * id : id of project.
	 *
	 * Method is used to delete the project
	 *
	 * @return string
	 */
	public function deleteProject( $args = '' ) {
		$default = [
			'id' => 0,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		$args['id'] = (int) $args['id'];

		## Send back if id missing.
		if ( empty( $args['id'] ) ) {
			return 'Project Id is invalid';
		}

		$response = $this->db->where( 'id', $args['id'] )->delete( self::table );
		if ( $response ) {
			return 'OK';
		} else {
			$this->db->error();
		}
	}

	/**
	 * @param string $args
	 * Set project id in session & in database.
	 * If session is restore then we will get data from database.
	 *
	 * @return string
	 */
	public function saveSelectedProjectInSession( $args = '' ) {
		$default    = [
			'id' => 0,
		];
		$args       = $this->customFunctions->set_args( $args, $default );
		$args['id'] = (int) $args['id'];

		$this->customFunctions->set_args( $args['id'] );
		## Store data in session.
		$this->session->set_userdata( 'ProjectID', $args['id'] );

		## Load option model.
		$this->load->model( 'option_model' );
		$option_ar = [
			'name'  => $this->user_model->get_current_user_id() . '_selected_project',
			'value' => $args['id'],
		];
		## save data in option table.
		$response = $this->option_model->addEditOption( $option_ar );
		if ( $response == 'OK' ) {
			return 'OK';
		} else {
			return $this->db->error();
		}
	}

	/**
	 * @return mixed
	 * return selected id of project which is in session
	 */
	public function getSelectedProjectWhichIsInSession() {
		$response = $this->session->userdata( 'ProjectID' );
		## search in database.
		if ( empty( $response ) ) {
			## Load option model
			$this->load->model( 'option_model' );

			## create array to get data
			$ar       = [
				'conditions' => [
					'name' => $this->user_model->get_current_user_id() . '_selected_project',
				],
			];
			$response = $this->option_model->getOption( $ar );

			if ( ! empty( $response ) ) {
				$response = $response[0]->value;
			} else {
				$response = 1;
			}
		}

		return $response;
	}

}