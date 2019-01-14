<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

Class Task_model extends CI_Model {

	const Table = 'tasks';
	const TaskPath = CONTENT_PATH . 'tasks';

	/**
	 * /**
	 * Task_model constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @param string $args
	 * Used to add or edit task.
	 *
	 * @return string
	 */
	public function addEditTask( $args = '', $files = '' ) {
		$default = [
			'id'          => '',
			'name'        => '',
			'project_id'  => '',
			'type'        => '', ## 1 mean bug, 2 mean newFeature, 3 Improvement
			'description' => '',
			'assign_to'   => '',
			'datetime'    => $this->customFunctions->mysql_datetime(),
			'added_by'    => $this->user_model->get_current_user_id(),
			'file_upload' => '',
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		$task_id = (int) $args['id'];

		## Unset id from array.
		unset( $args['id'] );
		unset( $args['file_upload'] );

		## Make assign to into comma base value.
		$args['assign_to'] = implode( ',', $args['assign_to'] );

		if ( empty( $task_id ) ) {
			## Insert record in database.
			$response = $this->db->insert( self::Table, $args );
		} else {
			## update record in database.
			$response = $this->db->where( 'id', $task_id )->update( self::Table, $args );
		}

		## return response.
		if ( $response ) {
			if ( ! empty( $files ) ) {
				$uploadfileAr = [
					'inputName' => array_keys( $files )[0],
					## if task id exits then upload to same directory else upload in insert record id
					'task_id'   => ! empty( $task_id ) ? $task_id : $this->db->insert_id(),
				];
				self::taskFileUPloadManager( $uploadfileAr );
			}

			if ( empty( $task_id ) ) {
				$res = 'OK';
			} else {
				$ar  = [
					'getAttachment' => $task_id,
					'conditions'    => [
						'id' => $task_id,
					],
				];
				$res = $tmp_ar = [
					'status' => 'OK',
					'data'   => self::getTaskList( $ar ),
				];
			}

			return $res;
		} else {
			return $this->db->error();
		}
	}

	/**
	 * @param $args
	 * Upload files in specific dir.
	 */
	public function taskFileUPloadManager( $args ) {
		$default = [
			'inputName'   => '',
			'upload_path' => CONTENT_PATH,
			'task_id'     => 0,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		$path = CONTENT_PATH . 'tasks/';

		## Create task folder if not created yet.
		if ( ! is_dir( $path ) ) {
			mkdir( $path );
		}

		## Update path
		$path .= md5( $args['task_id'] );
		if ( ! is_dir( $path ) ) {
			mkdir( $path );
		}

		## update path
		$args['upload_path'] = $path;

		## Unset task id.
		unset( $args['task_id'] );
		$this->customFunctions->move_upload_file_codeigniter( $args );
	}

	/**
	 * @param $args
	 *
	 * Get Task list
	 *
	 * @return mixed
	 */
	public function getTaskList( $args ) {
		$default = [
			'mysql_cols'    => '*',
			'conditions'    => [],
			'getAttachment' => '', ## task id which attachment needs.
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## Select from table.
		$this->db->select( $args['mysql_cols'] )->from( self::Table );

		if ( ! empty( $args['conditions'] ) ) {
			$this->db->where( $args['conditions'] );
		}

		## Make query
		$response = $this->db->get();
		if ( empty( $args['getAttachment'] ) ) {
			## Only return tasks.
			$response = $response->result_array();

			return $response;
		} else {
			## Load task model for fetching comments which is aginst the task
			$this->load->model( 'comment_model' );

			## Task attachments attaching with the object
			$TaskAttachments   = self::getTaskAttachment( $args['getAttachment'] );
			$n_o               = new StdClass();
			$n_o->res          = $response->result_array();
			$n_o->attachments  = $TaskAttachments;
			$comment_condition = [
				'conditions' => [
					'module_ref'  => $args['getAttachment'],
					'module_name' => 'task',
				],
			];
			$n_o->comments     = $this->comment_model->getComment( $comment_condition );

			return $n_o;
		}
	}

	/**
	 * @param $task_id
	 * Get attachements which attached to specific task
	 *
	 * @return mixed
	 */
	public function getTaskAttachment( $task_id ) {
		$path = self::TaskPath . DIRECTORY_SEPARATOR . md5( $task_id );
		$this->load->model( 'FilesHandler' );

		return $this->FilesHandler->getFilesNames( $path, true );
	}

	/**
	 * Count data form task tables.
	 *
	 * @return mixed
	 */
	public function Counts() {
		$response['total_tasks'] = $this->db->select( '*' )->from( self::Table )->count_all_results();
		foreach ( TaskTypes as $key => $value ) {
			if ( $value == 'New Feature' ) {
				$response['new_feature'] = $this->db->select( '*' )
				                                    ->from( self::Table )
				                                    ->where( 'type', $key )
				                                    ->count_all_results();
			} else {
				$response[ $value ] = $this->db->select( '*' )
				                               ->from( self::Table )
				                               ->where( 'type', $key )
				                               ->count_all_results();
			}
		}

		return $response;
	}


	/**
	 * @param $ref
	 *  Delete file
	 */
	public function deleteTaskAttachment( $args ) {
		$defautl = [
			'ref' => '',
		];
		$args    = $this->customFunctions->set_args( $args, $defautl );
		if ( ! empty( $args['ref'] ) ) {
			$ref = strtr( $args['ref'], [ BASEURL => FCPATH ] );
		}
		if ( is_file( $ref ) ) {
			## replace url from path
			unlink( $ref );
		}
	}
}