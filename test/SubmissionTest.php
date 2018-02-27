<?php

/**
 * @file
 * Test Submission.
 */

use PHPUnit\Framework\TestCase;
use Drupal\webform_d7_to_d8\Submission;

/**
 * Test Submission.
 *
 * @group myproject
 */
class SubmissionTest extends TestCase {

  /**
   * Test for alreadyProcessed().
   *
   * @param int $sid
   *   The submission id.
   * @param int $nid
   *   The webform id.
   * @param array $per_node
   *   Value of the webform_d7_to_d8_per_node state variable.
   * @param bool $expected
   *   The expected value.
   *
   * @cover ::alreadyProcessed
   * @dataProvider providerAlreadyProcessed
   */
  public function testAlreadyProcessed(int $sid, int $nid, array $per_node, bool $expected) {
    $object = $this->getMockBuilder(Submission::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods([
        'getSid',
        'getWebformNid',
        'stateGetArray',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $object->method('getSid')
      ->willReturn($sid);
    $object->method('getWebformNid')
      ->willReturn($nid);
    $object->method('stateGetArray')
      ->willReturn($per_node);

    $output = $object->alreadyProcessed();
    $this->assertTrue($output == $expected);
  }

  /**
   * Provider for testAlreadyProcessed().
   */
  public function providerAlreadyProcessed() {
    return [
      [
        'sid' => 1,
        'nid' => 2,
        'per_node' => [],
        'expected' => FALSE,
      ],
      [
        'sid' => 1,
        'nid' => 2,
        'per_node' => [
          2 => 0,
        ],
        'expected' => FALSE,
      ],
      [
        'sid' => 1,
        'nid' => 2,
        'per_node' => [
          2 => 1,
        ],
        'expected' => TRUE,
      ],
      [
        'sid' => 1,
        'nid' => 2,
        'per_node' => [
          2 => 100,
        ],
        'expected' => TRUE,
      ],
    ];
  }


}
