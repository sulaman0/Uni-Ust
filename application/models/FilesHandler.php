<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

##$this->customFunctions->debug(CONTENT_PATH_USER,false);
class FilesHandler extends CI_Model {

	public function __construct() {
	}

	/**
	 * Delete directory with its content
	 * RecursiveDirectoryIterator is used to make directory recursive.
	 */
	public function delete_directory( $directory ) {
		## If their is not directory
		if ( ! is_dir( $directory ) ) {
			return;
		}

		$it    = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );

		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				rmdir( $file->getRealPath() );
			} else {
				unlink( $file->getRealPath() );
			}
		}

		rmdir( $directory );
	}

	/**
	 * @param      $source_dir
	 * @param bool $getUrl
	 *
	 * get file name which is in dir.
	 *
	 * @return array
	 */
	public function getFilesNames( $source_dir, $getUrl = false ) {
		## Load directory helper to load all directory.
		$this->load->helper( 'directory' );

		$list = directory_map( $source_dir );

		## if found no attachments.
		if ( empty( $list ) ) {
			return [];
		}

		## Running loop
		foreach ( $list as $key => $value ) {
			## Only files will listed.
			if ( ! is_array( $value ) && is_file( $source_dir . DIRECTORY_SEPARATOR . $value ) ) {
				$list[ $key ] = $source_dir . DIRECTORY_SEPARATOR . $value;
			}
		}

		if ( $getUrl ) {
			return self::getFilesUrl( $list );
		}
	}

	/**
	 * @param $fileAr
	 *
	 * Return file url by giving full path of file.
	 *
	 * @return array
	 */
	public function getFilesUrl( $fileAr ) {
		$tmp_ar = [];
		foreach ( $fileAr as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$tmp_ar[ $key ] = strtr( $value, [ FCPATH => BASEURL ] );
			}
		}

		return $tmp_ar;
	}
}