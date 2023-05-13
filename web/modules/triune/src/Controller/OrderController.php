<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\OrderController.
 */
namespace Drupal\triune\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\triune\Entity\Office;
use Drupal\triune\Entity\Order;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Entity\Customer;
use Drupal\triune\Entity\OrderEmployee;

/**
 * Controller routines for triune_order page routes.
 */
class OrderController implements ContainerInjectionInterface
{

    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database;

    /**
     * The account session.
     *
     * @var \Drupal\Core\Session\AccountInterface;
     */
    protected $account;

    /**
     * Constructs a \Drupal\triune\Controller\OrderController object.
     *
     * @param \Drupal\Core\Database\Connection      $database
     *     The database connection.
     *
     * @param \Drupal\Core\Session\AccountInterface $account
     *     The account session.
     */
    public function __construct(Connection $database, AccountInterface $account)
    {

        $this->database = $database;

        // Set office list array
        $query = $this->database->select('triune_office', 'o');
        $query->fields('o', array('id', 'label', 'location_id'));
        $data = $query->execute()->fetchAll();
        $this->location_list = array();
        foreach ($data as $office) {
            $this->location_list[$office->id] = array(
            'location_id' => $office->location_id,
            'label' => $office->label
            );
        }

        // Set office for user
        $this->user = User::load($account->id());
        $query->condition('o.location_id', $this->user->field_office->value);
        $data = $query->execute()->fetch();
        $this->office = Office::load($data->id);

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


    public function listOrders($order_status = 'open')
    {

        $variables = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'place_orders' => $this->user->hasPermission('place_orders'),
        'queries' => \Drupal::request()->getQueryString() .'&',
        );
        $order_list = array();

        $condition = array(
        'order_id' => null,
        'shift' => null,
        'customer_id' => null,
        'date' => null,
        'location_id' => null,
        'page' => null
        );
        if (\Drupal::request()->query->get('op') != 'Reset') {
            if (\Drupal::request()->query->get('order-id')) {
                $condition['order_id'] = \Drupal::request()->query->get('order-id');
            }
            if (\Drupal::request()->query->get('shift')) {
                $condition['shift'] = \Drupal::request()->query->get('shift');
            }
            if (\Drupal::request()->query->get('customer')) {
                $condition['customer_id'] = \Drupal::request()->query->get('customer');
            }
            if (\Drupal::request()->query->get('date')) {
                $condition['date'] = strtotime(\Drupal::request()->query->get('date'));
                $variables['input_date'] = \Drupal::request()->query->get('date');
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
        $variables['condition'] = $condition;

        $customer_condition['test_data'] = $this->user->hasPermission('view_test_data');
        $variables['customers'] = CustomerController::getCustomerList($customer_condition);

        // Open Orders
        $order_condition = array(
        'admin' => $this->user->hasRole('triune_admin'),
        'status' => $order_status,
        'id' => $condition['order_id'],
        'shift' => $condition['shift'],
        'date' => $condition['date'],
        'customer_id' => $condition['customer_id'],
        'location_id' => $condition['location_id'],
        'orderBy' => array('field' => 'date', 'dir' => 'DESC'),
        );
        if (!$order_condition['admin']) {
            $order_condition['office_id'] = $this->office->id();
        }

        // Page control variables
        $order_condition['count'] = 20;
        $page = 0;
        $last = 0;
        $next = 0;
        if (\Drupal::request()->query->get('op') != 'Reset') {
            if (\Drupal::request()->query->get('page') != null) {
                $page = intval(\Drupal::request()->query->get('page'));
            }
        }
        $order_condition['page'] = $page;
        $variables['page'] = $page;
        $variables['last'] = $page - 1;
        $variables['next'] = $page + 1;
        $variables['max'] = false;

        $order_query = $this->queryOrders($order_condition);

        if (count($order_query) < $order_condition['count']) {
            $variables['max'] = true;
        }

        foreach ($order_query as $key => $order_data) {
            $order = Order::load($order_data->id);
            $order->time_created = date('m/d/Y', $order->date->value);
            $order_list[$order_status][$key] = $order;

            // Load Order Employees Count
            $employee_condition = array('order_id' => $order_data->id);


            // Handle order fill variable differently for active & complete orders
            if ($order_status != 'open') {
                $employee_condition['present'] = 1;
            }
            $order_employees = $this->queryOrderEmployeeList($employee_condition);

            $order_list[$order_status][$key]->fill = sizeof($order_employees);
            $order_list[$order_status][$key]->user = User::load($order->user_id->target_id);
            $order_list[$order_status][$key]->customer = Customer::load($order->customer_id->target_id);
            $order_list[$order_status][$key]->start_time = date('h:i A', $order->start->value);
            $order_list[$order_status][$key]->end_time = date('h:i A', $order->end->value);
            $order_list[$order_status][$key]->depart_time = date('h:i A', $order->depart->value);
        }


        /**
         * Send data to twig
         */
        return array(
          '#theme' => 'order_list_'. $order_status,
          '#order_list' => $order_list,
          '#variables' => $variables,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );

    }

    public function addOrder()
    {

        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderForm');

        /**
         * Send data to twig
         */
        return array(
          '#theme' => 'order_add',
          '#form' => $form,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );

    }


    public function editOrder($order_id)
    {

        \Drupal::service('page_cache_kill_switch')->trigger();

        $order = Order::load($order_id);

        $condition['order_id'] = $order_id;
        $employee_list = $this->queryOrderEmployeeList($condition);

        $customer = Customer::load($order->customer_id->target_id);
        $order->customer = $customer->label->value;
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderForm', $order_id, $employee_list);

        /**
         * Send data to twig
         */
        return array(
            '#theme' => 'order_edit',
            '#form' => $form,
            '#order' => $order,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );

    }

    public function viewOrder($order_id)
    {

        \Drupal::service('page_cache_kill_switch')->trigger();

        $order = Order::load($order_id);
        $order->customer = Customer::load($order->customer_id->target_id);
        $emails = json_decode($order->customer->email->value);
        $order->email = $emails[$order->shift->value - 1];
        $order->s1_email = $emails[$order->shift->value - 1];
        $order->s2_email = $emails[$order->shift->value - 1];
        $order->s3_email = $emails[$order->shift->value - 1];
        $order->driver = 'None';
        $order->start_time = date('h:i A', $order->start->value);
        $order->end_time = date('h:i A', $order->end->value);
        $order->depart_time = date('h:i A', $order->depart->value);
        $order->notes_value = $order->notes->value;

        $condition['order_id'] = $order_id;
        $employee_list = $this->queryOrderEmployeeList($condition);
        $variables['path_info'] = \Drupal::request()->getPathInfo();
        //$variables['path_info'] = Url::toUriString();
        $variables['employee_list'] = array();
        if ($employee_list) {
            foreach ($employee_list as $key => $value) {
                $employee = Employee::load($value->employee_id);
                if ($employee) {

                    $variables['employee_list'][$key]['id'] = $employee->id();
                    $variables['employee_list'][$key]['name'] = ucfirst($employee->last_name->value) .', '. ucfirst($employee->first_name->value);
                    $variables['employee_list'][$key]['resource_id'] = $employee->resource_id->value;

                    $job_array = array(
                        0 => '-',
                        40 => 'GEN',
                        57 => 'STF',
                        58 => 'APP',
                        127 => 'SPT',
                        128 => 'WLD',
                        129 => 'LNL',
                        130 => 'FLD',
                        131 => 'QYC',
                        132 => 'HSK',
                        133 => 'JAN',
                        134 => 'SNR',
                        135 => 'MCO',
                        136 => 'BTR',
                        137 => 'HVL',
                        138 => 'LTL',
                    );
                    $variables['employee_list'][$key]['job'] = $job_array[$employee->job->value];

                    $order_employee = OrderEmployee::load($value->id);
                    $variables['employee_list'][$key]['present'] = $order_employee->present->value;
                    // Set Driver
                    if ($order_employee->driver->value) {
                        $variables['employee_list'][$key]['job'] .= ' (Driver)';
                        $order->driver = $employee->last_name->value .', '. $employee->first_name->value .' ('. $employee->resource_id->value .')';
                    }

                    $customer = false;
                    if ($employee->customer_id->target_id) {
                        $customer = Customer::load($employee->customer_id->target_id);
                    }
                    if ($customer) {
                        $variables['employee_list'][$key]['customer'] = $customer->label->value;
                    } else {
                        $variables['employee_list'][$key]['customer'] = '';
                    }

                    $variables['employee_list'][$key]['phone'] = $employee->phone->value;

                    $office = Office::load($employee->office_id->target_id);
                    if ($office) {
                        $variables['employee_list'][$key]['office'] = $office->label->value;
                    } else {
                        $variables['employee_list'][$key]['office'] = '';
                    }

                }
            }
            $keys = array_column($variables['employee_list'], 'name');
            array_multisort($keys, SORT_ASC, $variables['employee_list']);

        }

        /**
         * Send data to twig
         */
        return array(
          '#theme' => 'order_view_'. $order->status->value,
          '#order' => $order,
          '#variables' => $variables,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );

    }


    /**
     * Duplicate order
     */
    public function copyOrder($order_id)
    {
        \Drupal::service('page_cache_kill_switch')->trigger();

        $order_copy = Order::load($order_id);
        $customer = Customer::load($order_copy->customer_id->target_id);

        // Get Do Not Return notices
        $condition = array(
        'type' => 'dnr',
        'customer_id' => $customer->id(),
        );
        $dnr_list = NoticeController::queryNoticeData($condition);

        // Get list of Employees
        $condition['order_id'] = $order_id;
        $copy_employee_list = $this->queryOrderEmployeeList($condition);
        $employee_list = array();
        foreach ($copy_employee_list as $value) {
            $employee = Employee::load($value->employee_id);
            if ($employee) {
                if ($employee->status->value) {
                    $employee_list[$employee->id()]['id'] = $employee->id();
                    $employee_list[$employee->id()]['name'] = $employee->last_name->value .', '. $employee->first_name->value;
                    $employee_list[$employee->id()]['resource_id'] = $employee->resource_id->value;
                    $employee_list[$employee->id()]['dnr'] = false;

                    foreach($dnr_list as $dnr) {
                        if ($dnr->employee_id == $employee->id()) {
                            $employee_list[$employee->id()]['dnr'] = true;
                        }
                    }
                }
            }
        }

        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderForm', $order_copy->id(), $employee_list, true);

        /**
         * Send data to twig
         */
        return array(
        '#theme' => 'order_copy',
        '#form' => $form,
        '#order' => $order_copy,
        '#employee_list' => $employee_list,
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );
    }


    public function deleteOrder($order_id)
    {

        $order = Order::load($order_id);

        $condition['order_id'] = $order_id;
        $employee_list = $this->queryOrderEmployeeList($condition);

        // Set each order employee back to 'available'
        foreach ($employee_list as $employee) {
            $order_employee = OrderEmployee::load($employee->id);
            if ($order_employee) {
                $order_employee->order_id->setValue(0);
                $order_employee->status->setValue('available');
                $order_employee->save();
            }
        }
        if ($order) {
            $order->delete();
        }

        $url = Url::fromRoute('triune.order.list', array('order_id' => $order_id));
        $response = new RedirectResponse($url->toString());
        return $response;
    }


    /**
     * Change order status
     */
    public function updateOrder($order_id, $status)
    {
        $order = Order::load($order_id);
        $order->status->value = $status;
        $order->save();

        // update employees work history
        if ($status == 'complete') {
            $condition['order_id'] = $order_id;
            $order_employees = $this->queryOrderEmployeeList($condition);
            foreach ($order_employees as $value) {
                if ($value->present) {
                    $employee = Employee::load($value->employee_id);
                    if ($employee) {
                        $employee->customer_id->setValue($order->customer_id->target_id);
                        $employee->save();
                    }
                }
            }
        }
        \Drupal::messenger()->addMessage('Order status has been set to '. $status);
        $url = Url::fromRoute('triune.order.view', array('order_id' => $order_id));
        $response = new RedirectResponse($url->toString());
        return $response;
    }


    /****************************
     * Order Employee Functions *
     ****************************/
    public function addOrderEmployeeListCalled($order_id)
    {

        \Drupal::service('page_cache_kill_switch')->trigger();

        $order = Order::load($order_id);
        $customer = Customer::load($order->customer_id->target_id);
        $order->customer = $customer->label->value;

        $customer_condition = array('test_data' => $this->user->hasPermission('view_test_data'));
        $variables = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'customers' => CustomerController::getCustomerList($customer_condition),
        'queries' => \Drupal::request()->getQueryString() .'&',
        'location_list' => $this->location_list,
        'jobs' => array(
            0 => '-',
            40 => 'GEN',
            57 => 'STF',
            58 => 'APP',
            127 => 'SPT',
            128 => 'WLD',
            129 => 'LNL',
            130 => 'FLD',
            131 => 'QYC',
            132 => 'HSK',
            133 => 'JAN',
            134 => 'SNR',
            135 => 'MCO',
            136 => 'BTR',
            137 => 'HVL',
            138 => 'LTL',
        ),
        );

        // Get Do Not Return notices
        $notice_condition = array(
        'type' => 'dnr',
        'customer_id' => $customer->id(),
        );
        $dnr_list = NoticeController::queryNoticeData($notice_condition);

        /**
         * Set OrderEmployee-specific conditions
         */
        $condition = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'test_data' => $this->user->hasPermission('view_test_data'),
        'admin' => $this->user->hasRole('triune_admin'),
        'count' => 50,
        'page' => 0,
        'status' => 'available',
        'shift' => $order->shift->value,
        'date' => $order->date->value,
        'office_id' => $order->office_id->target_id,
        );
        if (!$condition['office_access']) {
            $condition['office_id'] = $this->office->id();
            $condition['location_id'] = $this->office->location_id->value;
        }
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

            if (\Drupal::request()->query->get('customer') != 0) {
                $condition['customer_id'] = \Drupal::request()->query->get('customer');
            }
            if (\Drupal::request()->query->get('job')) {
                $condition['job'] = \Drupal::request()->query->get('job');
            }
            if (\Drupal::request()->query->get('page')) {
                $condition['page'] = intval(\Drupal::request()->query->get('page'));
                // Modify query string
                $variables['queries'] = str_replace('page='.$condition['page'], '', $variables['queries']);
            }
        }

