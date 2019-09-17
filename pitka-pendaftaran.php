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
			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

			add_shortcode( 'PITKA-Borang-Pendaftaran', array( $this, 'shortcode' ) );
			add_action( 'admin_menu', array( $this, 'pitka_membership_menu' ) );
			register_activation_hook( __FILE__, array( $this, 'create_tables' ) );
		}

		public static function load_custom_wp_admin_style() {
			wp_register_style( 'borang-pendaftaran-style', plugins_url( 'css/borang-pendaftaran.css', __FILE__ ) );
		}

		public static function pitka_membership_menu() {
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

		public static function show_members() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership.php' );
		}

		public static function show_unpaid_members() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-unpaid.php' );
		}

		public static function show_new_members() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-new.php' );
		}

		public static function show_membership_settings() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-settings.php' );
		}

		public static function show_membership_tools() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-tools.php' );
		}

		public static function show_membership_fees() {
			$this->enqueue();
			require( plugin_dir_path( __FILE__ ) . 'pitka-membership-fees.php' );
		}

		public static function register_styles() {
			wp_register_style( 'borang-pendaftaran-style', plugins_url( 'css/borang-pendaftaran.css', __FILE__ ) );
		}

		public static function register_scripts() {
			wp_register_script( 'borang-pendaftaran-script', plugins_url( 'js/borang-pendaftaran.js', __FILE__ ) );
			wp_register_script( 'autoExpandTextarea-script', plugins_url( 'js/autoExpandTextarea.js', __FILE__ ) );
			wp_register_script( 'fontawesome', 'https://kit.fontawesome.com/6cef02ea94.js' );
		}

		public static function enqueue() {
			wp_enqueue_style('borang-pendaftaran-style');
			wp_enqueue_script('borang-pendaftaran-script');
			wp_enqueue_script('autoExpandTextarea-script');
			wp_enqueue_script('fontawesome');
		}

		public static function shortcode() {
			$this->enqueue();
			$pitka_bp_form = file_get_contents( plugins_url( 'form.html', __FILE__ ) );
			return $pitka_bp_form;
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP,
				paid boolean,
				PRIMARY KEY  (id),
				CONSTRAINT `fk_payment_member` FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}pitka_member (id),
				CONSTRAINT `fk_payment_fee` FOREIGN KEY (fee_id) REFERENCES {$wpdb->prefix}pitka_fee (id)
			) $charset_collate;";

			$this->create_pitka_table( $sql );
		}

		public static function create_tables() {
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
	}

	$pitka_bp = new PITKA_Borang_Pendaftaran();
}