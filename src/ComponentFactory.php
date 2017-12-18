<?php

namespace Drupal\webform_d7_to_d8;

use Drupal\webform_d7_to_d8\Component\SelectComponent;
use Drupal\webform_d7_to_d8\traits\Singleton;

/**
 * Component factory.
 */
class ComponentFactory {

  use Singleton;

  /**
   * Create a component.
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
   *
   * @return Component
   *   A webform component.
   */
  public function create(Webform $webform, int $cid, array $info, array $options) : Component {
    switch ($info['type']) {
      case 'select':
        $class = SelectComponent::class;
        break;

      default:
        $class = Component::class;
        break;
    }

    return new $class($webform, $cid, $info, $options);
  }

}
