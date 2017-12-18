<?php

namespace Drupal\webform_d7_to_d8\traits;

/**
 * Implements the Singleton design pattern.
 */
trait Singleton {

  /**
   * The unique instance of this object.
   *
   * @var object
   */
  static private $instance;

  /**
   * Constructor; private because the only way to get an object is instance().
   */
  private function __construct() {
  }

  /**
   * Returns the only instance of the class.
   *
   * @return object
   *   The unique instance of this object.
   *
   * @throws Exception
   */
  static public function instance() {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

}
