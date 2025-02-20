<?php

declare(strict_types=1);

namespace Drupal\reward\Plugin\rest\resource;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\reward\ClaimManagerInterface;
use Drupal\reward\RewardInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Route;

/**
 * Represents Reward claim records as resources.
 *
 * @DCG
 * The plugin exposes key-value records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. An
 * example of such configuration can be located in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively, you can enable it through admin interface provider by REST UI
 * module.
 * @see https://www.drupal.org/project/restui
 *
 * @DCG
 * Notice that this plugin does not provide any validation for the data.
 * Consider creating custom normalizer to validate and normalize the incoming
 * data. It can be enabled in the plugin definition as follows.
 * @code
 *   serialization_class = "Drupal\foo\MyDataStructure",
 * @endcode
 *
 * @DCG
 * For entities, it is recommended to use REST resource plugin provided by
 * Drupal core.
 * @see \Drupal\rest\Plugin\rest\resource\EntityResource
 */
#[RestResource(
  id: 'reward_claim',
  label: new TranslatableMarkup('Reward claim'),
  uri_paths: [
    'create' => '/api/rest/reward/{reward}/claim',
  ],
)]
final class RewardClaimResource extends ResourceBase {

  /**
   * The claim manager.
   */
  protected ClaimManagerInterface $claimManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    ClaimManagerInterface $claim_manager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->claimManager = $claim_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('reward.claim'),
    );
  }

  /**
   * Responds to POST requests and saves the new record.
   */
  public function post(RewardInterface $reward): ModifiedResourceResponse {
    if (!\Drupal::currentUser()->isAuthenticated()) {
      throw new AccessDeniedHttpException();
    }
    $claim = $this->claimManager->claimReward((int) $reward->id(), (int) \Drupal::currentUser()->id());
    // Return the newly created record in the response body.
    return new ModifiedResourceResponse($claim, 201);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method): Route {
    $route = parent::getBaseRoute($canonical_path, $method);
    // Set ID validation pattern.
    if ($method === 'POST') {
      $route->setRequirement('reward', '\d+');
    }
    $parameters = $route->getOption('parameters') ?: [];
    $parameters['reward']['type'] = 'entity:reward';
    $route->setOption('parameters', $parameters);
    return $route;
  }

}
