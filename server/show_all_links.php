<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

include_once('./Link.php');

$db = new SQLite3('links.sqlite');
if (isset($_GET["cat"]) && $_GET["cat"] != null && $_GET["cat"] != "" && $_GET["cat"] != "all") {
	$cat = $_GET["cat"];
	$results = $db->query("select id, date_time_link_saved, url, title, addlcomments, category, sent from links where category = '" . $cat . "' order by date_time_link_saved desc");
} else {
	$cat = "all";
	$results = $db->query('select id, date_time_link_saved, url, title, addlcomments, category, sent from links order by date_time_link_saved desc');
}

$links = array();
while ($row = $results->fetchArray()) {
    $link = new Link();
    $link->id = $row['id'];
    $link->date_time_link_saved = $row['date_time_link_saved'];
    $link->url = $row['url'];
    $link->title = utf8_encode($row['title']);
    $link->addlcomments = $row['addlcomments'];
    $link->category = $row['category'];
    $link->sent = $row['sent'];
    array_push($links, $link);
}
$results = $db->query('select column_cd, column_tx from categories');
$categories = array();
while ($row = $results->fetchArray()) {
    $currRow = array();
    array_push($currRow, $row['column_cd']);
    array_push($currRow, $row['column_tx']);
    array_push($categories, $currRow);
}

$db->close();

$num_rec_per_page = 12.0;
if (isset($_GET["page"])) {
	$page = $_GET["page"];
} else {
	$page = 1;
}
$rangeStart = (($page-1) * $num_rec_per_page) + 1;
$totalRecs = sizeof($links);
$pageCount = ceil(sizeof($links) / $num_rec_per_page);
if ((($page * $num_rec_per_page) + $num_rec_per_page) <= $totalRecs) {
	$subset = array_slice($links, (($page-1) * $num_rec_per_page), $num_rec_per_page);
	$rangeEnd = (($page-1) * $num_rec_per_page) + ($num_rec_per_page);
} else {
	$subset = array_slice($links, (($page-1) * $num_rec_per_page), $num_rec_per_page - (($page * $num_rec_per_page) - $totalRecs));
	$rangeEnd = $totalRecs;
}
//error_log("The subset of the array has " . sizeof($subset) . " elements in it");
if ($page == 1) {
	$prevPage = $pageCount;
} else {
	$prevPage = $page - 1;
}

if ($page == $pageCount) {
	$nextPage = 1;
} else {
	$nextPage = $page + 1;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
 <title>All Saved Links</title>
 <script>
	var linksArray = <?=json_encode($links)?>;
	
	function searchTitles(evt) {
		var searchInput = document.getElementById("searchText");
		if (searchInput.value.length > 2) {
			document.getElementById("clear_button").style.display = "inline-block";
			// do the search if the search text is at least 3 characters
			var currentSearchResult = null;
			if ("<?=$cat?>" === "all") {
				currentSearchResult = linksArray.filter(link => link.title.toUpperCase().includes(searchInput.value.toUpperCase()));
			} else {
				currentSearchResult = linksArray
					.filter(link => link.title.toUpperCase().includes(searchInput.value.toUpperCase()))
					.filter(link => link.category === "<?=$cat?>");
			}
			var typeAheadResults = document.getElementById("typeAheadResults");
			if (currentSearchResult && currentSearchResult.length) {
				var listOfLinks = "<ol>";
				currentSearchResult.forEach(link => listOfLinks += "<li><a href=\"" + link.url + "\" target=\"_blank\">" + link.title + "</a> (" + link.category + ") <a href=\"/links/edit-saved-url.php?id=" + link.id + "\" target=\"_blank\">edit</a></li>");
				listOfLinks += "</ol>"
				typeAheadResults.innerHTML = listOfLinks;
				typeAheadResults.style.display = "block";
			} else {
				typeAheadResults.innerHTML = "";
				typeAheadResults.style.display = "none";
			}
		} else {
			document.getElementById("clear_button").style.display = "none";
			var typeAheadResults = document.getElementById("typeAheadResults");
			typeAheadResults.innerHTML = "";
			typeAheadResults.style.display = "none";			
		}
	}
	
	function clearSearchText() {
		document.getElementById('searchText').value = '';
		typeAheadResults.innerHTML = '';
		typeAheadResults.style.display = 'none';
		document.getElementById('clear_button').style.display = 'none';
		document.getElementById('searchText').focus();
	}
	function changeCategory(e) {
		location.href = 'show_all_links.php?cat=' + e.target.value;
	}
 </script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script><meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/><meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>

</head>

<body>
	<div class="container">
		<div>
			Search Titles: <input id="searchText" type="text" onkeyup="searchTitles(event)" /> 
			<button id="clear_button" style="display: none;" class="btn btn-danger" onclick="clearSearchText();">X</button>
		</div>
		<div id="typeAheadResults" style="display: none;">
		</div>
		<div class="row mb-2">
			<div class="col-sm-12">
				<a href="show_all_links.php?page=<?=$prevPage?>&cat=<?=$cat?>" role="button" class="btn btn-link" title="Previous Page"><strong>&lt;&lt;</strong> </a> 
				<strong><?=$rangeStart?> - <?=$rangeEnd?> of <?=$totalRecs?></strong> 
				<a href="show_all_links.php?page=<?=$nextPage?>&cat=<?=$cat?>" role="button" class="btn btn-link" title="Next Page"><strong>&gt;&gt;</strong> </a><br/>
				</div>
			<div class="col">
				<select id="category" name="category" onchange="changeCategory(event)">
					<option value="all"<?=$cat == "all" ? " selected" : ""?>>Select Category</option>
					<?php 
					foreach ($categories as $category) {
					?>
					<option value="<?=$category[0]?>"<?=$cat == $category[0] ? " selected" : ""?>><?=$category[1]?></option>
					<?php
					}
					?>
				</select>
			</div>
		</div>
			
		<?php 
		$counter = $rangeStart;
		foreach ($subset as $currLink) {
		?>
			<div class="card">
			  <div class="card-body">
				<h5 class="card-title"><?=$currLink->title?></h5>
				<h6 class="card-subtitle mb-2 text-muted"><strong>Category:</strong> <?=$currLink->category?></h6>
				<p class="card-text"><strong>Date/Time accessed:</strong> <?=$currLink->date_time_link_saved?></p>
				<p class="card-text"><strong>Sent?</strong> <?=$currLink->sent?></p>
				<a href="<?=$currLink->url?>" target="_blank" class="card-link">Open Link</a> <a href="/links/edit-saved-url.php?id=<?=$currLink->id?>" class="card-link text-success">Edit</a>
			  </div>
			</div>
		<?php
			$counter++;
		}
		?>
	</div>
	<script>document.getElementById("searchText").focus();</script>
</body>
</html>
