<?php

namespace Drupal\webform_d7_to_d8;

use Drupal\webform_d7_to_d8\traits\Utilities;
use Drupal\webform\WebformSubmissionForm;

/**
 * Represents a webform submission.
 */
class Submission {

  use Utilities;

  /**
   * Constructor.
   *
   * @param Webform $webform
   *   A webform to which this submission belongs
   *   (\Drupal\webform_d7_to_d8\Webform).
   * @param int $sid
   *   A submission ID on the legacy database.
   * @param array $info
   *   Extra info about the submission, corresponds to an associative array
   *   of legacy column names.
   * @param array $data
   *   Information about the data, keyed by form_key and containing the
   *   submitted data.
   * @param array $options
   *   Options originally passed to the migrator (for example ['nid' => 123])
   *   and documented in ./README.md.
   */
  public function __construct(Webform $webform, int $sid, array $info, array $data, array $options) {
    $this->webform = $webform;
    $this->sid = $sid;
    $this->info = $info;
    $this->data = $data;
    $this->options = $options;
  }

  /**
   * Check whether a submission was already processed.
   *
   * This is especially useful when an import was interrumpted.
   *
   * @return bool
   *   Whether a submission was already processed.
   *
   * @throws \Throwable
   */
  public function alreadyProcessed() : bool {
    $sid = $this->getSid();
    $nid = $this->getWebformNid();

    $items = $this->stateGetArray('webform_d7_to_d8_per_node', []);
    if (isset($items[$nid])) {
      return $items[$nid] >= $sid;
    }
    return FALSE;
  }

  /**
   * In cases where fields are required but not available, insert defaults.
   *
   * See ./README.md on how to set this up.
   */
  public function fillDefaults() {
    if (!empty($this->options['defaults if required'][$this->webform->getNid()])) {
      foreach ($this->options['defaults if required'][$this->webform->getNid()] as $default_key => $default_value) {
        if (empty($this->data[$default_key]['value'])) {
          $this->print('The required key @k was not set, setting it to @v', ['@k' => $default_key, '@v' => $default_value]);
          $this->data[$default_key]['value'] = $default_value;
        }
      }
    }
  }

  /**
   * Get the legacy submission ID.
   *
   * @return int
   *   The sid.
   */
  public function getSid() : int {
    return $this->sid;
  }

  /**
   * Get the legacy webform nid.
   *
   * @return int
   *   The nid for this webform.
   *
   * @throws \Exception
   */
  public function getWebformNid() : int {
    return $this->webform->getNid();
  }

  /**
   * Imports the submission.
   *
   * @return string
   *   The ID (simulated or real) of the new submission.
   *
   * @throws \Exception
   */
  public function process() : string {
    if ($this->alreadyProcessed()) {
      $this->print('Sid ' . $this->sid . ' for webform ' . $this->webform->getNid() . ' already processed; moving on...');
      return 'already processed';
    }

    // See https://www.drupal.org/docs/8/modules/webform/webform-cookbook/how-to-programmatically-create-a-submission
    $values = [
      'webform_id' => $this->webform->drupalObject->id(),
      'entity_type' => NULL,
      'entity_id' => NULL,
      'in_draft' => FALSE,
      'uid' => '1',
      'langcode' => 'en',
      'token' => 'pgmJREX2l4geg2RGFp0p78Qdfm1ksLxe6IlZ-mN9GZI',
      'uri' => '/webform/my_webform/api',
      'remote_addr' => '',
      'data' => [],
    ];
    $this->fillDefaults();
    foreach ($this->data as $key => $row) {
      $values['data'][$key] = $row['value'];
    }

    $errors = WebformSubmissionForm::validateFormValues($values);

    // Check there are no validation errors.
    if (!empty($errors)) {
      throw new \Exception('Errors with the following fields (they might be required, for example) for webform ' . $this->webform->getNid() . ': ' . implode(', ', array_keys($errors)) . '. See ./README.md on how to fix required fields.');
    }
    elseif (isset($this->options['simulate']) && $this->options['simulate']) {
      $this->print('Simulating new submission');
      $this->printR($this->data);
      $return = 'dummy-submission-id-' . md5(serialize($this->data));
    }
    else {
      // Submit values and get submission ID.
      $webform_submission = WebformSubmissionForm::submitFormValues($values);
      $this->rememberLastSid();
      $return = $webform_submission->id();
    }
    return $return;
  }

  /**
   * Remember that we processed this sid, to not do it multiple times.
   *
   * @throws \Exception
   */
  public function rememberLastSid() {
    $sid = $this->getSid();
    $nid = $this->getWebformNid();

    $items = $this->stateGetArray('webform_d7_to_d8_per_node');
    $items[$nid] = $sid;
    $items = \Drupal::state()->set('webform_d7_to_d8_per_node', $items);
  }

}
