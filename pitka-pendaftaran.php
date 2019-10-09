<?php
/**
 * @package PITKA\Registration
 */
/*
Plugin Name: PITKA Pendaftaran
Plugin URI: https://www.aggrippino.com/wordpress-plugins/pitka-pendaftaran
Description: PITKA Borang Pendaftaran Online
Version: 1.0.0
Author: Vince Aggrippino
Author URI: https://www.aggrippino.com
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'PITKA_Borang_Pendaftaran' ) ) {
	class PITKA_Borang_Pendaftaran {
		var $pitka_pendaftaran_db_version = '1.0.0';

		public function __construct() {
			// Handle a Pendaftaran form submission after WP page init
			add_action( 'init', array( $this, 'handle_form' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

			add_shortcode( 'PITKA-Borang-Pendaftaran', array( $this, 'shortcode' ) );

			add_action( 'admin_menu', array( $this, 'pitka_membership_menu' ) );
			register_activation_hook( __FILE__, array( $this, 'create_tables' ) );
		}

		public function load_custom_wp_admin_style() {
			wp_register_style( 'borang-pendaftaran-style', plugins_url( 'css/borang-pendaftaran.css', __FILE__ ) );
		}

		public function pitka_membership_menu() {
			$membership_menu = add_menu_page(
				'Membership',
				'Members',
				'manage_options',
				'pitka-membership',
				array( $this, 'show_members' )
			);
			add_action( 'load-' . $membership_menu, array( $this, 'enqueue' ) );

			$membership_submenu = add_submenu_page(
				'pitka-membership',
				'Members',
				'All Members',
				'manage_options',
				'pitka-membership',
				array( $this, 'show_members' )
			);
			add_action( 'load-' . $membership_submenu, array( $this, 'enqueue' ) );

			$unpaid_submenu = add_submenu_page(
				'pitka-membership',
				'Members with Unpaid Fees',
				'Unpaid Members',
				'manage_options',
				'pitka-membership-unpaid',
				array( $this, 'show_unpaid_members' )
			);
			add_action( 'load-' . $unpaid_submenu, array( $this, 'enqueue' ) );

			$new_submenu = add_submenu_page(
				'pitka-membership',
				'New Members',
				'New Members',
				'manage_options',
				'pitka-membership-new',
				array( $this, 'show_new_members' )
			);
			add_action( 'load-' . $new_submenu, array( $this, 'enqueue' ) );

			$tools_submenu = add_submenu_page(
				'pitka-membership',
				'Membership Tools',
				'Membership Tools',
				'manage_options',
				'pitka-membership-tools',
				array( $this, 'show_membership_tools' )
			);
			add_action( 'load-' . $tools_submenu, array( $this, 'enqueue' ) );

			$settings_submenu = add_submenu_page(
				'pitka-membership',
				'Membership Settings',
				'Membership Settings',
				'manage_options',
				'pitka-membership-settings',
				array( $this, 'show_membership_settings' )
			);
			add_action( 'load-' . $settings_submenu, array( $this, 'enqueue' ) );

			$fees_submenu = add_submenu_page(
				'pitka-membership',
				'Membership Fees',
				'Membership Fees',
				'manage_options',
				'pitka-membership-fees',
				array( $this, 'show_membership_fees' )
			);
			add_action( 'load-' . $fees_submenu, array( $this, 'enqueue' ) );
		}

		public function show_members() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership.php' );
		}

		public function show_unpaid_members() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-unpaid.php' );
		}

		public function show_new_members() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-new.php' );
		}

		public function show_membership_settings() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-settings.php' );
		}

		public function show_membership_tools() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-tools.php' );
		}

		public function show_membership_fees() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-fees.php' );
		}

		public function register_styles() {
			wp_register_style( 'borang-pendaftaran-style', plugins_url( 'css/borang-pendaftaran.css', __FILE__ ) );
		}

		public function register_scripts() {
			wp_register_script( 'borang-pendaftaran-script', plugins_url( 'js/borang-pendaftaran.js', __FILE__ ) );
			wp_register_script( 'autoExpandTextarea-script', plugins_url( 'js/autoExpandTextarea.js', __FILE__ ) );
			wp_register_script( 'autoFormatCurrency-script', plugins_url( 'js/autoFormatCurrency.js', __FILE__ ) );
			wp_register_script( 'printFriendlyCheckboxes-script', plugins_url( 'js/printFriendlyCheckboxes.js', __FILE__ ) );
			wp_register_script( 'fontawesome', 'https://kit.fontawesome.com/6cef02ea94.js' );
		}

		public function enqueue() {
			wp_enqueue_style('borang-pendaftaran-style');
			wp_enqueue_script('borang-pendaftaran-script');
			wp_enqueue_script('autoExpandTextarea-script');
			wp_enqueue_script('autoFormatCurrency-script');
			wp_enqueue_script('printFriendlyCheckboxes-script');
			wp_enqueue_script('fontawesome');
		}

		/**
		 * Process and return the registration form.
		 * 
		 * 1. Enqueue the plugin style & JavaScript.
		 * 2. Read in the HTML form.
		 * 3. Replace template-like strings with the related values.
		 * 
		 *  @return string The full HTML of the pendaftaran form.
		 */
		public function shortcode() {
			$this->enqueue();
			$nonce_field = wp_nonce_field( 'process_pitka_pendaftaran', 'pitka_pendaftaran_nonce', false );
			$pitka_bp_form = file_get_contents( plugins_url( 'form.html', __FILE__ ) );
			$pitka_bp_form = str_replace( '{{__NONCE_FIELD__}}', $nonce_field, $pitka_bp_form );
			return $pitka_bp_form;
		}

		/**
		 * Handle PITKA Pendaftaran form submission.
		 * 
		 * 1. Check the WordPress generated nonce
		 * 2. Create user
		 * 3. Add assets
		 * 4. Add permasalahan
		 * 
		 */
		public function handle_form() {
			if ( !empty( $_POST['pitka_pendaftaran_nonce'] ) ) {
				if ( !wp_verify_nonce( $_POST['pitka_pendaftaran_nonce'], 'process_pitka_pendaftaran' ) ) {
					die( 'You are not authorized to perform this action.' );
				} else {
					$new_member_id = $this->create_member( $_POST );
					$this->add_assets( $new_member_id, $_POST );
					$this->add_permasalahan( $new_member_id, $_POST );
				}
			}
		}

		private function create_pitka_table( $sql ) {
			global $wpdb;

			$result = $wpdb->query( $sql );
			if ( false === $result ) {
				die( $wpdb->last_error );
			}
		}

		private function create_table_member() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				nama varchar(255) NOT NULL,
				kad_pengenalan_baru varchar(14),
				tarikh_lahir date,
				tempat_lahir varchar(255),
				alamat_kediaman varchar(255),
				telefon_pejabat varchar(15),
				telefon_rumah varchar(15),
				telefon_bimbit varchar(15),
				bangsa varchar(30),
				agama varchar(30),
				jenis_pekerjaan varchar(255),
				jawatan varchar(255),
				nama_pekerja varchar(255),
				alamat_pekerja varchar(255),
				tingkat_pendapatan varchar(10),
				faktor_menjadi_ibu_tunggal varchar(20),
				bilangan_tanggungan tinyint,
				bilangan_anak_bersekolah tinyint,
				bilangan_anak_bekerja tinyint,
				pekerjaan_anak varchar(255),
				bilangan_anak_menganggur tinyint,
				PRIMARY KEY  (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_aset() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_aset';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				description varchar(255),
				sendiri boolean,
				PRIMARY KEY  (id),
				CONSTRAINT `fk_aset_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_permasalahan() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_permasalahan';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				description varchar(255),
				diri boolean,
				tanggungan boolean,
				PRIMARY KEY  (id),
				CONSTRAINT `fk_permasalahan_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_keperluan() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_keperluan';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				description varchar(255),
				diri boolean,
				tanggungan boolean,
				PRIMARY KEY  (id),
				CONSTRAINT `fk_keperluan_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_bantuan() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_bantuan';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				jenis varchar(255),
				agency varchar(255),
				PRIMARY KEY  (id),
				CONSTRAINT `fk_bantuan_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_program_received() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_program_received';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				description varchar(255),
				penganjur varchar(255),
				penilaian tinyint(1)
			) $charset_collate;";
		}

		private function create_table_program_suggested() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_program_suggested';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				description varchar(255),
				penganjur varchar(255),
				PRIMARY KEY  (id),
				CONSTRAINT `fk_program_suggested_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_fee() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_fee';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				description varchar(255),
				amount decimal(10,2),
				auto_add boolean,
				PRIMARY KEY  (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		private function create_table_payment() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_member_payment';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				member_id mediumint(9),
				fee_id mediumint(9),
				create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				paid boolean,
				PRIMARY KEY  (id),
				CONSTRAINT `fk_payment_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id),
				CONSTRAINT `fk_payment_fee` FOREIGN KEY (fee_id) REFERENCES {$wpdb->prefix}pitka_fee (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		public function create_tables() {
			$installed_db_version = get_option( 'pitka_pendaftaran_db_version' );
			if ( $installed_db_version !== $this->pitka_pendaftaran_db_version ) {
				$this->create_table_member();
				$this->create_table_aset();
				$this->create_table_permasalahan();
				$this->create_table_keperluan();
				$this->create_table_bantuan();
				$this->create_table_program_received();
				$this->create_table_fee();
				$this->create_table_payment();
			}
			update_option( 'pitka_pendaftaran_db_version', $this->pitka_pendaftaran_db_version );
		}

		/**
		 * Create member in the database.
		 * 
		 * @param array $member_data Data for a new user to be added to the database.
		 * @return int Insert ID generated by the database.
		 * 
		 */
		private function create_member( $member_data ) {
			global $wpdb;
			$result = $wpdb->insert( "{$wpdb->prefix}pitka_member", array(
				'create_date' => current_time( 'mysql', 0 ),
				'nama' => $member_data['nama'],
				'kad_pengenalan_baru' => $member_data['kad_pengenalan_baru'],
				'tarikh_lahir' => $member_data['tarikh_lahir'],
				'tempat_lahir' => $member_data['tempat_lahir'],
				'alamat_kediaman' => $member_data['alamat_kediaman'],
				'telefon_pejabat' => $member_data['telefon_pejabat'],
				'telefon_rumah' => $member_data['telefon_rumah'],
				'telefon_bimbit' => $member_data['telefon_rumah'],
				'bangsa' => $member_data['bangsa'],
				'agama' => $member_data['agama'],
				'jenis_pekerjaan' => $member_data['jenis_pekerjaan'],
				'jawatan' => $member_data['jawatan'],
				'nama_pekerja' => $member_data['nama_pekerja'],
				'alamat_pekerja' => $member_data['alamat_pekerja'],
				'tingkat_pendapatan' => $member_data['tingkat_pendapatan'],
				'faktor_menjadi_ibu_tunggal' => $member_data['faktor_menjadi_ibu_tunggal'],
				'bilangan_tanggungan' => $member_data['bilangan_tanggungan'],
				'bilangan_anak_bersekolah' => $member_data['bilangan_anak_bersekolah'],
				'bilangan_anak_bekerja' => $member_data['bilangan_anak_bekerja'],
				'pekerjaan_anak' => $member_data['pekerjaan_anak'],
				'bilangan_anak_menganggur' => $member_data['bilangan_anak_menganggur']
			) );

			if ( false === $result ) {
				die(
					'<strong>Error</strong>:<br>' .
					$wpdb->last_error .
					"<br>Please contact PITKA directly or PITKA Technical Support."
				);
			}

			return $wpdb->insert_id;
		}

		/**
		 * Insert member assets into the database.
		 * 
		 * @param string $member_id User ID generated by the database upon creation.
		 * @param array $member_data Data for a new user to be added to the database.
		 * 
		 */
		private function add_assets( $member_id, $member_data ) {
			$items = array();
			$item_index = 0;

			// Check if it's a description field
			$is_description = function( $field_name ) {
				$description_prefix = 'aset--description';
				return strncmp( $description_prefix, $field_name, strlen( $description_prefix ) ) === 0;
			};

			// Check if it's a sendiri field
			$is_sendiri = function( $field_name ) {
				$sendiri_prefix = 'aset--sendiri';
				return strncmp( $sendiri_prefix, $field_name, strlen( $sendiri_prefix ) ) === 0;
			};

			// Collect the assets from the member data
			foreach( $member_data as $key => $value ) {
				if ( $is_description( $key ) ) {
					if ( array_key_exists( $item_index, $items ) ) {
						$item_index = $item_index + 1;
					}

					$items[$item_index]['description'] = trim( $value );
				}

				if ( $is_sendiri( $key ) ) {
					$items[$item_index]['sendiri'] = $value;
				}
			}

			global $wpdb;
			foreach ( $items as $item ) {
				if ( $item['description'] === '' ) continue;

				$result = $wpdb->insert( "{$wpdb->prefix}pitka_member_aset", array(
					'member_id' => $member_id,
					'create_date' => current_time( 'mysql', 0 ),
					'description' => $item['description'],
					'sendiri' => $item['sendiri'],
				) );

				if ( false === $result ) {
					die(
						'<strong>Error</strong>:<br>' .
						$wpdb->last_error .
						"<br>Please contact PITKA directly or PITKA Technical Support."
					);
				}
			}
		}

		/**
		 * Insert member permasalahan into the database.
		 * 
		 * @param string $member_id User ID generated by the database upon creation.
		 * @param array $member_data Data for a new user to be added to the database.
		 * 
		 */
		private function add_permasalahan( $member_id, $member_data ) {
			$items = array();
			$item_index = 0;

			// Check if it's a description field
			$is_description = function( $field_name ) {
				$description_prefix = 'masalah--description';
				return strncmp( $description_prefix, $field_name, strlen( $description_prefix ) ) === 0;
			};

			// Check if it's a diri field
			$is_diri = function( $field_name ) {
				$diri_prefix = 'masalah--diri';
				return strncmp( $diri_prefix, $field_name, strlen( $diri_prefix ) ) === 0;
			};

			// Check if it's a tanggungan field
			$is_tanggungan = function( $field_name ) {
				$tanggungan_prefix = 'masalah--tanggungan';
				return strncmp( $tanggungan_prefix, $field_name, strlen( $tanggungan_prefix ) ) === 0;
			};

			// Collect the permasalahan from the member data
			foreach( $member_data as $key => $value ) {
				if ( $is_description( $key ) ) {
					if ( array_key_exists( $item_index, $items ) ) {
						$item_index = $item_index + 1;
					}

					$items[$item_index]['description'] = trim( $value );
				}

				if ( $is_diri( $key ) ) {
					$items[$item_index]['diri'] = $value;
				}

				if ( $is_tanggungan( $key ) ) {
					$items[$item_index]['tanggungan'] = $value;
				}
			}

			global $wpdb;
			foreach ( $items as $item ) {
				if ( $item['description'] === '' ) continue;

				array_key_exists( 'diri', $item ) || $item['diri'] = 0;
				array_key_exists( 'tanggungan', $item ) || $item['tanggungan'] = 0;

				$result = $wpdb->insert( "{$wpdb->prefix}pitka_member_permasalahan", array(
					'member_id' => $member_id,
					'create_date' => current_time( 'mysql', 0 ),
					'description' => $item['description'],
					'diri' => $item['diri'],
					'tanggungan' => $item['tanggungan'],
				) );

				if ( false === $result ) {
					die(
						'<strong>Error</strong>:<br>' .
						$wpdb->last_error .
						"<br>Please contact PITKA directly or PITKA Technical Support."
					);
				}
			}
		}
	}

	$pitka_bp = new PITKA_Borang_Pendaftaran();
}