<?php

/**
 * @file
 * Test Utilities.
 */

use Drupal\webform_d7_to_d8\traits\Utilities;
use PHPUnit\Framework\TestCase;

/**
 * Dummy object using Utilities for testing.
 */
class UtilitiesObject {
  use Utilities;

}

/**
 * Test Utilities.
 *
 * @group myproject
 */
class UtilitiesTest extends TestCase {

  /**
   * Smoke test for the utilities trait.
   */
  public function testSmokeTest() {
    $this->assertTrue(is_object(new UtilitiesObject()));
  }

}
