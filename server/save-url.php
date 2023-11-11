<?php
if ( isset($_GET["url"])) {
    $url = $_GET["url"];
} else {
    $url = "Unknown";
}
if ( isset($_GET["title"])) {
    $title = $_GET["title"];
} else {
    $title = "Unknown";
}
$db = new SQLite3('db/links.sqlite');
$results = $db->query("select column_cd, column_tx from categories where column_cd <> '' ORDER BY upper(column_tx)");
$categories = array();
while ($row = $results->fetchArray()) {
    $currRow = array();
    array_push($currRow, $row['column_cd']);
    array_push($currRow, $row['column_tx']);
    array_push($categories, $currRow);
}
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Save a URL</title>
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
        function getUrlTitle() {
            $.post( "/links-app/server/get-page-title.php", { url: document.getElementById("url").value })
                .done(function( data ) {
                    $("#title").val(data);
                });
        }
    </script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <meta http-equiv="Cache-Control" content="must-revalidate" />
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body>
<div class="container">
    <h2>Save URL</h2>
    <div class="row">
        <div class="col-md-12 well">
            <form class="form-horizontal" role="form" method="post" action="/links-app/server/send_page.php">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="url">URL:</label>
                    <div class="col-sm-10">
                        <input id="url" type="text" class="form-control" id="url" placeholder="Enter URL Here" name="url" value="<?=$url?>" onchange="getUrlTitle()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="title">Title:</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" value="<?=$title?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="addlcomments">Add'l Comments:</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="addlcomments" placeholder="Enter Any Additional Comments Here" name="addlcomments">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="category">Category:</label>
                    <div class="col-sm-10">
                        <select id="category" name="category">
                            <?php
                            foreach ($categories as $category) {
                                ?>
                                <option value="<?=$category[0]?>"<?=$category[0] == 'apologetics' ? ' selected' : ''?>><?=$category[1]?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="new_cat_cd">New Category:</label>
                    <div class="col-sm-5">
                        <input size="7" type="text" name="new_cat_cd" id="new_cat_cd"/> (cd)
                    </div>
                    <div class="col-sm-5">
                        <input type="text" name="new_cat_tx" id="new_cat_tx"/> (tx)
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-default">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>