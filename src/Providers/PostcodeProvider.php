<?php

namespace ApiCrumbs\Core\Providers;

use ApiCrumbs\Core\Contracts\ProviderInterface;
use GuzzleHttp\Client;

class PostcodeProvider implements ProviderInterface
{
    private Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client(['base_uri' => 'https://api.postcodes.io']);
    }

    public function getName(): string { return 'geo_location'; }

    public function fetchData(string $postcode): array
    {
        $response = $this->client->get("postcodes/{$postcode}");
        $res = json_decode($response->getBody(), true);

        if ($res['status'] !== 200) return [];

        $data = $res['result'];
        
        // Flattening: We only keep high-signal data for the LLM
        return [
            'postcode' => $data['postcode'],
            'region'   => $data['region'],
            'ward'     => $data['admin_ward'],
            'district' => $data['admin_district'],
            'lat_long' => "{$data['latitude']}, {$data['longitude']}"
        ];
    }
}