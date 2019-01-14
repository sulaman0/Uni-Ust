<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Migration_tasks extends CI_Migration {

	const ProjectTable = 'tasks';

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
			'project_id'  => array(
				'type'       => 'INT',
				'constraint' => 11,
				'unsigned'   => true,
				'default'    => 0,
			),
			'name'        => array(
				'type'       => 'varchar',
				'constraint' => 200,
				'default'    => 0,
			),
			'status'      => array(
				'type'       => 'tinyint',
				'constraint' => 1,
				'unsigned'   => true,
				'default'    => 1, ## 1 is pending, 2 mean in progress, 3 done
			),
			'description' => array(
				'type'    => 'longtext',
				'default' => '',
			),
			'type'        => array(
				'type'       => 'tinyint',
				'constraint' => 1,
				'default'    => 1, ## 1 mean bug, 2 mean newFeature, 3 Improvement
			),
			'assign_to'   => array(
				'type'       => 'varchar',
				'default'    => 0,
				'constraint' => 250,
			),
			'datetime'    => array(
				'type' => 'datetime',
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
			'type',
			'project_id',
			'assign_to',
			'description',
			'datetime',
			'added_by',
		];
		$this->dbforge->drop_column( self::ProjectTable, $dropColumns );
	}

}