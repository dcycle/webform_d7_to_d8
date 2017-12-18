<?php

namespace Drupal\webform_d7_to_d8;

/**
 * Represents a collection.
 */
abstract class Collection {

  /**
   * Constructor..
   *
   * @parram array $array
   *   The data as an array.
   *
   * @throws \Exception
   */
  public function __construct(array $array) {
    $this->array = $array;
    // Test that the type is right.
    $this->toArray();
  }

  /**
   * Get the collection as an array.
   *
   * @return array
   *   The data as an array.
   *
   * @throws \Exception
   */
  public function toArray() : array {
    $type = $this->type();
    foreach ($this->array as $row) {
      if (!is_a($row, $type)) {
        throw new \Exception('Data is the wrong type.');
      }
    }
    return $this->array;
  }

  /**
   * Get the type of data in the collection.
   *
   * @return string
   *   A data type.
   */
  abstract public function type() : string;

}
