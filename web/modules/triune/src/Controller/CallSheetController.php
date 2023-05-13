<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\CallSheetController.
 */
namespace Drupal\triune\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\views\Views;
use Drupal\views\ViewExecutable;
use Drupal\triune\Entity\Order;
use Drupal\triune\Entity\OrderEmployee;
use Drupal\triune\Entity\CallSheet;
use Drupal\triune\Entity\CallSheetEmployee;
use Drupal\triune\Entity\Office;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\Customer;

/**
 * Controller routines for triune_call_sheet page routes.
 */
class CallSheetController implements ContainerInjectionInterface
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database; 


    /**
     * Constructs a \Drupal\triune\Controller\CallSheetController object.
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
    public function listCallSheets()
    {
        if (!$this->user->isAnonymous()) {
            $query = \Drupal::database()->select('triune_callsheet', 'r');
            $query->fields('r', array('id', 'office_id'));
            //$query->condition('r.office_id', $this->office->id());
            $query->condition('r.user_id', $this->user->id());
            $callsheet_query = $query->execute()->fetchAll();

            foreach($callsheet_query as $key => $callsheet_data) {
                $callsheet = CallSheet::load($callsheet_data->id);
        
                // Format timestamp
                $callsheet->time_created = date('m/d/Y', $callsheet->date->value);
                $callsheet_list[$key] = $callsheet;
            }
      
            /**
             * Send data to twig
             */
            return array(
            '#theme' => 'callsheet_list',
            '#office' => $this->office->label->value,
            '#callsheets' => $callsheet_list,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
            );
        } else {
            return new RedirectResponse(Url::fromRoute('user.login'));
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function addCallSheet()
    {
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CallSheetForm', 0);

        $route['submit'] = '/triune/callsheet/add';
        $route['cancel'] = '/triune/callsheet/list';
        
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'callsheet_edit',
            '#form' => $form,
            '#route' => $route,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function editCallSheet($callsheet_id)
    {
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CallSheetForm', $callsheet_id);
        
        $route['submit'] = '/triune/callsheet/'. $callsheet_id .'/edit';
        $route['cancel'] = '/triune/callsheet/'. $callsheet_id .'/view';
        
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'callsheet_edit',
            '#form' => $form,
            '#route' => $route,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function viewCallSheet($callsheet_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        // Load CallSheet
        $callsheet = CallSheet::load($callsheet_id);
        $callsheet->notes_value = $callsheet->notes->value;
        
        $monday = $callsheet->date->value;
        $tuesday = strtotime("+1 day", $monday);
        $wednesday = strtotime("+1 day", $tuesday);
        $thursday = strtotime("+1 day", $wednesday);
        $friday = strtotime("+1 day", $thursday);
        $saturday = strtotime("+1 day", $friday);
        $sunday = strtotime("+1 day", $saturday);
        
        // Get Callsheet date range
        $variables['date_range'] = array(
          'mon' => $monday,
          'tue' => $tuesday,
          'wed' => $wednesday,
          'thu' => $thursday,
          'fri' => $friday,
          'sat' => $saturday,
          'sun' => $sunday,
        );
        
        $availability = array(
          'pending' => '',
          'available' => '✓',
          'unavailable' => 'x',
          'assigned' => 'A',
          'released' => 'R',
        );
        
        // Get Callsheet Employees already added to callsheet
        $callsheet_employee_list = $this->queryCallSheetEmployeeList($callsheet_id);
        $order_employee_list = array();
        
        $callsheet_employees = array();
        foreach ($callsheet_employee_list as $key => $value) {
          
            $callsheet_employee = CallSheetEmployee::load($value->id);
          
            if ($callsheet_employee) {
                $employee_id = $callsheet_employee->employee_id->target_id;
                $employee = Employee::load($employee_id);
            
                if ($employee) {
                    $callsheet_employees[$key]['id'] = $value->id;
                    $callsheet_employees[$key]['employee_id'] = $employee_id;
                    $callsheet_employees[$key]['resource_id'] = $employee->resource_id->value;
                    $callsheet_employees[$key]['name'] = $employee->last_name->value .', '. $employee->first_name->value;
                    $callsheet_employees[$key]['phone'] = $employee->phone->value;
                    $callsheet_employees[$key]['status'] = $callsheet_employee->status->value;
                    $callsheet_employees[$key]['notes'] = $callsheet_employee->notes->value;
              
                    $employee_condition['employee_id'] = $employee_id;
                    foreach ($variables['date_range'] as $day => $date) {
                        $employee_condition['date'] = $date;
                
                        $order_employee_data = OrderController::queryOrderEmployeeData($employee_condition);
                
                        if ($order_employee_data) {
                            $order_employee = OrderEmployee::load($order_employee_data->id);
                  
                            if ($order_employee) {
                                $variables['order_employee'][$employee->id()][$day] = $availability[$order_employee->status->value];
                            } else {
                                $variables['order_employee'][$employee->id()][$day] = '';
                            }
                        }
                    }
              
                }
            }
        }
        
        /** 
         * Send data to twig
         */
        return array(
            '#theme' => 'callsheet_view',
            '#variables' => $variables,
            '#callsheet' => $callsheet,
            '#callsheet_employees' => $callsheet_employees,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function deleteCallSheet($callsheet_id)
    {
        
        $callsheet = CallSheet::load($callsheet_id);
        
        if ($callsheet) {
            $callsheet->delete();
        }
        
        \Drupal::messenger()->addMessage('Removed Call Sheet');
        
        $path = Url::fromRoute('triune.callsheet.list')->toString();
        return new RedirectResponse($path);
        
    }
    
    
    /*********************************
     * Call Sheet Employee Functions *
     *********************************/
    public function addCallSheetEmployee($callsheet_id)
    {
      
        \Drupal::service('page_cache_kill_switch')->trigger();
      
        // Get conditions to build list of employees
        if (!$this->user->field_office->value) {
            $office_id = 0;
        } else {
            $office_id = $this->office->id();
        }
        $condition = array(
        'office_id' => $office_id,
        'status' => 1,
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'count' => 50,
        'page' => 0,
        );
        $customer_condition['test_data'] = $this->user->hasPermission('view_test_data');
        $variables = array(
        'callsheet_id' => $callsheet_id,
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'customers' => CustomerController::getCustomerList($customer_condition),
        'queries' => \Drupal::request()->getQueryString() .'&',
        'location_list' => $this->location_list,
        );

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
            if (\Drupal::request()->query->get('shift')) {
                $condition['shift'] = \Drupal::request()->query->get('shift');
            }
            if (\Drupal::request()->query->get('customer')) {
                $condition['customer_id'] = \Drupal::request()->query->get('customer');
            }
            if (\Drupal::request()->query->get('job')) {
                $condition['job'] = \Drupal::request()->query->get('job');
            }
            if (\Drupal::request()->query->get('driver')) {
                $condition['driver'] = \Drupal::request()->query->get('driver');
            }
            if (\Drupal::request()->query->get('office')) {
                $condition['location_id'] = \Drupal::request()->query->get('office');
            }
            if (\Drupal::request()->query->get('page')) {
                $condition['page'] = intval(\Drupal::request()->query->get('page'));
          
                // Modify query string
                $variables['queries'] = str_replace('page='.$condition['page'], '', $variables['queries']);
            }
        }
        $condition['test_data'] = $this->user->hasPermission('view_test_data');
        $variables['condition'] = $condition;

        // get Employee list
        $active_employee_list = EmployeeController::queryEmployeeList($condition);
      
        if (count($active_employee_list) < $condition['count']) {
            $variables['lastpage'] = true;
        }

        $employee_list = array();
        foreach ($active_employee_list as $value) {
            $employee = Employee::load($value->id);
            if ($employee) {
                $employee_list[$employee->id()]['id'] = $employee->id();
                $employee_list[$employee->id()]['name'] = $employee->last_name->value . ', '. $employee->first_name->value;
                $employee_list[$employee->id()]['resource_id'] = $employee->resource_id->value;
                $employee_list[$employee->id()]['phone'] = $employee->phone->value;
          
                if ($employee->shift->value != 0) {
                    $employee_list[$employee->id()]['shift'] = $employee->shift->value;
                } else {
                    $employee_list[$employee->id()]['shift'] = '-';
                }
                if ($employee->customer_id->target_id) {
                    $customer = Customer::load($employee->customer_id->target_id);
                    if ($customer) {
                        $employee_list[$employee->id()]['customer'] = $customer->label->value;
                    } else {
                        $employee_list[$employee->id()]['customer'] = '';
                    }
                } else {
                    $employee_list[$employee->id()]['customer'] = '';
                }
          
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
                $employee_list[$employee->id()]['job'] = $job_array[$employee->job->value];
                $employee_list[$employee->id()]['driver'] = $employee->driver->value?'Yes':'No';
                $employee_list[$employee->id()]['office'] = Office::load($employee->office_id->target_id)->label->value;
            }
        }
      
        // Get Employees already added to callsheet
        $callsheet_employee_list = $this->queryCallSheetEmployeeList($callsheet_id);
      
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CallSheetEmployeeAddForm', $callsheet_id, $employee_list, $callsheet_employee_list);
      
        /** 
         * Send data to twig
         */
        return array(
        '#theme' => 'callsheet_employee_add',
        '#form' => $form,
        '#variables' => $variables,
        '#callsheet_id' => $callsheet_id,
        '#employee_list' => $employee_list,
        /*'#view' => $return,*/
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );  
    }
    
    
    public function assignCallSheetEmployee($callsheet_id, $employee_id, $date, $shift)
    {
      
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $employee = Employee::load($employee_id);
      
        // Get Do Not Return notices
        $condition = array(
        'type' => 'dnr',
        'employee_id' => $employee_id,
        );
        $dnr_list = NoticeController::queryNoticeData($condition);
      
        // Get Available Orders
        $order_list = array();
        $order_list[0] = array(
        'id' => 0,
        'label' => 'None',
        'customer' => '',
        'office' => '',
        'dnr' => false,
        'full' => false,
        );
        $condition = array(
        'status' => 'open',
        'date' => $date,
        'office_id' => $employee->office_id->target_id,
        'fetchAll' => true,
        );
        if ($shift) {
            $condition['shift'] = $shift;
        }
      
        $open_order_list = OrderController::queryOrders($condition);
        foreach ($open_order_list as $value) {
            $order = Order::load($value->id);
            if ($order) {
                $order_list[$order->id()] = array(
                'id' => $order->id(),
                'label' => $order->label->value,
                'customer' => Customer::load($order->customer_id->target_id)->label->value,
                'office' => Office::load($order->office_id->target_id)->label->value,
                'shift' => $order->shift->value,
                'position' => $order->position->value,
                'pay_rate' => $order->pay_rate->value,
                'dnr' => false,
                'full' => false,
                );
          
                foreach($dnr_list as $dnr) {
                    if ($dnr->customer_id == $order->customer_id->target_id) {
                        $order_list[$order->id()]['dnr'] = true;
                    }
                }
          
                $condition = array(
                'order_id' => $order->id(),
                );
                $order_employee_list = OrderController::queryOrderEmployeeList($condition);
                if ($order->quantity->value == count($order_employee_list)) {
                    $order_list[$order->id()]['full'] = true;
                }
          
            }
        }
      
        $variables['callsheet_id'] = $callsheet_id;
        $variables['employee_id'] = $employee_id;
        $variables['employee'] = $employee->last_name->value .', '. $employee->first_name->value;
        $variables['date'] = $date;
        $variables['shift'] = $shift;
      
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CallSheetEmployeeAssignForm', $variables, $order_list);
      
      
      
        /** 
         * Send data to twig
         */
        return array(
        '#theme' => 'callsheet_employee_assign',
        '#form' => $form,
        '#variables' => $variables,
        '#order_list' => $order_list,
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );
      
    }
    
    
    public function editCallSheetEmployee($callsheet_id, $employee_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        $callsheet = CallSheet::load($callsheet_id);
        $callsheet->notes_value = $callsheet->notes->value;
        
        $employee = Employee::load($employee_id);

        // Get Employee Notice info - Accident Reports
        $notice_condition = array(
          'employee_id' => $employee_id,
          'type' => 'AR',
          'orderby' => 'changed',
          'dir' => 'asc',
        );
        $accidents = NoticeController::queryNoticeData($notice_condition);
        if ($accidents) {
            foreach ($accidents as $accident) {
                $employee->accident = array(
                'date' => date('m/d/Y', $accident->changed),
                'note' => $accident->label,
                );
            }
        }

        $monday = $callsheet->date->value;
        $tuesday = strtotime("+1 day", $monday);
        $wednesday = strtotime("+1 day", $tuesday);
        $thursday = strtotime("+1 day", $wednesday);
        $friday = strtotime("+1 day", $thursday);
        $saturday = strtotime("+1 day", $friday);
        $sunday = strtotime("+1 day", $saturday);
        
        // Get Callsheet date range
        $variables['date_range'] = array(
          'mon' => $monday,
          'tue' => $tuesday,
          'wed' => $wednesday,
          'thu' => $thursday,
          'fri' => $friday,
          'sat' => $saturday,
          'sun' => $sunday,
        );

        $condition['employee_id'] = $employee->id();
        foreach ($variables['date_range'] as $day => $date) {
            $condition['date'] = $date;
            $order_employee_data = OrderController::queryOrderEmployeeData($condition);
            if ($order_employee_data) {
                $variables['status'][$day] = $order_employee_data->status;
                if ($order_employee_data->status == 'assigned') {
                    $order = Order::load($order_employee_data->order_id);
                    $variables['order'][$day]['id'] = $order_employee_data->order_id;
                    $variables['order'][$day]['shift'] = $order->shift->value;
                    $customer = Customer::load($order->customer_id->target_id);
                    $variables['order'][$day]['name'] = $customer->label->value;
                }
            } else {
                $variables['status'][$day] = 'pending';
            }
        }
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CallSheetEmployeeEditForm', $callsheet_id, $employee_id, $variables['date_range']);
        
        /** 
         * Send data to twig
         */
        return array(
            '#theme' => 'callsheet_employee_edit',
            '#form' => $form,
            '#callsheet' => $callsheet,
            '#employee' => $employee,
            '#variables' => $variables,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function deleteCallSheetEmployee($callsheet_id, $employee_id)
    {
      
        $callsheet_employee = $this->queryCallSheetEmployeeData($callsheet_id, $employee_id);
        $entity = CallSheetEmployee::load($callsheet_employee->id);
        $entity->delete();
      
        \Drupal::messenger()->addMessage('Employee removed from Call Sheet', 'message');
      
        $url = Url::fromRoute('triune.callsheet.view', array('callsheet_id' => $callsheet_id));
        $response = new RedirectResponse($url->toString());
        return $response;
      
    }
    
    
    /**
     * Ajax Responders
     */
    public function ajaxCallSheetEmployeeData($callsheet_id, $employee_id)
    {
        $data = $this->queryCallSheetEmployeeData($callsheet_id, $employee_id);
        print json_encode($data);
        exit();
    }
    
    
    /**
     * Query Functions
     */
    public static function queryCallSheetEmployeeData($callsheet_id, $employee_id)
    {
        $query = \Drupal::database()->select('triune_callsheet_employee', 'r');
        $query->fields('r', array('id', 'callsheet_id', 'employee_id', 'status', 'notes__value'));
        $query->condition('r.callsheet_id', $callsheet_id);
        $query->condition('r.employee_id', $employee_id);
        return $query->execute()->fetchObject();
    }
    
    public static function queryCallSheetEmployeeList($callsheet_id)
    {
        $query = \Drupal::database()->select('triune_callsheet_employee', 'r');
        $query->fields('r', array('id', 'employee_id'));
        $query->condition('r.callsheet_id', $callsheet_id);
        $list = $query->execute()->fetchAll();

        $active_list = array();
        foreach ($list as $key => $data) {
            $employee = Employee::load($data->employee_id);
            if (!$employee->status->value) {
                unset($list[$key]);
            }
        }

        return $list;
    }
}
