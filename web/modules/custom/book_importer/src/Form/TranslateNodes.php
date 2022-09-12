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
class TranslateNodes extends FormBase {

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
    // @todo move languages in configuration form.
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

    $translator = \Drupal::service('book_importer.nodejs_translator');

    // Preprocess nodes.
    // UTF-8 decode for Russian language.
//    $body = $node->body->value;

    $en = $node->getTranslationStatus('en');
    $es = $node->getTranslationStatus('es');

    if (empty($es) && empty($en)) {
//      $body = html_entity_decode($body);
//      $body = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $body);
//      $body = str_replace('<div class="detailInfo__detailWebform">                                                    </div>', '', $body);
//      $body = str_replace('class="detailInfo__detailText detailText"', '', $body);
//      $body = str_replace('<!DOCTYPE html><html><head></head><body>', '', $body);
//      $body = str_replace('<body>', '', $body);
//      $body = str_replace('</body>', '', $body);
//      $body = str_replace('<html>', '', $body);
//      $body = str_replace('</html>', '', $body);

      // Remove all links from text.
//      for ($i = 0; $i < 30; $i++) {
/*        $body = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $body);*/
//      }

//      $node->body->value = $body;
//      $node->body->format = 'full_html';
//      $node->save();
    }

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