        // Get Available Employees
        $available_employee_list = $this->queryOrderEmployeeList($condition);

        /**
         * Set Employee-specific conditions
         */
        if (\Drupal::request()->query->get('op') != 'Reset') {
            if (\Drupal::request()->query->get('hired-after')) {
                $condition['hired_after'] = strtotime(\Drupal::request()->query->get('hired-after'));
            }
            if (\Drupal::request()->query->get('hired-before')) {
                $condition['hired_before'] = strtotime(\Drupal::request()->query->get('hired-before'));
            }
            if (\Drupal::request()->query->get('driver') != null) {
                if (\Drupal::request()->query->get('driver') == 1) {
                    $condition['driver'] = 1;
                } else {
                    $condition['driver'] = 0;
                }
            }
            if (\Drupal::request()->query->get('location-id') != 0 && !isset($condition['location_id'])) {
                $condition['location_id'] = \Drupal::request()->query->get('location-id');
            }
        }

        $employee_list = array();
        foreach ($available_employee_list as $value) {
            $employee = Employee::load($value->employee_id);
            if ($employee) {
                if ($employee->status->value) {
                    $match = true;
                    if (isset($condition['resource_id'])) {
                        if ($condition['resource_id'] != $employee->resource_id->value) {
                            $match = false;
                        }
                    }
                    if (isset($condition['first_name'])) {
                        if ($condition['first_name'] != $employee->first_name->value) {
                            $match = false;
                        }
                    }
                    if (isset($condition['last_name'])) {
                        if ($condition['last_name'] != $employee->last_name->value) {
                            $match = false;
                        }
                    }
                    if (isset($condition['customer_id'])) {
                        if ($condition['customer_id'] != $employee->customer_id->target_id) {
                            $match = false;
                        }
                    }
                    if (isset($condition['location_id'])) {
                        if ($condition['location_id'] != $this->location_list[$employee->office_id->target_id]['location_id']) {
                            $match = false;
                        }
                    }
                    if (isset($condition['driver'])) {
                        if ($condition['driver'] != $employee->driver->value) {
                            $match = false;
                        }
                    }

                    if ($match) {
                        $employee_list[$employee->id()]['id'] = $employee->id();
                        $employee_list[$employee->id()]['name'] = $employee->last_name->value .', '. $employee->first_name->value;
                        $employee_list[$employee->id()]['hire_date'] = date('m/d/Y', $employee->hire_date->value);
                        $employee_list[$employee->id()]['resource_id'] = $employee->resource_id->value;
                        $employee_list[$employee->id()]['customer'] = Customer::load($employee->customer_id->target_id)->label->value;
                        $employee_list[$employee->id()]['office'] = Office::load($employee->office_id->target_id)->label->value;
                        $employee_list[$employee->id()]['job'] = $variables['jobs'][$employee->job->value];
                        $employee_list[$employee->id()]['driver'] = $employee->driver->value?'Yes':'No';
                        $employee_list[$employee->id()]['dnr'] = false;
                    }

                    foreach($dnr_list as $dnr) {
                        if ($dnr->employee_id == $employee->id()) {
                            $employee_list[$employee->id()]['dnr'] = true;
                        }
                    }
                }
            }
        }

