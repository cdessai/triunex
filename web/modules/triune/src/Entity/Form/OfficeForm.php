<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\OfficeForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\triune\Entity\Office;

/**
 * Office form.
 *
 * @ingroup triune
 */
class OfficeForm extends ContentEntityForm
{
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        
        $form = parent::buildForm($form, $form_state);
        $entity = $this->entity;
        
        return $form;
        
    }
    

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $office = $this->entity;
        if ($office->isNew()) {
            $office = Office::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label')[0]['value'],
                'location_id' => $form_state->getValue('location_id')[0]['value'],
                'created' => time(),
                'changed' => time(),
                ]
            );
            // Remove button and internal Form API values from submitted values.
            $form_state->cleanValues();
            $this->entity = $this->buildEntity($form, $form_state);
            \Drupal::messenger()->addMessage($this->t('Created Office @name', array('@name' => $office->label->value)), 'status');
        }
        else {
            $office->label->setValue($form_state->getValue('label')[0]['value']);
            $office->location_id->setValue($form_state->getValue('location_id')[0]['value']);
            $office->changed->setValue(time());
            \Drupal::messenger()->addMessage($this->t('Saved Office @name', array('@name' => $office->label->value)), 'status');
        }
        $office->save();

        $form_state->setRedirect('triune.entity.office.collection');
        return $status;

    }
}
?>