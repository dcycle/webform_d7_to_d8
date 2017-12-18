<?php

namespace Drupal\webform_d7_to_d8;

use Drupal\webform_d7_to_d8\traits\Singleton;
use Drupal\webform_d7_to_d8\traits\Utilities;
use Drupal\webform_d7_to_d8\Collection\Webforms;

/**
 * Encapsulated code for the application.
 */
class WebformMigrator {

  use Singleton;
  use Utilities;

  /**
   * Add an error, which can be shown at the end of the process.
   *
   * @param string $string
   *   An error string.
   */
  public function addError(string $string) {
    if (!is_array(self::$errors)) {
      self::$errors = [];
    }
    // Using md5() as a key will prevent duplicates.
    self::$errors[md5($string)] = $string;
  }

  /**
   * Get all errors.
   *
   * @return array
   *   All errors.
   */
  public function errors() : array {
    if (!is_array(self::$errors)) {
      self::$errors = [];
    }
    return self::$errors;
  }

  /**
   * Run migration steps.
   *
   * See ./README.md for details and usage.
   *
   * @param array $options
   *   Associative array specificying options. See ./README.md for details.
   */
  public function run(array $options = []) {
    $this->testConnection();
    $webforms = $this->webforms($options)->toArray();
    foreach ($webforms as $webform) {
      $this->print('Processing webform with nid @n', ['@n' => $webform->getNid()]);
      $webform->process();
    }
    $errors = $this->errors();
    if (!count($errors)) {
      $this->print('No errors during import!');
    }
    else {
      $this->print('The following ERRORS occured during import.');
      foreach ($errors as $error) {
        $this->print($error);
      }
    }
  }

  /**
   * Throw an exception if the connection to the legacy database doesn't work.
   *
   * @throws \Exception
   */
  public function testConnection() {
    $this->print('About to test the connection to legacy');
    $this->print('database by trying to fetch nids from the');
    $this->print('webform table. Because the webform table only');
    $this->print('has a nid column on Drupal 7, if this does not');
    $this->print('fail, we know we have a good connection');
    $this->print('');
    $this->print('If you get a failure here, please make sure');
    $this->print('you follow the instructions in ./README.md');
    $this->print('');
    $query = $this->getConnection('upgrade')->select('webform', 'webform');
    $query->addField('webform', 'nid');
    $result = $query->execute()->fetchAllAssoc('nid');
    if (!count($result)) {
      throw new \Exception('Did not get any results, this probably means you have no webforms on the legacy site, so this module will not do anything!');
    }
    $this->print('');
    $this->print('OK, got at least one result: @r', ['@r' => array_pop(array_keys($result))]);
    $this->print('');
    $this->print('Good news: the connection to your legacy database seems OK!');
    $this->print('');
  }

  /**
   * Get all webforms as Webform objects.
   *
   * @param array $options
   *   Can contain "nid" => 123. If empty, all nodes will be loaded.
   *
   * @throws \Exception
   */
  public function webforms(array $options = []) : Webforms {
    $query = $this->getConnection('upgrade')->select('webform', 'webform');
    $query->addField('webform', 'nid');
    $query->join('node', 'n', 'n.nid = webform.nid');
    $query->addField('n', 'title');
    if (!empty($options['nid'])) {
      $query->condition('n.nid', $options['nid']);
      $this->print('Trying to find a legacy webform with nid @n', ['@n' => $options['nid']]);
    }
    $result = $query->execute()->fetchAllAssoc('nid');
    $array = [];
    foreach ($result as $nid => $info) {
      $array[$nid] = new Webform($nid, $info->title, $options);
    }
    if (!count($array)) {
      throw new \Exception('Could not find any webform.');
    }

    return new Webforms($array);
  }

}
