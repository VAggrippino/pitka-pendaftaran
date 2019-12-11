<?php
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