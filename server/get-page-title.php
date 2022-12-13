<?php
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept');
header('Content-Type: application/json; charset=utf8; Accept: application/json');
function file_get_contents_curl($url)
{
    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 
    $data = curl_exec($ch);
    curl_close($ch);
 
    return $data;
}
error_log("get-page-title.php - checking if isset(_POST['url'])...");
if ( isset($_POST["url"])) {
    $url = $_POST["url"];
} else {
    $url = "Unknown";
}
error_log("get-page-title.php - url is " . $url);
$html = file_get_contents_curl($url);
 
// Load HTML to DOM object
$doc = new DOMDocument();
@$doc->loadHTML($html);
 
// Parse DOM to get Title data
$nodes = $doc->getElementsByTagName('title');
$title = $nodes->item(0)->nodeValue;
error_log("get-page-title.php - title for URL: " . $url . " is " . $title);
$responseJson = json_encode($title);
error_log("get-page-title.php - sending back json for URL: " . $url . " - " . $responseJson);
print_r($responseJson);
?>
