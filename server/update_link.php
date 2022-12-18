<?php
$id = $_POST["id"];
$url = $_POST["url"];
$title = $_POST["title"];
$addlcomments = $_POST["addlcomments"];
$category = $_POST["category"];
$hasNewCat = TRUE;
if ( isset($_POST["new_cat_cd"])) {
    $new_cat_cd = $_POST["new_cat_cd"];
} else {
    $new_cat_cd = "";
    $hasNewCat = FALSE;
}
if ( isset($_POST["new_cat_tx"])) {
    $new_cat_tx = $_POST["new_cat_tx"];
} else {
    $new_cat_tx = "";
    $hasNewCat = FALSE;
}
if ($new_cat_tx == null || $new_cat_tx == "" || $new_cat_cd == null || $new_cat_cd == "") {
    $hasNewCat = FALSE;
}
$db = new SQLite3('db/links.sqlite');
if ($hasNewCat == TRUE) {
    $sql = "insert into categories(column_cd, column_tx) values (:column_cd, :column_tx)";
    $statement = $db->prepare($sql);
    error_log('binding values...');
    $statement->bindValue(':column_cd', $new_cat_cd);
    $statement->bindValue(':column_tx', $new_cat_tx);
    $statement->execute();
    error_log('Saved new category: ' . $new_cat_cd . ', ' . $new_cat_tx);
}
$statement = $db->prepare('update links set url = :url, title = :title, addlcomments = :addlcomments, category = :category where id = :id');
$categoryUsed = "";
if ($hasNewCat == TRUE) {
    $statement->bindValue(':url', $url);
    $statement->bindValue(':title', $title);
    $statement->bindValue(':addlcomments', $addlcomments);
    $statement->bindValue(':category', $new_cat_cd);
    $statement->bindValue(':id', $id);
    $categoryUsed = $new_cat_cd;
} else {
    $statement->bindValue(':url', $url);
    $statement->bindValue(':title', $title);
    $statement->bindValue(':addlcomments', $addlcomments);
    $statement->bindValue(':category', $category);
    $statement->bindValue(':id', $id);
    $categoryUsed = $category;
}
$statement->execute();
$statement->close();
$db->close();
?>
<html>
<head>
	<title>URL Saved</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<meta http-equiv="Cache-Control" content="must-revalidate" />
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body>
	<div class="container">
		<h2>The following data have been saved:</h2>
		<b>URL:</b> <?=$url?> <br>
		<b>Title:</b> <?=$title?> <br>
		<b>Additional Comments:</b> <?=$addlcomments?> <br>
		<b>Category:</b> <?=$categoryUsed?> <br>
		<a href="/links-app/server/show_all_links.php">View All Links...</a>
	</div>
</body>
</html>