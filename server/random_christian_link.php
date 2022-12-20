<!-- Updated today 11/27/2018 at 5:11 am -->
<?php

$db = new SQLite3('db/links.sqlite');

//$results = $db->query("SELECT DISTINCT SUBSTR(url, 0, INSTR(url, '//') + 2) || REPLACE(SUBSTR(SUBSTR(url, INSTR(url, '//') + 2, LENGTH(url)), 0, INSTR(SUBSTR(url, INSTR(url, '//') + 2, LENGTH(url)), '/')), 'www.', '') AS DOMAIN, REPLACE(SUBSTR(SUBSTR(url, INSTR(url, '//') + 2, LENGTH(url)), 0, INSTR(SUBSTR(url, INSTR(url, '//') + 2, LENGTH(url)), '/')), 'www.', '') AS DOMAIN_NO_HTTP FROM links where category = 'apologetics' AND DOMAIN_NO_HTTP NOT IN (SELECT domain FROM RANDOM_LINK_DOMAIN_EXCEPTIONS) order by DOMAIN_NO_HTTP");
$results = $db->query("SELECT DISTINCT REPLACE(SUBSTR(SUBSTR(url, INSTR(url, '//') + 2, LENGTH(url)), 0, INSTR(SUBSTR(url, INSTR(url, '//') + 2, LENGTH(url)), '/')), 'www.', '') AS DOMAIN_NO_HTTP FROM links where category = 'apologetics' AND DOMAIN_NO_HTTP NOT IN (SELECT domain FROM RANDOM_LINK_DOMAIN_EXCEPTIONS) order by DOMAIN_NO_HTTP");

$links = array();

while ($row = $results->fetchArray()) {
    array_push($links, "http://" . $row['DOMAIN_NO_HTTP']);
}
$db->close();
?>
<html>

<head>
<style>
input {
    max-width: 600px !important;
    width: 600px !important;
}
#button_holder {
    padding: 1.5rem;
}
/* responsive text queries */
@media screen and (max-width: 992px) {
  li {
    font-size: 30x !important;
  }
  #B1 {
    font-size: 40px !important;
  }
  input, #addLink {
    font-size: 40px !important;
  }
}

</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


<script>

var randomlinks = [];
var linksOriginalOrder = [];

$(document).ready(function() {
    getLinks();
    $("#addLink").click(addLink);
});

function logSiteAndOpen(site) {
    console.log('Here is the url that was opened: ' + site);
    window.open(site, '_blank');
}

function getLinks() {
	var html = "<ol class='list-group'>";
	<?php 
	$counter = 1;
	foreach ($links as $currLink) {
	?>
	linksOriginalOrder.push("<?=$currLink?>");
	html += "<li class='list-group-item'><?=$counter?>. <button type='button' class='btn btn-link' onClick='logSiteAndOpen(\"<?=$currLink?>\");'><?=$currLink?></button> <button type='button' class='btn btn-link' style='color: red; font-weight: bold; font-size: 1.2em;' onClick='removeLink(\"<?=$currLink?>\");'>X</button></li>";
	//html += "<li class='list-group-item'><?=$counter?>. <button type='button' class='btn btn-link' onClick='logSiteAndOpen(\"<?=$currLink?>\");'><?=$currLink?></button></li>";
	<?php
	$counter++;
	}
	?>
	html += "</ol>";

	$("#result").html(html);
	<?php 
	shuffle($links);
	foreach ($links as $currLink) {
	?>
	randomlinks.push("<?=$currLink?>");
	<?php
	}
	?>
}

var currIndex = 0;

function randomlink() {
    console.log('Here is the url that was opened: ' + randomlinks[currIndex]);
    window.open(randomlinks[currIndex], "_blank");
    currIndex += 1;
    if (currIndex === randomlinks.length) {
        currIndex = 0;
    }
}

function addLink() {
    var newLink = $('#newLink').val();
    var hostOnly = newLink.replace("http://", "");
    hostOnly = hostOnly.replace("https://", "");
    hostOnly = hostOnly.replace("/", "");
    hostOnly = hostOnly.trim();
    var addIt = true;
    for (var i = 0; i < randomlinks.length; i++) {
        if (randomlinks[i].includes(hostOnly)) {
            addIt = false;
            break;
        }
    }
    if (addIt) {
        linksOriginalOrder.push(newLink);
        console.log(linksOriginalOrder);
        $.ajax({
            type: "POST",
            url: "add_link.php",
            data: JSON.stringify(linksOriginalOrder),
            success: function(data) {
                console.log(data);
                location.reload();
            },
            error: function(data) {
                console.log(data);
            }
        });
    } else {
        console.log("The following host already exists in the list and will not be added: " + hostOnly);
    }
}

function removeLink(link) {
    var result = window.confirm('Are you sure you want to remove ' + link + '?');
    if (result == false) {
        return;
    }

    $.ajax({
        type: "POST",
        url: "remove_link.php",
        data: JSON.stringify(link),
        success: function(data) {
			console.log("In Success function " + data);
			if (data === "\"TRUE\"") {
				// update was successful, so remove link from our list
				/*var newLinkList = [];
				for (var i = 0; i < linksOriginalOrder.length; i++) {
					if (linksOriginalOrder[i] != link) {
						newLinkList.push(linksOriginalOrder[i]);
					}
				}*/
				console.log("Remove link " + link + " was successful, reloading page...");
				location.reload();
			} else {
				console.log("Remove link " + link + " was NOT successful, NOT reloading page.");
			}
        },
        error: function(data) {
            console.log("In Error function " + data);
        }
    });
}
</script>
<meta http-equiv="Cache-Control" content="must-revalidate" />
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta name="description" content=""/>
<meta name="author" content=""/>

</head>

<body>
<div class="container">
	<div class="row col-lg-12" id='button_holder'>
		<button class="btn btn-lg btn-block btn-primary" style="padding-top: 2.5rem; padding-bottom: 2.5rem; font-size: 30px;" type="button" name="B1" id="B1" onclick="randomlink()" target="_new">Random Link &gt;&gt;</button>
	</div>
	<!--<form class="form-inline">
	  <div class="form-group">
		<label class="sr-only" for="newLink">New Link: </label>
		<input type="text" class="form-control" id="newLink" name="newLink" placeholder="New Link to Add"/>
	  </div>
		  <button type="button" id="addLink" class="btn btn-success">Add Link</button>
	</form>-->
	<div class="row col-lg-12" id="result"></div>
</div>

</body>

</html>