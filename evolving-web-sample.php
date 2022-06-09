<?php
/**
 * Plugin Name: Evolving Web Sample
 * Description: This Plugin is a test project for Evolving Web. It registers two shortcodes ([evolving_web_form] and [evolving_web_entries]).
 * Author:      Josefina Lucía Cáceres
 * Text Domain: evolving-web
 * Domain Path: /languages
 * Version:     1.0.1
 *
 * @package     Evolving_Web_Sample
 */

define( 'EVOLVING_WEB_SAMPLE', '1.0.1' );

/**
 * Styles and scripts enqueuing callable function.
 */
function evolving_web_enqueue_scripts() {
	wp_register_script( 'evolving-web-ajax', WP_CONTENT_URL . '/plugins/evolving-web-sample/assets/js/ajax.js', array(), EVOLVING_WEB_SAMPLE, true );

	wp_register_script( 'evolving-web-form', WP_CONTENT_URL . '/plugins/evolving-web-sample/assets/js/form.js', array( 'evolving-web-ajax' ), EVOLVING_WEB_SAMPLE, true );

	wp_register_style( 'evolving-web-form', WP_CONTENT_URL . '/plugins/evolving-web-sample/assets/css/form.css', array(), EVOLVING_WEB_SAMPLE );

	wp_register_script( 'evolving-web-entries', WP_CONTENT_URL . '/plugins/evolving-web-sample/assets/js/entries.js', array( 'evolving-web-ajax' ), EVOLVING_WEB_SAMPLE, true );

	wp_register_style( 'evolving-web-entries', WP_CONTENT_URL . '/plugins/evolving-web-sample/assets/css/entries.css', array(), EVOLVING_WEB_SAMPLE );
}

/**
 * Language domain loading callable function.
 */
function evolving_web_language_domain() {
	load_theme_textdomain( 'evolving-web', __DIR__ . '/languages' );
}

/**
 * Prints the form in the Front End for visitors to fill and submit it.
 *
 * @return string HTML string with the form.
 */
function evolving_web_generate_form() {
	$user_data = evolving_web_get_user_data();

	$html  = '<form class="evolving-web-ajax-form" method="post" action="' . esc_attr( admin_url( 'admin-ajax.php' ) ) . '">';
	$html .= wp_nonce_field( 'evolving_web_ajax_submit_form', 'evolving_web_submit_form_nonce', true, false );
	$html .= '<label>' . esc_html__( 'First Name', 'evolving-web' ) . ': *<br><input class="evolving-web-form-field" type="text" name="first-name" value="' . esc_html( $user_data['first_name'] ) . '" required></label>';
	$html .= '<label>' . esc_html__( 'Last Name', 'evolving-web' ) . ': *<br><input class="evolving-web-form-field" type="text" name="last-name" value="' . esc_html( $user_data['last_name'] ) . '" required></label>';
	$html .= '<label>' . esc_html__( 'Email', 'evolving-web' ) . ': *<br><input class="evolving-web-form-field" type="email" name="email" value="' . esc_html( $user_data['user_email'] ) . '" required></label>';
	$html .= '<label>' . esc_html__( 'Subject', 'evolving-web' ) . ': *<br><input class="evolving-web-form-field" type="text" name="subject" required></label>';
	$html .= '<label>' . esc_html__( 'Message', 'evolving-web' ) . ': *<br><textarea class="evolving-web-form-field" name="message" required></textarea></label>';
	$html .= '<input type="submit" value="' . esc_html__( 'Send', 'evolving-web' ) . '" />';
	$html .= '<p class="evolving-web-response-message"></p>';
	$html .= '</form>';

	return $html;
}

/**
 * Retrieves user information as stored in WP Database.
 *
 * @return Array Array with email, first name and last name data of current user.
 */
