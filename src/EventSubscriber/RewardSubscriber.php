<?php

declare(strict_types=1);

namespace Drupal\reward\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\reward\Entity\Reward;
use Drupal\task\Event\TaskEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @todo Add description for this subscriber.
 */
final class RewardSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a RewardSubscriber object.
   */
  public function __construct(
    private readonly Connection $connection,
  ) {}

  /**
   * Kernel request event handler.
   */
  public function onTaskFinished(TaskFinishedEvent $event): void {
    $task = $event->getTask();
    /** @var \Drupal\account\Entity\AccountInterface $accountStorage */
    $accountStorage = $this->entityTypeManager->getStorage('account');
    $account = $accountStorage->loadByUser($event->getUid());
    // Process every task reward.
    $rewards = Reward::loadMultiple();
    foreach ($rewards as $reward) {

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
