<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Migration_projects extends CI_Migration {

	const ProjectTable = 'projects';

	/**
	 * Create Users Table.
	 */
	public function up() {
		## Add Columns to table.
		$fields = [
			'id'          => array(
				'type'           => 'INT',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			),
			'name'        => array(
				'type'       => 'varchar',
				'constraint' => 20,
				'after'      => 'id',
				'default'    => 0,
			),
			'description' => array(
				'type'       => 'varchar',
				'constraint' => 50,
				'default'    => 0,
			),
			'status'      => array(
				'type'       => 'tinyint',
				'constraint' => 1,
				'default'    => 1, ## 1 mean working, 2 mean done
			),
			'datetime'    => array(
				'type'       => 'datetime',
				'constraint' => 1,
				'default'    => 0,
			),
			'added_by'    => array(
				'type'       => 'int',
				'constraint' => 11,
				'default'    => 0,
			),
		];

		$this->dbforge->add_field( $fields );
		$this->dbforge->add_key( 'id', true );
		$this->dbforge->create_table( self::ProjectTable, true );
	}

	/**
	 * Reverse Up method functions in down methods.
	 */
	public function down() {
		$this->dbforge->drop_table( self::ProjectTable, true );
		$dropColumns = [
			'id',
			'name',
			'description',
			'datetime',
			'added_by',
		];
		$this->dbforge->drop_column( self::ProjectTable, $dropColumns );
	}

}