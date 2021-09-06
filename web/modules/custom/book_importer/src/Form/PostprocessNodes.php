<?php

namespace Drupal\book_importer\Form;

use Drupal\book_importer\NodejsTranslator;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Translate nodes.
 */
class PostprocessNodes extends FormBase {

  /**
   * Translator for text service.
   *
   * @var \Drupal\book_importer\NodejsTranslator
   */
  protected $translator;

  /**
   * Class constructor.
   *
   * @param \Drupal\book_importer\NodejsTranslator $translator
   *   Translate text.
   */
  public function __construct(NodejsTranslator $translator) {
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('book_importer.nodejs_translator'),
    );
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $content = '<table><tr><th>Russian</th><th>English</th><th>Spanish</th></tr></table>';

    $form['node_table'] = [
      '#markup' => $content,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Postprocess nodes'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @return string
   */
  public function getFormId() {
    return 'book_importer_postprocess_nodes_form';
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * Validate callback.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * Submit callback.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::entityQuery('node');
    $query->condition('type', "article");
    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    // @todo Move operation in separate service.
    foreach ($nodes as $node) {
      $operations[] = [
        '\Drupal\book_importer\Form\PostprocessNodes::processNodes',
        [$node->id()]
      ];
    }

    $batch = [
      'title' => $this->t('Processing nodes ...'),
      'operations' => $operations,
      'finished' => 'process_nodes_finished',
    ];
    batch_set($batch);
  }

  /**
   * Batch operation.
   */
  public function processNodes($row, &$context) {
    $node = Node::load($row);

    if (empty($node)) {
      return;
    }

    $post_process = \Drupal::service('book_importer.post_process');

    $en = $node->getTranslationStatus('en');
    $es = $node->getTranslationStatus('es');
    $ru = $node->getTranslationStatus('ru');

    // UTF-8 decode for Russian language.
    $body = $node->body->value;
//    $body = html_entity_decode($body);
//    $body = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $body);
//    $body = str_replace('<div class="detailInfo__detailWebform">                                                    </div>', '', $body);
//    $body = str_replace('class="detailInfo__detailText detailText"', '', $body);
//    $body = str_replace('<!DOCTYPE html><html><head></head><body>', '', $body);
//    $body = str_replace('<body>', '', $body);
//    $body = str_replace('</body>', '', $body);
//    $body = str_replace('<html>', '', $body);
//    $body = str_replace('</html>', '', $body);
    // Remove multiple spaces.
    //     $body = preg_replace('!\s+!', ' ', $body);

    // Remove all links from text.
//    for ($i = 0; $i < 30; $i++) {
/*      $body = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $body);*/
//    }


    $body = $post_process->processText($body);
    $node->body->value = $body;
    $node->body->format = 'full_html';
    $node->save();

    if (!empty($ru) && !empty($en)) {
      $translated_entity = $node->getTranslation('en');

      // Translate tags.
//      $tags = $node->field_tags->getValue();
//      $translated_entity->field_tags->setValue($tags);

      // Remove unused HTML.
      $body = $translated_entity->body->value;
      $body = $post_process->processText($body);
      $translated_entity->body->value = $body;
      $translated_entity->body->format = 'full_html';

      $translated_entity->save();
    }

    if (!empty($ru) && !empty($es)) {
      $translated_entity = $node->getTranslation('es');

      // Translate tags.
//      $tags = $node->field_tags->getValue();
//      $translated_entity->field_tags->setValue($tags);

      // Remove unused HTML.
      $body = $translated_entity->body->value;
//      $body = str_replace('<div class="detailInfo__detailWebform"><a name="webform"></a></div>', '', $body);
//      $body = str_replace('<!DOCTYPE html><html><head></head><body>', '', $body);
//      $body = str_replace('</body></html>', '', $body);
//      for ($i = 0; $i < 30; $i++) {
/*        $body = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $body);*/
//      }
      $body = $post_process->processText($body);
      $translated_entity->body->value = $body;
      $translated_entity->body->format = 'full_html';

      $translated_entity->save();
    }

    $context['results'][] = 'Processed node: ' . $node->id();
    $context['sandbox']['current_id'] = $node->id();
    $context['message'] = 'Processed page: ' . $node->label();
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   * Finished callback.
   */
  public function process_nodes_finished($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addStatus($message);
  }

}
