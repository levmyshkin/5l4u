<?php

namespace Drupal\book_importer\Form;

use Drupal\book_importer\Translator;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Translate terms.
 */
class TranslateTerms extends FormBase {

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
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "tags");
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    foreach ($terms as $term) {
      $content .= '<tr>';

      // Russian.
      $name = $term->getName();
      $content .= '<td>' . $name . '</td>';

      // English.
      if ($term->hasTranslation('en')) {
        $name = $term->getTranslation('en')->getName();
        $content .= '<td>' . $name . '</td>';
      }
      else {
        $content .= '<td> - </td>';
      }

      // Spanish.
      if ($term->hasTranslation('es')) {
        $name = $term->getTranslation('es')->getName();
        $content .= '<td>' . $name . '</td>';
      }
      else {
        $content .= '<td> - </td>';
      }
      $content .= '</tr>';
    }
    $content .= '</table>';


    $form['terms_table'] = [
      '#markup' => $content,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Translate terms'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @return string
   */
  public function getFormId() {
    return 'book_importer_translate_terms_form';
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
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "tags");
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    // Move operation in separate service.
    foreach ($terms as $term) {
      $operations[] = [
        '\Drupal\book_importer\Form\TranslateTerms::translateTerms',
        [$term->id()]
      ];
    }

    $batch = [
      'title' => $this->t('Translating terms ...'),
      'operations' => $operations,
      'finished' => 'translate_terms_finished',
    ];
    batch_set($batch);
  }

  /**
   * Batch operation.
   */
  public function translateTerms($row, &$context) {
    $term = Term::load($row);

    if (empty($term)) {
      return;
    }

    $translator = \Drupal::service('book_importer.translator');
    $russian_name = $term->getName();
    if (!$term->hasTranslation('en')) {
      $term_en = $term->addTranslation('en');
      $translated_name = $translator->translateText($russian_name, 'ru_RU', 'en_GB');
      $term_en->name = $translated_name;
      $term_en->save();
    }

    if (!$term->hasTranslation('es')) {
      $term_es = $term->addTranslation('es');
      $translated_name = $translator->translateText($russian_name, 'ru_RU', 'es_ES');
      $term_es->name = $translated_name;
      $term_es->save();
    }

    $context['results'][] = 'Created term: ' . $term->id();
    $context['sandbox']['current_id'] = $term->id();
    $context['message'] = 'Created page: ' . $term->getName();
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   * Finished callback.
   */
  public function translate_terms_finished($success, $results, $operations) {
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
