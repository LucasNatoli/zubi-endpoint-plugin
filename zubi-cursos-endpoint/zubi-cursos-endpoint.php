<?php

/*
Plugin Name: Zubi Learndash Courses
Version: 0.1
Description: Adding an endpoint to manage LearnDash Courses
Author: Lucas Natoli
Author URI: http://ciervo.boutique
*/

/** Create a new consultancy (sfwd-couse) based on the title provided
 * The created sfwd-couse has meta fachada_servicio = fachada_CONSULTORIA
 * @return object Created Consultancy
*/
function create_zubi_consultancy() {
	$title = $_POST['title'];
	$category = array($_POST['category']);
	$name = sanitize_title($title);
	$post = array (
		'post_title' => $title,
		'post_name' => $name,
		'post_author' => 1,
		'post_type' => 'sfwd-courses',
		'comment_status' => 'closed',
		'post_category' => $category
	);
	$post_id = wp_insert_post($post);
	update_post_meta( $post_id, 'fachada_servicio', 'fachada_CONSULTORIA' );

	if (is_wp_error($post_id)) {
		return new WP_Error('cant_insert_post', 'Consultancy could not be created', array ('status' => 500 ));
	} else {
		$new_post = get_post($post_id);
		return $new_post;
	};

}

add_action( 'rest_api_init', function() {
  register_rest_route( 'wp-react/v2', '/nueva-consultoria', array(
		'methods' => 'POST',
		'callback' => 'create_zubi_consultancy',
	) );	
} );

/**
 * Get consulting authored by current logged user
 * @return array List of consulting courses authored by the user
 */
function mis_consultorias() {
	$my_courses = get_posts( array(
		'post_type' => 'sfwd-courses',
		'post_author' => 1,
		'meta_key'   => 'fachada_servicio',
		'meta_value' => 'fachada_CONSULTORIA'	
	));

	/**
	 * ATENCION CON EL HARDCODE = 1
	 */
	return add_course_meta($my_courses);
}


function add_course_meta($posts) {
	foreach ($posts as $post) {
		$post->price = get_post_meta( $post->ID, 'sfwd-courses_course_price');
	}
	return $posts;
}

add_action( 'rest_api_init', function() {
  register_rest_route( 'wp-react/v2', '/mis-consultorias', array(
		'methods' => 'GET',
		'callback' => 'mis_consultorias',
	) );	
} );


/**
 * Get all courses authored by current logged user
 * @return array List of LearnDash courses authored by the user 
 */
function mis_capacitaciones() {
	$my_courses = get_posts( array(
		'post_type' => 'sfwd-courses',
		'post_author' => 1,
		'meta_key'   => 'fachada_servicio',
		'meta_value' => 'fachada_CAPACITACION'	
	));


	/**
	 * ATENCION CON EL HARDCODE = 1
	 */
	return add_course_meta($my_courses);
}

add_action( 'rest_api_init', function() {
  register_rest_route( 'wp-react/v2', '/mis-capacitaciones', array(
		'methods' => 'GET',
		'callback' => 'mis_capacitaciones',
	) );	
} );

/**
 * Get all Courses
 * @return array List of LearnDash Courses
 */
function get_all_courses() {
  $all_courses = get_posts( array(
			'post_type'   => 'sfwd-courses'		
  ) );
  return $all_courses;
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp-react/v2', '/get-all-courses', array(
      'methods' => 'GET',
      'callback' => 'get_all_courses',
  ) );
} );


/**
 * El usuario guarda una nueva cita
 * @return Cita creada
 */
function user_save_cita_post_type() {
  /* $all_courses = get_posts( array(
      'post_type'   => 'sfwd-courses'
  ) );

	return $all_courses; */

	$date = $_POST['date'];
	$startTime = $_POST['startTime'];	
	$endTime = $_POST['endTime'];	
	$instructor = $_POST['instructor'];


}

add_action( 'rest_api_init', function () {
  register_rest_route( 'wp-react/v2', '/new-cita', array(
      'methods' => 'POST',
      'callback' => 'user_save_cita_post_type',
  ) );
} );

/**
 * User Login Handler
 * @return user credentials
 */
function user_login_handler () {

	$data = json_decode( file_get_contents( 'php://input' ), true );
	$username = $data["username"];
	$is_email = strpos($username, '@');
	if ($is_email) {
			$ud = get_user_by_email($username);
			$username = $ud->user_login;
	}

	$credentials = array();
	$credentials['user_login'] = $username;
	$credentials['user_password'] = $data["password"];
	$credentials['remember'] = true;
	$wp_user = wp_signon( $credentials, false );

	if ( is_wp_error($wp_user) ) {
		return new WP_Error('invalid_login', 'Invalid login credentials', array ('status' => 403 ));
	} else {
		wp_set_current_user( $wp_user->ID, $wp_user->user_login ) ;
		//wp_set_auth_cookie( $wp_user->ID );
		//do_action( 'wp_login', $wp_user->user_login );

		return $wp_user;
	};

}

add_action( 'rest_api_init', function () {
	register_rest_route( 'wp-react/v2', '/login', array(
		'methods' => 'POST',
		'callback' => 'user_login_handler',
	) );
} );


	/**
 * User logged
 * 
 * @return boolean
 */
function user_loged () {
	return  wp_get_current_user();
	
	//return  $_SERVER['PHP_AUTH_USER'];

}

add_action( 'rest_api_init', function () {
	register_rest_route( 'wp-react/v2', '/logged-in', array(
		'methods' => 'GET',
		'callback' => 'user_loged',
	) );
} );

function get_agendas () {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}zubi_agenda_dia", OBJECT );
	return $results;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'wp-react/v2', '/agendas', array(
		'methods' => 'GET',
		'callback' => 'get_agendas',
	) );
} );


?>