<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
$request = file_get_contents('php://input');
if (empty($request)) {
    error_log("update_link.php - may be options call - JSON request not sent - exiting");
    exit();
}
error_log("update_link.php - Here is the JSON received: ");
error_log($request);
$updateLinkRequest = json_decode($request);
$updatedLinkObject = $updateLinkRequest->link;
$id = $updatedLinkObject->id;
$url = $updatedLinkObject->url;
$title = $updatedLinkObject->title;
$addlcomments = $updatedLinkObject->addlcomments;
$category = $updatedLinkObject->category;
$hasNewCat = $updateLinkRequest->hasNewCat;
$db = new SQLite3('db/links.sqlite');
$succeededStr = "FALSE";
$succeeded = true;
try {
    if ($hasNewCat) {
        $sql = "insert into categories(column_cd, column_tx) values (:column_cd, :column_tx)";
        $statement = $db->prepare($sql);
        error_log('update_link.php - new category - binding values...');
        $statement->bindValue(':column_cd', $updateLinkRequest->newCatCd);
        $statement->bindValue(':column_tx', $updateLinkRequest->newCatTx);
        $succeeded = $statement->execute();
        $statement->close();
        if ($succeeded) {
            $succeededStr = "TRUE";
            error_log('update_link.php - Saved new category: ' . $updateLinkRequest->newCatCd . ', ' . $updateLinkRequest->newCatTx);
        } else {
            error_log('update_link.php - Unable to save new category: ' . $updateLinkRequest->newCatCd . ', ' . $updateLinkRequest->newCatTx);
        }
    }
    if ($succeeded) {
        $statement = $db->prepare('update links set url = :url, title = :title, addlcomments = :addlcomments, category = :category where id = :id');
        $statement->bindValue(':url', $url);
        $statement->bindValue(':title', $title);
        $statement->bindValue(':addlcomments', $addlcomments);
        $statement->bindValue(':category', $category);
        $statement->bindValue(':id', $id);
        $succeeded = $statement->execute();
        $succeededStr = $succeeded ? "TRUE" : "FALSE";
        $statement->close();
    }
    $db->close();
} catch (Exception $e) {
    error_log($e);
}

print_r(json_encode($succeededStr));

