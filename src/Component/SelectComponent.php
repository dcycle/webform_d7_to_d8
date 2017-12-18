<?php

namespace Drupal\webform_d7_to_d8\Component;

use Drupal\webform_d7_to_d8\Component;

/**
 * Represents a select component.
 */
class SelectComponent extends Component {

  /**
   * {@inheritdoc}
   */
  public function extraInfo(&$array) {
    $extra = unserialize($this->info['extra']);
    $array['#options'] = $this->parseSelectOptions($extra['items']);
  }

  /**
   * Parse legacy select items.
   *
   * @param string $items
   *   Legacy items in the format 'a|b' . PHP_EOL . 'c|d'.
   *
   * @return array
   *   Select items in the format ['a' => 'b', 'c' => 'd'].
   */
  public function parseSelectOptions(string $items) : array {
    $return = [];
    $lines = explode(PHP_EOL, $items);
    foreach ($lines as $line) {
      $elements = explode('|', $line);
      if (count($elements) == 1) {
        $elements[1] = $elements[0];
      }
      if ($elements[0]) {
        $return[$elements[0]] = $elements[1];
      }
    }
    return $return;
  }

}
