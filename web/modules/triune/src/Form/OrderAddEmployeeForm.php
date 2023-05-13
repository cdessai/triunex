<?php
/**
 * @file
 * Contains \Drupal\triune\Form\OrderAddEmployeeForm.
 */
namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\triune\Entity\Order;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\OrderEmployee;
use Drupal\triune\Controller\OrderController;

/**
 * OrderAddEmployee form.
 */
class OrderAddEmployeeForm extends FormBase
{
    
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'order_employee_add_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $order_id = 0, $employee_list = array(), $order_employee_list = array())
    {
    
    
        $form['order_id'] = array(
        '#type' => 'hidden',
        '#title' => t(''),
        '#required' => true,
        '#default_value' => $order_id,
        );
    
        // generate checkbox list
        $options = array();
        foreach ($employee_list as $key => $value) {
            if (!$value['dnr']) {
                $employee = Employee::load($value['id']);
                $options[$employee->id()] = '';
            }
        }
    
        // Set default checkboxes
        $default = array();
        foreach ($order_employee_list as $key => $value) {
            $default[$key] = $value->employee_id;
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
    
        // Load Order
        $order = Order::load($form_state->getValue('order_id'));
    
        // Load Order Employees
        $condition = array(
        'order_id' => $order->id(),
        'status' => 'assigned',
        );
        $employees = OrderController::queryOrderEmployeeList($condition);

        // Reject form if too many employees selected
        $assigned_count = count($employees);
        //$i = 0;
        foreach ($form_state->getValue('employees') as $employee_id => $form_employee) {
            if ($form_employee) {
                $assigned_count++;
            }
        }
    
        if ($assigned_count > $order->quantity->value) {
            $form_state->setErrorByName('employees', t('Too many employees selected ('. $assigned_count .'). Order requires '. $order->quantity->value));
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    
        // Load Order
        $order = Order::load($form_state->getValue('order_id'));
    
        // Get existing order_employees
        $condition['order_id'] = $form_state->getValue('order_id');
        $order_employees = OrderController::queryOrderEmployeeList($condition);
    
        $existing = array();
        foreach ($order_employees as $order_employee) {
            $existing[$order_employee->employee_id] = true;
        } 
    
        // First loop through form employees
        foreach ($form_state->getValue('employees') as $employee_id => $form_employee) {
      
            $employee = Employee::load($employee_id);
      
            if ($form_employee == 0) {
        
                foreach ($order_employees as $order_employee) {
                    if ($order_employee->employee_id == $employee_id) {
                        $entity = OrderEmployee::load($order_employee->id);
                        // Unset order data from OrderEmployee
                        $entity->order_id->target_id = 0;
                        $entity->status->value = 'available';
                        $entity->present->value = null;
                        $entity->changed->value = time();
                        $entity->save();
                    }
                }
        
            } else {
        
                if (!$existing[$employee->id()]) {
                    // load Order Employee Data
                    $condition = array(
                    'employee_id' => $employee->id(),
                    'date' => $order->date->value,
                    'shift' => $order->shift->value
                    );
                    $order_employee_data = OrderController::queryOrderEmployeeData($condition);
                    $entity = OrderEmployee::load($order_employee_data->id);
          
                    if ($entity) {
                        $entity->order_id->target_id = $order->id();
                        $entity->status->value = 'assigned';
                        $entity->changed->value = time();
                        $entity->save();
            
                    } else {
                        // Create OrderEmployee
                        $order_employee = OrderEmployee::create(
                            [
                            'uuid' => \Drupal::service('uuid')->generate(),
                            'employee_id' => $employee->id(),
                            'order_id' => $order->id(),
                            'office_id' => $employee->office_id->target_id,
                            'callsheet_id' => null,
                            'date' => ''.$order->date->value,
                            'shift' => $order->shift->value,
                            'status' => 'assigned',
                            'driver' => 0,
                            'notes' => null,
                            'created' => time(),
                            'changed' => time(),
                            ]
                        );
                        $order_employee->save();

                        if (!$order_employee) {
                            \Drupal::messenger()->addMessage('Could not assign employee');
                        }
            
                    }
                }
            }
     
      
        }
    
    
        $form_state->setRedirect('triune.order.view', ['order_id' => $form_state->getValue('order_id')]);
        return;

    }
}
?>