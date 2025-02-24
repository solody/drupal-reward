<?php

declare(strict_types=1);

namespace Drupal\reward\EventSubscriber;

use Drupal\reward\RewardTypePluginManager;
use Drupal\task\Event\TaskEvents;
use Drupal\task\Event\TaskFinishedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Process reward when event dispatch.
 */
class RewardSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a RewardSubscriber object.
   */
  public function __construct(
    protected RewardTypePluginManager $rewardTypePluginManager,
  ) {}

  /**
   * Automatically claim the reward when corresponding task has been finished.
   */
  public function onTaskFinished(TaskFinishedEvent $event): void {
    // Process every task reward.
    /** @var \Drupal\reward\Plugin\RewardType\Task $plugin */
    $plugin = $this->rewardTypePluginManager->createInstance('task');
    $plugin->autoClaim($event);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      TaskEvents::TASK_FINISHED => ['onTaskFinished'],
    ];
  }

}
