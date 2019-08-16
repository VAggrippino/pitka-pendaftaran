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
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_shortcode( 'PITKA-Borang-Pendaftaran', array( $this, 'shortcode' ) );
		}

		public static function register_styles() {
			wp_register_style( 'borang-pendaftaran-style', plugins_url( 'css/borang-pendaftaran.css', __FILE__ ) );
		}

		public static function register_scripts() {
			wp_register_script( 'borang-pendaftaran-script', plugins_url( 'js/borang-pendaftaran.js', __FILE__ ) );
			wp_register_script( 'autoExpandTextarea-script', plugins_url( 'js/autoExpandTextarea.js', __FILE__ ) );
			wp_register_script( 'fontawesome', 'https://kit.fontawesome.com/6cef02ea94.js' );
		}

		public static function shortcode() {
			wp_enqueue_style('borang-pendaftaran-style');
			wp_enqueue_script('borang-pendaftaran-script');
			wp_enqueue_script('autoExpandTextarea-script');
			wp_enqueue_script('fontawesome');
			$pitka_bp_form = file_get_contents( plugins_url( 'form.html', __FILE__ ) );
			return $pitka_bp_form;
		}

		private function create_table_pendaftaran() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'pitka_pendaftaran';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				update_date timestamp DEFAULT CURRENT_TIMESTAMP
				nama varchar(255) NOT NULL
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
				nama_organisasi varchar(255),
				alamat_organisasi varchar(255),
				tingkat_pendapatan varchar(10),
				faktor_menjadi_ibu_tungga varchar(20),
				tanggungan_bilangan tinyint,
				tanggungan_anak_bersekolah tinyint,
				tanggungan_anak_berkerja tinyint,
				tanggungan_perkerjaan_anak varchar(255),
				tanggungan_anak_menggangur tinyint
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		public static function install() {
			$this->create_table_pendaftaran();
		}
	}

	$pitka_bp = new PITKA_Borang_Pendaftaran();
}