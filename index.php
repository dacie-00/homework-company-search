<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once "vendor/autoload.php";


$name = readline("Enter the company name - ");
$link = "https://data.gov.lv/dati/lv/api/3/action/datastore_search?q=$name&resource_id=25e80bf3-f107-4ab4-89ef-251b5b9374e9";

$client = new Client();
try {
    $response = $client->request('GET', $link);
} catch (BadResponseException $e) {
    $response = $e->getResponse();
    $code = $response->getStatusCode();
    $response = (json_decode((string)$response->getBody(), false));
    if (isset($response->error->resource_id)) {
        exit("{$response->error->__type} - {$response->error->resource_id[0]}");
    }
    exit("{$response->error->__type} - {$response->error->__extras[0]}");
}

$response = (json_decode((string)$response->getBody(), false));
if (!$response->success) {
    exit($response->error->message);
}

$table = (new Table(new ConsoleOutput()))
    ->setHeaderTitle("Results")
    ->setHeaders(["Name", "Address", "Registered"]);

if (empty($response->result->records)) {
    exit("No companies found!");
}
foreach ($response->result->records as $record) {
    $table->addRow([$record->name, $record->address, $record->registered]);
}

$table->render();