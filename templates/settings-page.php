<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">

		<?php settings_fields( 'wc_dynamic_taxes' ); ?>

		<?php do_settings_sections( 'dorzki-wc-dynamic-taxes' ); ?>

		<?php submit_button( __( 'Save Settings', 'dorzki-wc-dynamic-taxes' ) ); ?>

	</form>
</div>
