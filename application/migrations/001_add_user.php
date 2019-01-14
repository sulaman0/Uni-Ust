<?php

defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Migration_Add_user extends CI_Migration {

	const UsersTable = 'users';
	const DataBaseName = 'ust';

	/**
	 * Create Users Table.
	 */
	public function up() {
		## Add Columns to table.
		$fields = [
			'id'       => array(
				'type'           => 'INT',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			),
			'active'   => array(
				'type'       => 'tinyint',
				'constraint' => 1,
				'after'      => 'id',
				'default'    => 1,
			),
			'admin'    => array(
				'type'       => 'tinyint',
				'constraint' => 1,
				'after'      => 'active',
				'default'    => 0,
			),
			'username' => array(
				'type'       => 'VARCHAR',
				'constraint' => 50,
			),
			'email'    => array(
				'type'       => 'varchar',
				'constraint' => 50,
			),
			'gender'   => array(
				'type'       => 'tinyint',
				'constraint' => 1,
				'default'    => 1, ## 1 for male and 0 for fe-male.
			),
			'password' => array(
				'type'       => 'varchar',
				'constraint' => 250,
			),
			'address'  => array(
				'type'       => 'varchar',
				'constraint' => 50,
			),
		];
		$this->dbforge->add_field( $fields );
		$this->dbforge->add_key( 'id', true );
		$this->dbforge->create_table( self::UsersTable, true );
	}

	/**
	 * Reverse Up method functions in down methods.
	 */
	public function down() {
		$this->dbforge->drop_table( self::UsersTable, true );
		$dropColumns = [
			'id',
			'active',
			'admin',
			'username',
			'email',
			'gender',
			'password',
			'address',
		];
		$this->dbforge->drop_column( self::UsersTable, $dropColumns );
	}
}