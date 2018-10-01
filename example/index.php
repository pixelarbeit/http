<?php

namespace Pixelarbeit\Http;

require '../vendor/autoload.php';

$client = new JsonClient();
$response = $client->get('http://jsonplaceholder.typicode.com/users');

echo "<pre>";
var_dump($response);
echo "</pre>";
