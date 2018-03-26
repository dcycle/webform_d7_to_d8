<?php

namespace Drupal\webform_d7_to_d8;

use Drupal\webform_d7_to_d8\traits\Utilities;
use Drupal\webform_d7_to_d8\Collection\Components;
use Drupal\webform_d7_to_d8\Collection\Submissions;
use Drupal\webform\Entity\Webform as DrupalWebform;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Represents a webform.
 */
class Webform {

  use Utilities;

  /**
   * Constructor.
   *
   * @param int $nid
   *   The legacy Drupal node ID.
   * @param string $title
   *   The title of the legacy node which will become the title of the new
   *   webform (which, in Drupal 8, is not a node).
   * @param array $options
   *   Options originally passed to the migrator (for example ['nid' => 123])
   *   and documented in ./README.md.
   */
  public function __construct(int $nid, string $title, array $options) {
    $this->nid = $nid;
    $this->title = $title;
    $this->options = $options;
  }

  /**
   * Delete all submissions for this webform on the Drupal 8 database.
   *
   * This is never called, but is available to external code.
   *
   * @throws Exception
   */
  public function deleteSubmissions() {
    if (isset($this->options['simulate']) && $this->options['simulate']) {
      $this->print('SIMULATE: Delete submissions for webform before reimporting them.');
      return;
    }
    $query = $this->getConnection('default')->select('webform_submission', 'ws');
    $query->condition('ws.webform_id', 'webform_' . $this->getNid());
    $query->addField('ws', 'sid');
    $result = array_keys($query->execute()->fetchAllAssoc('sid'));

    $max = \Drupal::state()->get('webform_d7_to_d8_max_delete_items', 500);
    $this->print('Will delete @n submissions in chunks of @c to avoid avoid out of memory errors.', ['@n' => count($result), '@c' => $max]);

    $arrays = array_chunk($result, $max);

    $this->print('@n chunks generated.', ['@n' => count($arrays)]);

    $storage = \Drupal::entityTypeManager()->getStorage('webform_submission');
    foreach ($arrays as $array) {
      $submissions = WebformSubmission::loadMultiple($array);
      $this->print('Deleting @n submissions for webform @f', ['@n' => count($submissions), '@f' => $this->getNid()]);
      $storage->delete($submissions);
    }
  }

  /**
   * Return the first sid (submission id) to import.
   */
  public function firstSid() {
    return \Drupal::state()->get('webform_d7_to_d8', 0);
  }

  /**
   * Get the Drupal 8 Webform object.
   *
   * @return Drupal\webform\Entity\Webform
   *   The Drupal webform ojbect as DrupalWebform.
   */
  public function getDrupalObject() : DrupalWebform {
    return $this->drupalObject;
  }

  /**
   * Getter for nid.
   *
   * @return int
   *   The webform nid.
   */
  public function getNid() {
    return $this->nid;
  }

  /**
   * Import this webform, all its components and all its submissions.
   *
   * @param array $options
   *   Options originally passed to the migrator (for example ['nid' => 123])
   *   and documented in ./README.md.
   *
   * @throws \Exception
   */
  public function process($options = []) {
    $new_only = empty($options['new-only']) ? FALSE : TRUE;
    $continue = TRUE;
    $this->drupalObject = $this->updateD8Webform([
      'id' => 'webform_' . $this->getNid(),
      'title' => $this->title,
    ], $options, $new_only, $continue);
    if ($continue) {
      $components = $this->webformComponents();
      $this->print($this->t('Form @n: Processing components', ['@n' => $this->getNid()]));
      $this->updateD8Components($this->getDrupalObject(), $components->toFormArray(), $this->options);
    }
    else {
      $this->print($this->t('Form @n: NOT processing components', ['@n' => $this->getNid()]));
    }
    $submissions = $this->webformSubmissions()->toArray();
    foreach ($submissions as $submission) {
      $this->print($this->t('Form @n: Processing submission @s', ['@n' => $this->getNid(), '@s' => $submission->getSid()]));
      try {
        $submission->process();
      }
      catch (\Throwable $t) {
        $this->print('ERROR with submission (errors and possible fixes will be shown at the end of the process)');
        WebformMigrator::instance()->addError($t->getMessage());
      }
    }
    $node = node_load($this->getNid());
    if (isset($this->options['simulate']) && $this->options['simulate']) {
      $this->print('SIMULATE: Linking node to the webform we just created.');
    }
    elseif ($node) {
      try {
        $this->print('Linking node @n to the webform we just created.', ['@n' => $this->getNid()]);
        $node->webform->target_id = 'webform_' . $this->getNid();
        $node->save();
      }
      catch (\Exception $e) {
        $this->print('Node @n exists on the target environment, but we could not set the webform field to the appropriate webform, moving on...', ['@n' => $this->getNid()]);
      }
    }
    else {
      $this->print('Node @n does not exist on the target environment, moving on...', ['@n' => $this->getNid()]);
    }
  }

