<?php

namespace Drupal\book_importer\Form;

use Drupal\book_importer\Translator;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Translate nodes.
 */
class TranslateNodes extends FormBase {

  /**
   * Translator for text service.
   *
   * @var \Drupal\book_importer\Translator
   */
  protected $translator;

  /**
   * Class constructor.
   *
   * @param \Drupal\book_importer\Translator $translator
   *   Translate text.
   */
  public function __construct(Translator $translator) {
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('book_importer.translator'),
    );
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $content = '<table><tr><th>Russian</th><th>English</th><th>Spanish</th></tr>';
    $query = \Drupal::entityQuery('node');
    $query->condition('type', "article");
    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $content .= '<tr>';

      // Russian.
      $name = $node->label();
      $content .= '<td>' . $name . '</td>';

      // English.
      if ($node->hasTranslation('en')) {
        $name = $node->getTranslation('en')->label();
        $content .= '<td>' . $name . '</td>';
      }
      else {
        $content .= '<td> - </td>';
      }

      // Spanish.
      if ($node->hasTranslation('es')) {
        $name = $node->getTranslation('es')->label();
        $content .= '<td>' . $name . '</td>';
      }
      else {
        $content .= '<td> - </td>';
      }
      $content .= '</tr>';
    }
    $content .= '</table>';


    $form['node_table'] = [
      '#markup' => $content,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Translate nodes'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @return string
   */
  public function getFormId() {
    return 'book_importer_translate_nodes_form';
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
        '\Drupal\book_importer\Form\TranslateNodes::translateNodes',
        [$node->id()]
      ];
    }

    $batch = [
      'title' => $this->t('Translating nodes ...'),
      'operations' => $operations,
      'finished' => 'translate_nodes_finished',
    ];
    batch_set($batch);
  }

  /**
   * Batch operation.
   */
  public function translateNodes($row, &$context) {
    $node = Node::load($row);

    if (empty($node)) {
      return;
    }

    $translator = \Drupal::service('book_importer.translator');

    $en = $node->getTranslationStatus('en');
    $es = $node->getTranslationStatus('es');
    $ru = $node->getTranslationStatus('ru');

    if (!empty($ru) && empty($en)) {
      $node_ru = $node->getTranslation('ru');
      $node_en = $node->addTranslation('en');
      $title_ru = $node_ru->getTitle();
      $title_en = $translator->translateText($title_ru, 'ru_RU', 'en_GB');
      $body_ru = $node_ru->body->value;
      $body_en = $translator->translateText($body_ru, 'ru_RU', 'en_GB');
      $node_en->title = $title_en;
      $node_en->body->value = $body_en;
      $node_en->body->format = 'full_html';
      $node_en->save();
    }

    if (!empty($ru) && empty($es)) {
      $node_ru = $node->getTranslation('ru');
      $node_es = $node->addTranslation('es');
      $title_ru = $node_ru->getTitle();
      $title_es = $translator->translateText($title_ru, 'ru_RU', 'es_ES');
      $body_ru = $node_ru->body->value;
      $body_es = $translator->translateText($body_ru, 'ru_RU', 'es_ES');
      $node_es->title = $title_es;
      $node_es->body->value = $body_es;
      $node_es->body->format = 'full_html';
      $node_es->save();
    }

    $context['results'][] = 'Created node: ' . $node->id();
    $context['sandbox']['current_id'] = $node->id();
    $context['message'] = 'Created page: ' . $node->label();
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   * Finished callback.
   */
  public function translate_nodes_finished($success, $results, $operations) {
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
