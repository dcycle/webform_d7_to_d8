<?php

namespace Drupal\webform_d7_to_d8;

use Drupal\webform_d7_to_d8\traits\Utilities;

/**
 * Represents a webform component.
 */
class Component {

  use Utilities;

  /**
   * Constructor.
   *
   * @param Webform $webform
   *   A webform to which this component belongs
   *   (is a \Drupal\webform_d7_to_d8\Webform).
   * @param int $cid
   *   A component ID on the legacy database.
   * @param array $info
   *   Extra info about the component, corresponds to an associative array
   *   of legacy column names.
   * @param array $options
   *   Options originally passed to the migrator (for example ['nid' => 123])
   *   and documented in ./README.md.
   */
  public function __construct(Webform $webform, int $cid, array $info, array $options) {
    $this->webform = $webform;
    $this->cid = $cid;
    $this->info = $info;
    $this->options = $options;
  }

  /**
   * Based on legacy data, create a Drupal 8 form element.
   *
   * @return array
   *   An associative array with keys '#title', '#type'...
   *
   * @throws Exception
   */
  public function createFormElement() : array {
    $info = $this->info;
    $return = [
      '#title' => $info['name'],
      '#type' => $info['type'],
      '#required' => $info['required'],
      '#default_value' => '',
    ];

    $this->extraInfo($return);

    return $return;
  }

  /**
   * Add extra information to a form element if necessary.
   *
   * @param array $array
   *   An associative array with keys '#title', '#type'... This can be
   *   modified by this function if necessary.
   *
   * @throws Exception
   */
  public function extraInfo(&$array) {

  }

  /**
   * Get the legacy component ID.
   *
   * @return int
   *   The cid.
   */
  public function getCid() : int {
    return $this->cid;
  }

  /**
   * Return a form array with only the current element, keyed by form_key.
   *
   * @return array
   *   The result of ::createFormElement(), keyed by the form_key.
   */
  public function toFormArray() : array {
    $info = $this->info;
    return [
      $info['form_key'] => $this->createFormElement(),
    ];
  }

}
