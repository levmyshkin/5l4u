<?php

namespace Drupal\book_importer\Commands;

use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\book_importer\Commands
 */
class SingleTranslateCommand extends DrushCommands {

  /**
   * Drush command that translate.
   *
   * @param string $nid
   *   Argument with node ID.
   * @command book_importer:single_translate
   * @aliases single-translate
   * @usage book_importer:single_translate 42
   */
  public function message($nid, $options = []) {
    $node = Node::load($nid);

    if (empty($node)) {
      return;
    }

    $translator = \Drupal::service('book_importer.nodejs_translator');


    $ru = $node->getTranslationStatus('ru');
    if (empty($ru)) {
      return;
    }

    // Google translate langcodes are the same with Drupal langcodes.
    // GT langcodes => Drupal langcodes.
    $languages = [
      'en' => 'en',
      'es' => 'es',
    ];

    foreach ($languages as $language) {
      $translation_status = $node->getTranslationStatus($language);
      if (empty($translation_status)) {
        $node_ru = $node->getTranslation('ru');
        $node_translated = $node->addTranslation($language);
        $title_ru = $node_ru->getTitle();
        $title_translated = $translator->translateText($title_ru, 'ru', $language);
        $node_translated->title = $title_translated;

        $body_ru = $node_ru->body->value;
        $body_translated = $translator->translateText($body_ru, 'ru', $language);
        $node_translated->body->value = $body_translated;
        $node_translated->body->format = 'full_html';

        $tags = $node->field_tags->getValue();
        $node_translated->field_tags->setValue($tags);

        $node_translated->save();
      }
    }

    $this->output()->writeln('Node has been translated: /node/' . $nid);
  }
}