function evolving_web_get_user_data() {
	$user_data = array(
		'user_email' => '',
		'first_name' => '',
		'last_name'  => '',
	);

	if ( is_user_logged_in() ) {
		$user      = wp_get_current_user();
		$user_meta = get_user_meta( $user->ID );

		$user_data['user_email'] = $user->user_email;
		$user_data['first_name'] = ( isset( $user_meta['first_name'] ) ) ? $user_meta['first_name'][0] : '';
		$user_data['last_name']  = ( isset( $user_meta['last_name'] ) ) ? $user_meta['last_name'][0] : '';
	}

	return $user_data;
}

/**
 * Custom post type 'custom-form' registration callable function.
 */
function evolving_web_post_type_registration() {
	register_post_type(
		'custom-form',
		array(
			'label'                => __( 'Custom Form', 'evolving-web' ),
			'show_ui'              => true,
			'public'               => true,
			'has_archive'          => true,
			'supports'             => array( 'title' ),
			'capabilities'         => array(
				'edit_post'          => 'manage_options',
				'read_post'          => 'manage_options',
				'delete_post'        => 'manage_options',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'delete_posts'       => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
			),
			'register_meta_box_cb' => 'evolving_web_meta_box_cb',
		)
	);
}

/**
 * Meta box registration callback for 'custom-form' post type.
 */
function evolving_web_meta_box_cb() {
	add_meta_box(
		'evolving_web_meta_boxes',
		__( 'Entry Data', 'evolving-web' ),
		'evolving_web_print_meta_boxes',
		'custom-form'
	);
}

/**
 * Adds a meta box and its fields.
 *
 * @param object $post WP Post Object.
 */
function evolving_web_print_meta_boxes( $post ) {
	wp_nonce_field( 'evolving_web_meta_boxes', 'evolving_web_meta_boxes_nonce' );

	$metas = array(
		'first_name' => __( 'First Name', 'evolving-web' ),
		'last_name'  => __( 'Last Name', 'evolving-web' ),
		'email'      => __( 'Email', 'evolving-web' ),
		'subject'    => __( 'Subject', 'evolving-web' ),
		'message'    => __( 'Message', 'evolving-web' ),
	);

	foreach ( $metas as $meta_key => $meta_label ) {
		evolving_web_print_meta_box( $post, $meta_key, $meta_label );
	}
}

/**
 * Helps to print meta boxes html in the backend.
 *
 * @param object $post  WP Post Object.
 * @param string $name  Text to be used to complete the meta key.
 * @param string $label Text to be used to display the meta input label for the
 *                      user to see.
 */
function evolving_web_print_meta_box( $post, $name, $label ) {
	$input_data = get_post_meta( $post->ID, 'evolving_web_meta_box_' . sanitize_text_field( $name ), true );
	?>
<p>
	<label><strong><?php echo esc_html( $label ); ?></strong><br>
		<textarea class="widefat evolving-web-form-field" type="text" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_attr( ! empty( $input_data ) ? $input_data : '' ); ?></textarea>
	</label>
</p>
	<?php
}


/**
 * This function is hooked to wp_ajax and wp_ajax_nopriv. It gets the complete
 * data of all the form entries of a given page (works with pagination) and it
 * prints out the [evolving_web_entries] shortcode.
 */
function evolving_web_ajax_get_entries() {
	if ( isset( $_POST['evolving_web_get_entries_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['evolving_web_get_entries_nonce'] )
			),
			'evolving_web_ajax_get_entries'
		)
	) {
		$paged = ( ! empty( $_POST['page-number'] ) ) ? sanitize_text_field( wp_unslash( $_POST['page-number'] ) ) : 1;

		echo do_shortcode( '[evolving_web_entries paged="' . $paged . '"]' );
	}
	die();
}


/**
 * This function is hooked to wp_ajax and wp_ajax_nopriv. It gets the complete
 * data of a given form entry and prints it out.
 */
