<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\EmployeeForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Employee form.
 *
 * @ingroup triune
 */
class EmployeeForm extends ContentEntityForm
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
        
        $employee = $this->entity;
        if ($employee->isNew()) {
            \Drupal::messenger()->addMessage($this->t('Created Employee @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved Employee @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        }
        
        if (!$employee) {
            $employee = Employee::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'resource_id' => $form_state->getValue('resource_id'),
                'first_name' => $form_state->getValue('first_name'),
                'last_name' => $form_state->getValue('last_name'),
                'phone' => $form_state->getValue('phone'),
                'office_id' => $form_state->getValue('office_id'),
                'customer_id' => $form_state->getValue('customer_id'),
                'shift' => $form_state->getValue('shift'),
                'job' => $form_state->getValue('job'),
                'driver' => $form_state->getValue('driver'),
                //'start_date' => $form_state->getValue('start_date'),
                'status' => true,
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
            $employee->label->setValue($form_state->getValue('label'));
            $employee->resource_id->setValue($form_state->getValue('resource_id'));
            $employee->first_name->setValue($form_state->getValue('first_name'));
            $employee->last_name->setValue($form_state->getValue('last_name'));
            $employee->phone->setValue($form_state->getValue('phone'));
            $employee->status->setValue($form_state->getValue('status'));
            $employee->office_id->setValue($form_state->getValue('office_id'));
            $employee->customer_id->setValue($form_state->getValue('customer_id'));
            $employee->shift->setValue($form_state->getValue('shift'));
            $employee->job->setValue($form_state->getValue('job'));
            $employee->driver->setValue($form_state->getValue('driver'));
            //$employee->start_date->setValue($form_state->getValue('start_date'));
            //$employee->status->setValue($form_state->getValue('status'));
            $employee->changed->setValue(time());
        }
        $employee->save();

        $form_state->setRedirect('triune.entity.employee.collection');
        return;
        
    }
}
?>