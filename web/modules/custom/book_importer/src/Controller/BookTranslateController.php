<?php

namespace Drupal\book_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\book_importer\NodejsTranslator;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Stream\Stream;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookTranslateController extends ControllerBase {

  /**
   * Translator for text service.
   *
   * @var \Drupal\book_importer\NodejsTranslator
   */
  protected $translator;

  /**
   * BookTranslateController constructor.
   *
   * @param \Drupal\book_importer\NodejsTranslator $translator
   *   The text translator.
   */
  public function __construct(NodejsTranslator $translator) {
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('book_importer.nodejs_translator')
    );
  }

  /**
   * @param Node $node
   * @param Request $request
   * @return string[]
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * Postprocess signle node.
   */
  public function singleTranslate(Node $node, Request $request) {
    $en = $node->getTranslationStatus('en');
    $es = $node->getTranslationStatus('es');
    $ru = $node->getTranslationStatus('ru');

    if (!empty($ru) && empty($en)) {
      $node_ru = $node->getTranslation('ru');
      $node_en = $node->addTranslation('en');
      $title_ru = $node_ru->getTitle();
      $title_en = $this->translator->translateText($title_ru, 'ru', 'en');
      $body_ru = $node_ru->body->value;
      $body_en = $this->translator->translateText($body_ru, 'ru', 'en');
      $node_en->title = $title_en;
      $node_en->body->value = $body_en;
      $node_en->body->format = 'full_html';
      $node_en->save();
    }

    if (!empty($ru) && empty($es)) {
      $node_ru = $node->getTranslation('ru');
      $node_es = $node->addTranslation('es');
      $title_ru = $node_ru->getTitle();
      $title_es = $this->translator->translateText($title_ru, 'ru', 'es');
      $body_ru = $node_ru->body->value;
      $body_es = $this->translator->translateText($body_ru, 'ru', 'es');
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



}
