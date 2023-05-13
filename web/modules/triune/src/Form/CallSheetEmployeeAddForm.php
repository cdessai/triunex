<?php
/**
 * @file
 * Contains \Drupal\triune\Form\CallSheetEmployeeAddForm.
 */
namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\triune\Entity\CallSheetEmployee;
use Drupal\triune\Entity\CallSheet;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Controller\CallSheetController;

/**
 * CallSheetEmployeeAdd form.
 */
class CallSheetEmployeeAddForm extends FormBase
{
    
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'callsheet_employee_add_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $callsheet_id = 0, $employee_list = array(), $callsheet_employee_list = array())
    {
    
    
        $form['callsheet_id'] = array(
        '#type' => 'hidden',
        '#title' => t(''),
        '#required' => true,
        '#default_value' => $callsheet_id,
        );
    
        // generate checkbox list
        $options = array();
        foreach ($employee_list as $key => $value) {
            $employee = Employee::load($value['id']);
            $options[$employee->id()] = '';
        }
    
        // Set default checkboxes
        $default = array();
        foreach ($callsheet_employee_list as $key => $value) {
            $callsheet_employee = CallSheetEmployee::load($value->id);
            $default[$key] = $callsheet_employee->employee_id->target_id;
        }
    
        $form['employees'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Employees'),
        '#required' => false,
        '#options' => $options,
        '#default_value' => $default,
        );
    
        $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
        );
    
        return $form;
      
    }
    
  
    private function buildRow($form, array $employee)
    {
        // Set the basic properties.


        // Present a checkbox for installing and indicating the status of a module.
        $row['enable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Install'),
        '#default_value' => (bool) $module->status,
        '#disabled' => (bool) $module->status,
        ];
    

        return $row;
    }

  
  
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    
        // Get existing callsheet_employees
        $callsheet_employees = CallSheetController::queryCallSheetEmployeeList($form_state->getValue('callsheet_id'));
    
    
        // First loop through form employees
        foreach ($form_state->getValue('employees') as $key => $form_employee) {
            $existing = false;
            $delete = false;
            if ($form_employee == 0) {
                $delete = true;
            }
            // Then loop through existing callsheet employees
            foreach ($callsheet_employees as $callsheet_employee) {
                // Delete blanks from callsheet employees
                if ($delete && $callsheet_employee->employee_id == $key) {
                    $entity = CallSheetEmployee::load($callsheet_employee->id);
                    $entity->delete();
                } else if ($form_employee == $callsheet_employee->employee_id) {
                    $existing = true;
                }
            }
      
            // if form_employee doesn't exist as callsheet_employee
            if (!$existing && !$delete) {
                $entity = CallSheetEmployee::create(
                    [
                    'uuid' => \Drupal::service('uuid')->generate(),
                    'callsheet_id' => $form_state->getValue('callsheet_id'),
                    'employee_id' => $form_employee,
                    'notes' => '',
                    'status' => 'pending',
                    'created' => time(),
                    'changed' => time(),
                    ]
                );
                $entity->save();
            }
      
        }
    
        $form_state->setRedirect('triune.callsheet.view', ['callsheet_id' => $form_state->getValue('callsheet_id')]);
        return;
    }
}
?>