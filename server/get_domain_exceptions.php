<?php
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept');
$db = new SQLite3('db/links.sqlite');
$results = $db->query("SELECT domain FROM RANDOM_LINK_DOMAIN_EXCEPTIONS");

$links = array();
while ($row = $results->fetchArray()) {
    array_push($links, $row['domain']);
}
$db->close();
print_r(json_encode($links));
