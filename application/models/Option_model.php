<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

##$this->customFunctions->debug(CONTENT_PATH_USER,false);
class Option_model extends CI_Model {

	const table = 'options';

	public function __construct() {
		parent::__construct();

	}

	/**
	 * @param $args
	 *
	 * Option is used to save etc data in table.
	 *
	 * @return string
	 */
	public function addEditOption( $args ) {
		$default = [
			'name'  => '',
			'value' => '',
			'id'    => 0,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## if name is missing.
		if ( empty( $args['name'] ) ) {
			return 'Name is empty';
		}

		## if value is missing.
		if ( empty( $args['value'] ) ) {
			return 'value is empty';
		}

		## Unset Id.
		$option_id = $args['id'];
		unset( $args['id'] );


		## validate if we have same name value then update the record.
		$validate_ar = [
			'conditions' => [
				'name' => $args['name'],
			],
		];
		$response    = self::getOption( $validate_ar );

		if ( ! empty( $response ) ) {
			## If we found same name record then update get id of option table.
			$option_id = $response[0]->id;
		}

		## deciding what to do? Update or Insert.
		if ( empty( $option_id ) ) {
			## Insert new record in database.
			$response = $this->db->insert( self::table, $args );
		} else {
			## update record in database.
			$response = $this->db->where( 'id', $option_id )->update( self::table, $args );
		}

		## Send response.
		if ( $response ) {
			return 'OK';
		} else {
			return $this->db->error();
		}
	}

	/**
	 * @param string $args
	 * id : id of project.
	 *
	 * Method is used to delete the option table record
	 *
	 * @return string
	 */
	public function deleteOption( $args = '' ) {
		$default = [
			'id' => 0,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		$args['id'] = (int) $args['id'];

		## Send back if id missing.
		if ( empty( $args['id'] ) ) {
			return 'Id is invalid';
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
	 * Method is used to Get projects list.
	 *
	 * @return mixed
	 */
	public function getOption( $args = '' ) {
		$default = [
			'mysql_cols' => '*',
			'conditions' => [],
		];
		$args    = $this->customFunctions->set_args( $args, $default );
		$this->db->select( $args['mysql_cols'] )->from( self::table );

		## Implementing where conditions.
		foreach ( $args['conditions'] as $Key => $value ) {
			if ( empty( $value ) ) {
				unset( $args['conditions'][ $Key ] );
			} else {
				$this->db->where( $Key, $value );
			}
		}

		$query = $this->db->get();
		$res   = $query->result();

		return $res;
	}

}