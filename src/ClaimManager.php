<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\account\Entity\LedgerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\account\FinanceManagerInterface;
use Drupal\reward\Entity\Reward;
use Drupal\user\Entity\User;

/**
 * {@inheritdoc}
 */
class ClaimManager implements ClaimManagerInterface {

  /**
   * Constructs a ClaimManager object.
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private FinanceManagerInterface $financeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function claimReward(int $reward_id, int $uid): RewardClaimInterface {
    $reward = Reward::load($reward_id);
    $user = User::load($uid);

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

    return $claim;
  }

}
