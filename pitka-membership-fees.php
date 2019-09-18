<?php
global $wpdb;

if ( isset( $_POST['action'] ) && 'add-new' === $_POST['action'] ) {
  $fee = $_POST;
  $description = $_POST['description'];
  $amount = $_POST['amount'];
  $auto_add = isset( $_POST['auto_add'] ) ? 1 : 0;

  $field_types = array( '%s', '%s', '%d' );

  $wpdb->insert( "{$wpdb->prefix}pitka_fee", array(
    'description' => $description,
    'amount' => $amount,
    'auto_add' => $auto_add
  ), $field_types );
}

$fees = $wpdb->get_results("SELECT * from {$wpdb->prefix}pitka_fee", OBJECT);
?>
<div class="pitka-fees">
	<h1>PITKA Membership Fees</h1>
  <div class="pitka-fees-current">
		<h2>Current Fees</h2>
    <table>
      <tr>
        <th>Description</th>
        <th>Amount</th>
        <th>Automatic</th>
      </tr>
			<?php
			if ( 0 === count($fees) ) {
				echo "<tr><td colspan='3'>No records found.</td></tr>";
			}
			foreach ( $fees as $fee ) {
				echo "<tr>";
				echo "<td>{$fee->description}</td>";
				echo "<td>{$fee->amount}</td>";

				echo "<td><input type='checkbox' disabled ";
				if ( '1' === $fee->auto_add ) {
					echo "checked";
				}
        echo "></td>";
        /*
        echo "<td>{$fee->auto_add}</td>";
        echo "</tr>";
        */
			}
			?>
		</table>
	</div>
	<div class="pitka-fees-separator"></div>
  <form class="pitka-fees-form pitka-form" action="#" method="post">
		<h2>Add a New Fee</h2>
    <div class="field">
      <label for="description">Description:</label>
      <input name="description" id="description" required>
    </div>

    <div class="field">
      <label for="amount">Amount:</label>
      <input name="amount" id="amount" type="text"
        data-type="currency" placeholder="100.00" required>
    </div>

    <div class="field">
      <input type="checkbox" name="auto_add" id="auto_add">
      <label for="auto_add">Automatic:</label>
      <p class="comment">
        Fees marked as <em>automatic</em> will be automatically assigned to new
        members.
      </p>
    </div>

    <div class="field">
      <label for="recurrence">Recurrence:</label>
      <select name="recurrence" id="recurrence" disabled>
        <option value="none">None</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
      </select>
      <p class="comment">This feature is not yet implemented.</p>
    </div>
    <div class="field">
      <button type="submit" name="action" value="add-new">Add New Fee</button>
    </div>
  </form>
</div>
