<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

##$this->customFunctions->debug(CONTENT_PATH_USER,false);
class UrlHandler extends CI_Model {

	public function get_file_url( $filePath, $auth = false ) {
		## file path required
		if ( empty( $filePath ) ) {
			return 'file path is required';
		}

		## Load encrypt library
		$this->load->library( 'encrypt' );

		$url = BASEURL;

		if ( $auth ) {
			$url .= 'file=' . URLHANDER_KEY . $this->encrypt->encode( $filePath );
		} else {
			$url .= 'file=' . $this->encrypt->encode( $filePath );
		}
		## get extension
		$ext = pathinfo( $filePath, PATHINFO_EXTENSION );
		$url = '.' . $ext;

		return $url;
	}
}