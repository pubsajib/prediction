<?php
/* Custom Profile Fields */
class Profile {
	function __construct() {
		// FORM
		add_action( 'show_user_profile', [$this, 'crf_show_extra_profile_fields'] );
		add_action( 'edit_user_profile', [$this, 'crf_show_extra_profile_fields'] );
		// VALIDATION
		add_action( 'user_profile_update_errors', [$this, 'crf_user_profile_update_errors', 10, 3] );
		// UPDATE
		add_action( 'personal_options_update', [$this, 'crf_update_profile_fields'] );
		add_action( 'edit_user_profile_update', [$this, 'crf_update_profile_fields'] );
	}
	function crf_show_extra_profile_fields( $user ) {
		$country = get_the_author_meta( 'country', $user->ID );
		$highlight = get_the_author_meta( 'highlight', $user->ID );
		?>
		<h3><?php esc_html_e( 'Other Information', 'prediction' ); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="country"><?php esc_html_e( 'Country', 'prediction' ); ?></label></th>
				<td>
					<select id="country" name="country">
						<option value="">Select country</option>
						<option <?php echo esc_attr( $country ) == 'ind' ? ' selected' : '' ?> value="ind">India</option>
						<option <?php echo esc_attr( $country ) == 'bd' ? ' selected' : '' ?> value="bd">Bangladesh</option>
						<option <?php echo esc_attr( $country ) == 'pak' ? ' selected' : '' ?> value="pak">Pakistan</option>
					</select>
				</td>
			</tr>
			<?php if (is_admin()): ?>
				<tr>
					<th><label for="country"><?php esc_html_e( 'Highlighted', 'prediction' ); ?></label></th>
					<td>
						<label for="highlight"><input type="checkbox" name="highlight" id="highlight" <?php echo esc_attr( $highlight ) ? ' checked' : '' ?>> Special Predictor </label>
					</td>
				</tr>
			<?php endif ?>
		</table>
		<?php
	}
	function crf_user_profile_update_errors( $errors, $update, $user ) {
		if ( ! $update ) return;

		// if ( empty( $_POST['country'] ) ) {
		// 	$errors->add( 'year_of_birth_error', __( '<strong>ERROR</strong>: Please enter your year of birth.', 'prediction' ) );
		// }

		// if ( ! empty( $_POST['country'] ) && intval( $_POST['country'] ) < 1900 ) {
		// 	$errors->add( 'year_of_birth_error', __( '<strong>ERROR</strong>: You must be born after 1900.', 'prediction' ) );
		// }
	}
	function crf_update_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) return false;

		update_user_meta( $user_id, 'country', $_POST['country'] );
		if (is_admin()) update_user_meta( $user_id, 'highlight', $_POST['highlight'] );
	}
}
new Profile;