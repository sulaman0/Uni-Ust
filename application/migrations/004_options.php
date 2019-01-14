<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Migration_options extends CI_Migration {

	const Table = 'options';

	/**
	 * Create Users Table.
	 */
	public function up() {
		## Add Columns to table.
		$fields = [
			'id'    => array(
				'type'           => 'INT',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			),
			'name'  => array(
				'type'       => 'varchar',
				'constraint' => 100,
				'default'    => null,
			),
			'value' => array(
				'type'       => 'varchar',
				'constraint' => 250,
				'default'    => null,
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
			'name',
			'value',
		];
		$this->dbforge->drop_column( self::Table, $dropColumns );
	}

}