<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\OrderEmployeeForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\triune\Entity\OrderEmployee;

/**
 * OrderEmployee form.
 *
 * @ingroup triune
 */
class OrderEmployeeForm extends ContentEntityForm
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
        
        
        $order_employee = $this->entity;
        $is_new = $order_employee->isNew();
        
        if (!$order_employee) {
            $order_employee = OrderEmployee::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'order_id' => $form_state->getValue('order_id'),
                'employee_id' => $form_state->getValue('employee_id'),
                'office_id' => $form_state->getValue('office_id'),
                'date' => $form_state->getValue('date'),
                'shift' => $form_state->getValue('shift'),
                'status' => $form_state->getValue('status'),
                'driver' => $form_state->getValue('driver'),
                'present' => $form_state->getValue('present'),
                'notes' => $form_state->getValue('notes'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
          
            $order_employee->order_id = $form_state->getValue('order_id')[0];
            $order_employee->employee_id = $form_state->getValue('employee_id')[0];
            $order_employee->office_id = $form_state->getValue('office_id')[0];
            $order_employee->date->setValue($form_state->getValue('date'));
            $order_employee->shift->setValue($form_state->getValue('shift'));
            $order_employee->status->setValue($form_state->getValue('status'));
            $order_employee->driver->setValue($form_state->getValue('driver'));
            $order_employee->present->setValue($form_state->getValue('present'));
            $order_employee->notes->setValue($form_state->getValue('notes'));
            $order_employee->changed->setValue(time());
        }
        $order_employee->save();
        
        if ($is_new) {
            \Drupal::messenger()->addMessage($this->t('Created OrderEmployee @name', array('@name' => $form_state->getValue('id'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved OrderEmployee @name', array('@name' => $form_state->getValue('id'))), 'status');
        }

        $form_state->setRedirect('triune.entity.order_employee.collection');
        return;
        
    }
}
?>