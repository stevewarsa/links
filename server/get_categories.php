<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept');
$db = new SQLite3('db/links.sqlite');
$results = $db->query("select column_cd, column_tx from categories where column_cd <> '' order by column_tx");

$categories = array();
while ($row = $results->fetchArray()) {
    $category = new stdClass();
    $category->categoryCd = $row['column_cd'];
    $category->categoryTx = $row['column_tx'];
    array_push($categories, $category);
}
$db->close();
print_r(json_encode($categories));
