<?php
global $wpdb;

if ( isset( $_POST['action'] ) && 'pitka-search' === $_POST['action'] ) {
  $query = $_POST['pitka-search--query'];

  $sql = <<<ENDSQL
    SELECT *
    FROM {$wpdb->prefix}pitka_member
    WHERE nama LIKE '%{$query}%'
       OR kad_pengenalan_baru LIKE '%{$query}%'
ENDSQL;

$results = $wpdb->get_results($sql, OBJECT);
}
?>
<div class="pitka-search">
	<h1>PITKA Membership Search</h1>
  <form class="pitka-search--form" action="#" method="post">
    <input type="hidden" name="action" value="pitka-search">
    <div class="field search">
      <label for="pitka-search--query">Search:</label>
      <input type="text"
        name="pitka-search--query"
        id="pitka-search--query"
        value="<?php isset( $query ) && print( $query ) ?>"
        placeholder="Nama / IC">
      <button type="submit">Go</button>
    </div>
    <hr>
    <?php if ( isset( $results ) ) {
      ?>
      <table class="pitka-search--results">
        <tr>
          <th>Nama</th>
          <th>IC</th>
        </tr>
        <?php foreach ( $results as $row ) {
          echo "<tr>";
          echo "<td class='pitka-search--results--nama'>{$row->nama}</td>";
          echo "<td class='pitka-search--results--ic'>{$row->kad_pengenalan_baru}</td>";
          echo "</tr>";
        } ?>
      </table>
      <?php
    }
    ?>
  </form>
</div>