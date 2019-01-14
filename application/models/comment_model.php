<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class comment_model extends CI_Model {

	const Table = 'comments';
	const CommentPath = CONTENT_PATH . 'comments' . DIRECTORY_SEPARATOR;


	/**
	 * @param $args
	 *
	 * Add comment in database.
	 *
	 * @return string
	 */
	public function addComment( $args, $file = [] ) {
		$default    = [
			'id'          => '',
			'comment'     => '',
			'module_name' => '',
			'module_ref'  => '',
			'added_by'    => $this->user_model->get_current_user_id(),
			'datetime'    => $this->customFunctions->mysql_datetime(),
			'commentFile' => '',
		];
		$args       = $this->customFunctions->set_args( $args, $default );
		$comment_id = (int) $args['id'];

		// unset uneccessary values.
		unset( $args['id'] );
		unset( $args['commentFile'] );

		if ( empty( $comment_id ) ) {
			## Insert
			$response = $this->db->insert( self::Table, $args );
		} else {
			## Update
			$response = $this->db->where( 'id', $args['id'] )->update( self::Table, $args );
		}

		## file uplaod against comment
		if ( ! empty( $file ) ) {
			$uploadfileAr = [
				'inputName'  => array_keys( $file )[0],
				## if task id exits then upload to same directory else upload in insert record id
				'comment_id' => ! empty( $comment_id ) ? $comment_id : $this->db->insert_id(),
			];
			self::CommentFileUPloadManager( $uploadfileAr );
		}

		if ( empty( $response ) ) {
			return $this->db->error();
		} else {
			return 'OK';
		}
	}

	/**
	 * @param $args
	 * Get comments from database.
	 *
	 * @return mixed
	 */
	public function getComment( $args ) {
		$default = [
			'conditions' => [],
		];
		$args    = $this->customFunctions->set_args( $args, $default );
		$this->db->select( '*' )->from( self::Table );

		## implement where conditions.
		if ( ! empty( $args['conditions'] ) ) {
			$this->db->where( $args['conditions'] );
		}

		$response = $this->db->get();

		$rowObj = $response->result();
		foreach ( $rowObj as $key => $item ) {
			$item->attachment = self::getCommentAttachment( $item->id );
		}

		return $rowObj;
	}

	/**
	 * @param $args
	 * Upload files in specific dir.
	 */
	public function CommentFileUPloadManager( $args ) {
		$default = [
			'inputName'   => '',
			'upload_path' => CONTENT_PATH,
			'comment_id'  => 0,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		$path = self::CommentPath;


		## Create task folder if not created yet.
		if ( ! is_dir( $path ) ) {
			mkdir( $path );
		}

		## Update path
		$path .= md5( $args['comment_id'] );
		if ( ! is_dir( $path ) ) {
			mkdir( $path );
		}

		## update path
		$args['upload_path'] = $path;

		## Unset task id.
		unset( $args['comment_id'] );
		$this->customFunctions->move_upload_file_codeigniter( $args );
	}


	/**
	 * @param $comment_id
	 * Get attachments which attached to specific comment
	 *
	 * @return mixed
	 */
	public function getCommentAttachment( $comment_id ) {
		$path = self::CommentPath . md5( $comment_id );
		$this->load->model( 'FilesHandler' );

		return $this->FilesHandler->getFilesNames( $path, true );

	}
}