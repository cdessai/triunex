<?php
/**
 * @file
 * Contains \Drupal\triune\Form\CallSheetEmployeeAssignForm.
 */
namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\triune\Entity\Order;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\OrderEmployee;
use Drupal\triune\Entity\CallSheet;
use Drupal\triune\Entity\CallSheetEmployee;
use Drupal\triune\Controller\CallSheetController;
use Drupal\triune\Controller\EmployeeController;
use Drupal\triune\Controller\OrderController;

/**
 * CallSheetEmployeeAssign form.
 */
class CallSheetEmployeeAssignForm extends FormBase
{
    
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'callsheet_employee_assign_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $variables = array(), $order_list = array())
    {
    
        $form['callsheet_id'] = array(
        '#type' => 'textfield',
        '#title' => t(''),
        '#required' => true,
        '#default_value' => $variables['callsheet_id'],
        );
    
        $form['employee_id'] = array(
        '#type' => 'textfield',
        '#title' => t(''),
        '#required' => true,
        '#default_value' => $variables['employee_id'],
        );
    
        $form['date'] = array(
        '#type' => 'textfield',
        '#title' => t(''),
        '#required' => true,
        '#default_value' => $variables['date'],
        );
    
        $form['shift'] = array(
        '#type' => 'textfield',
        '#title' => t(''),
        '#required' => true,
        '#default_value' => $variables['shift'],
        );
    
        // generate checkbox list
        $options = array(
        0 => '',
        );
        foreach ($order_list as $key => $value) {
            if (!$value['dnr'] && !$value['full']) {
                $options[$key] = '';
            }
        }
        // Set default checkboxes
        $default = array();
        foreach ($order_list as $key => $value) {
            $order_employee = OrderEmployee::load($value['id']);
            //$default[$key] = 0;// $order_employee->order_id->target_id;
        }

        $form['orders'] = array(
        '#type' => 'radios',
        '#title' => t('Orders'),
        '#required' => false,
        '#options' => $options,
        '#default_value' => 0,
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
    
        // Load CallSheet
        $callsheet = CallSheet::load($form_state->getValue('callsheet_id'));
    
        // Load Employee
        $employee = Employee::load($form_state->getValue('employee_id'));
    
        $callsheet_employee_data = CallSheetController::queryCallSheetEmployeeData($callsheet->id(), $employee->id());
        // Load CallsheetEmployee
        $callsheet_employee = CallSheetEmployee::load($callsheet_employee_data->id);
    
        // Load Order
        $order = Order::load($form_state->getValue('orders'));
    
    

        // Get existing order_employees
        $condition = array(
        'employee_id' => $form_state->getValue('employee_id'),
        'date' => $form_state->getValue('date'),
        //      'shift' => $form_state->getValue('shift'),
        'fetchAll' => false,
        );
        $order_employee = OrderController::queryOrderEmployeeData($condition);
    
        // If order_employee exists set to new order
        if ($order_employee) {
            $entity = OrderEmployee::load($order_employee->id);
            $entity->order_id->setValue($order->id());
            $entity->status->setValue('assigned');
            $entity->office_id->setValue($employee->office_id->target_id);
            $entity->callsheet_id->setValue($callsheet->id());
            $entity->save();
        } else {
            // Create OrderEmployee
            $entity = OrderEmployee::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'employee_id' => $employee->id(),
                'order_id' => $order->id(),
                'office_id' => $employee->office_id->target_id,
                'callsheet_id' => $callsheet->id(),
                'date' => ''.$order->date->value,
                'shift' => $order->shift->value,
                'status' => 'assigned',
                'driver' => 0,
                'notes' => $callsheet_employee->notes->value,
                'created' => time(),
                'changed' => time(),
                ]
            );
            $entity->save();
        }
        // First loop through form orders
        /*foreach ($form_state->getValue('orders') as $key => $form_order) {
        $existing = False;
        $delete = False;
        if ($form_order == 0) {
        $delete = True;
        }
        // Then loop through existing order employees
        foreach ($order_employees as $order_employee) {
        $entity = OrderEmployee::load($order_employee->id);
        // Delete order employees marked for deletion
        if ($delete && $order_employee->order_id == $key) {
          $entity->delete();
        } else if ($form_order == $order_employee->order_id) {
          $existing = True;
        }
        }
      
        // if form_order doesn't exist as order_employee
        if (!$existing && !$delete) {
        // Load Order
        $order = Order::load($form_order);
        
        $callsheet_employee_data = CallSheetController::queryCallSheetEmployeeData($callsheet->id(), $employee->id());
        // Load CallsheetEmployee
        $callsheet_employee = CallSheetEmployee::load($callsheet_employee_data->id);
        // Set CallSheetEmployee as assigned
        $callsheet_employee->status->value = 'assigned';
        
        // Create OrderEmployee
        $entity = OrderEmployee::create([
          'uuid' => \Drupal::service('uuid')->generate(),
          'employee_id' => $employee->id(),
          'order_id' => $order->id(),
          'office_id' => $callsheet->office_id->target_id,
          'date' => ''.$order->date->value,
          'shift' => $order->shift->value,
          'status' => 'assigned',
          'driver' => 0,
          'notes' => $callsheet_employee->notes->value,
          'created' => time(),
          'changed' => time(),
        ]);
        $entity->save();

        }
      
        }*/
    
        \Drupal::messenger()->addMessage('OrderEmployee Created:');
        $form_state->setRedirect('triune.callsheet.employee.edit', ['callsheet_id' => $form_state->getValue('callsheet_id'), 'employee_id' => $form_state->getValue('employee_id')]);
        return;

    }
}
?>