<?php
error_log('Entering /links-app/server/remove_link.php...');
$inputJSON = file_get_contents('php://input');
error_log('[remove_link.php] Here is the incoming data: ' . $inputJSON);
$incomingData = json_decode( $inputJSON );
$succeededStr = "FALSE";
try {
	error_log('[remove_link.php] Creating SQLite database connection...');
	$db = new SQLite3('db/links.sqlite');
	error_log('[remove_link.php] SQLite database connection created');
	error_log('[remove_link.php] preparing statement...');
	$insertSql = "INSERT INTO random_link_domain_exceptions (domain) VALUES (:url)";
	$statement = $db->prepare($insertSql);
	error_log('[remove_link.php] binding values...');
	$doubleSlashPos = strrpos($incomingData, "://");
	$hostOnly = substr($incomingData, $doubleSlashPos + 3);
	error_log('[remove_link.php] $doubleSlashPos=' . $doubleSlashPos . ', $hostOnly=' . $hostOnly);
	$statement->bindValue(':url', $hostOnly);
	error_log('[remove_link.php] executing statement ' . $insertSql . ' with value ' . $hostOnly . '...');

	$succeeded = $statement->execute();
	$succeededStr = $succeeded == TRUE ? "TRUE" : "FALSE";
	error_log('[remove_link.php] closing statement... Did the statement succeed? ' . $succeededStr);
	$statement->close();

	error_log('[remove_link.php] closing database...');

	$db->close();
	error_log('[remove_link.php] Database closed.');

} catch (Exception $e) {
    error_log($e);
}

print_r(json_encode($succeededStr));
?>
