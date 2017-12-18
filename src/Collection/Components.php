<?php

namespace Drupal\webform_d7_to_d8\Collection;

use Drupal\webform_d7_to_d8\Collection;
use Drupal\webform_d7_to_d8\Component;

/**
 * Represents a collection.
 */
class Components extends Collection {

  /**
   * {@inheritdoc}
   */
  public function type() : string {
    return Component::class;
  }

  /**
   * Return as a form array.
   *
   * @return array
   *   Drupal 8 webform components as an array which should look like:
   *     form_key => [
   *       '#name' => 'name',
   *       '#type' => 'type',
   *     ].
   *
   * @throws Exception
   */
  public function toFormArray() : array {
    $return = [];

    foreach ($this->toArray() as $component) {
      $return = array_merge($return, $component->toFormArray());
    }

    return $return;
  }

}
