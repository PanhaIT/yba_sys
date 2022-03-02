<?php
// Function
include('includes/function.php');
$query=mysql_query("SELECT id,date FROM reconciles WHERE company_id='" . $companyId . "' AND chart_account_id='" . $coaId . "' ORDER BY created DESC");
while($data=mysql_fetch_array($query)){
?>
<option value="<?php echo $data['id']; ?>"><?php echo dateShort($data['date']); ?></option>
<?php } ?>