<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Migration_comments extends CI_Migration {

	const Table = 'commentmodel';

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
			'comment'     => array(
				'type'    => 'longtext',
				'default' => null,
			),
			'module_name' => array(
				'type'       => 'varchar',
				'constraint' => 50,
				'default'    => null,
			),
			'module_ref'  => array(
				'type'       => 'INT',
				'constraint' => 11,
				'default'    => 0,
			),
			'added_by'    => array(
				'type'       => 'INT',
				'constraint' => 11,
				'default'    => 0,
			),
			'datetime'    => array(
				'type'    => 'datetime',
			),
		];

		$this->dbforge->add_field( $fields );
		$this->dbforge->add_key( 'id', true );
		$this->dbforge->create_table( self::Table, true );
	}

	/**
	 * Reverse Up method functions in down methods.
	 */
	public function down() {
		$this->dbforge->drop_table( self::Table, true );
		$dropColumns = [
			'id',
			'comment',
			'added_by',
			'datetime',
			'module_ref',
			'module_name',
		];
		$this->dbforge->drop_column( self::Table, $dropColumns );
	}

}