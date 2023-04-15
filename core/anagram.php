<?php
if ($_SERVER["REQUEST_METHOD"] != "GET") die();

require_once "../class/DbConnect.php";
require_once "../class/AnagramFinder.php";

$language = $_GET["language"];
$word = $_GET["word"];
$dbConnect = new DbConnect("user", "");
$finder = new AnagramFinder($dbConnect, $language, $word);

$response = $finder->find();
echo json_encode($response);

$dbConnect->closeConnection();