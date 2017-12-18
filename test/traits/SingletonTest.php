<?php

/**
 * @file
 * Test Singleton.
 */

use Drupal\webform_d7_to_d8\traits\Singleton;
use PHPUnit\Framework\TestCase;

/**
 * Dummy Singleton for testing.
 */
class SingletonObject {
  use Singleton;

}

/**
 * Test Singleton.
 *
 * @group myproject
 */
class SingletonTest extends TestCase {

  /**
   * Test Singleton::instance().
   *
   * @covers ::instance().
   */
  public function testInstance() {
    $this->assertTrue(SingletonObject::instance() === SingletonObject::instance(), 'Singleton works correctly: instance is the same no matter how many times it is called.');
  }

}
