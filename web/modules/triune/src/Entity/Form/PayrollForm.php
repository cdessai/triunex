<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\JobRateForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\triune\Entity\Payroll;

/**
 * Order form.
 *
 * @ingroup triune
 */
class PayrollForm extends ContentEntityForm
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
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $payroll = $this->entity;
        if ($payroll->isNew()) {
            $payroll = Payroll::create([
                'uuid' => \Drupal::service('uuid')->generate(),
                'user_id' => $form_state->getValue('user_id'),
                // DrupalDateTime
                'date' => $form_state->getValue('date')[0]['value']->format('Y-m-d'),
                'customer_id' => $form_state->getValue('customer_id'),
                'position_name' => $form_state->getValue('position_name'),
                'position_code' => $form_state->getValue('position_code'),
                'rate' => $form_state->getValue('rate'),
            ]);
            $form_state->cleanValues();
            $this->entity = $this->buildEntity($form, $form_state);
            
            $this->messenger()->addStatus($this->t('Created payroll ID: @id', ['@id' => $payroll->id->value]));
        } else {
            $payroll->user_id->setValue($form_state->getValue('user_id'));
            $payroll->date->setValue($form_state->getValue('date')[0]['value']->format('Y-m-d'));
            $payroll->customer_id->setValue($form_state->getValue('customer_id'));
            $payroll->position_name->setValue($form_state->getValue('position_name'));
            $payroll->position_code->setValue($form_state->getValue('position_code'));
            $payroll->rate->setValue($form_state->getValue('rate'));
            $payroll->save();
            
            $this->messenger()->addStatus($this->t('Updated payroll ID: @id', ['@id' => $payroll->id->value]));
        }
        
        $form_state->setRedirect('triune.entity.payroll.collection');
    }
}
