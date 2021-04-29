<?php

namespace Drupal\book_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Import CSV form.
 */
class ImportCsvForm extends FormBase {

/**
 * @param array $form
 * @param FormStateInterface $form_state
 * @return array
 */
public function buildForm(array $form, FormStateInterface $form_state) {
  $form = [
    '#attributes' => ['enctype' => 'multipart/form-data'],
  ];

  $form['file_upload_details'] = [
    '#markup' => t('<b>The File</b>'),
  ];

  $validators = [
    'file_validate_extensions' => ['csv'],
  ];
  $form['csv_file'] = [
    '#type' => 'managed_file',
    '#name' => 'csv_file',
    '#title' => t('File *'),
    '#size' => 20,
    '#description' => t('CSV format only'),
    '#upload_validators' => $validators,
    '#upload_location' => 'public://content/csv/',
  ];

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Import CSV'),
    '#button_type' => 'primary',
  ];

  return $form;
}

  /**
   * @return string
   */
  public function getFormId() {
    return 'book_importer_import_csv_form';
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
    $file = \Drupal::entityTypeManager()->getStorage('file')
      ->load($form_state->getValue('csv_file')[0]);
    if (empty($file)) {
      return;
    }
    $full_path = $file->get('uri')->value;
    $file_name = basename($full_path);

    $inputFileName = \Drupal::service('file_system')->realpath('public://content/csv/' . $file_name);

    $rows = array_map('str_getcsv', file($inputFileName));
    array_shift($rows);

    foreach ($rows as $row) {
      $operations[] = [
        '\Drupal\book_importer\Form\ImportCsvForm::importCsvNode',
        [$row]
      ];
    }

    $batch = [
      'title' => $this->t('Importing nodes ...'),
      'operations' => $operations,
      'finished' => 'import_csv_nodes_finished',
    ];
    batch_set($batch);
  }

  /**
   * Batch operation.
   */
  public function importCsvNode($row, &$context) {
    $title = $row[0];
    $body = $row[1];

    $node = Node::create([
      'type' => 'article',
      'langcode' => 'ru',
      'created' => \Drupal::time()->getRequestTime(),
      'changed' => \Drupal::time()->getRequestTime(),
      'uid' => 88,
      'title' => $title,
      'body' => [
        'summary' => '',
        'value' => $body,
        'format' => 'full_html',
      ],
    ]);
    $node->save();
    $context['results'][] = 'Created node: ' . $node->id();
    $context['sandbox']['current_id'] = $node->id();
    $context['message'] = 'Created page: ' . $node->getTitle();
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   * Finished callback.
   */
  public function import_csv_nodes_finished($success, $results, $operations) {
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
