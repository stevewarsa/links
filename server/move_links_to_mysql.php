<?php

include_once('./connect.php');
include_once('./Link.php');

include_once('./Passage.php');

$db = new SQLite3('links.sqlite');
$mysqldb = getConnection();


$results = $db->query('select date_time_link_saved, url, title, addlcomments, category from links order by date_time_link_saved');


$sql = "insert into links (date_time_link_saved, url, title, addlcomments, category, sent) values (NOW(), ?, ?, ?, ?, ?)";
$statement = $mysqldb->prepare($sql);

while ($row = $results->fetchArray()) {

    $link = new Link();

    $link->date_time_link_saved = $row['date_time_link_saved'];

    $link->url = $row['url'];

    $link->title = $row['title'];

    $link->addlcomments = $row['addlcomments'];

    $link->category = $row['category'];
    $sent = $link->category == 'apologetics' ? 'Y' : 'N';

    $statement->bind_param('sssss', $link->url, $link->title, $link->addlcomments, $link->category, $sent);
    if ($statement->execute()) {
	// the user was successfully created in the DB
	error_log('Link was created ...');
    } else {
	echo('There was an error inserting the record: ' . $mysqldb->error);
	
    }
}

$db->close();
$mysqldb->close();
?>


