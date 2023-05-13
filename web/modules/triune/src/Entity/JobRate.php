<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\JobRateInterface;
use Drupal\user\UserInterface;

/**
 * Defines the JobRate entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_jobrate",
 *     label = @Translation("Triune JobRate"),
 *     base_table = "triune_jobrate",
 *     admin_permission = "administer jobrate entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\JobRateListBuilder",
 *         "views_data" = "Drupal\triune\Entity\JobRateViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\JobRateForm",
 *             "edit" = "Drupal\triune\Entity\Form\JobRateForm",
 *             "delete" = "Drupal\triune\Entity\Form\JobRateDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *         },
 *     },
 *     entity_keys = {
 *         "id" = "id",
 *         "label" = "label",
 *         "uuid" = "uuid", 
 *     },
 *     links = {
 *         "canonical" = "/triune/entity/jobrate/{triune_jobrate}",
 *         "edit-form" = "/triune/entity/jobrate/{triune_jobrate}/edit",
 *         "delete-form" = "/triune/entity/jobrate/{triune_jobrate}/delete",
 *         "collection" = "/admin/content/jobrate", 
 *     },
 *     field_ui_base_route = "triune.jobrate_settings",
 * )
 */

class JobRate extends ContentEntityBase implements JobRateInterface
{
    /**
     * {@inheritdoc}
     *
     * When a new entity instance is added, set the user_id entity reference to
     * the current user as the creator of the instance.
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
        $values += array(
            'user_id' => \Drupal::currentUser()->id(),
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getChangedTime()
    {
        return $this->get('changed')->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setChangedTime($timestamp)
    {
        $this->set('changed', $timestamp);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getChangedTimeAcrossTranslations()
    {
        $changed = $this->getUntranslated()->getChangedTime();
        return $changed;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->get('user_id')->entity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->get('user_id')->target_id;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->set('user_id', $account->id());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {

        // Standard field, used as unique if primary index.
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the JobRate entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current JobRate.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the JobRate entity.'))
            ->setReadOnly(true);
        
        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('User Id'))
            ->setDescription(t('The reference ID for the user.'))
            ->setSettings(
                array(
                'target_type' => 'user',
                'default_value' => 0,
                )
            )
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'list_default',
                'weight' => -6,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'options_select',
                'weight' => -6,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['customer_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Company'))
            ->setDescription(t('Associated Company ID'))
            ->setSettings(
                array(
                'target_type' => 'triune_customer',
                'default_value' => 0,
                )
            )
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'list_default',
                'weight' => -6,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'options_select',
                'weight' => -6,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
        $fields['position_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Position Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
        $fields['position_code'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Position Code'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['label'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Label'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
           
        $fields['status'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Status'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $fields['pay_rate'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Job Rate'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

            
        return $fields;
    }

    
    /**
     * {@inheritdoc}
     */
    public function getJobRate()
    {
        return $this->get('content')->value;
    }
}
?>