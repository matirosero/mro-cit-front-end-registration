<?php
/**
 * redefine new user notification function
 *
 * emails new users their login info
 * http://www.webtipblog.com/change-wordpress-user-registration-welcome-email/
 * http://www.webtipblog.com/change-wordpress-user-registration-welcome-email/
 *
 */
if ( !function_exists( 'wp_new_user_notification' ) ) {
    function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {

		if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

        // set content type to html
        // add_filter( 'wp_mail_content_type', 'wpmail_content_type' );

		global $wpdb, $wp_hasher, $wp_roles;
		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		// $membership_type = $user->mro_cit_user_membership;

		$user_roles = $user->roles;

		if ( 'user' !== $notify ) {
			$switched_locale = switch_to_locale( get_locale() );

			// Admin email is Afiliado Empresarial
			if ( in_array( 'afiliado_empresarial_pendiente', $user_roles) || in_array( 'afiliado_institucional_pendiente', $user_roles) ) {

				if ( in_array( 'afiliado_empresarial_pendiente', $user_roles) ) {
					$membership = 'afiliación empresarial';
					$member_type = 'Afiliado Empresarial';
					$entity = 'Empresa';
				} elseif ( in_array( 'afiliado_institucional_pendiente', $user_roles) ) {
					$membership = 'afiliación institucional';
					$member_type = 'Afiliado Institucional';
					$entity = 'Institución';
				}


				$subject = 'Solicitud de ' . $membership . ' al Club de Investigación Tecnológica';

				$message  = sprintf( __( 'Se ha registrado un nuevo afiliado al Club, y desea ser %s.' ), $member_type ) . "\r\n\r\n";

				/* translators: %s: user login */
				$message .= sprintf( __( 'Usuario: %s' ), $user->user_login ) . "\r\n\r\n";

				$message .= sprintf( __( 'Empresa/Institución: %s' ), $user->nickname ) . "\r\n\r\n";

				$message .= sprintf( __( 'Industria: %s' ), $user->mro_cit_user_sector ) . "\r\n\r\n";

				$message .= sprintf( __( 'País: %s' ), $user->mro_cit_user_country ) . "\r\n\r\n";

				$message .= sprintf( __( 'Teléfono: %s' ), $user->mro_cit_user_phone ) . "\r\n\r\n";

				$message .= sprintf( __( 'CONTACTO PRINCIPAL' ) ) . "\r\n";

				$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

				//Fix
				$message .= sprintf( __( 'Nombre: %1$s %2$s' ), $user->first_name, $user->last_name ) . "\r\n\r\n";

				$message .= sprintf( __( 'CONTACTO SECUNDARIO' ) ) . "\r\n";

				$message .= sprintf( __( 'Email: %s' ), $user->mro_cit_user_secondary_email ) . "\r\n";

				//Fix
				$message .= sprintf( __( 'Nombre: %1$s %2$s' ), $user->mro_cit_user_secondary_first, $user->mro_cit_user_secondary_last ) . "\r\n";


				$recipient = array(
					get_option( 'admin_email' ),
					'matirosero@icloud.com',
				);

			// Admin email is Afiliado Personal
			} else {
				$subject = 'Nuevo afiliado personal al  Club de Investigación Tecnológica';

				/* translators: %s: site title */
				$message  = sprintf( __( 'Se ha registrado un nuevo afiliado en %s:' ), $blogname ) . "\r\n\r\n";

				/* translators: %s: user login */
				$message .= sprintf( __( 'Usuario: %s' ), $user->user_login ) . "\r\n\r\n";
				/* translators: %s: user email address */
				$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";
				$message .= sprintf( __( 'Nombre: %1$s %2$s' ), $user->first_name, $user->last_name ) . "\r\n\r\n";

				$message .= sprintf( __( 'Teléfono: %s' ), $user->mro_cit_user_phone ) . "\r\n";

				$message .= sprintf( __( 'País: %s' ), $user->mro_cit_user_country ) . "\r\n";

				$message .= sprintf( __( 'Ocupación: %s' ), $user->mro_cit_user_occupation ) . "\r\n";

				$message .= sprintf( __( 'Empresa: %s' ), $user->mro_cit_user_company ) . "\r\n";

				$recipient = get_option( 'admin_email' );
			}


			// Set up admin notification email
			$wp_new_user_notification_email_admin = array(
				'to'      => $recipient,
				/* translators: Password change notification email subject. %s: Site title */
				'subject' => $subject,
				'message' => $message,
				'headers' => '',
			);

			/**
			 * Filters the contents of the new user notification email sent to the site admin.
			 *
			 * @since 4.9.0
			 *
			 * @param array   $wp_new_user_notification_email {
			 *     Used to build wp_mail().
			 *
			 *     @type string $to      The intended recipient - site admin email address.
			 *     @type string $subject The subject of the email.
			 *     @type string $message The body of the email.
			 *     @type string $headers The headers of the email.
			 * }
			 * @param WP_User $user     User object for new user.
			 * @param string  $blogname The site title.
			 */
			$wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );

			@wp_mail(
				$wp_new_user_notification_email_admin['to'],
				wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
				$wp_new_user_notification_email_admin['message'],
				$wp_new_user_notification_email_admin['headers']
			);

			if ( $switched_locale ) {
				restore_previous_locale();
			}
		}


		//New user email
		// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
		if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
			return;
		}

		
		/** Generate something random for a password reset key. */
		// $key = wp_generate_password( 20, false );

		/** This action is documented in wp-login.php */
		// do_action( 'retrieve_password_key', $user->user_login, $key );

		/** Now insert the key, hashed, into the DB. */
		// if ( empty( $wp_hasher ) ) {
		// 	require_once ABSPATH . WPINC . '/class-phpass.php';
		// 	$wp_hasher = new PasswordHash( 8, true );
		// }
		// $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		// $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

		$switched_locale = switch_to_locale( get_user_locale( $user ) );


		if (  in_array( 'afiliado_empresarial_pendiente', $user_roles) || in_array( 'afiliado_institucional_pendiente', $user_roles) ) {
			$message = sprintf(__('¡Nos da gran placer dar la bienvenida a %s al Club de Investigación Tecnológica!'), $user->nickname) . "\r\n\r\n";

			$message .= sprintf(__('Pronto nos pondremos en contacto para finalizar el proceso de afiliación. Mientras tanto, está cuenta está en estado "Pendiente": puede ingresar al sitio y descargar informes de investigación. En cuanto finalice la afiliación, también será posible tramitar sus reservas a nuestros eventos. ')) . "\r\n\r\n";

			$message .= sprintf(__('Puede usar estas credenciales para ingresar al sitio:')) . "\r\n";
			$message .= sprintf(__('Usuario: %s'), $user->user_login) . "\r\n";
			$message .= sprintf(__('Email: %s'), $user->user_email) . "\r\n\r\n";

		} else {
			/* translators: %s: user login */
			$message = '¡Bienvenido al Club de Investigación Tecnológica, ' . $user->first_name . '!' . "\r\n\r\n";
			$message .= 'Nos da mucho gusto que se haya unido a nosotros.' . "\r\n\r\n";
			$message .= 'A continuación, encontrará información importante sobre su cuenta.' . "\r\n\r\n";

			$message .= sprintf(__('Usuario: %s'), $user->user_login) . "\r\n";
			$message .= sprintf(__('Email: %s'), $user->user_email) . "\r\n\r\n";

			$message .= 'Puede usar estas credenciales para ingresar al sitio y descargar los informes de investigación o adquirir entradas a los eventos del Club.' . "\r\n\r\n";
		}


		// $message .= __('Para establecer su contraseña, visite la siguiente dirección:') . "\r\n\r\n";
		// $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";


		$message .= 'Saludos cordiales,' . "\r\n\r\n";
		$message .= 'Club de Investigación Tecnológica' . "\r\n";
		$message .= site_url();

		$wp_new_user_notification_email = array(
			'to'      => $user->user_email,
			/* translators: Password change notification email subject. %s: Site title */
			'subject' => __( 'Bienvenido al Club de Investigación Tecnológica' ),
			'message' => $message,
			'headers' => '',
		);

		/**
		 * Filters the contents of the new user notification email sent to the new user.
		 *
		 * @since 4.9.0
		 *
		 * @param array   $wp_new_user_notification_email {
		 *     Used to build wp_mail().
		 *
		 *     @type string $to      The intended recipient - New user email address.
		 *     @type string $subject The subject of the email.
		 *     @type string $message The body of the email.
		 *     @type string $headers The headers of the email.
		 * }
		 * @param WP_User $user     User object for new user.
		 * @param string  $blogname The site title.
		 */
		$wp_new_user_notification_email = apply_filters( 'wp_new_user_notification_email', $wp_new_user_notification_email, $user, $blogname );


		wp_mail(
			$wp_new_user_notification_email['to'],
			wp_specialchars_decode( sprintf( $wp_new_user_notification_email['subject'], $blogname ) ),
			$wp_new_user_notification_email['message'],
			$wp_new_user_notification_email['headers']
		);

		if ( $switched_locale ) {
			restore_previous_locale();
		}


