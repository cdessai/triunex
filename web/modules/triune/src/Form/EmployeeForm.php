<?php
/**
 * @file
 * Contains \Drupal\triune\Form\EmployeeForm.
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
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\Office;
use Drupal\triune\Controller\EmployeeController;
use Drupal\triune\Controller\CustomerController;

/**
 * Employee form.
 */
class EmployeeForm extends FormBase
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
        return 'employee_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $employee_id = 0)
    {
        $form_field_hide = 'textfield';
        $phone = '';
        $first_name = '';
        $last_name = '';
        $office_id = $this->office->location_id->value;
        $resource_id = '';
        $status = 1;
        $shift = 0;
        $driver = 0;
        $phone = '';
        $job = 'GL';
        $date = time();
        if ($employee_id) {
            $form_field_hide = 'hidden';
            $employee = Employee::load($employee_id);
            $office = Office::load($employee->office_id->target_id);
            $resource_id = $employee->resource_id->value;
            $first_name = $employee->first_name->value;
            $last_name = $employee->last_name->value;
            $office_id = $office->location_id->value;
            $status = $employee->status->value;
            $shift = $employee->shift->value;
            $job = $employee->job->value;
            $driver = $employee->driver->value;
            if ($employee->phone->value != '(___) ___-____') {
                $phone = $employee->phone->value;
            }
            $date = $employee->hire_date->value;
        }


        // Get Office List
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
        
        // Get Customer List
        $customer_condition['test_data'] = $this->user->hasPermission('view_test_data');
        $data = CustomerController::getCustomerList($customer_condition);
        $customer_list = array(0 => '---');
        foreach ($data as $value) {
            $customer_list[$value->id] = $value->label;
        }

        
        $form['id'] = array(
          '#type' => 'hidden',
          '#title' => t('ID'),
          '#required' => true,
          '#default_value' => $employee_id,
        );
            
        $form['resource_id'] = array(
          '#type' => $form_field_hide,
          '#title' => $this->t('Resource ID'),
          '#required' => true,
          '#default_value' => $resource_id,
        );
        $form['other_id'] = array(
          '#type' => $form_field_hide,
          '#title' => $this->t('Other ID'),
          '#required' => true,
          '#default_value' => $other_id,
          '#attributes' => [
            'style' => 'margin: 0 3 4;
                        color:red;
                        width: 100px;
                        '

          ],
        );
        
        $form['first_name'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('First Name'),
          '#required' => true,
          '#default_value' => $first_name,
        );

        $form['last_name'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Last Name'),
          '#required' => true,
          '#default_value' => $last_name,
        );

        $form['phone'] = array(
          '#type' => 'tel',
          '#title' => t('Phone Number'),
          //'#description' => t('Note: If Employee has a different phone number in Ascentis system, the import function will overwrite this phone number.'),
          '#required' => false,
          '#attributes' => array(
            'placeholder' => '(___) ___-____',
          ),
          '#default_value' => $phone,
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
            
        $form['shift'] = array(
          '#type' => 'select',
          '#title' => $this->t('Shift'),
          '#options' => [      
            '0' => $this->t('---'),
            '1' => $this->t('1st'),
            '2' => $this->t('2nd'),
            '3' => $this->t('3rd'),
          ],
          '#default_value' => $shift,
        );

        $job_val=[
          '40' => $this->t('General Labor'),
          '58' => $this->t('Applicant'), 
          '57' => $this->t('Triune'),
          '127' => $this->t('Spotter'),
          '128' => $this->t('Welder'),
          '129' => $this->t('Line Lead'),
          '130' => $this->t('Forklift Driver'),
          '131' => $this->t('Quality Control'),
          '132' => $this->t('House Keeping'),
          '133' => $this->t('Janitor'),
          '134' => $this->t('Shipping and Receiving'),
          '135' => $this->t('Machine Operator'),
          '136' => $this->t('Butcher'),
          '137' => $this->t('Heavy Lifter'),
          '138' => $this->t('Light Lifter'),
        ];
          $form['job'] = [
          '#type' => 'select',
          '#title' => $this->t('Job'),
          '#options' => $job_val,
          '#sort_options' => TRUE,
          '#default_value' => $job,
        ];

        $form['driver'] = array(
          '#type' => 'select',
          '#title' => $this->t('Driver'),
          '#options' => [
            '0' => $this->t('No'),
            '1' => $this->t('Yes'),
          ],
          '#default_value' => $driver,
        );

        $form['status'] = array(
          '#type' => 'select',
          '#title' => $this->t('Status'),
          '#options' => [      
            '0' => $this->t('Inactive'),
            '1' => $this->t('Active'),
          ],
          '#default_value' => $status,
        );
        $form['hire_date'] = array(
          '#type' => 'date',
          '#title' => $this->t('Hire Date'),
          '#required' => true,
          '#default_value' => date('Y-m-d', $date),
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
        // Check if resource ID already in use
        $query = $this->database->select('triune_employee', 't');
        $query->fields('t', array('id', 'resource_id'));
        $query->condition('t.id', $form_state->getValue('id'), '!=');
        $query->condition('t.resource_id', $form_state->getValue('resource_id'));
        $employee = $query->execute()->fetch();
        if ($employee) {
            $form_state->setErrorByName('employees', t('Employee '. $employee->resource_id .' already exists'));
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
        $employee = Employee::load($form_state->getValue('id'));

        // Load office by location id
        $query = $this->database->select('triune_office', 't');
        $query->fields('t', array('id', 'location_id'));
        $query->condition('t.location_id', $form_state->getValue('office_id'));
        $office = $query->execute()->fetch();
        
        if (!$employee) {
            $employee = Employee::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('first_name') .' '. $form_state->getValue('last_name') .' ('. $form_state->getValue('resource_id') .')',
                'resource_id' => $form_state->getValue('resource_id'),
                'first_name' => $form_state->getValue('first_name'),
                'last_name' => $form_state->getValue('last_name'),
                'phone' => $form_state->getValue('phone'),
                'office_id' => $office->id,
                'customer_id' => $form_state->getValue('customer_id'),
                'shift' => $form_state->getValue('shift'),
                'job' => $form_state->getValue('job'),
                'driver' => $form_state->getValue('driver'),
                'hire_date' => strtotime($form_state->getValue('hire_date')),
                'status' => $form_state->getValue('status'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
            $employee->label->setValue($form_state->getValue('first_name') .' '. $form_state->getValue('last_name') .' ('. $form_state->getValue('resource_id') .')');
            $employee->resource_id->setValue($form_state->getValue('resource_id'));
            $employee->first_name->setValue($form_state->getValue('first_name'));
            $employee->last_name->setValue($form_state->getValue('last_name'));
            $employee->phone->setValue($form_state->getValue('phone'));
            $employee->status->setValue($form_state->getValue('status'));
            $employee->office_id->setValue($office->id);
            $employee->customer_id->setValue($form_state->getValue('customer_id'));
            $employee->shift->setValue($form_state->getValue('shift'));
            $employee->job->setValue($form_state->getValue('job'));
            $employee->driver->setValue($form_state->getValue('driver'));
            $employee->hire_date->setValue(strtotime($form_state->getValue('hire_date')));
            $employee->status->setValue($form_state->getValue('status'));
            $employee->changed->setValue(time());
        }
        $employee->save();

        if ($employee->isNew()) {
            \Drupal::messenger()->addMessage($this->t('Created Employee @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved Employee @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        }

        $form_state->setRedirect('triune.employee.list');
        return;
    }
}
?>