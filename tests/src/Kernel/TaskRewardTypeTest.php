<?php

declare(strict_types=1);

namespace Drupal\Tests\reward\Kernel;

use Drupal\account\Entity\Account;
use Drupal\account\Entity\AccountType;
use Drupal\commerce_price\Price;
use Drupal\reward\Entity\Reward;
use Drupal\task\Entity\Task;
use Drupal\task_test\Event\TestPluginEvent;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test description.
 */
#[Group('reward')]
final class TaskRewardTypeTest extends CommerceKernelTestBase {

  use UserCreationTrait {
    createRole as drupalCreateRole;
    createUser as drupalCreateUser;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'reward', 'account', 'task', 'task_test',
    'node', 'state_machine', 'dynamic_entity_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Mock necessary services here.
    $this->installEntitySchema('reward');
    $this->installEntitySchema('reward_claim');
    $this->installEntitySchema('task');
    $this->installEntitySchema('user');
    $this->installEntitySchema('account');
    $this->installEntitySchema('account_type');
    $this->installEntitySchema('ledger');
    $this->installSchema('task', [
      'task_finish',
    ]);
  }

  /**
   * Test callback.
   */
  public function testSomething(): void {
    $user = $this->createUser();
    $account_type = AccountType::create([
      'id' => 'something',
      'label' => 'something',
      'currency' => 'USD',
    ]);
    $account_type->save();
    $account = Account::create([
      'type' => $account_type->id(),
      'name' => 'something',
      'uid' => $user,
    ]);
    $account->save();

    $task = Task::create([
      'type' => 'test',
    ]);
    $task->save();

    $reward = Reward::create([
      'type' => 'task',
      'name' => '10 Dolors',
      'amount' => new Price('10', 'USD'),
      'account_type' => $account_type->id(),
      'auto_claim' => TRUE,
      'task' => $task,
    ]);
    $reward->save();

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher */
    $event_dispatcher = $this->container->get('event_dispatcher');
    $event_dispatcher->dispatch(new TestPluginEvent((int) $user->id()), TestPluginEvent::EVENT_NAME);

    self::assertTrue(TRUE);
  }

}
