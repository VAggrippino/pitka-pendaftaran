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

function debug_dump( $data ) {
	$local_addresses = array( '127.0.0.1', '::1' );
	if ( in_array( $_SERVER['REMOTE_ADDR'] , array( '127.0.0.1', '::1' ) ) ) {
		echo "<textarea style='width: 100%; height: 30rem;'>";
		var_dump( $data );
		echo "</textarea>";
	}
}

function debug_show( $line ) {
	if ( in_array( $_SERVER['REMOTE_ADDR'] , array( '127.0.0.1', '::1' ) ) ) {
		echo "<p>$line</p>";
	}
}

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
			// If the nonce field isn't set, don't do anything.
			if ( !empty( $_POST['pitka_pendaftaran_nonce'] ) ) {
				if ( !wp_verify_nonce( $_POST['pitka_pendaftaran_nonce'], 'process_pitka_pendaftaran' ) ) {
					die( 'You are not authorized to perform this action.' );
				} else {
					$member_id = $this->create_member( $_POST );

					// The second level of the tables array must have indexes that match
					// both the HTML form field name and the database column for the
					// corresponding table.

					/* tables array format:
						*db_table_name* => array(
							*form field / column name* => *HTML input name*
						)
					*/
					$tables = array(
						'pitka_member_aset' => array(
							'description' => 'aset--description',
							'sendiri' => 'aset--sendiri',
						),

						'pitka_member_permasalahan' => array(
							'description' => 'masalah--description',
							'diri' => 'masalah--diri',
							'tanggungan' => 'masalah--tanggungan',
						),

						'pitka_member_keperluan' => array(
							'description' => 'keperluan--description',
							'diri' => 'keperluan--diri',
							'tanggungan' => 'keperluan--tanggungan',
						),

						'pitka_member_bantuan' => array(
							'jenis' => 'bantuan--jenis',
							'agency' => 'bantuan--agency',
						),

						'pitka_member_program_received' => array(
							'description' => 'program-received--description',
							'penganjur' => 'program-received--penganjur',
							'penilaian' => 'program-received--penilaian',
						),

						'pitka_member_program_suggested' => array(
							'description' => 'program-suggested--description',
							'penganjur' => 'program-suggested--penganjur',
							'pendek' => 'program-suggested--pendek',
							'panjang' => 'program-suggested--panjang',
						),
					);

					foreach( $tables as $table_name => $fields ) {
						$this->add_items( $table_name, $member_id, $fields, $_POST );
					}
				}
			}
		}

		private function create_pitka_table( $table_name, $fields ) {
			global $wpdb;

			$table = $wpdb->prefix . $table_name;
			$charset_collate = $wpdb->get_charset_collate();

			error_log( "Creating '$table' table..." );
			$result = $wpdb->query( "CREATE TABLE $table ( $fields ) $charset_collate;" );
			if ( false === $result ) {
				die( "Error while attempting to create '{$table}' table.\n{$wpdb->last_error}" );
			}
		}

		public function create_tables() {
			global $wpdb;
			$tables = array(
				'pitka_member' => "
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
					bilangan_tanggungan tinyint DEFAULT 0,
					bilangan_anak_bersekolah tinyint DEFAULT 0,
					bilangan_anak_bekerja tinyint DEFAULT 0,
					pekerjaan_anak varchar(255),
					bilangan_anak_menganggur tinyint DEFAULT 0,
					PRIMARY KEY  (id)
				",

				'pitka_member_aset' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					description varchar(255),
					sendiri boolean DEFAULT 0,
					PRIMARY KEY  (id),
					CONSTRAINT `fk_aset_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
				",

				'pitka_member_permasalahan' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					description varchar(255),
					diri boolean DEFAULT 0,
					tanggungan boolean DEFAULT 0,
					PRIMARY KEY  (id),
					CONSTRAINT `fk_permasalahan_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
				",

				'pitka_member_keperluan' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					description varchar(255),
					diri boolean DEFAULT 0,
					tanggungan boolean DEFAULT 0,
					PRIMARY KEY  (id),
					CONSTRAINT `fk_keperluan_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
				",

				'pitka_member_bantuan' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					jenis varchar(255),
					agency varchar(255),
					PRIMARY KEY  (id),
					CONSTRAINT `fk_bantuan_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
				",

				'pitka_member_program_received' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					description varchar(255),
					penganjur varchar(255),
					penilaian tinyint(1),
					PRIMARY KEY  (id),
					CONSTRAINT `fk_program_received_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
				",

				'pitka_member_program_suggested' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					description varchar(255),
					pendek boolean DEFAULT 0,
					panjang boolean DEFAULT 0,
					PRIMARY KEY  (id),
					CONSTRAINT `fk_program_suggested_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id)
				",

				'pitka_fee' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					description varchar(255),
					amount decimal(10,2),
					auto_add boolean DEFAULT 0,
					PRIMARY KEY  (id)
				",

				'pitka_member_payment' => "
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					member_id mediumint(9),
					fee_id mediumint(9),
					create_date timestamp DEFAULT '0000-00-00 00:00:00' NOT NULL,
					update_date timestamp DEFAULT CURRENT_TIMESTAMP,
					paid boolean DEFAULT 0,
					PRIMARY KEY  (id),
					CONSTRAINT `fk_payment_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id),
					CONSTRAINT `fk_payment_fee` FOREIGN KEY (fee_id) REFERENCES {$wpdb->prefix}pitka_fee (id)
				",
			);

			foreach ( $tables as $table_name => $fields ) {
				$this->create_pitka_table( $table_name, $fields );
			}

			$installed_db_version = get_option( 'pitka_pendaftaran_db_version' );
			//update_option( 'pitka_pendaftaran_db_version', $this->pitka_pendaftaran_db_version );
		}

		/**
		 * Create member in the database.
		 * 
		 * @param array $member_data Data for a new user to be added to the database.
		 * @return int Insert ID generated by the database.
		 * 
		 */
		private function create_member( $member_data ) {
			debug_show( "MEMBER DATA:" );
			debug_dump($member_data);

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

		private function add_items( $table, $member_id, $fields, $form_data ) {
			$items = array();
			$item_index = 0;

			// Determine if a form field is one of the ones we're looking for.
			// It's a "prefix" because there's a number appended to field names that are
			// part of lists.
			$prefix_matches = function( $prefix, $field_name ) {
				return strncmp( $prefix, $field_name, strlen( $prefix ) ) === 0;
			};

			// Check through all the form's fields
			foreach ( $form_data as $field_name => $value ) {

				// Check each required field
				foreach( $fields as $db_field => $prefix ) {

					// If this is one of our fields, add the value to the items array
					if ( $prefix_matches( $prefix, $field_name ) ) {

						// If this is the key field, increment the item index...
						if ( $db_field === array_key_first($fields) ) {

							// ...but only if the current index exists
							if ( array_key_exists( $item_index, $items ) ) {
								$item_index = $item_index + 1;
							}
						}

						// Add the value to the list of items
						$items[$item_index][$db_field] = $value;
					}
				}
			}

			// Insert new records into the database
			foreach ( $items as $fields ) {
				global $wpdb;
				$result = $wpdb->insert( "{$wpdb->prefix}$table",
					array_merge(
						array(
							'member_id' => $member_id,
							'create_date' => current_time( 'mysql', 0 ),
						),
						$fields
					)
				);

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