function evolving_web_ajax_get_single_entry() {
	if ( isset( $_POST['evolving_web_get_single_entry_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['evolving_web_get_single_entry_nonce'] )
			),
			'evolving_web_ajax_get_single_entry'
		)
	) {
		$entry_id = ( ! empty( $_POST['entry-id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['entry-id'] ) ) : null;
		$entry    = get_post( $entry_id );

		if ( $entry ) {
			$full_entry = evolving_web_get_full_entry( $entry );

			$html = '<p><strong>' . esc_html__( 'First Name', 'evolving-web' ) . ':</strong> ' . esc_html( $full_entry->first_name ) . '</p>
			<p><strong>' . esc_html__( 'Last Name', 'evolving-web' ) . ':</strong> ' . esc_html( $full_entry->last_name ) . '</p>
			<p><strong>' . esc_html__( 'Email', 'evolving-web' ) . ':</strong> ' . esc_html( $full_entry->email ) . '</p>
			<p><strong>' . esc_html__( 'Subject', 'evolving-web' ) . ':</strong> ' . esc_html( $full_entry->subject ) . '</p>
			<p><strong>' . esc_html__( 'Message', 'evolving-web' ) . ':</strong> ' . esc_html( $full_entry->message ) . '</p>';
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	die();
}

/**
 * This function is hooked to wp_ajax and wp_ajax_nopriv. It processes the
 * submissions of forms. It sends a json
 * with the result of the process.
 */
function evolving_web_ajax_submit_form() {
	$error_message = '';
	$response = array(
		'post_insertion' => false,
		'message'        => '',
	);

	if ( isset( $_POST['evolving_web_submit_form_nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['evolving_web_submit_form_nonce'] )
			),
			'evolving_web_ajax_submit_form'
		)
	) {
		$first_name = ( ! empty( $_POST['first-name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['first-name'] ) ) : '';
		$last_name  = ( ! empty( $_POST['last-name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['last-name'] ) ) : '';
		$email      = ( ! empty( $_POST['email'] ) ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
		$subject    = ( ! empty( $_POST['subject'] ) ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message    = ( ! empty( $_POST['message'] ) ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';

		if ( $first_name && $last_name && $email && $subject && $message ) {
			$response['post_insertion'] = wp_insert_post(
				array(
					'post_title'  => __( 'Form Entry', 'evolving-web' ) . ' ' . $first_name . ' ' . $last_name,
					'post_type'   => 'custom-form',
					'post_status' => 'draft',
					'meta_input'  => array(
						'evolving_web_meta_box_first_name' => $first_name,
						'evolving_web_meta_box_last_name' => $last_name,
						'evolving_web_meta_box_email'     => $email,
						'evolving_web_meta_box_subject'   => $subject,
						'evolving_web_meta_box_message'   => $message,
					),
				)
			);
		} else {
			if ( empty( $first_name ) ) {
				$error_message .= __( 'The name field cannot be empty. ' );
			}
			if ( empty( $last_name ) ) {
				$error_message .= __( 'The last name field cannot be empty. ' );
			}
			if ( empty( $email ) ) {
				$error_message .= __( 'The email field cannot be empty. ' );
			}
			if ( empty( $subject ) ) {
				$error_message .= __( 'The subject field cannot be empty. ' );
			}
			if ( empty( $message ) ) {
				$error_message .= __( 'The message field cannot be empty. ' );
			}
		}
	}

	do_action( 'evolving_web_after_form_data_processing' );

	if ( $response['post_insertion'] ) {
		$response['message'] = __( 'Thank you for sending us your feedback', 'evolving-web' );
		wp_send_json_success( $response );
	} else {
		$response['message'] = ! empty( $error_message ) ? $error_message : __( 'We were not able to insert your message in the database at this moment, please try again soon', 'evolving-web' );
		wp_send_json_error( $response );
	}
}

/**
 * Retrieves the data of the form entries.
 *
 * @param int $paged (optional) Number of the page to fetch data.
 *
 * @return array Array with the content of the form entries and the data for
 *               creating the pagination.
 */
function evolving_web_get_form_entries( $paged = 1 ) {
	$form_entries = array(
		'entries' => array(),
	);

	$args = array(
		'post_type'           => 'custom-form',
		'posts_per_page'      => 10,
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'desc',
		'post_status'         => array( 'publish', 'draft' ),
		'paged'               => $paged,
	);

	$query = new WP_Query( $args );

	if ( ! empty( $query->posts ) ) {
		foreach ( $query->posts as $post ) {
			$form_entries['entries'][] = evolving_web_get_full_entry( $post );
		}
	}

	$form_entries['pagination'] = array(
		'first_page' => ( 1 === $paged ) ? false : 1,
		'prev_page'  => ( 1 === $paged ) ? false : $paged - 1,
		'next_page'  => ( intval( $query->max_num_pages ) === intval( $paged ) ) ? false : $paged + 1,
		'last_page'  => ( intval( $query->max_num_pages ) && intval( $query->max_num_pages ) !== intval( $paged ) ) ? intval( $query->max_num_pages ) : false,
	);

	return $form_entries;
}

/**
 * Retrieves the complete data of a form entry.
 *
 * @param WP_Post $form_entry WP Post object of 'custom-form' Post Type.
 *
 * @return Object Object created with the data of a WP Post of 'custom-form'
 *                post type and its Post Metas.
 */
function evolving_web_get_full_entry( $form_entry ) {
	$full_entry = new stdClass();

	$full_entry->ID         = $form_entry->ID;
	$full_entry->first_name = get_post_meta( $form_entry->ID, 'evolving_web_meta_box_first_name', true );
	$full_entry->last_name  = get_post_meta( $form_entry->ID, 'evolving_web_meta_box_last_name', true );
	$full_entry->email      = get_post_meta( $form_entry->ID, 'evolving_web_meta_box_email', true );
	$full_entry->subject    = get_post_meta( $form_entry->ID, 'evolving_web_meta_box_subject', true );
	$full_entry->message    = get_post_meta( $form_entry->ID, 'evolving_web_meta_box_message', true );

	return $full_entry;
}

/**
 * Prints a table with the list of form entries (Posts of 'custom-form' post
 * type).
 *
 * @param int $paged (optional) Number of the page to be displayed (uses
 *                              pagination).
 *
 * @return string HTML string with the table and the pagination.
 */
function evolving_web_print_form_entries( $paged = 1 ) {
	$form_entries = evolving_web_get_form_entries( $paged );

	$html  = '<div id="evolving-web-form-entries">';
	$html .= wp_nonce_field( 'evolving_web_ajax_get_entries', 'evolving_web_get_entries_nonce', true, false );
	$html .= wp_nonce_field( 'evolving_web_ajax_get_single_entry', 'evolving_web_get_single_entry_nonce', true, false );
	$html .= '<input type="hidden" id="evolving-web-endpoint" value="' . esc_attr( admin_url( 'admin-ajax.php' ) ) . '">';

	$html .= '<div class="section-table"><table class="content-table">';
	$html .= '<thead>
		<tr>
			<th>' . esc_html__( 'First Name', 'evolving-web' ) . '</th>
			<th>' . esc_html__( 'Last Name', 'evolving-web' ) . '</th>
			<th>' . esc_html__( 'Email', 'evolving-web' ) . '</th>
			<th>' . esc_html__( 'Subject', 'evolving-web' ) . '</th>
			<th></th>
		</tr>
	</thead>
	<tbody>';

	if ( $form_entries['entries'] ) {
		foreach ( $form_entries['entries'] as $form_entry ) {
			$html .= '<tr>
				<td>' . esc_html( $form_entry->first_name ) . '</td>
				<td>' . esc_html( $form_entry->last_name ) . '</td>
				<td>' . esc_html( $form_entry->email ) . '</td>
				<td>' . esc_html( $form_entry->subject ) . '</td>
				<td><button class="show-details-button" data-entry-id="' . esc_attr( $form_entry->ID ) . '">+</td>
			</tr>';
		}
	}

	$html .= '</tbody></table></div><div id="full-entry-result"></div>';

	$html .= '<div id="custom-form-entry-pagination">';
	$html .= '<button class="pagination-button first-page" data-page-number="' . esc_attr( $form_entries['pagination']['first_page'] ) . '"><<</button>';
	$html .= '<button class="pagination-button prev-page" data-page-number="' . esc_attr( $form_entries['pagination']['prev_page'] ) . '"><</button>';
	$html .= '<span class="pagination-page">' . esc_html( $paged ) . '</span>';
	$html .= '<button class="pagination-button next-page" data-page-number="' . esc_attr( $form_entries['pagination']['next_page'] ) . '">></button>';
	$html .= '<button class="pagination-button last-page" data-page-number="' . esc_attr( $form_entries['pagination']['last_page'] ) . '">>></button>';
	$html .= '</div></div>';

	return $html;
}

/**
 * Handles the saving of post of 'custom-form' custom post type.
 *
 * @param int $post_id Post ID of the post being saved.
 */
function evolving_web_save_cv_post_back( $post_id ) {

	if ( ! isset( $_POST['evolving_web_meta_boxes_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce(
		sanitize_text_field(
			wp_unslash(
				$_POST['evolving_web_meta_boxes_nonce']
			)
		),
		'evolving_web_meta_boxes'
	) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'custom-form' !== get_post_type( $post_id ) ) {
		return;
	}

	$first_name = ( ! empty( $_POST['first_name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last_name  = ( ! empty( $_POST['last_name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$email      = ( ! empty( $_POST['email'] ) ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
	$subject    = ( ! empty( $_POST['subject'] ) ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message    = ( ! empty( $_POST['message'] ) ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';

	update_post_meta( $post_id, 'evolving_web_meta_box_first_name', $first_name );
	update_post_meta( $post_id, 'evolving_web_meta_box_last_name', $last_name );
	update_post_meta( $post_id, 'evolving_web_meta_box_email', $email );
	update_post_meta( $post_id, 'evolving_web_meta_box_subject', $subject );
	update_post_meta( $post_id, 'evolving_web_meta_box_message', $message );
}

add_action( 'wp_enqueue_scripts', 'evolving_web_enqueue_scripts' );
add_action( 'after_setup_theme', 'evolving_web_language_domain' );
add_action( 'init', 'evolving_web_post_type_registration' );
add_action( 'wp_ajax_evolving_web_get_entries', 'evolving_web_ajax_get_entries' );
add_action( 'wp_ajax_nopriv_evolving_web_get_entries', 'evolving_web_ajax_get_entries' );
add_action( 'wp_ajax_evolving_web_get_single_entry', 'evolving_web_ajax_get_single_entry' );
add_action( 'wp_ajax_nopriv_evolving_web_get_single_entry', 'evolving_web_ajax_get_single_entry' );
add_action( 'wp_ajax_evolving_web_submit_form', 'evolving_web_ajax_submit_form' );
add_action( 'wp_ajax_nopriv_evolving_web_submit_form', 'evolving_web_ajax_submit_form' );
add_action( 'save_post', 'evolving_web_save_cv_post_back' );

add_shortcode(
	'evolving_web_form',
	function ( $atts ) {
		wp_enqueue_script( 'evolving-web-form' );
		wp_enqueue_style( 'evolving-web-form' );

		return evolving_web_generate_form();
	}
);

add_shortcode(
	'evolving_web_entries',
	function ( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'paged' => 1,
			),
			$atts
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			return '<h2>' . esc_html__( 'You are not authorized to view the content of this page.', 'evolving-web' ) . '</h2>';
		}

		wp_enqueue_script( 'evolving-web-entries' );
		wp_enqueue_style( 'evolving-web-entries' );

		return evolving_web_print_form_entries( $atts['paged'] );
	}
);
