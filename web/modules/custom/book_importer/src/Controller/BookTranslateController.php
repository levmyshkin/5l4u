<?php

namespace Drupal\book_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

class BookTranslateController extends ControllerBase {

  /**
   * @param Node $node
   * @param Request $request
   * @return string[]
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * Translate page manually.
   */
  public function content(Node $node, Request $request) {
    $en = $node->getTranslationStatus('en');
    $es = $node->getTranslationStatus('es');
    $ru = $node->getTranslationStatus('ru');

    if (!empty($ru) && empty($en)) {
      $node_ru = $node->getTranslation('ru');
      $node_en = $node->addTranslation('en');
      $title_ru = $node_ru->getTitle();
      $title_en = $this->translateText($title_ru, 'ru_RU', 'en_GB');
      $body_ru = $node_ru->body->value;
      $body_en = $this->translateText($body_ru, 'ru_RU', 'en_GB');
      $node_en->title = $title_en;
      $node_en->body->value = $body_en;
      $node_en->body->format = 'full_html';
      $node_en->save();
    }

    if (!empty($ru) && empty($es)) {
      $node_ru = $node->getTranslation('ru');
      $node_es = $node->addTranslation('es');
      $title_ru = $node_ru->getTitle();
      $title_es = $this->translateText($title_ru, 'ru_RU', 'es_ES');
      $body_ru = $node_ru->body->value;
      $body_es = $this->translateText($body_ru, 'ru_RU', 'es_ES');
      $node_es->title = $title_es;
      $node_es->body->value = $body_es;
      $node_es->body->format = 'full_html';
      $node_es->save();
    }

    $en = $node->getTranslationStatus('en');
    $es = $node->getTranslationStatus('es');
    $ru = $node->getTranslationStatus('ru');

    $text = '<ul>';
    $text .= '<li>Russian translation: ' . $ru . '</li>';
    $text .= '<li>English translation: ' . $en . '</li>';
    $text .= '<li>Spanish translation: ' . $es . '</li>';
    $text .= '</ul>';
    return array(
      '#type' => 'markup',
      '#markup' => $text,
    );
  }

  // @todo move to Service.
  /**
   * @param $text
   * @param $from
   * @param $to
   * @return false
   *
   * Translate helper function.
   */
  public function translateText($text, $from, $to) {
    $base_url = 'https://api-b2b.backenster.com/b1/api/v3/translate';
    $api_key = 'a_lRJFW8NLtCLO7orpURi6bLpkIZoWqV6xIwuLRUmV6GlOYvkPPa2EYqbTCQEB4zFIYWZfD2GkPBurtORo';
    $client = \Drupal::service('http_client_factory')->fromOptions([
      'headers' => ['Authorization' => $api_key],
    ]);;
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
