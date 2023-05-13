<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\NoticeController.
 */
namespace Drupal\triune\Controller;

use \Datetime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\triune\Entity\Notice;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\Office;
use Drupal\triune\Entity\Customer;
use Drupal\triune\Entity\Order;
use Drupal\triune\Controller\OrderController;

/**
 * Controller routines for triune_notice page routes.
 */
class NoticeController implements ContainerInjectionInterface
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database;


    /**
     * Constructs a \Drupal\triune\Controller\NoticeController object.
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
        $this->location_list = $query->execute()->fetchAll();

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
    
    
    public function addNotice($type)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\NoticeForm', $type);
        
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'notice_add',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function editNotice($type, $notice_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\NoticeForm', $type, $notice_id);
                
        /** 
         * Send data to twig
         */
        return array(
            '#theme' => 'notice_add',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function viewNotice($id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        /** 
         * Send data to twig
         */
        return array(
            '#theme' => 'notice',
            '#markup' => time(),
            '#cache' => ['max-age' => 0],
        );
        
    }
    
    
    
    
    public function deleteNotice($notice_id)
    {
        
        $notice = Notice::load($notice_id);
        
        if ($notice) {
            $type = $notice->type->value;
            $notice->delete();
        }
        
        \Drupal::messenger()->addMessage('Removed Notice');
        
        $path = Url::fromRoute('triune.notice.list', ['type' => $type])->toString();
        return new RedirectResponse($path);
        
    }
    
    
    
    
    public static function listNotices($type = 'all')
    {
      
        $query = \Drupal::database()->select('triune_notice', 'r');
        $query->fields('r', array('id', 'label'));
        $query->condition('r.type', $type);
        $data = $query->execute()->fetchAll();
      
        $type_array = array(
        'dnr' => 'DNR',
        'cr' => 'CR',
        'ar' => 'AR',
        'other' => 'Other',
        );
        $notice_list = array();
        foreach ($data as $key => $value) {
            $notice = Notice::load($value->id);
        
            if ($notice) {
                $notice_list[$key]['id'] = $notice->id();
                $notice_list[$key]['type'] = $type_array[$notice->type->value];
                $notice_list[$key]['date'] = date('m/d/Y', $notice->changed->value);
                $notice_list[$key]['label'] = $notice->label->value;
                $notice_list[$key]['employee_name'] = '';
                $notice_list[$key]['employee_id'] = '';
                $notice_list[$key]['office'] = '';
                $notice_list[$key]['customer'] = '';
                $employee = Employee::load($notice->employee_id->target_id);
                if ($employee) {
                    $notice_list[$key]['employee_name'] = $employee->last_name->value .', '. $employee->first_name->value;
                    $notice_list[$key]['employee_id'] = $employee->resource_id->value;
                }
                $office = Office::load($notice->office_id->target_id);
                if ($office) {
                    $notice_list[$key]['office'] = $office->label->value;
                }
                $customer = Customer::load($notice->customer_id->target_id);
                if ($customer) {
                    $notice_list[$key]['customer'] = $customer->label->value;
                }
            }
        }
      
        $label_array = array(
        'dnr' => 'Do Not Return Notices',
        'cr' => 'Check Request Notices',
        'ar' => 'Accident Reports',
        'other' => 'Other Notices',
        'all' => 'All Notices',
        );
        $label = $label_array[$type];
        
        /** 
         * Send data to twig
         */
        return array(
        '#theme' => 'notice_list',
        '#label' => $label,
        '#type' => $type,
        '#notice_list' => $notice_list,
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );
      
    }
    
    
    public function viewOrderReport()
    {
      
      
    }
    
    /**
     * {@inheritdoc}
     */
    public function viewDailyReport()
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        // Handle date parameter in request
        if (\Drupal::request()->query->get('date')) {
            $date = \Drupal::request()->query->get('date');
            $time = strtotime($date);
            $start = new DateTime("monday this week ". $date);
            $end = new DateTime("sunday this week ". $date);
        } else {
            $time = time();
            // Get set of dates
            $start = new DateTime("monday this week");
            $end = new DateTime("sunday this week");
        }

        $variables['input_date'] = date('Y-m-d', $time);
        $variables['date'] = $start->format('M d, Y');
        $variables['week_end'] = $end->format('m/d/Y');

        $variables['weekdays'] = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
        $dates = array();
        $d = 0;
        while ($start <= $end) {
            $dates = array_merge($dates, array(clone $start));
            $start->modify('+1 day');
            $d++;
        }
        $variables['dates'] = $dates;
      

        // Handle shift parameter in request
        if (\Drupal::request()->query->get('shift')) {
            $shift = \Drupal::request()->query->get('shift');
        } else {
            $shift = 0;
        }
        $variables['input_shift'] = $shift;
        $variables['shift_labels'] = array('All Shifts', '1st Shift', '2nd Shift', '3rd Shift');

        // Handle office parameter in request
        if (\Drupal::request()->query->get('office')) {
            $location_id = \Drupal::request()->query->get('office');
            $show_none = false;
        } else {
            $location_id = 0;
            $show_none = true;
        }
        $variables['input_office'] = $location_id;
        // Create Office array
        $variables['offices'] = array();
        foreach ($this->location_list as $location) {
            $variables['offices'][$location->location_id] = $location; 
        }
        $variables['offices'][0]->label = 'Select'; 
      

        // Handle customer parameter in request
        if (\Drupal::request()->query->get('customer')) {
            $customer_id = \Drupal::request()->query->get('customer');
            $customer_entity = Customer::load($customer_id);
            $variables['customer_name'] = $customer_entity->label->value;
        } else {
            $customer_id = 0;
        }
        $variables['input_customer'] = $customer_id;
        $customer_condition = array('test_data' => $this->user->hasPermission('view_test_data'));
        $variables['customers'] = CustomerController::getCustomerList($customer_condition);

        $employee_data = array();

        // Loop through days
        $d = 0;
        foreach ($dates as $date) {
        
            $day = $variables['weekdays'][$d];
            $d++;

            //$variables['total'][$day] += 0;
            $variables['total'][$day] = 0;

            // Get Order List for Customer
            $orders = array();
            if (!$show_none) {
                $time = $date->getTimestamp();
                $order_condition['date'] = $time;
                $order_condition['status'] = 'complete';
                if ($customer_id > 0) {
                    $order_condition['customer_id'] = $customer_id;
                }
                if ($shift > 0) {
                    $order_condition['shift'] = $shift;
                }
                $orders = OrderController::queryOrders($order_condition);
            }

            // Should only be one order per employee per day
            foreach ($orders as $order) {
                $entity = Order::load($order->id);
                // Load OrderEmployee
                $condition = array(
                'order_id' => $order->id,
                );
                $office_label = '';
                if ($location_id > 1) {
                    $condition['office_id'] = $variables['offices'][$location_id]->id;
                }
                $order_employee_list = OrderController::queryOrderEmployeeList($condition);
          
                if ($order_employee_list) {
                    foreach ($order_employee_list as $order_employee) {
                        if ($order_employee->present) {
                            $employee = Employee::load($order_employee->employee_id);
                            if ($employee) {
                                $office = Office::load($employee->office_id->target_id);
                                $employee_data[$employee->id()][$entity->shift->value]['id'] = $employee->id();
                                $employee_data[$employee->id()][$entity->shift->value]['resource_id'] = $employee->resource_id->value;
                                $employee_data[$employee->id()][$entity->shift->value]['office'] = $office->label->value;
                                $employee_data[$employee->id()][$entity->shift->value]['name'] = $employee->last_name->value .', '. $employee->first_name->value;
                                $employee_data[$employee->id()][$entity->shift->value]['value'] = $entity->shift->value;
                                $employee_data[$employee->id()][$entity->shift->value][$day] = 'X';
                                if ($order_employee->driver) {
                                    $employee_data[$employee->id()][$entity->shift->value][$day] = 'Y';
                                }
                                $variables['total'][$day] += 1;
                            }
                        }
                    }
                    $employee_data[$employee->id][$day] = $oe_status;
                }
          
            }
        }

        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'report_daily',
          '#variables' => $variables,
          '#employee_data' => $employee_data,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );
        
    }


    public function viewDifferenceReport()
    {
      
        \Drupal::service('page_cache_kill_switch')->trigger();
      
        if (\Drupal::request()->query->get('date')) {
            $today = strtotime(\Drupal::request()->query->get('date'));
            $variables['date'] = date('m/d/Y', $today);
            $variables['input_date'] = date('Y-m-d', $today);
        } else {
            $variables['date'] = date('m/d/Y', time());
            $variables['input_date'] = date('Y-m-d', time());
        }
        $variables['is_admin'] = $this->user->hasRole('triune_admin');

        $today = strtotime($variables['date']);

        $condition['office_id'] = $this->office->id();
        $condition['date'] = $today;
        $today_orders = OrderController::queryOrders($condition);
      
        $customer_condition = array('test_data' => $this->user->hasPermission('view_test_data'));
        $customers = CustomerController::getCustomerList($customer_condition);
        $customer_list = array();
        foreach($customers as $customer) {
            $customer_list = array_merge($customer_list, array($customer->id));
        }

        $orders = array();
        foreach ($today_orders as $key => $order) {
            $entity = Order::load($order->id);
            $entity->customer = Customer::load($entity->customer_id->target_id);

            if (in_array($entity->customer_id->target_id, $customer_list)) {

                $employee_condition = array('order_id' => $order->id);
                $order_employees = OrderController::queryOrderEmployeeList($employee_condition);
                $entity->filled = count($order_employees);
          
          
                $yesterday = strtotime("-1 day", $today);
                $condition = array(
                'date' => $yesterday,
                'customer_id' => $entity->customer_id->target_id,
                'shift' => $entity->shift->value,
                'fetchAll' => false
                );
                $yesterday_order = OrderController::queryOrders($condition);
                $entity->yesterday = new \stdClass();
          
                if ($yesterday_order) {
                    $yesterday_entity = Order::load($yesterday_order->id);

                    $employee_condition = array('order_id' => $yesterday_order->id);
                    $yesterday_employees = OrderController::queryOrderEmployeeList($employee_condition);
                    $entity->yesterday->quantity = $yesterday_entity->quantity->value;
                    $entity->yesterday->filled = count($yesterday_employees);
                } else {
            
                    $entity->yesterday->quantity = 0;
                    $entity->yesterday->filled = 0;
                }
                $orders[$key] = $entity;
            }
        }
      
        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'report_difference',
          '#orders' => $orders,
          '#variables' => $variables,
          '#markup' => time(),
          '#cache' => ['max-age' => 0],
        );
        
    }
    
    private function generateWeeklyData()
    {

        // Handle date parameter in request
        if (\Drupal::request()->query->get('date')) {
            $date = \Drupal::request()->query->get('date');
            $time = strtotime($date);
            $start = new DateTime("monday this week ". $date);
            $end = new DateTime("sunday this week ". $date);
        } else {
            $time = time();
            // Get set of dates
            $start = new DateTime("monday this week");
            $end = new DateTime("sunday this week");
        }

        $return_data['variables']['input_date'] = date('Y-m-d', $time);
        $return_data['variables']['date'] = $start->format('M d, Y');
        $return_data['variables']['week_end'] = $end->format('m/d/Y');

        $dates = array();
        while ($start <= $end) {
            $dates = array_merge($dates, array(clone $start)); 
            $start->modify('+1 day');
        }
        $return_data['variables']['dates'] = $dates;
        $day_array = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');

        // Handle shift parameter in request
        if (\Drupal::request()->query->get('shift')) {
            $shift = \Drupal::request()->query->get('shift');
        } else {
            $shift = 0;
        }
        $return_data['variables']['input_shift'] = $shift;
        $return_data['variables']['shift_labels'] = array('All Shifts', '1st Shift', '2nd Shift', '3rd Shift');


        // Create office conversion array
        $query = $this->database->select('triune_office', 't');
        $query->fields('t', array('id', 'location_id'));
        $data = $query->execute()->fetchAll();
      
        $office_conv = array();
        foreach ($data as $office_data) {
            $office_conv[$office_data->location_id] = $office_data->id;
        }
        // Handle office parameter in request
        if (\Drupal::request()->query->get('office') != null) {
            $office_id = \Drupal::request()->query->get('office');
        } else {
            $office_id = 1;
        }
        if ($office_id > 1) {
            // If specific office:
            $each_office = false;
            $show_none = false;
        } else if ($office_id == 0) {
            // If all offices:
            $each_office = true;
            $show_none = false;
        } else {
            // If 'select office':
            // Set to retrieve data for each office for csv download
            $each_office = true;
            // If csv download, still retrieve data
            if (\Drupal::request()->query->get('csv')) {
                $show_none = false;
            } else {
                $show_none = true;
            }
        }
        $return_data['variables']['input_office'] = $office_id;
        $return_data['variables']['offices'][1] = array(
        'id' => 0,
        'location_id' => 1,
        'label' => 'Select Office'
        );
        foreach ($this->location_list as $location) {
            $location->label = $location->label . ' Office';
            $return_data['variables']['offices'][$location->location_id] = $location;
        }
        $return_data['variables']['offices'][0]->label = 'All Offices';

        // Initialize total array
        if ($each_office) {
            foreach($office_conv as $office_key => $office_val) {
                $return_data['total']['week'][$office_key][$shift]['ordered'] = 0;
                $return_data['total']['week'][$office_key][$shift]['filled'] = 0;
            }
        } else {
            $return_data['total']['week'][$office_id][$shift] = array(
            'ordered' => 0,
            'filled' => 0,
            );
        }
        $d = 0;
        foreach($dates as $date) {
            $day = $day_array[$d];
            $d++;

            if ($each_office) {
                foreach($office_conv as $office_key => $office_val) {
                    $return_data['total'][$day][$office_key][$shift] = array(
                    'ordered' => 0,
                    'filled' => 0,
                    );
                }
            } else {
                $return_data['total'][$day][$office_id][$shift] = array(
                'ordered' => 0,
                'filled' => 0,
                );
            }
        
        }

        $return_data['customers'] = array();
        if (!$show_none) {
            $customer_condition = array('test_data' => $this->user->hasPermission('view_test_data'));
      
            $data = CustomerController::getCustomerList($customer_condition);
            foreach ($data as $value) {
                $customer_list[$value->id] = $value->label;

                /* customers[id][day][office][shift][ordered/filled] */
                $return_data['customers'][$value->id]['label'] = $value->label;

                if ($each_office) {
                    foreach($office_conv as $office_key => $office_val) {
                        $return_data['customers'][$value->id]['week'][$office_key][$shift] = array(
                        'ordered' => 0,
                        'filled' => 0,
                        );
                    }
                } else {
                    $return_data['customers'][$value->id]['week'][$office_id][$shift] = array(
                    'ordered' => 0,
                    'filled' => 0,
                    );
                }

                $d = 0;
                foreach($dates as $date) {
                    $day = $day_array[$d];
                    $d++;

                    if ($each_office) {
                        foreach($office_conv as $office_key => $office_val) {
                            $return_data['customers'][$value->id][$day][$office_key][$shift] = array(
                              'ordered' => 0,
                              'filled' => 0,
                            );
                        }
                    } else {
                        $return_data['customers'][$value->id][$day][$office_id][$shift] = array(
                        'ordered' => 0,
                        'filled' => 0,
                        );
                    }
                }
          
            }
      
            if ($shift > 0) {
                $condition['shift'] = $shift;
            }
        
            foreach ($customer_list as $customer_id => $customer) {
          
                $condition['customer_id'] = $customer_id;
          
                $d = 0;
                foreach ($dates as $date) {
            
                    $day = $day_array[$d];
                    $d++;

                    $time = $date->getTimestamp();
                    $condition['date'] = $time;
                    $orders = OrderController::queryOrders($condition);
            
                    foreach ($orders as $key => $value) {
                        $order = Order::load($value->id);
                        $office = Office::load($order->office_id->target_id);
              
                        // Query Order Employees
                        $employee_condition = array(
                        'order_id' => $order->id(),
                        'status' => 'assigned',
                        'present' => 1,
                        'fetchall' => true,
                        );

                        // Quantity Order requested
                        $return_data['customers'][$customer_id][$day][$office_id][$shift]['ordered'] += $order->quantity->value;
                        $return_data['total'][$day][$office_id][$shift]['ordered'] += $order->quantity->value;

                        $return_data['customers'][$customer_id]['week'][$office_id][$shift]['ordered'] += $order->quantity->value;
                        $return_data['total']['week'][$office_id][$shift]['ordered'] += $order->quantity->value;

                        $employees = OrderController::queryOrderEmployeeData($employee_condition);
                        foreach ($employees as $employee) {
                
                            $employee_entity = Employee::load($employee->employee_id);
                            $oid = $employee_entity->office_id->target_id;
                            $office_entity = Office::load($oid);
                            $offid = $office_entity->location_id->value;

                            // Quantity Order filled
                            $return_data['customers'][$customer_id][$day][0][$shift]['filled'] += 1;
                            $return_data['customers'][$customer_id]['week'][0][$shift]['filled'] += 1;
                            $return_data['total'][$day][0][$shift]['filled'] += 1;
                            $return_data['total']['week'][0][$shift]['filled'] += 1;

                            $return_data['customers'][$customer_id][$day][$offid][$shift]['filled'] += 1;
                            $return_data['total'][$day][$offid][$shift]['filled'] += 1;

                            $return_data['customers'][$customer_id]['week'][$offid][$shift]['filled'] += 1;
                            $return_data['total']['week'][$offid][$shift]['filled'] += 1;
                        }
                    }
            
                }
            }
        }

        return $return_data;

    }


    public function viewWeeklyReport()
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
      
      
        $data = $this->generateWeeklyData();
      
        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'report_weekly',
          '#customers' => $data['customers'],
          '#total' => $data['total'],
          '#variables' => $data['variables'],
          '#markup' => time(),
          '#cache' => ['max-age' => 0],
        );
      
    }
    
    public function downloadWeeklyReport()
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
      
        $data = $this->generateWeeklyData(true);
        /* data array: customers[id][day][office][shift][ordered/filled] */
        $day_array = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
        $day_array_long = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Weekly Total');

        $shift = $data['variables']['input_shift'];

        // Convert to CSV
        /*
        $output = "Week End,Monday,,,,,Tuesday,,,,,Wednesday,,,,,Thursday,,,,,Friday,,,,,Saturday,,,,,Sunday,,,,,Weekly Total,,,,,\r\n";
        $output .= $data['variables']['week_end'] .",Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,Triune,25th,60th,Brook,Total,\r\n";
        $output .= $shift ." Shift,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,Order,Filled,Filled,Filled,Filled,\r\n";
        */
        $output = "Week End,";
        foreach ($day_array_long as $day_label) {
            foreach ($this->location_list as $location) {
                if ($location->location_id == 0) {
                    $output .= $day_label .",";
                } else {
                    $output .= ",";
                }
            }
            $output .= ",";
        }
        $output .= "\r\n". $data['variables']['week_end'] .",";
        foreach ($day_array_long as $day_label) {
            foreach ($this->location_list as $location) {
                if ($location->location_id == 0) {
                    $output .= "Triune,";
                } else {
                    $output .= $location->label .",";
                }
            }
            $output .= "Total,";
        }
        $output .= "\r\n". $shift ." Shift,";
        foreach ($day_array_long as $day_label) {
            foreach ($this->location_list as $location) {
                if ($location->location_id == 0) {
                    $output .= "Order,";
                } else {
                    $output .= "Filled,";
                }
            }
            $output .= "Filled,";
        }
        $output .= "\r\n";

        // Customer Rows
        foreach ($data['customers'] as $line) {
            // Customer Name
            $output .= str_replace(',', '', $line['label']). ',';
            // Loop through weekdays
            $d = 0;
            foreach ($data['variables']['dates'] as $date) {
                $day = $day_array[$d];
                $d++;
                //$output .= $line[$day][0][$shift]['ordered'] .','. $line[$day][1811][$shift]['filled'] .','. $line[$day][1810][$shift]['filled'] .','. $line[$day][1812][$shift]['filled'] .','. $line[$day][0][$shift]['filled'] .',';
                foreach ($this->location_list as $location) {
                    if ($location->location_id == 0) {
                        $output .= $line[$day][$location->location_id][$shift]['ordered'] .',';
                    } else {
                        $output .= $line[$day][$location->location_id][$shift]['filled'] .',';
                    }
                }
                $output .= $line[$day][0][$shift]['filled'] .',';
            }
            // Count Week totals
            //$output .= $line['week'][0][$shift]['ordered'] .','. $line['week'][1811][$shift]['filled'] .','. $line['week'][1810][$shift]['filled'] .','. $line['week'][1812][$shift]['filled'] .','. $line['week'][0][$shift]['filled'] .",\r\n";
            foreach ($this->location_list as $location) {
                if ($location->location_id == 0) {
                    $output .= $line['week'][$location->location_id][$shift]['ordered'] .',';
                } else {
                    $output .= $line['week'][$location->location_id][$shift]['filled'] .',';
                }
            }
            $output .= $line['week'][0][$shift]['filled'] .",\r\n";
        }
        $output .= 'Grand Total,';
        // Loop through weekdays
        $d = 0;
        foreach ($data['variables']['dates'] as $date) {
            $day = $day_array[$d];
            $d++;
            //$output .= $data['total'][$day][0][$shift]['ordered'] .','. $data['total'][$day][1811][$shift]['filled'] .','. $data['total'][$day][1810][$shift]['filled'] .','. $data['total'][$day][1812][$shift]['filled'] .','. $data['total'][$day][0][$shift]['filled'] .',';
            foreach ($this->location_list as $location) {
                if ($location->location_id == 0) {
                    $output .= $data['total'][$day][$location->location_id][$shift]['ordered'] .',';
                } else {
                    $output .= $data['total'][$day][$location->location_id][$shift]['filled'] .',';
                }
            }
            $output .= $data['total'][$day][0][$shift]['filled'] .',';
        }
      
        // Weekly Grand Total
        // Count Week totals
        //$output .= $data['total']['week'][0][$shift]['ordered'] .','. $data['total']['week'][1811][$shift]['filled'] .','. $data['total']['week'][1810][$shift]['filled'] .','. $data['total']['week'][1812][$shift]['filled'] .','. $data['total']['week'][0][$shift]['filled'] .",\r\n";
        foreach ($this->location_list as $location) {
            if ($location->location_id == 0) {
                $output .= $data['total']['week'][$location->location_id][$shift]['ordered'] .',';
            } else {
                $output .= $data['total']['week'][$location->location_id][$shift]['filled'] .',';
            }
        }
        $output .= $data['total']['week'][0][$shift]['filled'] .",\r\n";

        // Return CSV file download
        $response = new Response(
            'Content',
            Response::HTTP_OK,
            ['content-type' => 'text/csv', 'content-disposition' => 'attachment; filename="order-report.csv"']
        );
        $response->setContent($output);
        return $response;
      
    }



    /**
     * Query Functions
     */
    public static function queryNoticeData($condition = array())
    {
        $query = \Drupal::database()->select('triune_notice', 'r');
        $query->fields('r', array('id', 'label', 'changed', 'type', 'employee_id', 'office_id', 'customer_id'));
        if (isset($condition['type'])) {
            $query->condition('r.type', $condition['type']);
        }
        if (isset($condition['employee_id'])) {
            $query->condition('r.employee_id', $condition['employee_id']);
        }
        if (isset($condition['office_id'])) {
            $query->condition('r.office_id', $condition['office_id']);
        }
        if (isset($condition['customer_id'])) {
            $query->condition('r.customer_id', $condition['customer_id']);
        }
        if (isset($condition['orderby'])) {
            $query->orderby($condition['orderby'], $condition['dir']);
        }
        return $query->execute()->fetchAll();
    }
}
