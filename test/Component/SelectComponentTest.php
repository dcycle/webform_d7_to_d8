<?php

/**
 * @file
 * Test Drupal\webform_d7_to_d8\Component\SelectComponent.
 */

use Drupal\webform_d7_to_d8\Component\SelectComponent;
use PHPUnit\Framework\TestCase;

/**
 * Test Drupal\webform_d7_to_d8\Component\SelectComponent.
 *
 * @group myproject
 */
class SelectComponentTest extends TestCase {

  /**
   * Test for parseSelectOptions().
   *
   * @param string $input
   *   The input.
   * @param array $expected
   *   The expected result.
   *
   * @cover ::parseSelectOptions
   * @dataProvider providerParseSelectOptions
   */
  public function testParseSelectOptions(string $input, array $expected) {
    $object = $this->getMockBuilder(SelectComponent::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue($object->parseSelectOptions($input) == $expected);
  }

  /**
   * Provider for testParseSelectOptions().
   */
  public function providerParseSelectOptions() {
    return [
      [
        'a|b
c|d',
        [
          'a' => 'b',
          'c' => 'd',
        ],
      ],
      [
        'a|b
c',
        [
          'a' => 'b',
          'c' => 'c',
        ],
      ],
      [
        'a|b
c|d
',
        [
          'a' => 'b',
          'c' => 'd',
        ],
      ],
    ];
  }

}
