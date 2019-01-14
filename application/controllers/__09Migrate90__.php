<?php

class __09Migrate90__ extends CI_Controller {

	public function index() {
		$this->load->library( 'migration' );

		if ( $this->migration->current() === false ) {
			show_error( $this->migration->error_string() );
		} else {
			echo '<h1> Migration runs Successfully. </h1>';
		}
	}

}