<?php

namespace Drupal\book_importer;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\migrate_media_handler\MediaMaker;
use Drupal\Core\File\FileSystemInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Import CSV form.
 */
class CategoryParser {

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Constructs a new CustomService object.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_manager
   *   Entity manager.
   */
  public function __construct(MediaMaker $media_maker, EntityTypeManager $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Render categories.
   */
  public function renderCategory($value) {
    $categories = [];
    if (!empty($value)) {
      $term_names = explode(',', $value);
      if (!empty($term_names)) {
        foreach ($term_names as $term_name) {
          $values = [
            'name' => $term_name,
            'langcode' => 'ru',
            'default_langcode' => [0, 1],
          ];
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($values);
          $term = reset($term);
          if (!empty($term)) {
            $categories[] = [
              'target_id' => $term->tid->value,
            ];
          }
          else {
            // Create a new term with russian translation.
            // Create the taxonomy term.
            $new_term = Term::create([
              'name' => $term_name,
              'vid' => 'tags',
              'parent' => [],
              'langcode' => 'ru',
            ]);

            // Save the taxonomy term.
            $new_term->save();
            $categories[] = [
              'target_id' => $new_term->tid->value,
            ];
          }
        }
      }
    }

    return $categories;
  }
}
