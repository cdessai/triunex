<?php
/**
 * @file
 * Contains \Drupal\triune\Form\JobRateForm.
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
use Drupal\triune\Entity\JobRate;


/**
 * JobRate form.
 */
class PayRollForm extends FormBase
{
    
    
    /**
     * Class constructor
     */
    public function __construct(Connection $database, AccountInterface $account)
    {
        
        $this->database = $database;
        $this->user = User::load($account->id());
        
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
        return 'jobrate_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $customer = null;
        $customer_list = [];
        $position = 'GEN';
        $pay_rate = 0;
        $form['customer_id'] = array(
            '#type' => 'select',
            '#title' => $this->t('Company'),
            '#options' => $customer_list,
            '#default_value' => $customer,
        );        

        $form['position'] = [
          '#type' => 'select',
          '#title' => $this->t('Position'),
          '#options' => [      
            'GEN' => $this->t('General Labor'),
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
       
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

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
                'customer_id' => $form_state->getValue('customer_id'),
                'position_name' => $form_state->getValue('position_name'),
                'position_code' => $form_state->getValue('position_code'),
                'pay_rate' => $form_state->getValue('pay_rate'),
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