<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
include_once('./Passage.php');
$db = new SQLite3('db/links.sqlite');
$results = $db->query("select date_time_link_saved, url, title, addlcomments, category from links where category = 'apologetics' and sent = 'N' order by date_time_link_saved");
$links = array();
while ($row = $results->fetchArray()) {
    $link = new stdClass;
    $link->date_time_link_saved = $row['date_time_link_saved'];
    $link->url = $row['url'];
    $link->title = $row['title'];
    $link->addlcomments = $row['addlcomments'];
    $link->category = $row['category'];
    array_push($links, $link);
}

$db->close();
$db = new SQLite3('../../bible-app/server/db/biblenuggets.db');
$results = $db->query('select passage_id, book_id, chapter, start_verse, end_verse from passage');
$passages = array();
while ($row = $results->fetchArray()) {
    $passage = new Passage();
    $passage->passageId = $row['passage_id'];
    $passage->bookId = $row['book_id'];
    $passage->chapter = $row['chapter'];
    $passage->startVerse = $row['start_verse'];
    $passage->endVerse = $row['end_verse'];
    array_push($passages, $passage);
}
$db->close();
shuffle($passages);
$translation = 'niv';
$db = new SQLite3('../../bible-app/server/db/' . $translation . '.db');
$bookId = $passages[0]->bookId;
$chapter = $passages[0]->chapter;
$startVerse = $passages[0]->startVerse;
$endVerse = $passages[0]->endVerse;
error_log('Getting verse text for passage book:' . $bookId . ',chapter:' . $chapter . ',startVerse:' . $startVerse . ',endVerse:' . $endVerse);
$statement = $db->prepare('select verse, verse_part_id, verse_text, is_words_of_christ, book_id, book_name from verse v, book b where v.book_id = b._id and b._id = :book_id and chapter = :chapter and verse >= :start_verse and verse <= :end_verse order by verse, verse_part_id');
$statement->bindValue(':book_id', $bookId);
$statement->bindValue(':chapter', $chapter);
$statement->bindValue(':start_verse', $startVerse);
$statement->bindValue(':end_verse', $endVerse);
$results = $statement->execute();
$passage = $passages[0];
$lastVerse = $startVerse;
$verse = new Verse();
$passage->addVerse($verse);
while ($row = $results->fetchArray()) {
    $currentVerse = $row["verse"];
    if ($currentVerse != $lastVerse) {
        $lastVerse = $currentVerse;
        $verse = new Verse();
        $passage->addVerse($verse);
    }
    $versePart = new VersePart();
    $versePart->verseNumber = $currentVerse;
    $versePart->versePartId = $row["verse_part_id"];
    $versePart->verseText = $row["verse_text"];
    if ($row["is_words_of_christ"] == "Y") {
        $versePart->wordsOfChrist = TRUE;
    } else {
        $versePart->wordsOfChrist = FALSE;
    }
    $verse->addVersePart($versePart);
    $passage->bookName = $row["book_name"];
}
$statement->close();
$db->close();

$db = new SQLite3('../../bible-app/server/db/memory_Guest.db');
$results = $db->query("SELECT quote_tx FROM quote q, quote_tag qt, tag t where q.quote_id = qt.quote_id and qt.tag_id = t.tag_id and t.tag_name = 'apologetics'");
$quotes = array();
while ($row = $results->fetchArray()) {
    array_push($quotes, $row['quote_tx']);
}
shuffle($quotes);
$db->close();
$update = $_GET["update"];
if ($update == "Y") {
    error_log('Updating records to sent = Y');
    $db = new SQLite3('db/links.sqlite');
    $statement = $db->prepare("update links set sent = 'Y' where category = 'apologetics' and sent = 'N'");
    $statement->execute();
    $statement->close();
    $db->close();
    error_log('Updated records to sent = Y');
} else {
    error_log('NOT Updating records to sent...');
}
$arrayName = array();
array_push($arrayName, $passage);
array_push($arrayName, $links);
array_push($arrayName, $quotes[0]);
print_r(json_encode($arrayName));
?>


