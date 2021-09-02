<?php

namespace Drupal\book_importer\Commands;

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
    // @todo Translate node here.

//    if ($options['uppercase']) {
//      $text = strtoupper($text);
//    }
//    if ($options['reverse']) {
//      $text = strrev($text);
//    }
    $this->output()->writeln($text);
  }
}
