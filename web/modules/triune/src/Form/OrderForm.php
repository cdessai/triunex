<?php
/**
 * @file
 * Contains \Drupal\triune\Form\OrderForm.
 */
namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Connection;
use Drupal\triune\Entity\Order;
use Drupal\triune\Entity\Office;
use Drupal\triune\Entity\Customer;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\OrderEmployee;
use Drupal\triune\Controller\OrderController;
use Drupal\triune\Controller\CustomerController;
use Drupal\triune\Controller\EmployeeController;

/**
 * Order form.
 */
class OrderForm extends FormBase
{
    
    
    /**
     * Class constructor
     */
    public function __construct(Connection $database, AccountInterface $account)
    {
        
        $this->database = $database;
        $this->user = User::load($account->id());
        $query = $this->database->select('triune_office', 't');
        $query->fields('t', array('id', 'location_id'));
        $query->condition('t.location_id', $this->user->field_office->value);
        $data = $query->execute()->fetch();
        $this->office = Office::load($data->id);
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        
        // Instantiates this form class.
        // Load the service required to construct this class
        return new static(
            $container->get('database'),
            $container->get('current_user')
        );
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'order_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $order_id = 0, $employee_array = array(), $copy = false)
    {
        
        if ($order_id == 0) {
            // New Order
            $customer = '';
            $date = time();
            $shift = 1;
            $start = 9;
            $end = 17;
            $depart = 8;
            $status = 'open';
            $office_id = $this->office->location_id->value;
            $quantity = 0;
            $position = 'GL';
            $pay_rate = '';
            $label = '';
            $notes = '';
            $driver = 0;
        } else {
            // Edit order
            $order = Order::load($order_id);
            $office = Office::load($order->office_id->target_id);
            $customer = $order->customer_id->target_id;
            $label = $order->label->value;
            $date = $order->date->value;
            $shift = $order->shift->value;
            $start = date('H:i', $order->start->value);
            $end = date('H:i', $order->end->value);
            $depart = date('H:i', $order->depart->value);
            $status = $order->status->value;
            $office_id = $office->location_id->value;
            $quantity = $order->quantity->value;
            $position = $order->position->value;
            $pay_rate = $order->pay_rate->value;
            $notes = $order->notes->value;
            $driver = 0;
        }

        $min = 1;
        if ($employee_array) {
            $min = count($employee_array);
        }

        $customer_condition['test_data'] = $this->user->hasPermission('view_test_data');
        $data = CustomerController::getCustomerList($customer_condition);
        $customer_list = array();
        foreach ($data as $value) {
            $customer_list[$value->id] = $value->label;
        }
        
        $user_office_id = $this->office->location_id->value;
        $office_list = array();
        if ($this->user->hasPermission('access_all_offices')) {
            // Set office list array
            $query = $this->database->select('triune_office', 't');
            $query->fields('t', array('id', 'label', 'location_id'));
            $data = $query->execute()->fetchAll();
            foreach ($data as $office) {
                $office_list[$office->location_id] = $this->t($office->label);
            }
        } else {
            $office_list = array(
            0 => $this->t('- Any -'),
            $user_office_id => $this->t($this->office->label->value .' Office'),
            );
        }

        // Get Driver options from employee list
        $driver_list = array(
          0 => 'None',
        );
        $employee_list = array();
        if ($order_id != 0 && !$copy) {
            $condition = array(
            'order_id' => $order_id,
            );
            $employee_list = OrderController::queryOrderEmployeeList($condition);
            foreach ($employee_list as $key => $employee_data) {
                $employee = Employee::load($employee_data->employee_id);
                if ($employee->driver->value) {
                    $driver_list[$employee->id()] = $employee->first_name->value .' '. $employee->last_name->value .' ('. $employee->resource_id->value .') [Driver]';
                } else {
                    $driver_list[] = $employee->first_name->value .' '. $employee->last_name->value .' ('. $employee->resource_id->value .')';
                }
                // Set Default Driver
                $order_employee = OrderEmployee::load($employee_data->id);
                if ($order_employee->driver->value) {
                    $driver = $order_employee->employee_id->target_id;
                }
            }
        }

        // Handle copy stuff
        $employee_options = array();
        $employee_defaults = array();
        if ($copy) {
            // Set Order_id to 0 to save form as new order
            $order_id = 0;
            $date = time();
            // Add Employee Form field data
            foreach ($employee_array as $key => $value) {
                if (!$value['dnr']) {
                    $employee = Employee::load($value['id']);
                    $employee_options[$employee->id()] = '';
                }
            }
            // Set default checkboxes
            foreach ($employee_list as $key => $value) {
                $employee_defaults[$key] = $value->employee_id;
            }
        }

        $form['order_id'] = array(
          '#type' => 'hidden',
          '#title' => $this->t('Order ID'),
          '#required' => true,
          '#default_value' => $order_id,
        );
        $form['label'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Label'),
          '#required' => true,
          '#default_value' => $label,
        );
        
        $form['office_id'] = [
          '#type' => 'select',
          '#title' => $this->t('Office'),
          '#options' => $office_list,
          '#default_value' => $office_id,
        ];
        $form['customer_id'] = array(
          '#type' => 'select',
          '#title' => $this->t('Company'),
          '#options' => $customer_list,
          '#default_value' => $customer,
        );
        $form['date'] = array(
          '#type' => 'date',
          '#title' => $this->t('Date'),
          '#required' => true,
          '#default_value' => date('Y-m-d', $date),
        );
        $form['shift'] = [
          '#type' => 'select',
          '#title' => $this->t('Shift'),
          '#options' => [      
            '1' => $this->t('1st'),
            '2' => $this->t('2nd'),
            '3' => $this->t('3rd'),
          ],
          '#default_value' => $shift,
        ];
        $form['start'] = array(
          '#type' => 'time',
          '#title' => $this->t('Shift Start'),
          '#default_value' => $start,
        );
        $form['end'] = array(
          '#type' => 'time',
          '#title' => $this->t('Shift End'),
          '#default_value' => $end,
        );
        $form['depart'] = array(
          '#type' => 'time',
          '#title' => $this->t('Depart Time'),
          '#default_value' => $depart,
        );
        $form['quantity'] = array(
          '#type' => 'number',
          '#title' => $this->t('Quantity Resources'),
          '#required' => true,
          '#default_value' => $quantity,
          '#min' => $min,
        );
        $form['position'] = [
          '#type' => 'select',
          '#title' => $this->t('Position'),
          '#options' => [      
            'GL' => $this->t('General Labor'),
          ],
          '#default_value' => $position,
        ];
        $form['pay_rate'] = array(
          '#type' => 'number',
          '#title' => $this->t('Pay Rate'),
          '#default_value' => $pay_rate,
          '#field_prefix' => $this->t('$ '),
          '#min' => 0.00,
          '#placeholder' => '0.00',
          '#step' => 0.01,
        );
        $form['driver'] = [
          '#type' => 'select',
          '#title' => $this->t('Driver'),
          '#description' => t('*Non-driver resources may appear in this list. Driver resources will be marked [Driver]'),
          '#options' => $driver_list,
          '#default_value' => $driver,
        ];
        $form['notes'] = array(
          '#type' => 'textarea',
          '#title' => t('Order Notes'),
          '#default_value' => $notes,
          '#maxlength' => 255,
        );

        // Create Employee Checkbox field
        $form['employees'] = array(
          '#type' => 'checkboxes',
          '#title' => t('Employees'),
          '#required' => false,
          '#options' => $employee_options,
          '#default_value' => $employee_defaults,
        );

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Save & Continue »'),
        );
        return $form;
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Validate employees on copy forms
        if ($form_state->getValue('employees')) {

            $date = strtotime($form_state->getValue('date'));
            $condition = array(
            'date' => $date,
            );

            // First loop through form employees
            foreach ($form_state->getValue('employees') as $employee_id => $form_employee) {
                if ($form_employee) {
            
                    $condition['employee_id'] = $employee_id;
                    $data = OrderController::queryOrderEmployeeData($condition);
            
                    if ($data) {
                        if ($data->status == 'assigned') {
                            $employee = Employee::load($employee_id);
                            $form_state->setErrorByName('employees', t('Employee '. $employee->label->value .' is already assigned to an order on selected date'));
                        } else if ($data->status == 'unavailable') {
                            $employee = Employee::load($employee_id);
                            $form_state->setErrorByName('employees', t('Employee '. $employee->label->value .' is marked unavailable on selected date'));
                        }
                    }
            
                }
            }
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      
        // Load office by location id
        $query = $this->database->select('triune_office', 't');
        $query->fields('t', array('id', 'location_id'));
        $query->condition('t.location_id', $form_state->getValue('office_id'));
        $office = $query->execute()->fetch();
        
        // If id is 0 then insert as a new order.
        if ($form_state->getValue('order_id') == 0) {
            $order = Order::create(
                [
                'user_id' => $this->user->id(),
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'office_id' => $office->id,
                'customer_id' => $form_state->getValue('customer_id'),
                'date' => strtotime($form_state->getValue('date')),
                'shift' => $form_state->getValue('shift'),
                'start' => $form_state->getValue('start'),
                'end' => $form_state->getValue('end'),
                'depart' => $form_state->getValue('depart'),
                'quantity' => $form_state->getValue('quantity'),
                'position' => $form_state->getValue('position'),
                'pay_rate' => $form_state->getValue('pay_rate'),
                'notes' => $form_state->getValue('notes'),
                'status' => 'open',
                'created' => time(),
                'changed' => time(),
                ]
            );
        
        } else {
            $order = Order::load($form_state->getValue('order_id'));
            $order->label->setValue($form_state->getValue('label'));
            $order->office_id->setValue($office->id);
            $order->customer_id->setValue($form_state->getValue('customer_id'));
            $order->date->setValue(strtotime($form_state->getValue('date')));
            $order->shift->setValue($form_state->getValue('shift'));
            $order->start->setValue($form_state->getValue('start'));
            $order->end->setValue($form_state->getValue('end'));
            $order->depart->setValue($form_state->getValue('depart'));
            $order->quantity->setValue($form_state->getValue('quantity'));
            $order->position->setValue($form_state->getValue('position'));
            $order->pay_rate->setValue($form_state->getValue('pay_rate'));
            $order->notes->setValue($form_state->getValue('notes'));
            $order->changed->setValue(time());
        }
        $order->save();
      
        if ($form_state->getValue('employees')) {

            // Initial condition set as Order Date & Shift
            $condition = array(
            'date' => $order->date->value,
            'shift' => $order->shift->value
            );

            // First loop through form employees
            foreach ($form_state->getValue('employees') as $employee_id => $form_employee) {
          
                if ($form_employee != 0) {

                    // load Order Employee Data
                    $employee = Employee::load($employee_id);
                    $condition['employee_id'] = $employee_id;
                    $order_employee_data = OrderController::queryOrderEmployeeData($condition);
            
                    if ($order_employee_data) {
                        // Edit OrderEmployee
                        $entity = OrderEmployee::load($order_employee_data->id);
                        $entity->order_id->target_id = $order->id();
                        $entity->status->value = 'assigned';
                        $entity->changed->value = time();

                        $entity->save();
              
                    } else {
                        // Create OrderEmployee
                        $entity = OrderEmployee::create(
                            [
                            'uuid' => \Drupal::service('uuid')->generate(),
                            'employee_id' => $employee_id,
                            'order_id' => $order->id(),
                            'office_id' => $employee->office_id->target_id,
                            'date' => ''.$order->date->value,
                            'shift' => $order->shift->value,
                            'status' => 'assigned',
                            'driver' => 0,
                            'notes' => '',
                            'created' => time(),
                            'changed' => time(),
                            ]
                        );
                        $entity->save();
                    }
                }
            }
        }

        if ($form_state->getValue('driver')) {

            // Set Driver Data
            $condition = array(
            'order_id' => $form_state->getValue('order_id'),
            );
            $employee_list = OrderController::queryOrderEmployeeList($condition);
            foreach ($employee_list as $employee_data) {
                $order_employee = OrderEmployee::load($employee_data->id);
                // Set Driver
                if ($order_employee->employee_id->target_id == $form_state->getValue('driver')) {
                    $order_employee->driver->setValue('1');
                } else {
                    $order_employee->driver->setValue('0');
                }
                $order_employee->save();
            }
        }

        $form_state->setRedirect('triune.order.view', ['order_id' => $order->id()]);
        return;
    }
}
?>