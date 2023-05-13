<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\CallSheetInterface;
use Drupal\user\UserInterface;

/**
 * Defines the CallSheet entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_callsheet",
 *     label = @Translation("Triune Call Sheet"),
 *     base_table = "triune_callsheet",
 *     admin_permission = "administer callsheet entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\CallSheetListBuilder",
 *         "views_data" = "Drupal\triune\Entity\CallSheetViewsData",
 *         "form" = {
 *             "default" = "Drupal\triune\Entity\Form\CallSheetForm",
 *             "add" = "Drupal\triune\Entity\Form\CallSheetForm",
 *             "edit" = "Drupal\triune\Entity\Form\CallSheetForm",
 *             "delete" = "Drupal\triune\Entity\Form\CallSheetDeleteForm",
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
 *         "canonical" = "/triune/entity/callsheet/{triune_callsheet}",
 *         "edit-form" = "/triune/entity/callsheet/{triune_callsheet}/edit",
 *         "delete-form" = "/triune/entity/callsheet/{triune_callsheet}/delete",
 *         "collection" = "/admin/content/callsheet", 
 *     },
 *     field_ui_base_route = "triune.callsheet_settings",
 * )
 */

class CallSheet extends ContentEntityBase implements CallSheetInterface
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
        // Standard field, unique outside of the scope of the current call_sheet.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Call Sheet entity.'))
            ->setReadOnly(true);
        // User ID
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
            
        $fields['office_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Office'))
            ->setDescription(t('Associated Office ID'))
            ->setSettings(
                array(
                'target_type' => 'triune_office',
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
            
        $fields['date'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Date'))
            ->setDescription(t(''))
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
    public function getCallSheet()
    {
        return $this->get('content')->value;
    }
}
?>