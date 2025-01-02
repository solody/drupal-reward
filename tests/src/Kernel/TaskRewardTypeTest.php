<?php

declare(strict_types=1);

namespace Drupal\Tests\reward\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test description.
 */
#[Group('reward')]
final class TaskRewardTypeTest extends CommerceKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['reward', 'account', 'task', 'state_machine', 'dynamic_entity_reference'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Mock necessary services here.
    $this->installEntitySchema('reward');
    $this->installEntitySchema('task');
  }

  /**
   * Test callback.
   */
  public function testSomething(): void {
    self::assertTrue(TRUE);
  }

}
