<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
error_log("get_links.php - entering");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept');
header('Content-Type: application/json; charset=utf8; Accept: application/json');

error_log("get_links.php - opening DB...");
$db = new SQLite3('db/links.sqlite');
error_log("get_links.php - DB opened, running query");
$results = $db->query('select id, date_time_link_saved, url, title, addlcomments, category, sent from links order by date_time_link_saved desc');
error_log("get_links.php - query ran, processing results");

$links = array();
while ($row = $results->fetchArray()) {
    $link = new stdClass;
    $link->id = $row['id'];
    $link->date_time_link_saved = $row['date_time_link_saved'];
    $link->url = $row['url'];
    $link->title = utf8_encode($row['title']);
    $link->addlcomments = $row['addlcomments'];
    $link->category = $row['category'];
    $link->sent = $row['sent'];
    array_push($links, $link);
}
error_log("get_links.php - closing DB");

$db->close();
print_r(json_encode($links));
