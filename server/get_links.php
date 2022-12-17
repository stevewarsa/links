<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept');
$db = new SQLite3('db/links.sqlite');
$results = $db->query('select date_time_link_saved, url, title, addlcomments, category, sent from links order by date_time_link_saved desc');

$links = array();
while ($row = $results->fetchArray()) {
    $link = new stdClass();
    $link->date_time_link_saved = $row['date_time_link_saved'];
    $link->url = $row['url'];
    $link->title = $row['title'];
    $link->addlcomments = $row['addlcomments'];
    $link->category = $row['category'];
    $link->sent = $row['sent'];
    array_push($links, $link);
}
$db->close();
print_r(json_encode($links));
