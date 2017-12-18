<?php

namespace Drupal\webform_d7_to_d8\traits;

use Drupal\Core\Database\Database;
// Not sure why this class is said not be used by the linter...
// @codingStandardsIgnoreStart
use Drupal\Core\Database\Driver\mysql\Connection;
// @codingStandardsIgnoreEnd

/**
 * Wrapper around elements external to the application logic.
 */
trait Environment {

  /**
   * Mockable wrapper around Database::getConnection().
   *
   * @throws \Throwable
   */
  public function drupalGetConnection($target = 'default', $key = NULL) : Connection {
    return Database::getConnection($target, $key);
  }

  /**
   * Mockable wrapper around Database::getAllConnectionInfo().
   *
   * @throws \Throwable
   */
  public function getAllConnectionInfo() : array {
    return Database::getAllConnectionInfo();
  }

  /**
   * Mockable wrapper around print().
   */
  public function phpPrint($string) {
    print($string);
  }

  /**
   * Mockable wrapper around print_r().
   */
  public function printR($mixed) {
    print_r($mixed);
  }

  /**
   * Mockable wrapper around t().
   */
  public function t($string, array $args = array()) : string {
    // @codingStandardsIgnoreStart
    return t($string, $args);
    // @codingStandardsIgnoreEnd
  }

}
