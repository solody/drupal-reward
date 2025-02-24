<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\account\Entity\LedgerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\account\FinanceManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\reward\Entity\Reward;
use Drupal\user\Entity\User;

/**
 * {@inheritdoc}
 */
class ClaimManager implements ClaimManagerInterface {

  const OPERATION_ID = 'claim_reward';

  /**
   * Constructs a ClaimManager object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FinanceManagerInterface $financeManager,
    private LockBackendInterface $lock,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function claimReward(int $reward_id, int $uid): RewardClaimInterface {

    $is_get_lock = $this->lock->acquire(self::OPERATION_ID);
    if (!$is_get_lock) {
      if (!$this->lock->wait(self::OPERATION_ID, 30)) {
        $is_get_lock = $this->lock->acquire(self::OPERATION_ID);
      }
    }

    if ($is_get_lock) {
      try {
        $reward = Reward::load($reward_id);
        $user = User::load($uid);

        if (!$reward->getRewardTypePlugin()->canClaim($reward, $user)) {
          throw new \Exception('Reward can not be claimed so far.');
        }

        /** @var \Drupal\reward\RewardClaimStorageInterface $rewardClaimStorage */
        $rewardClaimStorage = $this->entityTypeManager->getStorage('reward_claim');
        $claim = $rewardClaimStorage->addRewardClaim((int) $reward->id(), (int) $user->id());

        // Add amount to user account.
        $account_type = $reward->get('account_type')->referencedEntities();
        $account_type = reset($account_type);
        $currency = $reward->getAmount()->getCurrencyCode();
        $account = $this->financeManager->getAccount($user, $account_type->id(), $currency);
        $reward_id = $reward->id();
        $task_id = $reward->get('task_id')->target_id;

        $this->financeManager->createLedger(
          $account,
          LedgerInterface::AMOUNT_TYPE_DEBIT,
          $reward->getAmount(),
          "Reward $reward_id got when task $task_id finished",
          $claim
        );

        // Invalidate the cache.
        Cache::invalidateTags(["task:$task_id", 'task_list', "reward:$reward_id", 'reward_list']);

        return $claim;
      }
      finally {
        $this->lock->release(self::OPERATION_ID);
      }
    }
    else {
      throw new \Exception('Can not acquire lock [' . self::OPERATION_ID . ']');
    }
  }

}
