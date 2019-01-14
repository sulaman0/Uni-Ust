<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

##$this->customFunctions->debug(CONTENT_PATH_USER,false);
class User_model extends CI_Model {

	const table = 'users';
	const LoginCookieName = '__09UST90__Login_';
	var $CONTENT_PATH_OF_LOGIN_USER = '';

	/**
	 * User_model constructor.
	 */
	public function __construct() {
		parent::__construct();

		## Implementing DB migrations.
		$this->load->model( 'customFunctions' );
		## Loading Libraries
		$this->load->library( 'encrypt' );

		## Create current user content upload directory
		$this->create_and_return_current_user_content_path();
	}

	/**
	 * Create current user logged in directory and return path of this directory
	 *
	 * @return string
	 */
	public function create_and_return_current_user_content_path( $UserId = '', $createDir = true, $prev_ref = CONTENT_PATH ) {
		if ( empty( $UserId ) ) {
			$UserId = $this->get_current_user_id();
		}
		$UserId    = (int) $UserId;
		$createDir = (bool) $createDir;

		$this->CONTENT_PATH_OF_LOGIN_USER = $prev_ref . md5( $UserId );
		$this->CONTENT_PATH_OF_LOGIN_USER .= '-' . $UserId . '--';
		if ( $createDir ) {
			if ( ! is_dir( $this->CONTENT_PATH_OF_LOGIN_USER ) ) {
				mkdir( $this->CONTENT_PATH_OF_LOGIN_USER );
			}
		}

		return $this->CONTENT_PATH_OF_LOGIN_USER;
	}

	/**
	 * Validate current user is logged in or not.
	 *
	 * @return mixed
	 */
	public function is_logged_in() {
		## If cookie is saved by remember me then it will login using cookie
		if ( ! $this->session->has_userdata( self::LoginCookieName ) && get_cookie( self::LoginCookieName ) ) {
			$this->login( get_cookie( self::LoginCookieName ) );
		}

		return $this->session->has_userdata( self::LoginCookieName );
	}

	/**
	 * @param string $args
	 *
	 * @return string
	 *
	 * Used to login for admin user.
	 */
	public function login( $args = '' ) {
		$default = [
			'email'       => '',
			'password'    => '',
			'remember_me' => 0,
			'id'          => 0,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## If cookie found then in computer then logged in user to his account.
		if ( ! empty( $args['id'] ) ) {
			$query = $this->db->select( '*' )->from( self::table )->where( 'id', $args )->where( 'active', 1 )->get();

			## Check if user is available in database
			if ( $query->result_id->num_rows == 0 ) {
				return 'Invalid username dd';
			}

			## Getting data from database
			$row = $query->row();
		} else {
			$query = $this->db->select( '*' )
			                  ->from( self::table )
			                  ->where( 'email', $args['email'] )
			                  ->where( 'active', 1 )
			                  ->get();

			## Check if user is available in database
			if ( $query->result_id->num_rows == 0 ) {
				return 'Invalid Email';
			}

			## Getting data from database
			$row = $query->row();
			if ( $row->password != md5( $args['password'] ) ) {
				return 'Invalid password';
			}
		}

		## Setting session data after login.
		$this->session->set_userdata( self::LoginCookieName, $row );

		## Set cookie if remember me selected
		if ( $args['remember_me'] == 1 ) {
			$args['remember_me'] = (int) $args['remember_me'];
			$this->input->set_cookie( [
				'name'   => self::LoginCookieName,
				'value'  => $row->id,
				'expire' => 1209600,                ## Two weeks
			] );
		}

		return 'OK';
	}

	/**
	 * @param $user_id
	 *
	 * Logs bad attempt for login and if attempts reach to 5 then deactivate user account.
	 */
	private function add_bad_attempt( $user_id ) {
		$user_id = (int) $user_id;
		$this->db->set( 'bad_attempt', 'bad_attempt+1', false );
		$this->db->where( 'id', $user_id )->update( self::table );

		$query    = $this->db->select( 'bad_attempt' )->from( self::table )->where( 'id', $user_id )->get();
		$attempts = $query->row()->bad_attempt;
		if ( $attempts >= 5 ) {
			$this->db->set( 'active', 'No' );
			$this->db->where( 'id', $user_id )->update( self::table );
		}
	}

	/**
	 * User will logged out.
	 */
	public function logout() {
		if ( get_cookie( self::LoginCookieName ) ) {
			delete_cookie( self::LoginCookieName );
		}

		$this->session->unset_userdata( self::LoginCookieName );

		redirect( base_url(), 'refresh' );
	}

	/**
	 * @Author SULAMANA KHAN <Sulaman@sulaman.pk>
	 *
	 * @param $email
	 *
	 * Checking Email existance in database.
	 *
	 * @return bool
	 */
	public function email_check( $email, $others = [] ) {
		## This array manage operations by passing parameters.
		$default = [
			## do not check this user email.
			## e.g select * from admin where email = 'email' and id <> 'id'
			## this parameter is used to for last check in above query
			'not_check_record_of_this_user' => 0,
		];

		$args = $this->customFunctions->set_args( $others, $default );

		## Validate an email
		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return 'Email is invalid';
		}

		$this->db->select( '*' )->from( self::table )->where( 'email', $email );
		if ( ! empty( $args['not_check_record_of_this_user'] ) ) {
			$this->db->where( 'id !=', $args['not_check_record_of_this_user'] );
		}

		$query = $this->db->get();
		if ( $query->num_rows() > 0 ) {
			return $email . " e-mail already occupied, \n Please try some different e-mail.";
		} else {
			return 1;
		}

	}

