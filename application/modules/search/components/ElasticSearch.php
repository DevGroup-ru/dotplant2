<?php

namespace app\modules\search\components;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Yii;
use yii\base\Component;

class ElasticSearch extends Component
{
    public $hosts = [
        '127.0.0.1:9200',
    ];

    public $retries = 1;

    /** @var Client */
    private $client = null;

    public function init()
    {
        parent::init();
        $this->client = ClientBuilder::create()
            ->setHosts($this->hosts)
            ->setRetries($this->retries)
            ->build();
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->client;
    }
}