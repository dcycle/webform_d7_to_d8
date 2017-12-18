<?php

namespace Drupal\webform_d7_to_d8\Collection;

use Drupal\webform_d7_to_d8\Collection;
use Drupal\webform_d7_to_d8\Submission;

/**
 * Represents a collection.
 */
class Submissions extends Collection {

  /**
   * {@inheritdoc}
   */
  public function type() : string {
    return Submission::class;
  }

}
