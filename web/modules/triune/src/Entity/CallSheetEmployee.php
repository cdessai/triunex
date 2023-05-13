<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\CallSheetEmployeeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the CallSheetEmployee entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_callsheet_employee",
 *     label = @Translation("Triune Call Sheet Employee"),
 *     base_table = "triune_callsheet_employee",
 *     admin_permission = "administer callsheet_employee entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\CallSheetEmployeeListBuilder",
 *         "views_data" = "Drupal\views\Entity\EntityViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\CallSheetEmployeeForm",
 *             "edit" = "Drupal\triune\Entity\Form\CallSheetEmployeeForm",
 *             "delete" = "Drupal\triune\Entity\Form\CallSheetEmployeeDeleteForm",
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
 *         "canonical" = "/triune/entity/callsheet_employee/{triune_callsheet_employee}",
 *         "edit-form" = "/triune/entity/callsheet_employee/{triune_callsheet_employee}/edit",
 *         "delete-form" = "/triune/entity/callsheet_employee/{triune_callsheet_employee}/delete",
 *         "collection" = "/admin/content/callsheet_employee", 
 *     },
 *     field_ui_base_route = "triune.callsheet_employee_settings",
 * )
 */

class CallSheetEmployee extends ContentEntityBase implements CallSheetEmployeeInterface
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
            ->setDescription(t('The ID of the Call Sheet entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current call_sheet_employee.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Call Sheet entity.'))
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
        
        $fields['status'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Status'))
            ->setDescription(t(''))
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
    public function getCallSheetEmployee()
    {
        return $this->get('content')->value;
    }
}
?>