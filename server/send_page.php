<?php
try {
    error_log('Entering /links-app/server/send_page.php...');
    if ( isset($_POST["url"])) {
        $url = $_POST["url"];
    } else {
        $url = "Unknown";
    }
    if ( isset($_POST["title"])) {
        $title = $_POST["title"];
    } else {
        $title = "Unknown";
    }
    if ( isset($_POST["addlcomments"])) {
        $addlcomments = $_POST["addlcomments"];
    } else {
        $addlcomments = "";
    }
    error_log("Here is the URL: '" . $url . "'");
    error_log("Here is the Title: '" . $title . "'");
    error_log("Here are the Additional Comments: '" . $addlcomments . "'");
    error_log('Creating SQLite database connection...');
    $db = new SQLite3('db/links.sqlite');
    error_log('SQLite database connection created');
    if ( isset($_POST["new_cat_cd"]) && $_POST["new_cat_cd"] != null && $_POST["new_cat_cd"] != "" && isset($_POST["new_cat_tx"]) && $_POST["new_cat_tx"] != null && $_POST["new_cat_tx"] != "") {
        $newCategoryCd = $_POST["new_cat_cd"];
        $newCategoryTx = $_POST["new_cat_tx"];
        $statement = $db->prepare('insert into categories (column_cd, column_tx) values (:newCategoryCd, :newCategoryTx)');
        $statement->bindValue(':newCategoryCd', $newCategoryCd);
        $statement->bindValue(':newCategoryTx', $newCategoryTx);
        error_log('Inserting new category ' . $newCategoryCd . '...');
        $succeeded = $statement->execute();
        $statement->close();
        if ($succeeded == TRUE) {
            $category = $newCategoryCd;
        } else if ( isset($_POST["category"])) {
            $category = $_POST["category"];
        } else {
            $category = "";
        }
    } else if ( isset($_POST["category"])) {
        $category = $_POST["category"];
    } else {
        $category = "";
    }
    error_log("Here is the category: '" . $category . "'");
    error_log('preparing statement...');
    $statement = $db->prepare('insert into links (date_time_link_saved, url, title, addlcomments, category) values (datetime(\'now\'), :url, :title, :addlcomments, :category)');
    error_log('binding values...');
    $statement->bindValue(':url', $url);
    $statement->bindValue(':title', $title);
    $statement->bindValue(':addlcomments', $addlcomments);
    $statement->bindValue(':category', $category);
    error_log('executing statement...');
    $succeeded = $statement->execute();
    $succeededStr = $succeeded == TRUE ? "TRUE" : "FALSE";
    error_log('closing statement... Did the statement succeed? ' . $succeededStr);
    error_log('Last row id inserted: ' . $db->lastInsertRowID());
    $statement->close();
    error_log('closing database...');
    $db->close();
    error_log('Database closed.');
} catch (Exception $e) {
    error_log($e);
}
?>
<html>
<head>
    <title>URL Saved</title>
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
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
    <b>Category:</b> <?=$category?> <br>
</div>
</body>
</html>