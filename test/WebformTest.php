<?php

/**
 * @file
 * Test Webform.
 */

use PHPUnit\Framework\TestCase;
use Drupal\webform_d7_to_d8\Webform;

/**
 * Test Webform.
 *
 * @group myproject
 */
class WebformTest extends TestCase {

  /**
   * Smoke test to see if we can load the class (no PHP errors).
   */
  public function testSmoke() {
    $object = new Webform(12345, 'whatever', []);

    $this->assertTrue(get_class($object) == Webform::class, 'No PHP error in Webform code.');
  }

}
