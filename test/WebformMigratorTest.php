<?php

/**
 * @file
 * Test WebformMigrator.
 */

use PHPUnit\Framework\TestCase;
use Drupal\webform_d7_to_d8\WebformMigrator;

/**
 * Test WebformMigrator.
 *
 * @group myproject
 */
class WebformMigratorTest extends TestCase {

  /**
   * Smoke test to see if we can load the class (no PHP errors).
   */
  public function testSmoke() {
    $object = WebformMigrator::instance();

    $this->assertTrue(get_class($object) == WebformMigrator::class, 'No PHP error in WebformMigrator code.');
  }

}
