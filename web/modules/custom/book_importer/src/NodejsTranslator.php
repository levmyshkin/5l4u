<?php

namespace Drupal\book_importer;

use Drupal\Core\Http\ClientFactory;

/**
 * Node.js Translator for text.
 */
class NodejsTranslator {

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
    if (strlen($text) > 4900) {
      $chunks = $this->breakLongText($text);
    }
    else {
      $chunks = [$text];
    }

    $translated_text = '';
    foreach ($chunks as $chunk) {
      $translated_text .= $this->getTranslation($chunk, $from, $to);
      $random_time = rand(25, 35);
      sleep($random_time);
    }
    $translated_text = str_replace(' / ', '/', $translated_text);
    $translated_text = str_replace('/ ', '/', $translated_text);

    $translated_text = str_replace('0 -0', '0-0', $translated_text);
    $translated_text = str_replace('-04 -', '-04-', $translated_text);
    $translated_text = str_replace('2021 -0', '2021-0', $translated_text);

    // Remove spaces from src attribute.
//    $translated_text = preg_replace('/src\s*=\s*"[^" ]*\K\h|(?!^)\G[^" ]*\K\h/gm', '', $translated_text);


    $translated_text = str_replace(' .png', '.png', $translated_text);
    $translated_text = str_replace('. png', '.png', $translated_text);
    $translated_text = str_replace(' .jpg', '.jpg', $translated_text);
    $translated_text = str_replace('. jpg', '.jpg', $translated_text);

    $translated_text = str_replace('/content /', '/content/', $translated_text);
    $translated_text = str_replace('/files /', '/files/', $translated_text);

    $translated_text = str_replace('alt = "', '" alt="', $translated_text);
    $translated_text = str_replace('alt ="', '" alt="', $translated_text);
    $translated_text = str_replace('alt= "', '" alt="', $translated_text);

    $translated_text = str_replace('title = "', ' title="', $translated_text);
    $translated_text = str_replace('title ="', ' title="', $translated_text);
    $translated_text = str_replace('title= "', ' title="', $translated_text);

    $translated_text = str_replace('src = "', ' src="', $translated_text);
    $translated_text = str_replace('src ="', ' src="', $translated_text);
    $translated_text = str_replace('src= "', ' src="', $translated_text);

    $translated_text = str_replace('class = "', ' class="', $translated_text);
    $translated_text = str_replace('class= "', ' class="', $translated_text);
    $translated_text = str_replace('class ="', ' class="', $translated_text);

    $translated_text = str_replace('id = "', ' class="', $translated_text);
    $translated_text = str_replace('id= "', ' class="', $translated_text);
    $translated_text = str_replace('id ="', ' class="', $translated_text);

    $translated_text = str_replace('data-entity-uuid = "', ' data-entity-uuid="', $translated_text);
    $translated_text = str_replace('data-entity-uuid= "', ' data-entity-uuid="', $translated_text);
    $translated_text = str_replace('data-entity-uuid ="', ' data-entity-uuid="', $translated_text);

    return $translated_text;
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
  public function getTranslation($text, $from, $to) {
    $client = \Drupal::service('http_client_factory')->fromOptions();

    // @todo Move endpoint URL to configs.
    $request = $client->post('http://localhost:8000', [
      'form_params' => [
        'translateText'=> $text,
        'translateFrom' => $from,
        'translateTo' => $to,
      ],
    ]);
    $response = $request->getBody();
    $contents = $response->getContents();
    if (empty($contents) || $request->getStatusCode() != '200') {
      return FALSE;
    }
    return $contents;
  }

  /**
   * @param $text
   * @param int $length
   * @param int $maxLength
   * @return array
   *
   * Break long text into chunks.
   */
  public function breakLongText($text, $length = 4000, $maxLength = 4500){
    // Text length.
    $textLength = strlen($text);

    // Initialize empty array to store split text.
    $splitText = array();

    // Return without breaking if text is already short.
    if (!($textLength > $maxLength)) {
      $splitText[] = $text;
      return $splitText;
    }

    // Guess sentence completion.
    $needle = '</div>';

    // Iterate over $text length as substr_replace deleting it.
    while (strlen($text) > $length){
      $end = strpos($text, $needle, $length);
      if ($end === false) {
        //Returns FALSE if the needle (in this case ".") was not found.
        $splitText[] = substr($text,0);
        $text = '';
        break;
      }

      $end = $end + 6;
      $splitText[] = substr($text,0,$end);
      $text = substr_replace($text,'',0,$end);
    }

    if ($text){
      $splitText[] = substr($text,0);
    }

    return $splitText;
  }
}