	/**
	 * @Author SULAMANA KHAN <Sulaman@sulaman.pk>
	 *
	 * @param $args
	 *
	 * When Staff user request for forget password.
	 *
	 * 1. Validate email.
	 * 2. Create Encrypted URL on base of Validated email.
	 * 3. Sent Email to Staff User.
	 *
	 * @return string|void
	 */
	public function forget_request( $args ) {
		$default = [
			'email' => '',
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## Checking email exist in database or not.
		$response = (bool) $this->email_check( $args['email'] );

		## If we found email in database then sent him email.
		if ( ! $response ) {
			return 'No email found in system';
		}

		##Sent Email to User.
		$email_params = [
			'email'  => $args['email'],
			'action' => 'forget-password',
			'fields' => [
				'reset_password_link' => base_url() . 'user/update_request.html?password-update-request=true&secret=' . $this->encrypt->encode( $args['email'] ),
			],
		];

		return $email_params['fields']['reset_password_link'];

		return $this->sent_email( $email_params );
	}

	/**
	 * @Author SULAMANA KHAN <Sulaman@sulaman.pk>
	 *
	 * @param $args
	 *
	 * Update password
	 * 1. Validate email.
	 * 2. If email validate successfully then update password.
	 *
	 * Basic USE :
	 *  This method is use when staff user update his password via forget password procedure.
	 *
	 * @return array|string
	 */
	public function udpate_request( $args ) {
		$default = [
			'password'  => '',
			'rpassword' => '',
			'secret'    => '',
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## Load encryption Library.
		$this->load->library( 'encrypt' );

		## decrypt email
		$email = $this->encrypt->decode( $args['secret'] );

		## Validate email
		$response = $this->email_check( $email );

		if ( ! $response ) {
			return 'Please request again for forget password';
		}

		## Set array which sent to update profile method.
		$update_profile_params = [
			'on_which_base' => [
				'email' => $email,
			],
			'update_field'  => [
				'password'  => $args['password'],
				'rpassword' => $args['rpassword'],
			],
		];

		$response = $this->update_profile( $update_profile_params );
		if ( $response <> 'OK' ) {
			return 'Please contact Admin.';
		} else {
			return 'OK';
		}
	}

	/**
	 * @Author SULAMANA KHAN <Sulaman@sulaman.pk>
	 *
	 * @param $args
	 *
	 * This method is use to update profile of staff user.
	 *
	 * @return string
	 */
	public function update_profile( $args ) {
		$default = [
			## on which base element contain table's column name and values on which bases we update user profile.
			## F.x : where email = 'sulaman@sulaman.pk'
			'on_which_base' => [],

			## Which field which you want to update just right their field name or value against this.
			## F.x password = 'symi'
			'update_field'  => [
				'password'  => '',
				'rpassword' => '',
				'username'  => '',
				'email'     => '',
				'address'   => '',
			],
		];

		$args = $this->customFunctions->set_args( $args, $default );

		## Email Validation
		if ( ! empty( $args['update_field']['email'] ) ) {
			## Check this email is took by other user or not
			$email_validation_array    = [
				'not_check_record_of_this_user' => $this->get_current_user_id(),
			];
			$email_validation_response = $this->email_check( $args['update_field']['email'], $email_validation_array );
			if ( substr( $email_validation_response, 0, 1 ) <> 1 ) {
				return $email_validation_response;
			}
		}

		## Password Check
		if ( ! empty( $args['update_field']['password'] ) ) {
			$password_validation_array    = [
				'password'  => $args['update_field']['password'],
				'rpassword' => $args['update_field']['rpassword'],
			];
			$passowrd_validation_response = $this->validate_user_password_of_same_user( $password_validation_array );
			if ( substr( $passowrd_validation_response, 0, 2 ) <> 'OK' ) {
				return $passowrd_validation_response;
			}
			$args['update_field']['password'] = substr( $passowrd_validation_response, 2 );
		}

		## Unset rpassword (Confirm Password field)
		unset( $args['update_field']['rpassword'] );

		## Update user data.
		$query = $this->db->where( $args['on_which_base'] )->update( self::table, $args['update_field'] );

		## Verify data is update successfully or not.
		if ( $query ) {
			return 'OK';
		} else {
			return $this->db->error();
		}
	}

	/*
		 * @Author SULAMANA KHAN <Sulaman@sulaman.pk>
		 *
		 * Set Email to User on different basis
		 * */
	public function sent_email( $args ) {

		$ci =& get_instance();

		$config  ['protocol']  = 'smtp';
		$config ['smtp_host']  = '82.163.77.195';
		$config ['smtp_port']  = '25';
		$config ['_smtp_auth'] = true;
		$config ['smtp_user']  = 'developer@bindia.dk';
		$config ['smtp_pass']  = '&NcgQqLdDlSA';
		$config  ['mailtype']  = 'html';
		$config  ['charset']   = 'iso-8859-1';
		$ci->email->initialize( $config );
		$this->email->from( 'symikhan70@gmail.com', 'SYMI' );
		$this->email->to( 'symikhan8@gmail.com' );
		$this->email->cc( 'symikhan8@gmail.com' );
		$this->email->bcc( 'them@their-example.com' );

		$this->email->subject( 'Email Test' );
		$this->email->message( 'Testing the email class.' );

		$this->email->send();

		die();

		$default = [
			'email'  => '',
			'action' => '',
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## forget password request.
		if ( $args['action'] == 'forget-password' ) {
		}
	}

	/**
	 * @return bool
	 *
	 * Check if current user is an Administrator or not.
	 */
	public function is_administrator() {
		if ( ! $this->is_logged_in() ) {
			return false;
		}

		$data = $this->session->userdata( self::LoginCookieName );

		return strtoupper( $data->is_admin ) == 'YES';
	}

	/**
	 * @return int
	 *
	 * Get ID of logged in user
	 */
	public function get_current_user_id() {

		if ( ! $this->is_logged_in() ) {
			return 0;
		}
		$data = $this->session->userdata( self::LoginCookieName );

		return (int) $data->id;
	}

	/**
	 * @return string
	 *
	 * Get username of logged in user
	 */
	public function get_current_user_name() {
		if ( ! $this->is_logged_in() ) {
			return '';
		}

		$data = $this->session->userdata( self::LoginCookieName );

		return (string) $data->username;
	}

	/**
	 * @return object
	 *
	 * Get object of logged in user
	 */
	public function get_current_user() {
		if ( ! $this->is_logged_in() ) {
			return (object) [];
		}

		$this->db->select( '*' )->from( self::table )->where( 'id', $this->get_current_user_id() );

		## Query
		$response              = $this->db->get()->row();
		$response->profile_url = $this->get_user_profile_url( $this->get_current_user_id() );

		return $response;
	}

	/**
	 * @param string $userId
	 *
	 * Get profile url of staff user.
	 * If Id is not given then it will get current user it.
	 *
	 * @return string
	 */
	public function get_user_profile_url( $userId = '' ) {
		if ( empty( $userId ) ) {
			$userId = $this->get_current_user_id();
		}
		$userId = (int) $userId;
		$url    = $this->create_and_return_current_user_content_path( $userId, false, CONTENT_URL );
		$path   = $this->create_and_return_current_user_content_path( $userId, false );
		$path   .= DIRECTORY_SEPARATOR . 'profile' . DIRECTORY_SEPARATOR;
		$url    .= DIRECTORY_SEPARATOR . 'profile' . DIRECTORY_SEPARATOR;

		if ( is_dir( $path ) ) {
			$filesInDir = scandir( $path, 1 );

			return $url . $filesInDir[0];
		}

		return 'https://dummyimage.com/200x300/000/' . self::get_current_user_name();

	}

	/**
	 * @param string $args
	 * @param string $profile_photo
	 *
	 * Get all user from database.
	 *
	 * @return false|string
	 */
	public function get_users( $args = '', $profile_photo = '' ) {
		$default            = [
			'mysql_cols' => '*',
			'orderby'    => 'username',
			'order'      => 'ASC',
			'active'     => '1',
			'return'     => 'object',           // Object, Array and Json is possible.
		];
		$args               = $this->customFunctions->set_args( $args, $default );
		$args['mysql_cols'] = (string) $args['mysql_cols'];
		$this->db->select( $args['mysql_cols'] )->from( self::table )->order_by( $args['orderby'], $args['order'] );

		$args['active'] = trim( (string) $args['active'] );

		if ( $args['active'] <> '' ) {
			$this->db->where( 'active', $args['active'] );
		}

		$query = $this->db->get();

		$args['return'] = strtolower( trim( (string) $args['return'] ) );
		if ( $args['return'] == 'object' ) {
			$rows = $query->result();
		} else {
			$rows = $query->result_array();

			if ( $args['return'] == 'json' ) {
				$rows = json_encode( $rows );
			}
		}

		return $rows;
	}

	## Before update profile validate user.
	public function udpate_profile_precocious_check( $args, $profile_photo ) {
		$default = [
			'email'     => '',
			'username'  => '',
			'password'  => '',
			'rpassword' => '',
			'gender'    => 1,
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## Update profile data
		if ( $args['gender'] == 'on' ) {
			$args['gender'] = 1; ## Male
		} else {
			$args['gender'] = 0; ## Female
		}

		$updateProfileArgs     = [
			'on_which_base' => [
				'id' => $this->get_current_user_id(),
			],
			'update_field'  => [
				'email'     => $args['email'],
				'password'  => $args['password'],
				'rpassword' => $args['rpassword'],
				'username'  => $args['username'],
				'gender'    => $args['gender'],
				'address'   => $args['address'],
			],
		];
		$updateProfileResponse = $this->update_profile( $updateProfileArgs );
		if ( is_array( $updateProfileResponse ) || substr( $updateProfileResponse, 0, 2 ) <> 'OK' ) {
			return $updateProfileResponse;
		}

		## Update profile photo
		$updateProfilePictureResponse = $this->upload_user_profile( $profile_photo );

		if ( is_array( $updateProfilePictureResponse ) || substr( $updateProfilePictureResponse, 0, 2 ) <> 'OK' ) {
			return $updateProfilePictureResponse;
		}

		return 'OK';
	}

	## validate user password which he/she entered.
	## This method check both user passwords entered are same or not.
	public function validate_user_password_of_same_user( $args ) {
		$default = [
			'password'  => '',
			'rpassword' => '',
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## If one them is empty
		if ( empty( $args['password'] ) || empty( $args['rpassword'] ) ) {
			return 'Please type both password carefully';
		}

		## if they are not same.
		if ( $args['password'] <> $args['rpassword'] ) {
			return 'Confirm password does not match with real password';
		}

		return 'OK' . md5( $args['password'] );
	}

	## Upload user profile photo
	public function upload_user_profile( $file_ar ) {
		if ( empty( $file_ar ) ) {
			return 'OK';
		}

		## Target path
		$target_path = $this->CONTENT_PATH_OF_LOGIN_USER . DIRECTORY_SEPARATOR . 'profile';

		## Load FileHandler Directory
		$this->load->model( 'FilesHandler', 'FilesHandler' );

		## Delete directory first.
		$this->FilesHandler->delete_directory( $target_path );

		## upload file.
		$target_path = $this->CONTENT_PATH_OF_LOGIN_USER . DIRECTORY_SEPARATOR . 'profile';
		if ( ! is_dir( $target_path ) ) {
			mkdir( $target_path );
		}

		## Setting file array.
		$file_ar = $file_ar['user_picture'];

		## If error found then return back.
		if ( empty( $file_ar['name'] ) || $file_ar['error'] <> 0 || empty( $file_ar['tmp_name'] ) ) {
			return $file_ar;
		}

		$target_path .= DIRECTORY_SEPARATOR . basename( $file_ar['name'] );

		## Upload file code.
		if ( move_uploaded_file( $file_ar['tmp_name'], $target_path ) ) {
			return 'OK';
		} else {
			return 'File is not uploaded';
		}
	}

	/**
	 * @param $args
	 *
	 * Register New User
	 *
	 * @return bool|string
	 */
	public function register_user( $args ) {
		$default = [
			'username'  => '',
			'email'     => '',
			'password'  => '',
			'rpassword' => '',
		];
		$args    = $this->customFunctions->set_args( $args, $default );

		## if user name is not mentioned
		if ( empty( $args['username'] ) ) {
			return ( 'Please type user name' );
		}

		## Validating user email address.
		$email_validation_response = self::email_check( $args['email'], [] );

		if ( substr( $email_validation_response, 0, 1 ) <> 1 ) {
			return $email_validation_response;
		}

		## Validating password.
		$tmp_ar              = [
			'password'  => $args['password'],
			'rpassword' => $args['rpassword'],
		];
		$password_validation = self::validate_user_password_of_same_user( $tmp_ar );
		if ( substr( $password_validation, '0', 2 ) <> 'OK' ) {
			return $password_validation;
		} else {
			$args['password'] = substr( $password_validation, 2 );
		}

		unset( $args['rpassword'] );
		if ( $this->db->insert( self::table, $args ) ) {
			return 'OK';
		} else {
			return $this->db->error();
		}
	}
}