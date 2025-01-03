<?php

declare(strict_types=1);

namespace Drupal\Tests\reward\Kernel;

use Drupal\account\Entity\Account;
use Drupal\account\Entity\AccountType;
use Drupal\commerce_price\Price;
use Drupal\reward\Entity\Reward;
use Drupal\reward\Entity\RewardClaim;
use Drupal\reward\RewardClaimInterface;
use Drupal\task\Entity\Task;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test description.
 */
#[Group('reward')]
final class RewardCalimStorageTest extends CommerceKernelTestBase {

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
  }

  /**
   * Test callback.
   */
  public function testSomething(): void {
    $user = $this->createUser();
    $account_type = AccountType::create([
      'id' => 'something',
      'label' => 'something',
    ]);
    $account_type->save();
    $account = Account::create([
      'type' => $account_type->id(),
      'name' => 'something',
      'uid' => $user,
      'concurrency_code' => 'USD',
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

    /** @var \Drupal\reward\RewardClaimStorageInterface $reward_claim_storage */
    $reward_claim_storage = $this->entityTypeManager->getStorage('reward_claim');
    $reward_claim_storage->addRewardClaim((int) $reward->id(), (int) $user->id());
    $reward_claim_storage->addRewardClaim((int) $reward->id(), (int) $user->id());
    $reward_claim_storage->addRewardClaim((int) $reward->id(), (int) $user->id());

    self::assertEquals(1, count($reward_claim_storage->loadAllOfReward((int) $reward->id())));
    self::assertEquals(1, count($reward_claim_storage->loadAllOfUser((int) $user->id())));
    self::assertTrue($reward_claim_storage->getRewardClaim((int) $reward->id(), (int) $user->id()) instanceof RewardClaimInterface);
  }

}
