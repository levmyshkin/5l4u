<?php

namespace Drupal\book_importer;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\migrate_media_handler\MediaMaker;
use Drupal\Core\File\FileSystemInterface;

/**
 * Import CSV form.
 */
class TextParser {

  /**
   * Document to use.
   *
   * @var \DOMDocument
   */
  protected $document;

  /**
   * Xpath query object.
   *
   * @var \DOMXPath
   */
  protected $xpath;

  /**
   * Media Maker service.
   *
   * @var \Drupal\migrate_media_handler\MediaMaker
   */
  protected $mediaMaker;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Replace variable.
   *
   * @var string
   */
  protected $imgReplace = '';

  /**
   * Constructs a new CustomService object.
   * @param \Drupal\migrate_media_handler\MediaMaker $media_maker
   *   Media maker.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_manager
   *   Entity manager.
   */
  public function __construct(MediaMaker $media_maker, EntityTypeManager $entity_manager) {
    $this->mediaMaker = $media_maker;
    $this->entityManager = $entity_manager;
  }

  /**
   * Render body text.
   */
  public function renderBody($value) {
    // Initialize DOM handling on this value.
    $d = new \DOMDocument();
    $d->loadHTML('<?xml encoding="utf-8" ?>' . $value);

    $this->document = $d;
    $this->xpath = new \DOMXPath($this->document);

    // Loop through images to make into file/media entities and replace.
    foreach ($this->xpath->query('//img') as $html_node) {
      // Get attributes from the image.
      $src = $html_node->getAttribute('src');
      $alt = $html_node->getAttribute('alt');
      $title = $html_node->getAttribute('title');

      // Make sure it's not an inline png.
      if (!stripos($src, 'image/png')) {

        // Using migrate_media_handler.settings, find the actual file.
        if (preg_match('/https?:\/\//', $src) !== 0) {
          $file = system_retrieve_file($src, 'public://content', TRUE, FileSystemInterface::EXISTS_RENAME);
        }

        if (!$file) {
          continue;
        }

        $media = $this->makeImageEntity($file->id());
        $muuid = $media->uuid();

        if (!empty($muuid)) {
          // Create a new DOM element for the image in the text.
          $new_node = $this->document->createElement('img', "");
          // Add attributes to that element - start with uuid.
          $dom_att = $this->document->createAttribute('data-entity-uuid');
          $dom_att->value = $muuid;
          $new_node->appendChild($dom_att);

          $dom_att = $this->document->createAttribute('src');
          $dom_att->value = file_create_url($file->getFileUri());
          $new_node->appendChild($dom_att);

          $dom_att = $this->document->createAttribute('alt');
          $dom_att->value = $alt;
          $new_node->appendChild($dom_att);

          $dom_att = $this->document->createAttribute('title');
          $dom_att->value = $title;
          $new_node->appendChild($dom_att);

          // Replace the <img> with <drupal-media>.
          $html_node->parentNode->replaceChild($new_node, $html_node);
        }
      }
    }

    // Remove script. Method 1.
    $script_tags = $d->getElementsByTagName('script');
    $length = $script_tags->length;

    for ($i = 0; $i < $length; $i++) {
      if(is_object($script_tags->item($i)->parentNode)) {
        $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
      }
    }

    // Remove script. Method 2.
    $tags = $d->getElementsByTagName('script');
    $remove = [];
    foreach($tags as $item) {
      $remove[] = $item;
    }
    foreach ($remove as $item) {
      $item->parentNode->removeChild($item);
    }

    // Remove iframe. Method 1.
    $script_tags = $d->getElementsByTagName('iframe');
    $length = $script_tags->length;

    for ($i = 0; $i < $length; $i++) {
      if(is_object($script_tags->item($i)->parentNode)) {
        $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
      }
    }

    // Remove script. Method 2.
    $tags = $d->getElementsByTagName('iframe');
    $remove = [];
    foreach($tags as $item) {
      $remove[] = $item;
    }
    foreach ($remove as $item) {
      $item->parentNode->removeChild($item);
    }

    $html = $this->document->saveHTML();
    $html = str_replace('<?xml encoding="utf-8" ?>', '', $html);

    return $html;
  }

  /**
   * Make an image media entity out of a file. Add alt, title if avail.
   *
   * @param int $file_id
   *   ID of existing file entity.
   *
   * @return bool|\Drupal\media\Entity\Media
   *   Return media entity or FALSE
   */
  public function makeImageEntity(int $file_id) {
    $media = FALSE;
    // Load file entity.
    $file = $this->entityManager->getStorage('file')->load($file_id);
    // If that's successful, carry on.
    if ($file) {
      // Create media entity with saved file.
      // Please note accessibility concerns around empty alt & title.
      $media = $this->entityManager->getStorage('media')->create([
        'bundle' => 'image',
        'field_media_image' => [
          'target_id' => $file_id,
          'alt' => '',
          'title' => '',
        ],
        'langcode' => 'en',
      ]);
      $owner = $file->getOwnerId();
      $filename = $file->getFilename();
      $media->setOwnerId($owner);
      $media->setName($filename);
      $media->save();

    }
    return $media;
  }

}
