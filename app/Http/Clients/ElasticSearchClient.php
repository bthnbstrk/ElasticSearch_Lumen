<?php

namespace App\Http\Clients;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticSearchClient {
    private static $instance;
    private function __constructor() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = ClientBuilder::create()
                ->setHosts(['localhost:9200'])
                ->build();
        }
        return self::$instance;
    }

}
