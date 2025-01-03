<?php

declare(strict_types=1);

namespace Drupal\reward\EventSubscriber;

use Drupal\reward\RewardTypePluginManager;
use Drupal\Core\Database\Connection;
use Drupal\reward\Plugin\RewardType\Task;
use Drupal\task\Event\TaskEvents;
use Drupal\task\Event\TaskFinishedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo Add description for this subscriber.
 */
final class RewardSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a RewardSubscriber object.
   */
  public function __construct(
    private readonly Connection $connection,
    private readonly RewardTypePluginManager $rewardTypePluginManager,
  ) {}

  /**
   * Kernel request event handler.
   */
  public function onTaskFinished(TaskFinishedEvent $event): void {
    // Process every task reward.
    $plugin = $this->rewardTypePluginManager->createInstance('task');
    if ($plugin instanceof Task) {
      $plugin->onTaskFinished($event);
    }

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
