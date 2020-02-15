<?php

/*
 * registration form fields
 */
function mro_cit_print_field( $field = null, $membership = 'personal' ) {

	if ( $field == null) {
		return false;
	}

	// If not logged in, check if
	if ( $membership == 'empresarial' ) {
		$entity = 'empresa';
	} elseif ( $membership == 'institucional' ) {
		$entity = 'institución';
	} elseif ( $membership == 'choose' ) {
		$entity = 'empresa o institución';
	}


	// Set labels for email and name according to type of membership
	if ( $membership == 'empresarial' || $membership == 'institucional' ) {
		$first_label = __('Representative\'s First Name', 'mro-cit-frontend');
		$last_label = __('Representative\'s  Last Name', 'mro-cit-frontend');
		$email_label = __('Representative\'s  Email', 'mro-cit-frontend');
	} else {
		$first_label = __('First Name', 'mro-cit-frontend');
		$last_label = __('Last Name', 'mro-cit-frontend');
		$email_label = __('Email', 'mro-cit-frontend');
	}


	switch ($field):

		case 'choose_membership': ?>

			<legend><?php _e('Choose Membership type', 'mro-cit-frontend'); ?><span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></legend>


			<input type="radio" name="mro_cit_user_membership" value="afiliado_personal" id="choose_personal" data-toggle="personal" checked required><label for="choose_personal" checked>Personal</label>

			<input type="radio" name="mro_cit_user_membership" value="afiliado_empresarial" id="choose_empresarial" data-toggle="enterprise"><label for="formBlue">Empresarial</label>

			<input type="radio" name="mro_cit_user_membership" value="afiliado_institucional" id="choose_institucional" data-toggle="enterprise"><label for="choose_institucional">Institucional</label>

			<input type="radio" name="mro_cit_user_membership" value="junta_directiva" id="choose_junta" data-toggle="junta"><label for="choose_junta">Junta directiva</label>

			<?php
			break;


		case 'username': ?>

			<p>
				<label for="pippin_user_Login"><?php _e('Username', 'mro-cit-frontend'); ?> <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
				<input name="pippin_user_login" id="pippin_user_login" class="required" type="text"/>
				<?php
				if ( $membership == 'empresarial' || $membership == 'institucional' ) { ?>
					<p class="help-text">Sugerimos utilizar algo relacionado al nombre de la <?php echo $entity; ?>.</p>
				<?php } ?>
			</p>

			<?php
			break;


		case 'name_business': ?>

			<?php if ( $membership == 'choose' ) { ?>
				<p data-showfor="enterprise" aria-hidden="true">
			<?php } else { ?>
				<p>
			<?php } ?>

				<label for="mro_cit_user_nickname"><?php echo ucfirst ( $entity ); ?> <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
				<input name="mro_cit_user_nickname" id="mro_cit_user_nickname" type="text"/>
			</p>
			

			<?php
			break;


		case 'email': ?>

			<p>
				<label for="pippin_user_email"><?php echo $email_label; ?> <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
				<input name="pippin_user_email" id="pippin_user_email" class="required" type="email"/>
			</p>
			<?php
			if ( $membership != 'personal' ) { ?>
				<p class="help-text">Este correo será utilizado para administrar la cuenta: notificaciones, cambios de contraseña, etc. Además será suscrito a nuestra lista de correos para recibir las invitaciones a nuestros eventos.</p>
			<?php }

			break;


		case 'name_first': ?>

			<p>
				<label for="pippin_user_first"><?php echo $first_label; ?></label>
				<input name="pippin_user_first" id="pippin_user_first" type="text"/>
			</p>

			<?php
			break;


		case 'name_last': ?>

			<p>
				<label for="pippin_user_last"><?php echo $last_label; ?></label>
				<input name="pippin_user_last" id="pippin_user_last" type="text"/>
			</p>

			<?php
			break;


		case 'phone': ?>

			<p>
	            <label for="mro_cit_user_phone"><?php _e( 'Phone', 'mro-cit-frontend' ) ?></label>
                <input type="text" name="mro_cit_user_phone" id="mro_cit_user_phone" class="input" value="" size="25" />
	        </p>

	        <?php
			break;


		case 'business_sector': 

			if ( $membership == 'choose' ) { ?>
				<p data-showfor="enterprise" aria-hidden="true">
			<?php } else { ?>
				<p>
			<?php } ?>

	            <label for="mro_cit_user_sector"><?php _e( 'Business sector', 'mro-cit-frontend' ) ?></label>
                <input type="text" name="mro_cit_user_sector" id="mro_cit_user_sector" class="input" value="" size="25" />
	        </p>


	        <?php
			break;


		case 'occupation':

			if ( $membership == 'choose' ) { ?>
				<p data-showfor="personal" aria-hidden="false">
			<?php } else { ?>
				<p>
			<?php } ?>

				<label for="mro_cit_user_occupation"><?php _e( 'Occupation', 'mro-cit-frontend' ) ?></label>
		                <input type="text" name="mro_cit_user_occupation" id="mro_cit_user_occupation" class="input" value="" size="25" />
	        </p>

	        <?php
			break;


		case 'workplace':

			if ( $membership == 'choose' ) { ?>
				<p data-showfor="personal" aria-hidden="false">
			<?php } else { ?>
				<p>
			<?php } ?>

				<label for="mro_cit_user_company"><?php _e( 'Company', 'mro-cit-frontend' ) ?></label>
			                <input type="text" name="mro_cit_user_company" id="mro_cit_user_company" class="input" value="" size="25" />
			</p>

			<?php
			break;


		case 'country': ?>

	        <p>
	            <label for="mro_cit_user_country"><?php _e( 'Country', 'mro-cit-frontend' ) ?><br />

                <select class="cmb2_select" name="mro_cit_user_country" id="mro_cit_user_country">

                    <?php
                    $countries = country_list();

                    foreach ($countries as $key => $country) {
                        echo '<option value="' . $key . '">' . $country . '</option>';
                    }
                    ?>

                </select>
	             </label>
	        </p>

	        <?php
			break;


		case 'password': ?>

			<p>
				<label for="password"><?php _e('Password', 'mro-cit-frontend'); ?> <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
				<input name="pippin_user_pass" id="password" class="required" type="password"/>
			</p>

			<p>
				<label for="password_again"><?php _e('Password Again', 'mro-cit-frontend'); ?> <span aria-hidden="true" role="presentation" class="field_required" style="color:#ee0000;">*</span></label>
				<input name="pippin_user_pass_confirm" id="password_again" class="required" type="password"/>
			</p>

			<span id="password-strength"></span>

			<?php
			break;


		case 'submit': ?>

			<p>
				<input type="hidden" name="pippin_register_nonce" value="<?php echo wp_create_nonce('pippin-register-nonce'); ?>"/>

				<?php
				if ( $membership == 'empresarial' ) { ?>
					<input type="hidden" name="mro_cit_user_membership" value="afiliado_empresarial_pendiente"/>
				<?php } elseif ( $membership == 'institucional' ) { ?>
					<input type="hidden" name="mro_cit_user_membership" value="afiliado_institucional_pendiente"/>
				<?php } elseif ( $membership == 'personal' )  { ?>
					<input type="hidden" name="mro_cit_user_membership" value="afiliado_personal"/>
				<?php } ?>

				<?php
				if ( current_user_can( 'manage_temp_subscribers' ) ) { ?>
					<input type="submit" class="button button-primary" value="<?php _e('Add member', 'mro-cit-frontend'); ?>" />
				<?php } else { ?>
					<input type="submit" class="button button-primary" value="<?php _e('Become a member', 'mro-cit-frontend'); ?>" />
				<?php } ?>

			</p>

			<?php
			break;

	endswitch;
}