/*

        // user
        $user = new WP_User( $user_id );
        $userEmail = stripslashes( $user->user_email );
        $siteUrl = get_site_url();
        $logoUrl = plugin_dir_url( __FILE__ ).'/images/logo_cit.png';

        $subject = 'Bienvenido al Club de Investigaci&oacute;n Tecnol&oacute;gica';
        $headers = 'From: Club de Investigaci&oacute;n Tecnol&oacute;gica <gekidasa@gmail.com>';

        // admin email
        $message  = "Un nuevo afiliado ha sido registrado"."\r\n\r\n";
        $message .= 'Email: '.$userEmail."\r\n";
        @wp_mail( get_option( 'admin_email' ), 'Nuevo afiliado', $message, $headers );

        ob_start();
        include plugin_dir_path( __FILE__ ).'/email_welcome.php';
        $message = ob_get_contents();
        ob_end_clean();

        @wp_mail( $userEmail, $subject, $message, $headers );

        // remove html content type
        remove_filter ( 'wp_mail_content_type', 'wpmail_content_type' );
*/
    }
}


/*
 * Email user on role change
 */
function mro_cit_user_role_update( $user_id, $role ) {
    // write_log('role changed');
    // write_log('new '.$role);

    // write_log('old '.$old_roles);
    if ( $role == 'afiliado_empresarial' || $role == 'afiliado_institucional' ) {
        $site_url = get_bloginfo('wpurl');
        $user_info = get_userdata( $user_id );
        $to = $user_info->user_email;
        $subject = "Su afiliación al Club de Investigación Tecnológica ha sido procesada";
        $message = "Hola " .$user_info->display_name . ",\r\n\r\n";
        $message .= "¡Su afiliación al Club de Investigación Tecnológica ha sido procesada con éxito! A partir de ahora podrá reservar espacios en los eventos del Club sin costo alguno; simplemente debe accesar la página del evento, ingresar a su cuenta y llenar el formulario correspondiente.\r\n\r\n";
        $message .= "Igualmente, puede descargar los informes de investigación del Club.\r\n\r\n";
        $message .= "Saludos,\r\n";
        $message .= "Club de Investigación Tecnológica";
        wp_mail($to, $subject, $message);
    }
}
add_action( 'set_user_role', 'mro_cit_user_role_update', 10, 2);




