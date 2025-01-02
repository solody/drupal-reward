<?php

declare(strict_types=1);

namespace Drupal\reward\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\reward\RewardClaimInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the reward claim entity class.
 *
 * @ContentEntityType(
 *   id = "reward_claim",
 *   label = @Translation("Reward claim"),
 *   label_collection = @Translation("Reward claims"),
 *   label_singular = @Translation("reward claim"),
 *   label_plural = @Translation("reward claims"),
 *   label_count = @PluralTranslation(
 *     singular = "@count reward claims",
 *     plural = "@count reward claims",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\reward\RewardClaimListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\reward\RewardClaimAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\reward\Form\RewardClaimForm",
 *       "edit" = "Drupal\reward\Form\RewardClaimForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "reward_claim",
 *   admin_permission = "administer reward_claim",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/claim",
 *     "add-form" = "/admin/reward/reward-claim/add",
 *     "canonical" = "/admin/reward/reward-claim/{reward_claim}",
 *     "edit-form" = "/admin/reward/reward-claim/{reward_claim}/edit",
 *     "delete-form" = "/admin/reward/reward-claim/{reward_claim}/delete",
 *     "delete-multiple-form" = "/admin/content/claim/delete-multiple",
 *   },
 * )
 */
final class RewardClaim extends ContentEntityBase implements RewardClaimInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Claimer'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user')
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

    $fields['reward_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Claimer'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'reward')
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the reward claim was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
