<?php

namespace app\modules\core\components;

class PackagistClient extends \Packagist\Api\Client
{
    /**
     * Search packages
     *
     * Available filters :
     *
     *    * vendor: vendor of package (require or require-dev in composer.json)
     *    * type:   type of package (type in composer.json)
     *    * tags:   tags of package (keywords in composer.json)
     *
     * @since 1.0
     *
     * @param string $query   Name of package
     * @param array  $filters An array of filters
     *
     * @return array The results
     */
    public function search($query, array $filters = array(), $page = 1)
    {
        $results = $response = array();
        $filters['q'] = $query;
        $filters['page'] = $page;
        $url = '/search.json?' . http_build_query($filters);
        $response['next'] = $this->url($url);


        $response = $this->request($response['next']);
        $response = $this->parse($response);
        $results = array_merge($results, $this->create($response));


        return $results;
    }
}