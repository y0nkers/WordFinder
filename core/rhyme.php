<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "../class/DbConnect.php";
require_once "../class/RhymeFinder.php";

$language = $_GET["language"];
$word = $_GET["word"];
$dbConnect = new DbConnect("user", "");
$finder = new RhymeFinder($dbConnect, $language, $word);

$response = $finder->find();
echo json_encode($response);

$dbConnect->closeConnection();
