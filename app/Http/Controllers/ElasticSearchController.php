<?php

namespace App\Http\Controllers;

use App\Http\Clients\ElasticSearchClient;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Faker\Factory as Faker;

class ElasticSearchController extends Controller
{
    private $elastic_search_client;

    public function __construct()
    {
        $this->elastic_search_client = ElasticSearchClient::getInstance();
    }

    public function bulk()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 100000; $i++) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'customer_info_new',
                ]
            ];
            $params['body'][] = [
                'parent_id' => random_int(1, 100),
                'customer_name' => $faker->name,
                'customer_phone' => $faker->phoneNumber,
                'customer_email' => $faker->email,
            ];
        }
        $result = $this->elastic_search_client->bulk($params);
        dd($result);
    }

    public function info()
    {
        $client = ClientBuilder::create()
            ->setHosts(['localhost:9200'])
            ->build();

        $response = $client->info();
        die($response['version']['number']);
    }

    public function insert()
    {
        $params = [
            'index' => 'company_docs',
            'id' => null,
            'body' => [
                'parent_id' => 10,
                'company_name' => 'Company Test',
                'company_phone' => '123-456-444',
                'company_email' => ['info@company.com', 'support@company.com', 'learn@company.com'],
            ]
        ];

        try {
            $response = $this->elastic_search_client->index($params);
            dd($response->asArray());
        } catch (ClientResponseException $e) {
            dd($e->getMessage());
        } catch (ServerResponseException $e) {
            dd($e->getMessage());
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    public function update($index, $id)
    {

        $params = [
            'index' => $index,
            'id' => $id,
            'body' => [
                'company_name' => 'Updated Company',
                'company_phone' => '1222-222-222',
            ]
        ];

        try {
            $response = $this->elastic_search_client->update($params);
            dd($response->asArray());
        } catch (ClientResponseException $e) {
            dd($e->getMessage());
        } catch (ServerResponseException $e) {
            dd($e->getMessage());
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $response = $this->elastic_search_client->delete([
                'index' => 'company_docs',
                'id' => $id
            ]);
            dd($response);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                dd("The document does not exist!");
            }
        }

    }

    public function find()
    {

        /** Query 1 **/
        /*
        $params = [
            'index' => 'company_docs',
            'from'=>0,
            'size'=>10,
            'body' => [
                'query' => [
                    'wildcard' => [
                        'company_name' => '*spe*',
                    ],
                ]
            ]
        ];
       */

        /** Query 2 **/
        /*
        $params = [
            'index' => 'company_docs',
            'from' => 0,
            'size' => 1000,
            'body' => [
                'query' => [
                    'bool' => [
                        'must'=>[
                            'match'=>[
                                'parent_id'=>8
                            ]
                        ],
                        'filter' => [
                            [
                                'query_string' => [
                                    'query' => "*2384*",
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];*/

        /** Query 3 **/
        /** SQL: Select * from company_info_new where parent_id=2185 and company_phone='+19525402384' and company_email!='abc@gmail.com'  **/
        /*
        $params = [
            'index' => 'company_docs',
            'from' => 0,
            'size' => 1000,
            'body' => [
                "query" => [
                    "bool" => [
                        "must" => [['match' => ['parent_id' => 2185]], ['match_phrase' => ['company_phone' => '+19525402384']],],
                        "must_not" => [['match' => ['company_email' => "abc@gmail.com"]]]
                        ]
                ]
            ]
        ];
        echo "<pre>";
        print_r($params);
        echo "</pre>";
        echo "--------".PHP_EOL;
        */

        /** Query 4 **/
        /*
       $params = [
           'index' => 'company_docs',
           'from' => 0,
           'size' => 100,
           'body' => [
               'query' => [
                   'bool' => [
                       'must' => [
                           'match' => [
                               'parent_id' => 3,
                           ]
                       ],
                       'filter' => [
                           [
                               'match' => [
                                   'phones' => 122,
                               ]
                           ]
                       ],
                   ]
               ]
           ]
       ];*/

        /** Query 5 **/
        $params = [
            'index' => 'company_docs',
            'from' => 0,
            'size' => 1000,
            'body' => [
                "query" => [
                    "bool" => [
                        "must" => [['match' => ['company_email' => 'info@company.com']], ['match_phrase' => ['company_phone' => '+19525402384']],],
                    ]
                ]
            ]
        ];
        $params['body']['query']['bool']['must_not'] = [['match' => ['parent_id' => 2185]]];


        $response = $this->elastic_search_client->search($params);
        echo "<pre>";
        printf("Total docs: %d\n", $response['hits']['total']['value']);
        printf("Max score : %.4f\n", $response['hits']['max_score']);
        printf("Took      : %d ms\n", $response['took']);
        print_r($response['hits']['hits']); // documents
        echo "</pre>";
    }

    public function count($index)
    {
        $params = [
            'index' => $index,
        ];

        $response = $this->elastic_search_client->count($params);
        $data["count"] = $response->count;
        $data["index"] = $index;
        return $data;
    }
}