/*
 * Email update email
 */
add_filter( 'email_change_email', 'mro_cit_change_email_mail_message', 10, 3 );
function mro_cit_change_email_mail_message( $email_change, $user, $userdata 
) {

    $modified_email_message = __( 'Hola ###USERNAME###,

Esta es una confirmación de que el correo electrónico asociado con su cuenta en ###SITENAME### fue cambiado a ###NEW_EMAIL###.

Si usted no cambió su correo, por favor contacte al administrador del sitio al correo ###ADMIN_EMAIL###

Este mensaje fue enviado a ###EMAIL###

Saludos cordiales,
###SITENAME###
###SITEURL###' ); //put your modified content in this section

    $email_change[ 'message' ] = $modified_email_message;

    return $email_change;
}


/*
 * Password update email
 */
add_filter( 'password_change_email', 'mro_cit_change_password_mail_message', 10, 3 );
function mro_cit_change_password_mail_message( $pass_change_mail, $user, $userdata 
) {
  $new_message_txt = __( 'Hola ###USERNAME###,

Esta es una confirmación de que su contraseña en ###SITENAME### fue cambiada.

Si usted no cambió su contraseña, por favor contacte al administrador del sitio al correo ###ADMIN_EMAIL###

Este mensaje fue enviado a ###EMAIL###

Saludos cordiales,
###SITENAME###
###SITEURL###' );
  $pass_change_mail[ 'message' ] = $new_message_txt;
  return $pass_change_mail;
}





add_action('wp_mail_failed', 'log_mailer_errors', 10, 1);
function log_mailer_errors(){
  $fn = ABSPATH . '/mail.log'; // say you've got a mail.log file in your server root
  $fp = fopen($fn, 'a');
  // write_log("Mailer Error: " . $mailer->ErrorInfo );
  fputs($fp, "Mailer Error: " . $mailer->ErrorInfo ."\n");
  fclose($fp);
}

/**
 * wpmail_content_type
 * allow html emails
 *
 * @author Joe Sexton <joe@webtipblog.com>
 * @return string
 */
function wpmail_content_type() {

    return 'text/html';
}