<?php
/*
 * @Author SULAMANA KHAN <Sulaman@sulaman.pk>
 *
 * This Model handles all User functions, handles request or serve data according to need.
 * */
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class User extends CI_Controller {

	public function __construct() {
		parent::__construct();
		## Loading modals
		$this->load->model( [
			'user_model',
		] );
		## Loading Upload library.
		$this->load->library( 'upload' );

		## Loading Form and urls.
		$this->load->helper( [ 'form', 'url' ] );

	}

	/**
	 * Serve page according to need.
	 */
	public function index() {
		if ( $this->user_model->is_logged_in() ) {
			$this->load->view( 'index.html' );
		} else {
			self::login();
		}
	}

	/**
	 * Serve Login view.
	 */
	public function login() {
		$this->load->view( 'user/login.html' );
	}

	/**
	 * Logout User.
	 */
	public function logout() {
		$this->user_model->logout();
	}

	/**
	 * Show register page
	 * If user is logged in and try to register new account then first logout him.
	 */
	public function register() {
		if ( $this->user_model->is_logged_in() ) {
			self::logout();
		} else {
			$this->load->view( "user/register.html" );
		}
	}

	/**
	 * This method handle ajax request of all User's Model.
	 */
	public function ajax() {
		## Login
		if ( $this->input->get( 'login' ) !== null ) {
			## Login
			echo $this->user_model->login( $this->input->post() );
			exit();
		} ## Forget Password
		else if ( $this->input->get( 'forget-password' ) !== null ) {

			## Staff User request for forget password action
			print_r( $this->user_model->forget_request( $this->input->post() ) );
			exit();
		} ## Update Password
		else if ( $this->input->get( 'update-password' ) !== null ) {
			print_r( $this->user_model->udpate_request( $this->input->post() ) );
			exit();
		} ## Update profile
		else if ( $this->input->get( 'updateUserProfile' ) !== null ) {
			print_r( $this->user_model->udpate_profile_precocious_check( $this->input->post(), $_FILES ) );
			exit();
		} else if ( $this->input->get( 'register' ) !== null ) {
			print_r( $this->user_model->register_user( $this->input->post() ) );
			exit();
		}
	}

	/**
	 * View Profile Page
	 */
	public function profile() {
		$this->load->view( 'user/profile.html' );
	}

	/**
	 *
	 * WHen staff user request for new password by clicking on the forget password then email sent to relevant staff
	 * user. WHen User click on this link then we have to show view in which we ask new password, So this method is use
	 * to this specific view.
	 */
	public function update_request() {
		$this->load->view( 'user/update-password-by-forget-password.html.twig' );
	}
}