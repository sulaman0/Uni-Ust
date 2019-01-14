<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class customFunctions extends CI_Model {

	/**
	 * @param      $var
	 * @param bool $debug_global_ar
	 *
	 * Method debug string, create logs.txt file.
	 */
	function debug( $var, $debug_global_ar = false ) {
		if ( is_array( $var ) || is_object( $var ) ) {
			$str = print_r( $var, true ) . PHP_EOL;
		} elseif ( is_null( $var ) ) {
			$str = "NULL" . PHP_EOL;
		} elseif ( is_bool( $var ) ) {
			if ( $var ) {
				$str = "TRUE" . PHP_EOL;
			} else {
				$str = "FALSE" . PHP_EOL;
			}
		} else {
			$str = $var . PHP_EOL;
		}

		$bt   = debug_backtrace();
		$file = $bt[0]["file"];
		$line = $bt[0]["line"];


		$str             .= PHP_EOL . "debug function in file" . PHP_EOL . $file . PHP_EOL;
		$debug_global_ar = (bool) $debug_global_ar;
		if ( $debug_global_ar ) {
			$str .= "GET: " . print_r( $_GET, true ) . PHP_EOL;
			$str .= "POST: " . print_r( $_POST, true ) . PHP_EOL;
			$str .= PHP_EOL . "Line # " . $line . PHP_EOL . "Time: " . date( 'Y-m-d H:i:s' );
			$str .= PHP_EOL . '================================== END Of Debug.' . PHP_EOL;
		}


		@file_put_contents( FCPATH . 'logs.txt', $str, FILE_APPEND );
	}

	/**
	 * @param $args
	 * @param $default
	 *
	 * @return array|string
	 *
	 * Sets arguments. This function is mostly used in methods of classes in Bindia library.
	 *
	 */
	function set_args( $args, $default ) {
		if ( ! is_array( $args ) ) {
			$args = str_replace( " = ", "=", $args );
			$args = str_replace( " =", "=", $args );
			$args = str_replace( " =", "=", $args );
			$args = str_replace( " =", "=", $args );
			$args = stripslashes( $args );
			parse_str( $args, $args );
		}
		if ( ! is_array( $args ) ) {
			return [];
		}
		$default = self::make_array( $default );
		if ( ! is_array( $default ) ) {
			return $default;
		}
		foreach ( $default as $k => $v ) {
			if ( ! isset( $args[ $k ] ) ) {
				$args[ $k ] = $v;
			}
		}
		if ( get_magic_quotes_gpc() ) {
			$args = array_map( "stripslashes", $args );
		}

		return $args;
	}


	/**
	 * @param        $arr
	 * @param string $sep
	 * @param bool   $considerLineBreaks
	 *
	 * @return array|mixed
	 *
	 * Convert string or json data into an array
	 */
	function make_array( $arr, $sep = ",", $considerLineBreaks = false ) {
		if ( is_string( $arr ) && trim( $arr ) == "" ) {
			return [];
		}
		if ( is_array( $arr ) ) {
			return $arr;
		}
		if ( self::is_json( $arr ) ) {
			$arr = json_decode( $arr, true );

			return $arr;
		}
		if ( $considerLineBreaks ) {
			$arr = str_replace( "\n", $sep, $arr );
		}
		//$arr = str_replace("'", "''", $arr);            // To avoid error MySQL insertion/update.
		$arr = explode( $sep, $arr );
		$arr = array_map( 'trim', $arr );
		$arr = array_filter( $arr );

		return $arr;
	}


	/**
	 * @param string $string
	 *
	 * @return bool
	 *
	 * Check if provided string is json or not.
	 */
	function is_json( $string ) {
		$json = json_decode( $string );

		return $json && $string != $json;
	}


	/**
	 * @param null   $date
	 * @param string $format
	 *
	 * @return false|string
	 *
	 * Get MySQL format Date and Time.
	 */
	public function mysql_datetime( $date = null, $format = "Y-m-d H:i:s" ) {
		if ( empty( $date ) ) {
			return date( $format );
		} else {
			return date( $format, strtotime( $date ) );
		}
	}

	/**
	 * @param $args
	 * Codeigniter file upload method this is used for multiple and single file upload.
	 *
	 * @return string
	 */
	public function move_upload_file_codeigniter( $args ) {
		$default = [
			'upload_path'   => CONTENT_PATH,
			'allowed_types' => 'gif|jpg|png|jpeg|docx|doc|xls',
			'max_size'      => 1000000,
			//			'max_width'     => 1024,
			//			'max_height'    => 768,
			'overwrite'     => 1,
			'inputName'     => '',
		];
		$args    = $this->set_args( $args, $default );
		if ( empty( $args['inputName'] ) ) {
			return 'Please input field name';
		}

		## unset input name because this not part of upload config file part.
		$inputName = $args['inputName'];
		unset( $args['inputName'] );

		## Load upload library.
		$this->load->library( 'upload', $args );

		$files  = $_FILES;
		$_FILES = [];
		for ( $i = 0; $i < count( $files[ $inputName ]['name'] ); $i ++ ) {

			if ( is_array( $files[ $inputName ]['name'] ) ) {
				## Multiple file upload codes.
				$_FILES['tmp']['name']     = $files[ $inputName ]['name'][ $i ];
				$_FILES['tmp']['type']     = $files[ $inputName ]['type'][ $i ];
				$_FILES['tmp']['tmp_name'] = $files[ $inputName ]['tmp_name'][ $i ];
				$_FILES['tmp']['error']    = $files[ $inputName ]['error'][ $i ];
				$_FILES['tmp']['size']     = $files[ $inputName ]['size'][ $i ];
			} else {
				## Single file upload codes.
				$_FILES['tmp']['name']     = $files[ $inputName ]['name'];
				$_FILES['tmp']['type']     = $files[ $inputName ]['type'];
				$_FILES['tmp']['tmp_name'] = $files[ $inputName ]['tmp_name'];
				$_FILES['tmp']['error']    = $files[ $inputName ]['error'];
				$_FILES['tmp']['size']     = $files[ $inputName ]['size'];
			}

			if ( ! $this->upload->do_upload( 'tmp' ) ) {
				self::debug( $this->upload->display_errors() );
			}
		}
		$_FILES = [];
	}

	/*
	 * Extract Project Info and then append to js file.
	 * */
	public function saveDataToJsFile() {
		## Load Models
		$newLine = ";\n";

		## Get User Info
		$js_content = "var StaffUsers=";
		$staffUser  = [];
		$response   = $this->user_model->get_users();
		foreach ( $response as $key => $value ) {
			$value->profile_url      = $this->user_model->get_user_profile_url( $value->id );
			$staffUser[ $value->id ] = $value;
		}
		$string     = json_encode( $staffUser );
		$js_content .= $string . $newLine;

		## Get project list
		$js_content  .= "var ProjectList=";
		$ProjectList = [];
		$response    = $this->project_model->projectList();
		foreach ( $response as $key => $value ) {
			$ProjectList[ $value->id ] = $value;
		}
		$string     = json_encode( $ProjectList );
		$js_content .= $string . $newLine;

		## Handle file
		if ( is_file( ProjectInfoJS ) ) {
			unlink( ProjectInfoJS );
		}
		if ( file_put_contents( ProjectInfoJS, $js_content ) === false ) {
			self::debug( "ProjectInfo.js not created!" );
		}

	}
}