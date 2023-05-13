<?php
/**
 * @file
 * Contains \Drupal\triune\Form\CallSheetEmployeeEditForm.
 */
namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\triune\Entity\CallSheet;
use Drupal\triune\Entity\CallSheetEmployee;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\OrderEmployee;
use Drupal\triune\Controller\CallSheetController;
use Drupal\triune\Controller\OrderController;

/**
 * CallSheetEmployeeEdit form.
 */
class CallSheetEmployeeEditForm extends FormBase
{
    
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'callsheet_employee_edit_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $callsheet_id = 0, $employee_id = 0, $date_range = array())
    {
    
        $callsheet = CallSheet::load($callsheet_id);
        $employee = Employee::load($employee_id);
        $shift = $employee->shift->value;
        $status = $employee->status->value;
        $notes = '';
    
        $callsheet_employee_data = CallSheetController::queryCallSheetEmployeeData($callsheet_id, $employee_id);
        $callsheet_employee = CallSheetEmployee::load($callsheet_employee_data->id);
        $employee_notes = $callsheet_employee->notes->value;
    
    
        $form['id'] = array(
        '#type' => 'hidden',
        '#title' => t('ID'),
        '#required' => true,
        '#default_value' => 0,
        );
        $form['callsheet_id'] = array(
        '#type' => 'textfield',
        '#title' => t('Call Sheet'),
        '#required' => true,
        '#default_value' => $callsheet->id(),
        );
        $form['employee_id'] = array(
        '#type' => 'textfield',
        '#title' => t('Employee'),
        '#required' => true,
        '#default_value' => $employee->id(),
        );
      
        $condition['employee_id'] = $employee->id();
        foreach ($date_range as $key => $date) {
            $shift = $employee->shift->value;
            $status = $employee->status->value;
            $notes = '';
      
            $condition['date'] = $date;
            $order_employee_data = OrderController::queryOrderEmployeeData($condition);
            if ($order_employee_data) {
                $order_employee = OrderEmployee::load($order_employee_data->id);
                $shift = $order_employee->shift->value;
                $status = $order_employee->status->value;
                $notes = $order_employee->notes->value;
            }
      
            $form['shift_'. $key] = array(
            '#type' => 'select',
            '#title' => t(''),
            '#required' => false,
            '#options' => [
            0 => $this->t('-Any-'),
            1 => $this->t('1st'),
            2 => $this->t('2nd'),
            3 => $this->t('3rd'),
            ],
            '#default_value' => $shift,
            );
            $status_options = array(
            'pending' => t('Pending'),
            'available' => t('Available'),
            'unavailable' => t('Unavailable'),
            //'released' => t('Released'),
            );
            if ($status == 'assigned') {
                $status_options['assigned'] = t('Assigned');
            }
            $form['status_'. $key] = array(
            '#type' => 'select',
            '#title' => t(''),
            '#required' => false,
            '#options' => $status_options,
            '#default_value' => $status,
            );
            $form['notes_'. $key] = array(
            '#type' => 'textfield',
            '#title' => t(''),
            '#required' => false,
            '#default_value' => $notes,
            '#maxlength' => 255,
            );
        }
    
        $form['employee_notes'] = array(
        '#type' => 'textarea',
        '#title' => t('Employee Notes'),
        '#required' => false,
        '#default_value' => $employee_notes,
        '#maxlength' => 255,
        );
    
        $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save & Return'),
        );
    
        return $form;
      
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
    
    
        // Load Callsheet
        $callsheet = CallSheet::load($form_state->getValue('callsheet_id'));
        $employee = Employee::load($form_state->getValue('employee_id'));
    
        // Find Callsheet Employee ID
        $callsheet_employee_data = CallSheetController::queryCallSheetEmployeeData($form_state->getValue('callsheet_id'), $form_state->getValue('employee_id'));
        $callsheet_employee = CallSheetEmployee::load($callsheet_employee_data->id);
    
        if ($callsheet_employee) {
            //$callsheet_employee->status->setValue($form_state->getValue('status'));
            $callsheet_employee->status->value = $form_state->getValue('employee_status');
            $callsheet_employee->notes->value = $form_state->getValue('employee_notes');
            $callsheet_employee->changed->value = time();

    
            $callsheet_employee->save();
        } else {
            \Drupal::messenger()->addMessage($callsheet_employee_data->id);
        }
    
        // set week values
        $monday = $callsheet->date->value;
        $tuesday = strtotime("+1 day", $monday);
        $wednesday = strtotime("+1 day", $tuesday);
        $thursday = strtotime("+1 day", $wednesday);
        $friday = strtotime("+1 day", $thursday);
        $saturday = strtotime("+1 day", $friday);
        $sunday = strtotime("+1 day", $saturday);
    
        // Get Callsheet week array
        $weekday = array(
        'mon' => $monday,
        'tue' => $tuesday,
        'wed' => $wednesday,
        'thu' => $thursday,
        'fri' => $friday,
        'sat' => $saturday,
        'sun' => $sunday,
        );

        $condition['employee_id'] = $form_state->getValue('employee_id');
        foreach ($weekday as $key => $date) {
      
            $shift = $form_state->getValue('shift_'. $key);
            $status = $form_state->getValue('status_'. $key);
            $notes = $form_state->getValue('notes_'. $key);
      
            $condition['date'] = $date;
            $order_employee_data = OrderController::queryOrderEmployeeData($condition);
            if ($order_employee_data) {
                $order_employee = OrderEmployee::load($order_employee_data->id);
            } else {
                $order_employee = false;
            }
      
            if ($order_employee) {
                // Update existing OrderEmployee
                $order_employee->status->value = $status;
                $order_employee->date->value = $date;
                $order_employee->shift->value = $shift;
                $order_employee->notes->value = $notes;
                $order_employee->office_id->target_id = $employee->office_id->target_id;
                $order_employee->changed->value = time();
            } else {
                // Set Blank OrderEmployee
                $order_employee = OrderEmployee::create(
                    [
                    'uuid' => \Drupal::service('uuid')->generate(),
                    'employee_id' => $form_state->getValue('employee_id'),
                    'order_id' => 0,
                    'callsheet_id' => $callsheet->id(),
                    'office_id' => $employee->office_id->target_id,
                    'date' => $date,
                    'shift' => $shift,
                    'status' => $status,
                    'notes' => $notes,
                    'driver' => 0,
                    'created' => time(),
                    'changed' => time(),
                    ]
                );
            }
      
            $order_employee->save();
      
        }
      
        //$form_state->setRedirect('triune.callsheet.list');
        $form_state->setRedirect('triune.callsheet.view', ['callsheet_id' => $form_state->getValue('callsheet_id')]);
        return;

    }
}
?>