  /**
   * Set the Drupal 8 Webform object.
   *
   * @param Drupal\webform\Entity\Webform $webform
   *   The Drupal webform ojbect as DrupalWebform.
   */
  public function setDrupalObject(DrupalWebform $webform) {
    $this->drupalObject = $webform;
  }

  /**
   * Get all legacy submitted data for this webform.
   *
   * @return array
   *   Submissions keyed by legacy sid (submission ID).
   *
   * @throws Exception
   */
  public function submittedData() : array {
    $return = [];
    $query = $this->getConnection('upgrade')->select('webform_submitted_data', 'wd');
    $query->join('webform_component', 'c', 'c.cid = wd.cid AND c.nid = wd.nid');
    $query->addField('c', 'form_key');
    $query->addField('wd', 'sid');
    $query->addField('wd', 'data');
    $query->condition('wd.nid', $this->getNid(), '=');
    $result = $query->execute()->fetchAll();
    $return = [];
    foreach ($result as $row) {
      $return[$row->sid][$row->form_key] = [
        'value' => $row->data,
      ];
    }
    return $return;
  }

  /**
   * Get all legacy components for a given webform.
   *
   * @return Components
   *   The components.
   *
   * @throws \Exception
   */
  public function webformComponents() : Components {
    $query = $this->getConnection('upgrade')->select('webform_component', 'wc');
    $query->addField('wc', 'cid');
    $query->addField('wc', 'form_key');
    $query->addField('wc', 'name');
    $query->addField('wc', 'required');
    $query->addField('wc', 'type');
    $query->addField('wc', 'extra');
    $query->condition('nid', $this->getNid(), '=');
    $query->orderBy('weight');

    $result = $query->execute()->fetchAllAssoc('cid');
    $array = [];
    foreach ($result as $cid => $info) {
      $array[] = ComponentFactory::instance()->create($this, $cid, (array) $info, $this->options);
    }

    return new Components($array);
  }

  /**
   * Get all legacy submissions for a given webform.
   *
   * @return Submissions
   *   The submissions.
   *
   * @throws \Exception
   */
  public function webformSubmissions() : Submissions {
    if (isset($this->options['max_submissions']) && $this->options['max_submissions'] !== NULL) {
      $max = $this->options['max_submissions'];
      if ($max === 0) {
        $this->print('You specified max_submissions to 0, so no submissions will be loaded.');
        return new Submissions([]);
      }
    }

    $this->print('Only getting submission ids > @s because we have already imported the others.', ['@s' => $this->firstSid()]);

    $query = $this->getConnection('upgrade')->select('webform_submissions', 'ws');
    $query->addField('ws', 'sid');
    $query->condition('nid', $this->getNid(), '=');
    $query->condition('sid', $this->firstSid(), '>');

    if (isset($max)) {
      $this->print('You speicifc max_submissions to @n, so only some submissions will be processed.', ['@n' => $max]);
      $query->range(0, $max);
    }
    $submitted_data = $this->submittedData();

    $result = $query->execute()->fetchAllAssoc('sid');
    $array = [];
    foreach ($result as $sid => $info) {
      if (empty($submitted_data[$sid])) {
        $this->print('In the legacy system, there is a submission with');
        $this->print('id @id, but it does not have any associated data.', ['@id' => $sid]);
        $this->print('Ignoring it and moving on...');
        continue;
      }
      $this->print('Importing submission @s', ['@s' => $sid]);
      $array[] = new Submission($this, $sid, (array) $info, $submitted_data[$sid], $this->options);
    }

    return new Submissions($array);
  }

}