        // Get Employees already added to order
        $prev_condition = array(
        'order_id' => $order_id,
        );
        $order_employee_list = $this->queryOrderEmployeeList($prev_condition);
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderAddEmployeeForm', $order_id, $employee_list, $order_employee_list);

        $variables['condition'] = $condition;
        // Send data to twig
        return array(
        '#theme' => 'order_employee_add_called',
        '#form' => $form,
        '#order' => $order,
        '#employee_list' => $employee_list,
        '#variables' => $variables,
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );
    }

    public function addOrderEmployeeListAll($order_id)
    {

        \Drupal::service('page_cache_kill_switch')->trigger();

        $order = Order::load($order_id);
        $customer = Customer::load($order->customer_id->target_id);
        $order->customer = $customer->label->value;

        $customer_condition = array('test_data' => $this->user->hasPermission('view_test_data'));
        $variables = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'customers' => CustomerController::getCustomerList($customer_condition),
        'queries' => \Drupal::request()->getQueryString() .'&',
        'location_list' => $this->location_list,
        );

        // Get Do Not Return notices
        $notice_condition = array(
        'type' => 'dnr',
        'customer_id' => $customer->id(),
        );
        $dnr_list = NoticeController::queryNoticeData($notice_condition);

        // Get conditions to build list of employees
        $condition = array(
        'office_access' => $this->user->hasPermission('access_all_offices'),
        'admin' => $this->user->hasRole('triune_admin'),
        'count' => 50,
        'page' => 0,
        //'status' => 'active',
        'office_id' => $order->office_id->target_id,
        );
        if (!$condition['office_access']) {
            $condition['office_id'] = $this->office->id();
            $condition['location_id'] = $this->office->location_id->value;
        }

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
            if (\Drupal::request()->query->get('customer') != 0) {
                $condition['customer_id'] = \Drupal::request()->query->get('customer');
            }
            if (\Drupal::request()->query->get('location-id') != 0 && !isset($condition['location_id'])) {
                $condition['location_id'] = \Drupal::request()->query->get('location-id');
            }
            if (\Drupal::request()->query->get('job')) {
                $condition['job'] = \Drupal::request()->query->get('job');
            }
            if (\Drupal::request()->query->get('driver') != null) {
                if (\Drupal::request()->query->get('driver') == 1) {
                    $condition['driver'] = 1;
                } else {
                    $condition['driver'] = 0;
                }
            }
            if (\Drupal::request()->query->get('page')) {
                $condition['page'] = intval(\Drupal::request()->query->get('page'));

                // Modify query string
                $variables['queries'] = str_replace('page='.$condition['page'], '', $variables['queries']);
            }
        }

        $condition['test_data'] = $this->user->hasPermission('view_test_data');

        $variables['jobs'] = array(
            0 => '-',
            40 => 'GEN',
            57 => 'STF',
            58 => 'APP',
            127 => 'SPT',
            128 => 'WLD',
            129 => 'LNL',
            130 => 'FLD',
            131 => 'QYC',
            132 => 'HSK',
            133 => 'JAN',
            134 => 'SNR',
            135 => 'MCO',
            136 => 'BTR',
            137 => 'HVL',
            138 => 'LTL',
        );

        // Get Available Employees
        $employee_list = array();
        $all_employee_list = EmployeeController::queryEmployeeList($condition);

        if (count($all_employee_list) < $condition['count']) {
            $variables['lastpage'] = true;
        }


        foreach ($all_employee_list as $employee) {
            if ($employee) {
                if ($employee->status) {
                    $employee_list[$employee->id]['id'] = $employee->id;
                    $employee_list[$employee->id]['name'] = $employee->last_name .', '. $employee->first_name;
                    $employee_list[$employee->id]['resource_id'] = $employee->resource_id;
                    $employee_list[$employee->id]['hire_date'] = date('m/d/Y', $employee->hire_date);
                    $employee_list[$employee->id]['customer'] = Customer::load($employee->customer_id)->label->value;
                    $employee_list[$employee->id]['office'] = Office::load($employee->office_id)->label->value;
                    $employee_list[$employee->id]['job'] = $variables['jobs'][$employee->job];
                    $employee_list[$employee->id]['driver'] = $employee->driver?'Yes':'No';
                    $employee_list[$employee->id]['dnr'] = false;

                    foreach($dnr_list as $dnr) {
                        if ($dnr->employee_id == $employee->id) {
                            $employee_list[$employee->id]['dnr'] = true;
                        }
                    }
                }
            }
        }

        // Get Employees already added to order
        $prev_condition = array(
        'order_id' => $order_id,
        );
        $order_employee_list = $this->queryOrderEmployeeList($prev_condition);
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderAddEmployeeForm', $order_id, $employee_list, $order_employee_list);

        $variables['condition'] = $condition;
        // Send data to twig
        return array(
        '#theme' => 'order_employee_add_all',
        '#form' => $form,
        '#order' => $order,
        '#employee_list' => $employee_list,
        '#variables' => $variables,
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );
    }


    public function editOrderEmployee($order_id, $employee_id)
    {
        \Drupal::service('page_cache_kill_switch')->trigger();
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\OrderEmployeeForm');
        $employees = array();
        /**
         * Send data to twig
         */
        return array(
        '#theme' => 'order_employee',
        '#form' => $form,
        '#employees' => $employees,
        '#cache' => ['max-age' => 0,],
        '#markup' => time(),
        );
    }


    public static function deleteOrderEmployee($order_id, $employee_id)
    {
        $condition = array(
        'order_id' => $order_id,
        'employee_id' => $employee_id,
        );
        $order_employee = OrderController::queryOrderEmployeeData($condition);
        $entity = OrderEmployee::load($order_employee->id);
        // Unset order data from OrderEmployee
        $entity->order_id->target_id = 0;
        $entity->status->value = 'available';
        $entity->present->value = null;
        $entity->changed->value = time();
        $entity->save();
        $url = Url::fromRoute('triune.order.view', array('order_id' => $order_id));
        $response = new RedirectResponse($url->toString());
        return $response;
    }


    public function updateOrderEmployee($order_id, $employee_id, $field, $value)
    {
        $condition['order_id'] = $order_id;
        $condition['employee_id'] = $employee_id;
        $order_employee = OrderController::queryOrderEmployeeData($condition);
        $entity = OrderEmployee::load($order_employee->id);
        if ($entity) {
            if ($field == 'status') {
                $entity->status->value = $value;
            }
            if ($field == 'present') {
                $entity->present->setValue($value);
            }
            $entity->save();
        }
        $url = Url::fromRoute('triune.order.view', array('order_id' => $order_id));
        $response = new RedirectResponse($url->toString());
        return $response;
    }


    /**
     * Email Function
     */
    public function emailOrderReport($order_id)
    {

        // Load order
        $order = Order::load($order_id);

        // Load customer
        $customer = Customer::load($order->customer_id->target_id);

        // Load order employees to be sent
        $condition['order_id'] = $order_id;
        $order_employees = $this->queryOrderEmployeeList($condition);

        // Prepare content for email
        $content = "Customer: ". $customer->label->value ."\n";
        $content .= "Date: ". date('m/d/Y', $order->date->value) ."\n";
        $content .= "Start Time: ". date('h:i A', $order->start->value) ."\n";
        $content .= "End Time: ". date('h:i A', $order->end->value) ."\n\n";
        $content .= "Resource List:\n";
        foreach ($order_employees as $value) {
            if ($value->present) {
                $employee = Employee::load($value->employee_id);
                if ($employee) {
                    $employee->customer_id->setValue($order->customer_id->target_id);
                    $content .= $employee->last_name->value .', '. $employee->first_name->value ."\n";
                }
            }
        }

        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'triune';
        $key = 'triune_order_report';

        $emails = json_decode($customer->email->value);
        $to = $emails[$order->shift->value - 1];

        $params['from'] = $this->user->getEmail();

        $params['title'] = date('m/d/Y', $order->date->value);
        $params['message'] = $content;

        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;

        $result = $mailManager->mail($module, $key, $to, $langcode, $params, null, $send);
        if ($result['result'] != true) {
            $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
            \Drupal::logger('mail-log')->error($message);
        } else {
            $message = t('An email notification has been sent to @email ', array('@email' => $to));
            \Drupal::logger('mail-log')->notice($message);
        }
        \Drupal::messenger()->addMessage($message);
        return new RedirectResponse(Url::fromRoute('triune.order.view', ['order_id' => $order_id]));
    }


    /**
     * Query Functions
     */
    public static function queryOrders($condition = array())
    {
        $query = \Drupal::database()->select('triune_order', 'o');
        $query->fields('o', array('id', 'date'));

        if (isset($condition['id'])) {
            $query->condition('o.id', $condition['id']);
        }
        if (isset($condition['office_id'])) {

            // Find ID of -all office-
            $all_office = 1;

            $or_group = $query->orConditionGroup()
                ->condition('office_id', $condition['office_id'])
                ->condition('office_id', 1);
            $query->condition($or_group);
        }
        if (isset($condition['date'])) {
            $query->condition('o.date', $condition['date']);
        }
        if (isset($condition['status'])) {
            $query->condition('o.status', $condition['status']);
        }
        if (isset($condition['customer_id'])) {
            $query->condition('o.customer_id', $condition['customer_id']);
        }
        if (isset($condition['shift'])) {
            $query->condition('o.shift', $condition['shift']);
        }
        if (isset($condition['page'])) {
            $query->range($condition['page'] * $condition['count'], ($condition['page'] * $condition['count']) + $condition['count']);
        }
        if (isset($condition['orderBy'])) {
            $query->orderBy($condition['orderBy']['field'], $condition['orderBy']['dir']);
        }
        if (isset($condition['fetchAll'])) {
            if ($condition['fetchAll']) {
                return $query->execute()->fetchAll();
            } else {
                return $query->execute()->fetchObject();
            }
        }
        return $query->execute()->fetchAll();
    }


    public static function queryOrderEmployeeData($condition = array())
    {
        $query = \Drupal::database()->select('triune_order_employee', 'd');
        $query->fields('d', array('id', 'order_id', 'office_id', 'employee_id', 'status', 'date', 'present', 'driver', 'shift', 'notes__value'));
        if (isset($condition['order_id'])) {
            $query->condition('d.order_id', $condition['order_id']);
        }
        if (isset($condition['employee_id'])) {
            $query->condition('d.employee_id', $condition['employee_id']);
        }
        if (isset($condition['status'])) {
            $query->condition('d.status', $condition['status']);
        }
        if (isset($condition['date'])) {
            $query->condition('d.date', $condition['date']);
        }
        if (isset($condition['shift'])) {
            $or_group = $query->orConditionGroup()
                ->condition('d.shift', $condition['shift'])
                ->condition('d.shift', 0);
            $query->condition($or_group);
        }
        if (isset($condition['present'])) {
            $query->condition('d.present', $condition['present']);
        }
        if (isset($condition['driver'])) {
            $query->condition('d.driver', $condition['driver']);
        }
        if (isset($condition['office_id'])) {
            if ($condition['office_id'] != 1) {
                $query->condition('d.office_id', $condition['office_id']);
            }
        }
        if (isset($condition['fetchall'])) {
            return $query->execute()->fetchAll();
        }
        return $query->execute()->fetchObject();
    }

    public static function queryOrderEmployeeList($condition = array())
    {
        $query = \Drupal::database()->select('triune_order_employee', 'r');
        $query->fields('r', array('id', 'employee_id', 'order_id', 'status', 'date', 'shift', 'office_id', 'present', 'driver'));
        if (isset($condition['order_id'])) {
            $query->condition('r.order_id', $condition['order_id']);
        }
        if (isset($condition['status'])) {
            $query->condition('r.status', $condition['status']);
        }
        if (isset($condition['date'])) {
            $query->condition('r.date', $condition['date']);
        }
        if (isset($condition['shift'])) {
            $or_group = $query->orConditionGroup()
                ->condition('r.shift', $condition['shift'])
                ->condition('r.shift', 0);
            $query->condition($or_group);
        }
        if (isset($condition['present'])) {
            $query->condition('r.present', $condition['present']);
        }
        if (isset($condition['driver'])) {
            $query->condition('r.driver', $condition['driver']);
        }
        if (isset($condition['office_id'])) {
            if ($condition['office_id'] != 1) {
                $query->condition('r.office_id', $condition['office_id']);
            }
        }
        return $query->execute()->fetchAll();
    }

    // Bulk Employee checkin/checkout
    public function bulkupdateOrderEmployee($order_id)
    {
        $condition = [
        'order_id' => $order_id,
        ];

        $order_employee = OrderController::queryOrderEmployeeList($condition);

        foreach($order_employee as $value) {
            $entity = OrderEmployee::load($value->id);
            if ($entity->hasField('present')) {
                if($entity->present->value == 0) {
                    $entity->present->value = 1;
                }
                $entity->save();
            }
        }

        $url = Url::fromRoute('triune.order.view', array('order_id' => $order_id));
        $response = new RedirectResponse($url->toString());
        return $response;
    }

}
