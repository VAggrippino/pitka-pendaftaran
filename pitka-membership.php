<?php
global $wpdb;
$members = $wpdb->get_results( "SELECT * from {$wpdb->prefix}pitka_member" );

?>
<div class="pitka-membership">
	<h1 class="pitka-membership--title">PITKA Members</h1>
	<div class="pitka-membership--filter">
		<label for="pitka-membership--filter--search">Search:</label>
		<input type="text"
			id="pitka-membership--filter--search"
			class="pitka-membership--filter--search"
			placeholder="Nama / IC">
	</div>
	<table class="pitka-membership--table">
		<thead>
			<tr>
				<th>Nama</th>
				<th>Kad Pengenalan Baru</th>
				<th>Dikemaskinikan</th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach ( $members as $member ) {
		$birthdate = explode( '-', $member->tarikh_lahir );
		$birth_year = $birthdate[0];
		$birth_month = $birthdate[1];
		$birth_day = $birthdate[2];

		$current_date = explode( '-', current_time( 'mysql' ) );
		$current_year = $current_date[0];
		$current_month = $current_date[1];
		$current_day = $current_date[2];

		$umur = $current_year - $birth_year;

		if ( $birth_month > $current_month ) {
			$umur = $umur - 1;
		} elseif ( $birth_month === $current_month ) {
			if ( $current_day < $birth_day ) {
				$umur = $umur - 1;
			} elseif ( $current_day === $birth_day ) {
				$umur = $umur . ' <i class="fas fa-birthday-cake"></i>';
			}
		}

		$umur = $umur;

		echo "<tr>";
		echo "<td>{$member->nama}</td>";
		echo "<td>{$member->kad_pengenalan_baru}</td>";
		echo "<td>{$member->update_date}</td>";
		echo "</tr>";
	}
	?>
	</tbody>
	</table>
</div>