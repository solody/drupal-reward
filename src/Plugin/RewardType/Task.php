<?php

declare(strict_types=1);

namespace Drupal\reward\Plugin\RewardType;

use Drupal\account\Entity\LedgerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\reward\Attribute\RewardType;
use Drupal\reward\RewardInterface;
use Drupal\reward\RewardTypePluginBase;
use Drupal\task\Event\TaskFinishedEvent;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the reward_type.
 */
#[RewardType(
  id: 'task',
  label: new TranslatableMarkup('Task'),
  description: new TranslatableMarkup('Finished specified task to get the reward.'),
)]
final class Task extends RewardTypePluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['task'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel($this->t('Task'))
      ->setRequired(TRUE)
      ->setDescription($this->t('Which task should be finished to get the reward.'))
      ->setSetting('target_type', 'task')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['task_goal'] = BaseFieldDefinition::create('integer')
      ->setLabel($this->t('Task goal'))
      ->setDescription($this->t('Which task goal be finished to get the reward.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ]);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function canClaim(RewardInterface $reward, AccountInterface $user): bool {

    return TRUE;
  }

  /**
   * On TaskFinished.
   *
   * @param \Drupal\task\Event\TaskFinishedEvent $event
   *   The event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onTaskFinished(TaskFinishedEvent $event) {

    /** @var \Drupal\reward\RewardClaimStorageInterface $rewardClaimStorage */
    $rewardClaimStorage = $this->entityTypeManager->getStorage('reward_claim');

    foreach ($this->loadAllRewards() as $reward) {
      $task = $reward->get('task')->referencedEntities();
      $task_goal = (int) $reward->get('task_goal')->value;
      if (!empty($task)) {
        $task = reset($task);
        if (
          (int) $task->id() === $event->getTaskId()
          && $event->getGoal() === $task_goal
          && $reward->get('auto_claim')->value
        ) {
          $rewardClaimStorage->addRewardClaim((int) $reward->id(), $event->getUid());
          // Add amount to user account.
          $account_type = $reward->get('account_type')->referencedEntities();
          $account_type = reset($account_type);
          $currency = $account_type->getCurrency();
          $account = $this->financeManager->createAccount($event->getUser(), $account_type->id(), $currency);
          $this->financeManager->createLedger(
            $account,
            LedgerInterface::AMOUNT_TYPE_DEBIT,
            $reward->getAmount(),
            $this->t('Reward got when task finished.'),
            $rewardClaimStorage->getRewardClaim((int) $reward->id(), $event->getUid())
          );

        }
      }
    }

  }

}
