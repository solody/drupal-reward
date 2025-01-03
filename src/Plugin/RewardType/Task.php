<?php

declare(strict_types=1);

namespace Drupal\reward\Plugin\RewardType;

use Drupal\account\Entity\LedgerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\reward\Attribute\RewardType;
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
    return $fields;
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
      if (!empty($task)) {
        $task = reset($task);
        if ((int) $task->id() === $event->getTaskId() && $reward->get('auto_claim')->value) {
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
