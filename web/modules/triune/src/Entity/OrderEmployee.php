<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\OrderEmployeeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the OrderEmployee entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_order_employee",
 *     label = @Translation("Triune Order Employee"),
 *     base_table = "triune_order_employee",
 *     admin_permission = "administer order_employee entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\OrderEmployeeListBuilder",
 *         "views_data" = "Drupal\views\Entity\EntityViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\OrderEmployeeForm",
 *             "edit" = "Drupal\triune\Entity\Form\OrderEmployeeForm",
 *             "delete" = "Drupal\triune\Entity\Form\OrderEmployeeDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *         },
 *     },
 *     entity_keys = {
 *         "id" = "id",
 *         "label" = "id",
 *         "uuid" = "uuid", 
 *     },
 *     links = {
 *         "canonical" = "/triune/entity/order_employee/{triune_order_employee}",
 *         "edit-form" = "/triune/entity/order_employee/{triune_order_employee}/edit",
 *         "delete-form" = "/triune/entity/order_employee/{triune_order_employee}/delete",
 *         "collection" = "/admin/content/order_employee", 
 *     },
 *     field_ui_base_route = "triune.order_employee_settings",
 * )
 */

class OrderEmployee extends ContentEntityBase implements OrderEmployeeInterface
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
            ->setDescription(t('The ID of the Order entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current order_employee.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Order entity.'))
            ->setReadOnly(true);
            
        $fields['callsheet_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Call Sheet'))
            ->setDescription(t('The Call Sheet ID'))
            ->setSetting('target_type', 'triune_callsheet')
            ->setSetting('handler', 'default')
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'entity_reference_label',
                'weight' => -3,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'entity_reference_autocomplete',
                'settings' => array(
                'match_operator' => 'CONTAINS',
                'size' => 60,
                'autocomplete_type' => 'tags',
                'placeholder' => '',
                ),
                'weight' => -3,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Order'))
            ->setDescription(t('The Order ID'))
            ->setSetting('target_type', 'triune_order')
            ->setSetting('handler', 'default')
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'entity_reference_label',
                'weight' => -3,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'entity_reference_autocomplete',
                'settings' => array(
                'match_operator' => 'CONTAINS',
                'size' => 60,
                'autocomplete_type' => 'tags',
                'placeholder' => '',
                ),
                'weight' => -3,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['employee_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Employee'))
            ->setDescription(t('The Employee ID'))
            ->setSetting('target_type', 'triune_employee')
            ->setSetting('handler', 'default')
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'entity_reference_label',
                'weight' => -3,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'entity_reference_autocomplete',
                'settings' => array(
                'match_operator' => 'CONTAINS',
                'size' => 60,
                'autocomplete_type' => 'tags',
                'placeholder' => '',
                ),
                'weight' => -3,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
        
        $fields['office_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Office'))
            ->setDescription(t('The Office ID'))
            ->setSetting('target_type', 'triune_office')
            ->setSetting('handler', 'default')
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'entity_reference_label',
                'weight' => -3,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'entity_reference_autocomplete',
                'settings' => array(
                'match_operator' => 'CONTAINS',
                'size' => 60,
                'autocomplete_type' => 'tags',
                'placeholder' => '',
                ),
                'weight' => -3,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['status'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Status'))
            ->setDescription(t('Available, Unavailable, Assigned, Released'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -1,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -1,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['date'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Date'))
            ->setDescription(t(''))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);
            
        $fields['shift'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Shift'))
            ->setDescription(t('Which shift the employee is assigned'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);
            
        $fields['present'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Present'))
            ->setDescription(t('Whether Employee arrived for work'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);
        
        $fields['driver'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Driver'))
            ->setDescription(t('Whether the employee is designated as transport driver'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);
            
        $fields['notes'] = BaseFieldDefinition::create('text_long')
            ->setLabel(t('Notes'))
            ->setDescription(t(''))
            ->setTranslatable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'hidden',
                'type' => 'text_default',
                'weight' => 0,
                )
            )
            ->setDisplayConfigurable('view', true)
            ->setDisplayOptions(
                'form', array (
                'type' => 'text_textfield',
                'weight' => 0,
                )
            )
            ->setDisplayConfigurable('form', true);
            
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
    public function getOrderEmployee()
    {
        return $this->get('content')->value;
    }
}
?>