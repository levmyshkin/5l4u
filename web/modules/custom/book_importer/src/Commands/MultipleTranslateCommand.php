<?php

namespace Drupal\book_importer\Commands;

use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\book_importer\Commands
 */
class MultipleTranslateCommand extends DrushCommands {

  /**
   * Drush command that translate.
   *
   * @command book_importer:multiple_translate
   * @aliases multiple-translate
   * @usage book_importer:multiple_translate
   */
  public function message($options = []) {
    // Google translate langcodes are the same with Drupal langcodes.
    // GT langcodes => Drupal langcodes.
    $languages = [
      'en' => 'en',
      'es' => 'es',
    ];

    $translator = \Drupal::service('book_importer.nodejs_translator');

    $query = \Drupal::entityQuery('node');
    $query->condition('type', "article");
    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $all_nodes_count = count($nodes);
    $count = 0;
    foreach ($nodes as $node) {
      $count++;
      $node = Node::load($node->id());

      if (empty($node)) {
        continue;
      }

      $ru = $node->getTranslationStatus('ru');
      if (empty($ru)) {
        continue;
      }

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

      $this->output()->writeln( 'Progress status: ' . $count . '/' . $all_nodes_count);
      $this->output()->writeln(date("Y.m.d H:m:i") . ' - Node has been translated: ' . $node->id());
    }
  }
}
