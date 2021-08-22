<?php

namespace Drupal\book_importer;

use Drupal\Core\Http\ClientFactory;

/**
 * Translator for text.
 */
class Translator {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * Constructs a new TranslateText object.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   A Guzzle client object.
   */
  public function __construct(ClientFactory $http_client_factory) {
    $this->httpClientFactory = $http_client_factory;
  }

  /**
   * @param $text
   * @param $from
   * @param $to
   * @return false
   *
   * Translate helper function.
   *
   * Languages: ru_RU, es_ES, en_GB
   */
  public function translateText($text, $from, $to) {
    $base_url = 'https://api-b2b.backenster.com/b1/api/v3/translate';
    $api_key = 'a_lRJFW8NLtCLO7orpURi6bLpkIZoWqV6xIwuLRUmV6GlOYvkPPa2EYqbTCQEB4zFIYWZfD2GkPBurtORo';
    $client = $this->httpClientFactory->fromOptions([
      'headers' => ['Authorization' => $api_key],
    ]);
    $request = $client->post($base_url, [
      'json' => [
        'from'=> $from,
        'to' => $to,
        'data' => $text,
        'platform' => 'api',
        'translateMode' => 'html',
      ],
    ]);
    $response = json_decode($request->getBody());
    if (empty($response) || !empty($response->err)) {
      return FALSE;
    }
    return $response->result;
  }
}
