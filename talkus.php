<?php

/**
 *	Plugin Name: Talkus. Your help desk. In Slack.
 *	Plugin URI: https://wordpress.org/plugins/talkus/
 *	Description: With Talkus, Slack becomes the place for your team to communicate with customers, personally, on your website by live chat or by email, phone or SMS.
 *	Version: 1.1
 *	Author: Francois Lecroart / Talkus
 **/

// Security check...
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access to this file is forbidden.' );
	exit;
}

class Talkus {

  	var $options = array();
	var $db_version = 1;

	function __construct() {


		//add admin init hook
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		//add admin panel
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

 		// Setting plugin defaults here:
		$this->option_defaults = array(
			'appId' => '',
			'showInFrontend' => true,
			'showInBackend' => false,
			'db_version' => $this->db_version,
		);



		// Get options
		$this->options = wp_parse_args( get_option( 'talkus_options' ), $this->option_defaults );
		//add frontend wp_head hook
		if($this->options['showInFrontend'])	add_action( 'wp_head', array( $this, 'show_chat' ) );
		//add backend admin_head hook
		if($this->options['showInBackend']) 	add_action( 'admin_head', array( $this, 'show_chat' ) );


	}

	function show_chat() {


		if ( isset ( $this->options['appId'] ) ) {

			$this->options['appId'] = esc_attr( $this->options['appId'] );

			$logged_user = wp_get_current_user();
			?>
			<script type="text/javascript">
			(function(t,a,l,k,u,s,e){if(!t[u]){t[u]=function(){(t[u].q=t[u].q||[]).push(arguments)},t[u].l=1*new Date();s=a.createElement(l),e=a.getElementsByTagName(l)[0];s.async=1;s.src=k;e.parentNode.insertBefore(s,e)}})(window,document,"script","//www.talkus.io/plugin.beta.js","talkus");

				<?php if ($logged_user->ID) { ?>
				talkus('init', '<?php echo $this->options['appId']; ?>', {
					id: '<?php echo $logged_user->ID; ?>',
					name: '<?php echo $logged_user->display_name; ?>',
					email: '<?php echo $logged_user->user_email; ?>',
				});
				<?php } else { ?>
				talkus('init', '<?php echo $this->options['appId']; ?>');
				<?php } ?>

			</script>
			<?php

		}
	}

	function admin_init() {
		//Fetch and set up options.
		$this->options = wp_parse_args( get_option( 'talkus_options' ), $this->option_defaults );

		// Register Settings
		$this->register_settings();
	}

	function admin_menu() {
		add_management_page( __('Talkus'), __('Talkus'), 'manage_options', 'talkus-settings', array( $this, 'talkus_settings' ) );
	}

	function register_settings() {
		register_setting( 'talkus', 'talkus_options', array( $this, 'talkus_sanitize' ) );

		// The main section
		add_settings_section( 'talkus_settings_section', 'Talkus Settings', array( $this, 'talkus_settings_section_callback'), 'talkus-settings' );

		// The application ID Fields
		add_settings_field( 'appId', 'Application ID', array( $this, 'widget_id_callback'), 'talkus-settings', 'talkus_settings_section' );

		// The Show in frontend checkbox
		add_settings_field( 'showInFrontend', 'Show in frontend', array( $this, 'showInFrontend_callback'), 'talkus-settings', 'talkus_settings_section' );

		// The Show in backend checkbox
		add_settings_field( 'showInBackend', 'Show in backend', array( $this, 'showInBackend_callback'), 'talkus-settings', 'talkus_settings_section' );


	}

	function widget_id_callback() {
		?>
		<input type="input" id="talkus_options[appId]" name="talkus_options[appId]" value="<?php echo ( $this->options['appId'] ); ?>" >
		<label for="talkus_options[appId]"><?php _e('Add your application ID to enable Talkus', 'talkus'); ?></label>
		<?php
	}


	function showInFrontEnd_callback() {
		?>
		<input type="checkbox" <?php if ( $this->options['showInFrontend'] ) echo "checked"; ?> id="talkus_options[showInFrontend]" name="talkus_options[showInFrontend]" value="1" >
		<label for="talkus_options[showInFrontend]"><?php _e('Show Talkus in the front end', 'talkus'); ?></label>
		<?php
	}


	function showInBackEnd_callback() {
		?>
		<input type="checkbox" <?php if ( $this->options['showInBackend'] ) echo "checked"; ?> id="talkus_options[showInBackend]" name="talkus_options[showInBackend]" value="1" >
		<label for="talkus_options[showInBackend]"><?php _e('Show Talkus in the back end', 'talkus'); ?></label>
		<?php
	}


	function talkus_settings() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Talkus', 'talkus' ); ?></h2>
			<form action="options.php" method="POST" >
		    <?php
			    settings_fields('talkus');
			    do_settings_sections('talkus-settings');
			    submit_button();
		    ?>
			</form>
		</div>
		<?php
	}

	function talkus_sanitize($input) {
		$options = $this->options;

		$input['db_version'] = $this->db_version;

		foreach ($options as $key=>$value) {
			$output[$key] = sanitize_text_field($input[$key]);
		}

		return $output;
	}

	function add_settings_link($links, $file) {
		if (plugin_basename( __FILE__ ) == $file) {
			$settings_link = '<a href="' . admin_url('tools.php?page=talkus-settings') .'">' . __('Settings', 'talkus') . '</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}


	function talkus_settings_callback(){

	}

	function talkus_settings_section_callback () {
		echo 'Set your Application Id and if where you want the plugin (front end or admin).<br>';
		echo 'If you want to customize your widget go to <a target="_blank" href="https://app.talkus.io/admin/settings">Talkus settings page</a>';
	}

}

new Talkus();
