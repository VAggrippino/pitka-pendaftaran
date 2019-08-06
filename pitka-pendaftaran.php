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
			add_shortcode( 'PITKA-Borang-Pendaftaran', array( $this, 'shortcode' ) );
		}

		public static function register_styles() {
			wp_register_style( 'borang-pendaftaran-style', plugins_url( 'css/borang-pendaftaran.css', __FILE__ ) );
			wp_register_script( 'borang-pendaftaran-script', plugins_url( 'js/borang-pendaftaran.js', __FILE__ ) );
			wp_register_script( 'autoExpandTextarea-script', plugins_url( 'js/autoExpandTextarea.js', __FILE__ ) );
		}

		public static function shortcode() {
			wp_enqueue_style('borang-pendaftaran-style');
			wp_enqueue_script('borang-pendaftaran-script');
			wp_enqueue_script('autoExpandTextarea-script');
			$pitka_bp_form = file_get_contents( plugins_url( 'form.html', __FILE__ ) );
			return $pitka_bp_form;
		}
	}

	$pitka_bp = new PITKA_Borang_Pendaftaran();
}