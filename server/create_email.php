<?php

class Link {

    public $date_time_link_saved;

    public $url;

    public $title;

    public $addlcomments;

    public $category;
}

class Passage {

    public $passageId;

    public $bookId;

    public $bookName;

    public $translationId;

    public $translationName;

    public $chapter;

    public $startVerse;

    public $endVerse;

    public $verseText;
	

    public $frequencyDays = -1;    
    public $last_viewed_str = "N/A";
    public $last_viewed_num = -1;
	public $passageRefAppendLetter = "";

    public $verses = array();

    

    function addVerse($verse) {

        array_push($this->verses, $verse);

    }

}



class Verse {

    public $passageId;

    public $verseParts = array();

    

    function addVersePart($versePart) {

        array_push($this->verseParts, $versePart);

    }

}



class VersePart {

    public $verseNumber;

    public $versePartId;

    public $verseText;

    public $wordsOfChrist;

}


$db = new SQLite3('links.sqlite');


$results = $db->query('select date_time_link_saved, url, title, addlcomments, category from links where category = \'apologetics\' and sent = \'N\' order by date_time_link_saved');



$links = array();

while ($row = $results->fetchArray()) {

    $link = new Link();

    $link->date_time_link_saved = $row['date_time_link_saved'];

    $link->url = $row['url'];

    $link->title = $row['title'];

    $link->addlcomments = $row['addlcomments'];

    $link->category = $row['category'];

    array_push($links, $link);

}

$db->close();

$db = new SQLite3('../nuggets_mobile_app/db/biblenuggets.db');



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

$db = new SQLite3('../nuggets_mobile_app/db/' . $translation . '.db');

$bookId = $passages[0]->bookId;

$chapter = $passages[0]->chapter;

$startVerse = $passages[0]->startVerse;

$endVerse = $passages[0]->endVerse;

error_log('Getting verse text for passage book:' . $bookId . ',chapter:' . $chapter . ',startVerse:' . $startVerse . ',endVerse:' . $endVerse);
$statement = $db->prepare('select verse, verse_part_id, verse_text, is_words_of_christ, book_id, book_name from verse v, book b where b._id = :book_id and chapter = :chapter and verse >= :start_verse and verse <= :end_verse order by verse, verse_part_id');

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




$to      = 'steve_warsa@yahoo.com';

$subject = 'mid-week apologetics booster';

$message = '<html><body style="font-family: Calibri;">';
$message .= 'Good morning friends,<br/><br/>';
$message .= 'Here are your weekly links:<br/>';
$message .= '<ol>';
foreach ($links as $currLink) {
	$message .= '<li><b>' . $currLink->title . ': </b><a href="' . $currLink->url . '" target="_blank">' . $currLink->url . '</a></li>';
}
$message .= '</ol>';
$message .= '<br/><br/>';
$verseLen = $passage->verses.length;
$verseText = "";
foreach ($passage->verses as $currVerse) {
	foreach ($currVerse->verseParts as $versePart) {
		$verseText .= $versePart->verseText . " ";
	}
}
$message .= $verseText . $passage->bookName . ' ' . $passage->chapter . ':' . ($verseLen == 1 ?  $passage->startVerse : $passage->startVerse . '-' . $passage->endVerse) . '<br/><br/>';
$message .= 'Blessings,<br/>';
$message .= 'Steve';
$message .= '</body></html>';

$headers = 'From: steve_warsa@coolgadgetssoftware.com ' . "\r\n" .
	'Reply-To: steve_warsa@coolgadgetssoftware.com ' . "\r\n" .
	'Content-type: text/html; charset=iso-8859-1' . "\ r\n" .
	'MIME-Version: 1.0' . "\r\n" .

	/*'Cc: swarsa480@gmail.com ' . "\r\n" .*/

	'X-Mailer: PHP/' . phpversion();

error_log(' Sending mail to ' . $to . ', subject: ' . $subject . ', message: ' . $message . ', headers: ' . $headers);

if (mail($to, $subject, $message, $headers)) {
	error_log('Your message was sent successfully');
	error_log('Updating records to sent = Y');
	//$db = new SQLite3('links.sqlite');
	//$statement = $db->prepare('update links set sent = \'Y\' where category = \'apologetics\' and sent = \'N\'');
	//$statement->execute();
	//$statement->close();
	//$db->close();
	error_log('Updated records to sent = Y');
} else {
	error_log('Unable to send email. Please try again.');
}

echo $message;

?>


