<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\EmployeeController.
 */
namespace Drupal\triune\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\OrderEmployee;
use Drupal\triune\Entity\Office;
use Drupal\triune\Entity\Customer;
use Drupal\triune\Controller\AscentisAPIController;

/**
 * Controller routines for triune_employee page routes.
 */
class EmployeeController implements ContainerInjectionInterface
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database; 


    /**
     * Constructs a \Drupal\triune\Controller\EmployeeController object.
     *
     * @param \Drupal\Core\Database\Connection $database
     *     The database connection.
     */
    public function __construct(Connection $database, AccountInterface $account)
    {
        
        $this->database = $database;
        $this->user = User::load($account->id());

        // Set office list array
        $query = $this->database->select('triune_office', 't');
        $query->fields('t', array('id', 'label', 'location_id'));
        $data = $query->execute()->fetchAll();
        $this->location_list = array();
        foreach ($data as $office) {
            $this->location_list[$office->id] = array(
            'location_id' => $office->location_id,
            'label' => $office->label
            );
        }
        // Set user-specific office
        $query->condition('t.location_id', $this->user->field_office->value);
        $data = $query->execute()->fetch();
        $this->office = Office::load($data->id);
        $this->location_id = $data->location_id;
        
    }
    
    
    /**
     * Delete all data from database tables if tables exist.
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        
        return new static(
            $container->get('database'),
            $container->get('current_user')
        );
        
    }    
    
    
    public function addEmployee()
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\EmployeeForm');
        
        /** 
         * Send data to twig...
         */
        return array(
            '#theme' => 'employee',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function editEmployee($employee_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $employee = Employee::load($employee_id);
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\EmployeeForm', $employee_id);
                
        /** 
         * Send data to twig...
         */
        return array(
            '#theme' => 'employee',
            '#form' => $form,
            '#employee' => $employee,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function viewEmployee($id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        /** 
         * Send data to twig...
         */
        return array(
            '#theme' => 'employee',
            '#markup' => time(),
            '#cache' => ['max-age' => 0],
        );
        
    }
    
    
    
    
    public function deleteEmployee($employee_id)
    {
        
        $employee = Employee::load($callsheet_id);
        if ($employee) {
            $employee->delete();
        }
        
        \Drupal::messenger()->addMessage('Removed Employee');
        
        $path = Url::fromRoute('triune.employee.list')->toString();
        return new RedirectResponse($path);
        
    }
    
    
    
    
    public function listEmployees()
    {
      
        // Get conditions to build list of employees
        $condition = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'admin' => $this->user->hasRole('triune_admin'),
        'count' => 50,
        'page' => 0,
        );
        if (!$condition['admin']) {
            $condition['office_id'] = $this->office->id();
            $condition['location_id'] = $this->office->location_id->value;
        }

        $customer_condition = array('test_data' => $this->user->hasPermission('view_test_data'));
        $variables = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'resource_access' => $this->user->hasPermission('edit_employees'),
        'customers' => CustomerController::getCustomerList($customer_condition),
        'queries' => \Drupal::request()->getQueryString() .'&',
        'location_list' => $this->location_list,
        );

        // Handle request query filters
        if (\Drupal::request()->query->get('op') != 'Reset') {
            if (\Drupal::request()->query->get('employee-id')) {
                $condition['resource_id'] = \Drupal::request()->query->get('employee-id');
            }
            if (\Drupal::request()->query->get('first-name')) {
                $condition['first_name'] = \Drupal::request()->query->get('first-name');
            }
            if (\Drupal::request()->query->get('last-name')) {
                $condition['last_name'] = \Drupal::request()->query->get('last-name');
            }
            if (\Drupal::request()->query->get('hire-date')) {
                $condition['hire_date'] = strtotime(\Drupal::request()->query->get('hire-date'));
                $variables['input_hire_date'] = \Drupal::request()->query->get('hire-date');
            }
            if (\Drupal::request()->query->get('hired-after')) {
                $condition['hired_after'] = strtotime(\Drupal::request()->query->get('hired-after'));
                $variables['input_hired_after'] = \Drupal::request()->query->get('hired-after');
            }
            if (\Drupal::request()->query->get('hired-before')) {
                $condition['hired_before'] = strtotime(\Drupal::request()->query->get('hired-before'));
                $variables['input_hired_before'] = \Drupal::request()->query->get('hired-before');
            }
            if (\Drupal::request()->query->get('shift') != 0) {
                $condition['shift'] = \Drupal::request()->query->get('shift');
            }
            if (\Drupal::request()->query->get('customer') != 0) {
                $condition['customer_id'] = \Drupal::request()->query->get('customer');
            }
            if (\Drupal::request()->query->get('job')) {
                $condition['job'] = \Drupal::request()->query->get('job');
            }
            if (\Drupal::request()->query->get('driver')) {
                $condition['driver'] = \Drupal::request()->query->get('driver');
            }
            if (\Drupal::request()->query->get('status') != null) {
                $condition['status'] = \Drupal::request()->query->get('status');
            }
            if (\Drupal::request()->query->get('office') != 0) {
                $condition['location_id'] = \Drupal::request()->query->get('office');
            }
            if (\Drupal::request()->query->get('page')) {
                $condition['page'] = intval(\Drupal::request()->query->get('page'));
          
                // Modify query string
                $variables['queries'] = str_replace('page='.$condition['page'], '', $variables['queries']);
            }
        } else {
            $condition['status'] = null;
        }
        $condition['test_data'] = $this->user->hasPermission('view_test_data');
        $variables['condition'] = $condition;

        $employee_list = $this->queryEmployeeList($condition);

        if (count($employee_list) < $condition['count']) {
            $variables['lastpage'] = true;
        }

        foreach($employee_list as $key => $employee_data) {
            $employee = Employee::load($employee_data->id);
            $employee_list[$key]->name = $employee->last_name->value .', '. $employee->first_name->value;
            if ($employee->office_id->target_id) {
                $office = Office::load($employee->office_id->target_id);
                $employee_list[$key]->office = $office->label->value;
            } else {
                $employee_list[$key]->office = '';
            }
            if ($employee->hire_date->value) {
                $employee_list[$key]->hire_date = date('m/d/Y', $employee->hire_date->value);
            }
            if ($employee->customer_id->target_id) {
                $customer = Customer::load($employee->customer_id->target_id);
                if ($customer) {
                    $employee_list[$key]->customer = $customer->label->value;
                } else {
                    $employee_list[$key]->customer = '';
                }
            } else {
                $employee_list[$key]->customer = '';
            }
            $employee_list[$key]->driver = $employee->driver->value?'Yes':'No';
            $employee_list[$key]->status = $employee->status->value?'Active':'Inactive';
            $employee_list[$key]->phone = $employee->phone->value;
            $job_array = array(
            0 => '-',
            40 => 'General Labor',
            57 => 'Triune',
            58 => 'Applicant',
            127 => 'Spotter',
            128 => 'Welder',
            129 => 'Line Lead',
            130 => 'Forklift Driver',
            131 => 'Quality Control',
            132 => 'House Keeping',
            133 => 'Janitor',
            134 => 'Shipping and Receiving',
            135 => 'Machine Operator',
            136 => 'Butcher',
            137 => 'Heavy Lifter',
            138 => 'Light Lifter',
            );
            $employee_list[$key]->job = $job_array[$employee->job->value];
            $employee_list[$key]->office = Office::load($employee->office_id->target_id)->label->value;
        }
        // job values to list in job filter.
        $variables['jobs'] = [
            0 => '-',
            40 => 'General Labor',
            57 => 'Triune',
            58 => 'Applicant',
            127 => 'Spotter',
            128 => 'Welder',
            129 => 'Line Lead',
            130 => 'Forklift Driver',
            131 => 'Quality Control',
            132 => 'House Keeping',
            133 => 'Janitor',
            134 => 'Shipping and Receiving',
            135 => 'Machine Operator',
            136 => 'Butcher',
            137 => 'Heavy Lifter',
            138 => 'Light Lifter',
        ];
        
      
        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'employee_list',
          '#employee_list' => $employee_list,
          '#variables' => $variables,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );    
      
    }
    
    
    public function assignOrderEmployee($employee_id)
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $employee = Order::load($employee_id);
        
        // Get Open Orders
        $order_condition = array(
          'employee_id' => $employee_id,
          'status' => 'open'
        );
        $orders = OrderController::queryOrderEmployeeData($order_condition);
        
        // Get order_employees
        $query = \Drupal::database()->select('triune_order_employee', 'e');
        $query->fields('e', array('id', 'office_id'));
        $query->condition('e.date', $order->date->value);
        $query->condition('e.shift', $order->shift->value);
        $query->condition('e.office_id', $this->office->id());
        $query->condition('e.status', 'available');
        $employee_list = $query->execute()->fetchAll();
        
        
        foreach ($employee_list as $value) {
            $employee = Employee::load($value->id);
            $variables['employee_list'][$employee->id()]['id'] = $employee->id();
            $variables['employee_list'][$employee->id()]['name'] = $employee->last_name->value . ', '. $employee->first_name->value;
            $variables['employee_list'][$employee->id()]['resource_id'] = $employee->resource_id->value;
        }
        
        // Get Employees already added to order
        $order_employee_list = $this->queryOrderEmployeeList($order_id);
        foreach ($order_employee_list as $value) {
            $employee = Employee::load($value->employee_id);
            $variables['order_employee_list'][$employee->id()]['name'] = $employee->last_name->value . ', '. $employee->first_name->value;
            $variables['order_employee_list'][$employee->id()]['resource_id'] = $employee->resource_id->value;
        }
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderAddEmployeeForm', $order_id, $employee_list, $order_employee_list);
        
        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'order_employee_add',
          '#form' => $form,
          '#variables' => $variables,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );  
      
    }
    
    public static function getEmployeeById($resource_id)
    {
      
        $query = \Drupal::database()->select('triune_employee', 'r');
        $query->fields('r', array('id', 'resource_id'));
        $query->condition('r.resource_id', $resource_id);
        $data = $query->execute()->fetch();
        if ($data) {
            return($data->id);
        } else {
            return(false);
        }
      
    }
    
    public function getResourceData($resource_id)
    {
      
        $ascentis = new AscentisAPIController($this->database);
      
        $data = $ascentis->getEmployee($resource_id);
        $content = json_encode($data->data[0]->employee);
      
        /** 
         * Send data to twig...
         */
        return array(
          '#theme' => 'employee',
          '#content' => $content,
          '#employee' => $data->data[0]->employee,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );
    }
    
    /****************************
     * Order Employee Functions *
     ****************************/
    public static function listEmployeeOrders($employee_id)
    {
        $query = \Drupal::database()->select('triune_order_employee', 'r');
        $query->fields('r', array('id', 'order_id', 'employee_id'));
        $query->condition('r.employee_id', $employee_id);
        $order_employee_list = $query->execute()->fetchAll();
      
        return $order_employee_list;
    }
    
    /*******************
     * Query Functions *
     *******************/
    public static function queryEmployeeList($condition = array())
    {
      
        $query = \Drupal::database()->select('triune_employee', 'e');
        $query->fields('e', array('id', 'resource_id', 'first_name', 'last_name', 'status', 'office_id', 'customer_id', 'job', 'shift', 'driver', 'hire_date'));

        if ($condition != null) {
            if (isset($condition['resource_id'])) {
                $query->condition('e.resource_id', $condition['resource_id']);
            }
            if (isset($condition['status'])) {
                $query->condition('e.status', $condition['status']);
            }
            // Filter down to available employees
            if (isset($condition['location_id'])) {
                // Filter down to selected locations
                $office_query = \Drupal::database()->select('triune_office', 'a');
                $office_query->fields('a', array('id', 'location_id'));
                $office_list = $office_query->execute()->fetchAll();
                foreach ($office_list as $office) {
                    if ($office->location_id == $condition['location_id']) {
                        $query->condition('e.office_id', $office->id);
                    }
                }
            } else if (!$condition['office_access']) {
                $or_group = $query->orConditionGroup()
                    ->condition('e.office_id', $condition['office_id'])
                    ->condition('e.office_id', 0);
                $query->condition($or_group);
            }

            if (isset($condition['first_name'])) {
                $query->condition('e.first_name', $condition['first_name']);
            }
            if (isset($condition['last_name'])) {
                $query->condition('e.last_name', $condition['last_name']);
            }
            if (isset($condition['customer_id'])) {
                $query->condition('e.customer_id', $condition['customer_id']);
            }
            if (isset($condition['job'])) {
                $query->condition('e.job', $condition['job']);
            }
            if (isset($condition['shift'])) {
                $query->condition('e.shift', $condition['shift']);
            }
            if (isset($condition['driver'])) {
                $query->condition('e.driver', $condition['driver']);
            }
            if (isset($condition['hire_date'])) {
                //$query->condition('e.hire_date', Null, 'IS NULL');
                $query->condition('e.hire_date', $condition['hire_date'], '=');
            }
            if (isset($condition['hired_before'])) {
                $query->condition('e.hire_date', $condition['hired_before'], '<');
            }
            if (isset($condition['hired_after'])) {
                $query->condition('e.hire_date', $condition['hired_after'], '>');
            }
            if (isset($condition['test_data'])) {
                if (!$condition['test_data']) {
                    $query->condition('e.resource_id', 8897008, '!=');
                    $query->condition('e.resource_id', 8896446, '!=');
                    $query->condition('e.resource_id', 8896445, '!=');
                    $query->condition('e.resource_id', 5, '!=');
                }
            }
            if (isset($condition['page'])) {
                $start = $condition['page'] * $condition['count'];
                $query->range($start, $condition['count']);
            }
        }
        return $query->execute()->fetchAll();
    }
    
    public function fixOrderEmployeeOffice()
    {

        $query = \Drupal::database()->select('triune_order_employee', 'oe');
        $query->fields('oe', array('id', 'order_id', 'employee_id', 'callsheet_id', 'office_id', 'status'));
        $query->condition('oe.office_id', 0);
        $order_employees = $query->execute()->fetchAll();
      
        $i = 0;
        foreach ($order_employees as $order_employee) {
            $employee = Employee::load($order_employee->employee_id);
            $entity = OrderEmployee::load($order_employee->id);
            if ($entity) {
                if ($employee->office_id->target_id) {
                    $entity->office_id->setValue($employee->office_id->target_id);
                    $entity->save();
                    $i++;
                }
            }
        }
        echo($i);exit();
    }
}
