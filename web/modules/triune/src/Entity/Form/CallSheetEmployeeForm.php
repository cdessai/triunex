<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\CallSheetEmployeeForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\triune\Entity\CallSheetEmployee;

/**
 * CallSheetEmployee form.
 *
 * @ingroup triune
 */
class CallSheetEmployeeForm extends ContentEntityForm
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
        
        
        $callsheet_employee = $this->entity;
        $is_new = $callsheet_employee->isNew();
        
        if (!$callsheet_employee) {
            $callsheet_employee = CallSheetEmployee::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'callsheet_id' => $form_state->getValue('callsheet_id'),
                'employee_id' => $form_state->getValue('employee_id'),
                'status' => $form_state->getValue('status'),
                'notes' => $form_state->getValue('notes'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
          
            $callsheet_employee->callsheet_id = $form_state->getValue('callsheet_id')[0];
            $callsheet_employee->employee_id = $form_state->getValue('employee_id')[0];
            $callsheet_employee->status->setValue($form_state->getValue('status'));
            $callsheet_employee->notes->setValue($form_state->getValue('notes'));
            $callsheet_employee->changed->setValue(time());
        }
        $callsheet_employee->save();
        //$status = parent::save($form, $form_state);
        if ($is_new) {
            \Drupal::messenger()->addMessage($this->t('Created CallSheetEmployee @name', array('@name' => $form_state->getValue('id'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved CallSheetEmployee @name', array('@name' => $form_state->getValue('id'))), 'status');
        }

        $form_state->setRedirect('triune.entity.callsheet_employee.collection');
        return;
        
    }
}
?>