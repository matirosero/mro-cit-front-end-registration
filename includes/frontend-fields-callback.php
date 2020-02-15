<?php

/*
 * registration form fields
 */
function cit_print_field( $field = null, $membership = 'personal' ) {

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


		case 'username':

			$username = '';

			// If form has been submitted
			if ( isset( $_POST["pippin_user_login"] ) ) {
				$username = sanitize_user($_POST["pippin_user_login"]);
			} ?>

			<p>
				<label for="pippin_user_Login"><?php _e('Username', 'mro-cit-frontend'); ?></label>
				<input name="pippin_user_login" id="pippin_user_login" class="required" type="text" value="<?php echo $username; ?>" />
				<?php
				if ( $membership == 'empresarial' || $membership == 'institucional' ) { ?>
					<p class="help-text">Sugerimos utilizar algo relacionado al nombre de la <?php echo $entity; ?>.</p>
				<?php } ?>
			</p>

			<?php
			break;


		case 'name_business':

			$name_business = '';

			// If form has been submitted
			if ( isset( $_POST["mro_cit_user_nickname"] ) ) {
				$name_business = sanitize_text_field($_POST["mro_cit_user_nickname"]);
			} ?>

			<?php if ( $membership == 'choose' ) { ?>
				<p data-showfor="enterprise" aria-hidden="true">
			<?php } else { ?>
				<p>
			<?php } ?>

				<label for="mro_cit_user_nickname"><?php echo ucfirst ( $entity ); ?></label>
				<input name="mro_cit_user_nickname" id="mro_cit_user_nickname" class="required" type="text" value="<?php echo $name_business; ?>" />
			</p>
			

			<?php
			break;


		case 'email':

			$email = '';

			// If form has been submitted
			if ( isset( $_POST["pippin_user_email"] ) ) {
				$email = sanitize_email($_POST["pippin_user_email"]);
			} ?>

			<p>
				<label for="pippin_user_email"><?php echo $email_label; ?></label>
				<input name="pippin_user_email" id="pippin_user_email" class="required" type="email" value="<?php echo $email; ?>" />
			</p>
			<?php
			if ( $membership != 'personal' ) { ?>
				<p class="help-text">Este correo será utilizado para administrar la cuenta: notificaciones, cambios de contraseña, etc. Además será suscrito a nuestra lista de correos para recibir las invitaciones a nuestros eventos.</p>
			<?php }

			break;


		case 'name_first':

			$name_first = '';

			// If form has been submitted
			if ( isset( $_POST["pippin_user_first"] ) ) {
				$name_first = sanitize_text_field($_POST["pippin_user_first"]);
			} ?>

			<p>
				<label for="pippin_user_first"><?php echo $first_label; ?></label>
				<input name="pippin_user_first" id="pippin_user_first" class="required" type="text" value="<?php echo $name_first; ?>" />
			</p>

			<?php
			break;


		case 'name_last': 

			$name_last = '';

			// If form has been submitted
			if ( isset( $_POST["pippin_user_last"] ) ) {
				$name_last = sanitize_text_field($_POST["pippin_user_last"]);
			} ?>

			<p>
				<label for="pippin_user_last"><?php echo $last_label; ?></label>
				<input name="pippin_user_last" id="pippin_user_last" class="required" type="text" value="<?php echo $name_last; ?>" />
			</p>

			<?php
			break;


		case 'phone': 

			$phone = '';

			// If form has been submitted
			if ( isset( $_POST["mro_cit_user_phone"] ) ) {
				$phone = sanitize_text_field($_POST["mro_cit_user_phone"]);
			} ?>

			<p>
	            <label for="mro_cit_user_phone"><?php _e( 'Phone', 'mro-cit-frontend' ) ?></label>
                <input type="text" name="mro_cit_user_phone" id="mro_cit_user_phone" class="input required" size="25" value="<?php echo $phone; ?>" />
	        </p>

	        <?php
			break;


		case 'business_sector': 

			$sector = '';

			// If form has been submitted
			if ( isset( $_POST["mro_cit_user_sector"] ) ) {
				$sector = sanitize_text_field($_POST["mro_cit_user_sector"]);
			} 

			if ( $membership == 'choose' ) { ?>
				<p data-showfor="enterprise" aria-hidden="true">
			<?php } else { ?>
				<p>
			<?php } ?>

	            <label for="mro_cit_user_sector"><?php _e( 'Business sector', 'mro-cit-frontend' ) ?></label>
                <input type="text" name="mro_cit_user_sector" id="mro_cit_user_sector" class="input" size="25"  value="<?php echo $sector; ?>" />
	        </p>


	        <?php
			break;


		case 'occupation': 

			$occupation = '';

			// If form has been submitted
			if ( isset( $_POST["mro_cit_user_occupation"] ) ) {
				$occupation = sanitize_text_field($_POST["mro_cit_user_occupation"]);
			}


			if ( $membership == 'choose' ) { ?>
				<p data-showfor="personal" aria-hidden="false">
			<?php } else { ?>
				<p>
			<?php } ?>

				<label for="mro_cit_user_occupation"><?php _e( 'Occupation', 'mro-cit-frontend' ) ?></label>
                <input type="text" name="mro_cit_user_occupation" id="mro_cit_user_occupation" class="required input" size="25" value="<?php echo $occupation; ?>" />
	        </p>

	        <?php
			break;


		case 'workplace': 

			$workplace = '';

			// If form has been submitted
			if ( isset( $_POST["mro_cit_user_company"] ) ) {
				$workplace = sanitize_text_field($_POST["mro_cit_user_company"]);
			}

			if ( $membership == 'choose' ) { ?>
				<p data-showfor="personal" aria-hidden="false">
			<?php } else { ?>
				<p>
			<?php } ?>

				<label for="mro_cit_user_company"><?php _e( 'Company or educational institution', 'mro-cit-frontend' ) ?></label>
                <input type="text" name="mro_cit_user_company" id="mro_cit_user_company" class="required input" size="25" value="<?php echo $workplace; ?>" />
			</p>

			<?php
			break;


		case 'country':  

			$selected_country = '';

			// If form has been submitted
			if ( isset( $_POST["mro_cit_user_country"] ) && mro_cit_validate_country( $_POST["mro_cit_user_country"] ) ) {

				$selected_country = $_POST["mro_cit_user_country"];

			} ?>

	        <p>
	            <label for="mro_cit_user_country"><?php _e( 'Country', 'mro-cit-frontend' ) ?><br />

                <select class="cmb2_select" name="mro_cit_user_country" id="mro_cit_user_country">

                    <?php
                    $countries = country_list();

                    foreach ($countries as $key => $country) {

                    	if ( $country == $selected_country ) {
                    		echo '<option value="' . $key . '"  selected="selected" >' . $country . '</option>';
                    	} else {
                    		echo '<option value="' . $key . '">' . $country . '</option>';
                    	}

                    } ?>

                </select>
	            </label>
	        </p>

	        <?php
			break;


		case 'password':  

			$password = '';

			// If form has been submitted
			if ( isset( $_POST["pippin_user_pass"] ) && isset( $_POST["pippin_user_pass_confirm"] ) && $_POST["pippin_user_pass"] == $_POST["pippin_user_pass_confirm"] ) {
				
				$password = $_POST["pippin_user_pass"];

			} ?>

			<p>
				<label for="password"><?php _e('Password', 'mro-cit-frontend'); ?></label>
				<input name="pippin_user_pass" id="password" class="required" type="password" value="<?php echo $password; ?>" />
			</p>

			<p>
				<label for="password_again"><?php _e('Password Again', 'mro-cit-frontend'); ?></label>
				<input name="pippin_user_pass_confirm" id="password_again" class="required" type="password" value="<?php echo $password; ?>" />
			</p>

			<span id="password-strength"></span>

			<?php
			break;


		case 'submit': ?>

			<p>
				<input type="hidden" name="pippin_register_nonce" value="<?php echo wp_create_nonce('pippin-register-nonce'); ?>"/>

				<?php
				if ( $membership == 'empresarial' ) { 
					$membership_type = 'afiliado_empresarial_pendiente';
				} elseif ( $membership == 'institucional' ) { 
					$membership_type = 'afiliado_institucional_pendiente';
				} elseif ( $membership == 'personal' )  {  
					$membership_type = 'afiliado_personal';
				} 

				if ( $membership != 'choose' ) { ?>
					<input type="hidden" name="mro_cit_user_membership" value="<?php echo $membership_type; ?>"/>
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