<?php
global $wpdb;
$members = $wpdb->get_results( "SELECT * from {$wpdb->prefix}pitka_member" );

?>
<h1>PITKA Members</h1>
<table>
  <tr>
    <th>Nama</th>
    <th>Umur</th>
    <th>Agama</th>
    <th>Dikemaskinikan</th>
  </tr>
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
  echo "<td>{$umur}</td>";
  echo "<td>{$member->agama}</td>";
  echo "<td>{$member->update_date}</td>";
  echo "</tr>";
}
?>
</table>
