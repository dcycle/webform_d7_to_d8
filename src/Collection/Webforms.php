<?php

namespace Drupal\webform_d7_to_d8\Collection;

use Drupal\webform_d7_to_d8\Collection;
use Drupal\webform_d7_to_d8\Webform;

/**
 * Represents a collection.
 */
class Webforms extends Collection {

  /**
   * {@inheritdoc}
   */
  public function type() : string {
    return Webform::class;
  }

}
