<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base class for reward_type plugins.
 */
abstract class RewardTypePluginBase extends PluginBase implements RewardTypeInterface {

  /**
   * The database connection used.
   */
  protected Connection $connection;

  /**
   * The event_dispatcher.
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * The entityTypeManager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $connection,
    EventDispatcherInterface $event_dispatcher,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->eventDispatcher = $event_dispatcher;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('event_dispatcher'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
