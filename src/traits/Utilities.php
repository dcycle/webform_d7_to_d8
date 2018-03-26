<?php

namespace Drupal\webform_d7_to_d8\traits;

use Drupal\webform\Entity\Webform;
// Not sure why this class is said not be used by the linter...
// @codingStandardsIgnoreStart
use Drupal\Core\Database\Driver\mysql\Connection;
// @codingStandardsIgnoreEnd

/**
 * Utility functions.
 */
trait Utilities {

  use Environment;

  /**
   * Errors this class needs to keep track of.
   *
   * @var array
   */
  public static $errors;

  /**
   * Update a Drupal 8 webform.
   *
   * @param array $info
   *   Info about the webform, must contain the keys id and title.
   * @param array $options
   *   Options originally passed to the migrator (for example ['nid' => 123])
   *   and documented in ./README.md.
   * @param bool $new_only
   *   Set to TRUE if you want to completely ignore webforms which preexist
   *   on the target environment.
   * @param bool $continue
   *   This function will set this to FALSE if further processing, except
   *   importing submissions (which will always happen), should occur.
   *
   * @return Webform
   *   A Drupal 8 webform object (Drupal\webform\Entity\Webform).
   */
  public function updateD8Webform(array $info, array $options = [], bool $new_only = FALSE, bool &$continue = TRUE) : Webform {
    if (empty($info['title'])) {
      throw new \Exception('Name cannot be empty');
    }
    $webform = Webform::load($info['id']);
    if ($webform && $new_only) {
      $this->print('Webform ' . $info['id'] . ' already exists, aborting.');
      $continue = FALSE;
      return $webform;
    }
    if (!$webform) {
      $webform = Webform::create($info);
    }
    else {
      foreach ($info as $key => $value) {
        $webform->set($key, $value);
      }
    }
    if (isset($options['simulate']) && $options['simulate']) {
      $this->print('Simulating new webform');
      $this->printR($info);
    }
    else {
      $webform->save();
    }
    return $webform;
  }

  /**
   * Update D8 webform comonents; delete existing components and replace them.
   *
   * @param Drupal\webform\Entity\Webform $webform
   *   A Drupal 8 webform object.
   * @param array $info
   *   Drupal 8 webform components as an array which should look like:
   *     form_key => [
   *       '#name' => 'name',
   *       '#type' => 'type',
   *     ].
   * @param array $options
   *   Options originally passed to the migrator (for example ['nid' => 123])
   *   and documented in ./README.md.
   *
   * @return Webform
   *   A Drupal 8 webform object (\Drupal\webform\Entity\Webform).
   *
   * @throws Exception
   */
  public function updateD8Components(Webform $webform, array $info, array $options = []) : Webform {
    if (isset($options['simulate']) && $options['simulate']) {
      $this->print('Simulating new components');
      $this->printR($info);
    }
    $this->print('Replacing components for the webform with...');
    $this->printR($info);
    $webform->setElements($info);
    if (!(isset($options['simulate']) && $options['simulate'])) {
      $webform->save();
    }
    return $webform;
  }

  /**
   * Get a database connection.
   *
   * @param string $name
   *   The name of the connection in the settings.php file, see ./README.md
   *   for details.
   *
   * @throws \Exception
   */
  public function getConnection(string $name = '') : Connection {
    $connections = $this->getAllConnectionInfo();
    $realname = $name ?: 'default';
    if (!isset($connections[$realname])) {
      throw new \Exception($this->t('Could not connect to @name, please make sure you have access to the legacy database and it is defined in your settings.php file, as described in .../webform_d7_to_d8/README.md.', ['@name' => $realname]));
    }
    return $this->drupalGetConnection('default', $name);
  }

  /**
   * Print a translatable string to screen.
   *
   * @param string $string
   *   String to be passed to t().
   * @param array $vars
   *   Vars to be passed to t().
   */
  public function print(string $string, array $vars = []) {
    // @codingStandardsIgnoreStart
    $this->phpPrint($this->t($string, $vars) . PHP_EOL);
    // @codingStandardsIgnoreEnd
  }

  /**
   * Get a state variable which must be an array.
   *
   * @param string $variable
   *   The variable name.
   * @param array $default
   *   The defalut value.
   *
   * @return array
   *   The value.
   *
   * @throws Exception
   */
  public function stateGetArray(string $variable, array $default = []) : array {
    return \Drupal::state()->get($variable, $default);
  }

}
