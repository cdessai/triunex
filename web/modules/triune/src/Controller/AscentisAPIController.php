<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\AscentisAPIController.
 */
namespace Drupal\triune\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Controller\EmployeeController;

/**
 * Controller routines for calling Ascentis API
 */
class AscentisAPIController implements ContainerInjectionInterface
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database; 


    /**
     * Constructs a \Drupal\triune\Controller\AscentisAPIController object.
     *
     * @param \Drupal\Core\Database\Connection $database
     *     The database connection.
     */
    public function __construct(Connection $database, 
        EmployeeController $employee_controller
    ) {
        
        $this->database = $database;
        $this->employee_controller = $employee_controller;
        $this->filename = "ascentis-api.lock";
        $this->pid = 0;
        
    }
    
    
    /**
     * Delete all data from database tables if tables exist.
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        
        return new static(
            $container->get('database'),
            $container->get('triune.employeecontroller')
        );
        
    }


    public function importEmployees(int $id, $passive = false, $verbose = false)
    {
      
        $pid = getmypid();
        $sid = posix_getsid($pid);
        $response = array(
        'status' => 'pending',
        'loaded' => 0,
        'total' => 10,
        'message' => '',
        'new_active' => 0,
        'new_inactive' => 0,
        'old_active' => 0,
        'old_inactive' => 0,
        'id' => $id,
        'pid' => $pid,
        'sid' => $sid,
        );

        // Ensure this is only instance of import running
        if (file_exists($this->filename)) {
            // Read lock file
            $lock = fopen($this->filename, 'r');
            $lock_pid = fgets($lock);
            fclose($lock);

            // ID input should match placeholder from request function
            if ($lock_pid != $id) {
                $response['success'] = false;
                $response['message'] = 'another process 
                running: '. $id . ' '. $lock_pid;
            } else {
          
                // Write the import function process id into lock file
                // so other request functions fail while this process is active
                $lock = fopen($this->filename, 'w+');
                fwrite($lock, $pid);
                fclose($lock);

                // Call Ascentis API
                /* result object structure:
                * result
                * - uid
                * - employee (obj)
                * - job (obj)
                */
                $method = 'GET';
                $result = $this->callApi($method, 'rawdata?include=employee,job&active=true');
                $response['total'] = count($result->data);

                $query = $this->database->select('triune_office', 't');
                $query->fields('t', array('id', 'location_id'));
                $data = $query->execute()->fetchAll();
          
                $office_conv = array();
                foreach ($data as $office) {
                    $office_conv[$office->location_id] = $office->id;
                }
                $job_array = array(
                    40 => 'General Labor',
                    57 => 'Triune',
                    58 => 'Applicant',
                    0 => '-',
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
                foreach ($result->data as $key => $value) {

                    $employee = $value->employee;
                    $job = $value->job;

                    if ($verbose) {
                        if ($job->locationID != 0 && $job->locationID != 1810 
                            && $job->locationID != 1811 && $job->locationID != 1812 
                            && $job->locationID != 7051 && $job->locationID != 7052
                        ) {
                            $response['verbose'][$job->locationID] = $value;
                        }
                    }

            
                    $phone = '(___) ___-____';
                    $phone_missing = false;
                    if ($employee->contact->home_phone != '') {
                        $phone = $employee->contact->home_phone;
                    } else if ($employee->contact->work_phone != '') {
                        $phone = $employee->contact->work_phone;
                    } else {
                        $phone_missing = true;
                    }
            
                    // Convert locationID to Office ID
                    $office_id = 0;
                    if ($job->locationID != '') {
                        if (array_key_exists($job->locationID, $office_conv)) {
                            $office_id = $office_conv[$job->locationID];
                        }
                    }
            
                    // Must be set as Active and belong to an Office
                    if ($job->status == 'F' && $office_id != 0 
                        && array_key_exists($job->jobTitleID, $job_array)
                    ) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
            
                    if ($verbose) {
                        // Put data into array
                        $employee_data = array(
                        'resource_id' => $employee->employeeID,
                        'first_name' => $employee->firstName,
                        'last_name' => $employee->lastName,
                        'phone' => $phone,
                        'office_id' => $office_id,
                        'customer_id' => 0,
                        'job' => $job->jobTitleID,
                        'shift' => 0,
                        'status' => $status,
                        'hire_date' => strtotime($job->startDate),
                        );
                    }

                    // Check if Employee exists in TriuneX
                    $exists = EmployeeController::getEmployeeById($employee->employeeID);

                    if ($passive) {
                        // Handle Dump data first
                        if (!$exists) {
                            if ($status) {
                                  $response['new_active']++;
                            } else {
                                $response['new_inactive']++;
                            }
                
                        } else {
                            if ($status) {
                                $response['old_active']++;
                            } else {
                                $response['old_inactive']++;
                            }
                        }
                        $response['loaded']++;

                    } else {
                        // Create new Employee if it doesn't already exist, but don't bother importing inactive resources
                        if (!$exists) {
                            if ($status) {
                                $entity = Employee::create(
                                    [
                                    'uuid' => \Drupal::service('uuid')->generate(),
                                    'label' => $employee->lastName .', '. $employee->firstName .' ('. $employee->employeeID .')',
                                    'resource_id' => $employee->employeeID,
                                    'first_name' => $employee->firstName,
                                    'last_name' => $employee->lastName,
                                    'phone' => $phone,
                                    'office_id' => $office_id,
                                    'customer_id' => 0,
                                    'job' => $job->jobTitleID,
                                    'shift' => 0,
                                    'status' => $status,
                                    'hire_date' => strtotime($job->startDate),
                                    'created' => time(),
                                    'changed' => time(),
                                    ]
                                );
                                  $entity->save();
                            }
                        } else {
                            // Update any outdated info for existing Employees
                            $entity = Employee::load($exists);
                            if ($entity->first_name->value != $employee->firstName) {
                                $entity->first_name->setValue($employee->firstName);
                            }
                            if ($entity->last_name->value != $employee->lastName) {
                                $entity->last_name->setValue($employee->lastName);
                            }
                            if ($entity->phone->value != $phone) {
                                if (!$phone_missing) {
                                    $entity->phone->setValue($phone);
                                }
                            }
                            if ($entity->office_id->target_id != $office_id) {
                                $entity->office_id->setValue($office_id);
                            }
                            if ($entity->job->value != $job->jobTitleID) {
                                $entity->job->setValue($job->jobTitleID);
                            }
                            if ($entity->status->value != $status) {
                                $entity->status->setValue($status);
                            }
                            if ($entity->hire_date->value != strtotime($job->startDate)) {
                                $entity->hire_date->setValue(strtotime($job->startDate));
                            }
                            $entity->save();
                        }
                        $response['loaded']++;
                    }
            
                }
      
                // After import, remove the process id
                $lock = fopen($this->filename, 'w+');
                fwrite($lock, '');
                fclose($lock);

                $response['success'] = true;
                $response['status'] = 'complete';
            }
        } else {
            $response['success'] = false;
            $response['message'] = "lock file does not exist";
        }
      
        if ($response['success']) {
            \Drupal::messenger()->addMessage('Resource import successful!');
        } else {
            \Drupal::messenger()->addMessage('Resource import unsuccessful: '. $response['message']);
        }

        if ($verbose) {
            echo '<pre>';
            var_dump($response);
            echo '</pre>';
        } else {
            print json_encode($response);
        }
        exit();
    }


    /**
     * Single Employee Import
     */
    public function importEmployeeSingle($employee_id, $passive = false, $verbose = false)
    {

        $response = array(
        'status' => 'pending',
        'loaded' => 0,
        'changed' => false,
        'created' => false,
        'message' => '',
        'new_active' => 0,
        'new_inactive' => 0,
        'old_active' => 0,
        'old_inactive' => 0,
        'success' => false,
        );

        // Set office_conv array & job_array
        $query = $this->database->select('triune_office', 't');
        $query->fields('t', array('id', 'location_id'));
        $data = $query->execute()->fetchAll();
      
        $office_conv = array();
        foreach ($data as $office) {
            $office_conv[$office->location_id] = $office->id;
        }
      
        $job_array = array(40 => 'General Labor',58 => 'Applicant', 57 => 'Triune', 0 => '-');

        // Call Ascentis API (Employee)
        /* result object structure:
        * employees[0]
        * - uid
        * - firstname
        * - lastname
        * - employeeID
        */
        try {
            $employee_result = $this->callApi('GET', 'employees?empid='. $employee_id);
        } catch (Exception  $e) {
            \Drupal::messenger()->addMessage('Resource import unsuccessful: Employee record '. $employee_id .' not found in Ascentis system. Error: '. $e->getMessage());
            print json_encode($response);
            exit();
        }

        if (count($employee_result->employees) > 0) {
            $employee = $employee_result->employees[0];
            if ($verbose) {
                $response['employee'] = $employee;
            }
        } else {
            \Drupal::messenger()->addMessage('Resource import unsuccessful: Employee record '. $employee_id .' not found in Ascentis system.');
            print json_encode($response);
            exit();
        }

        // Call Ascentis API (Job)
        /* result object structure:
        * - uid
        * - hireDate
        * - status
        * - locationID
        * - jobTitleID
        * - startDate
        */
        try {
            $job_result = $this->callApi('GET', 'employee/'. $employee->uid .'/job');
        } catch (Exception  $e) {
            \Drupal::messenger()->addMessage('Resource import unsuccessful: Job record for Employee '. $employee_id .' not found in Ascentis system. Error: '. $e->getMessage());
            print json_encode($response);
            exit();
        }
        if ($job_result) {
            $job = $job_result;
            if ($verbose) {
                $response['job'] = $job;
            }
        } else {
            \Drupal::messenger()->addMessage('Resource import unsuccessful: Job record for Employee '. $employee_id .' not found in Ascentis system.');
            return $response;
        }
      
        if ($verbose) {
            if ($job->locationID != 0 && $job->locationID != 1810 && $job->locationID != 1811 && $job->locationID != 1812 && $job->locationID != 7051 && $job->locationID != 7052) {
                $response['verbose'][$job->locationID] = array_merge($employee, $job);
            }
        }
        /*$employees = array(
        $key => array(
          'jobTitleID' => $job->jobTitleID,
          'locationID' => $job->locationID,
        ),
        );*/
        //$employees[$key]->jobTitleID = $job->jobTitleID;
        //$employees[$key]->locationID = $job->locationID;
        $employee->jobTitleID = $job->jobTitleID;
        $employee->locationID = $job->locationID;
        $phone = '(___) ___-____';
        $phone_missing = false;
        if ($employee->contact->home_phone != '') {
            $phone = $employee->contact->home_phone;
        } else if ($employee->contact->work_phone != '') {
            $phone = $employee->contact->work_phone;
        } else {
            $phone_missing = true;
        }
      
        // Convert locationID to Office ID
        $office_id = 0;
        if ($job->locationID != '') {
            if (array_key_exists($job->locationID, $office_conv)) {
                $office_id = $office_conv[$job->locationID];
            }
        }
      
        // Must be set as Active and belong to an Office
        if ($job->status == 'F' && $office_id != 0 && array_key_exists($job->jobTitleID, $job_array)) {
            $status = 1;
        } else {
            $status = 0;
        }
      
        if ($verbose) {
            // Put data into array
            $employee_data = array(
            'resource_id' => $employee->employeeID,
            'first_name' => $employee->firstName,
            'last_name' => $employee->lastName,
            'phone' => $phone,
            'office_id' => $office_id,
            'customer_id' => 0,
            'job' => $job->jobTitleID,
            'shift' => 0,
            'status' => $status,
            'hire_date' => strtotime($job->startDate),
            );
            $response['employee_data'] = $employee_data;
        }

        // Check if Employee exists in TriuneX
        $exists = EmployeeController::getEmployeeById($employee->employeeID);

      
        if ($passive) {
            // Handle Dump data first
            if (!$exists) {
                if ($status) {
                    $response['new_active']++;
                } else {
                    $response['new_inactive']++;
                }
          
            } else {
                if ($status) {
                    $response['old_active']++;
                } else {
                    $response['old_inactive']++;
                }
            }
            $response['loaded']++;
            $response['success'] = true;
            $response['status'] = 'complete';

        } else {
            // Create new Employee if it doesn't already exist, but don't bother importing inactive resources
            if (!$exists) {
                if ($status) {
                    $entity = Employee::create(
                        [
                        'uuid' => \Drupal::service('uuid')->generate(),
                        'label' => $employee->lastName .', '. $employee->firstName .' ('. $employee->employeeID .')',
                        'resource_id' => $employee->employeeID,
                        'first_name' => $employee->firstName,
                        'last_name' => $employee->lastName,
                        'phone' => $phone,
                        'office_id' => $office_id,
                        'customer_id' => 0,
                        'job' => $job->jobTitleID,
                        'shift' => 0,
                        'status' => $status,
                        'hire_date' => strtotime($job->startDate),
                        'created' => time(),
                        'changed' => time(),
                        ]
                    );
                    $entity->save();
                    $response['created'] = true;
                }
            } else {
                // Update any outdated info for existing Employees
                $entity = Employee::load($exists);
                if ($entity->first_name->value != $employee->firstName) {
                    $entity->first_name->setValue($employee->firstName);
                    $response['changed'] = true;
                }
                if ($entity->last_name->value != $employee->lastName) {
                    $entity->last_name->setValue($employee->lastName);
                    $response['changed'] = true;
                }
                if ($entity->phone->value != $phone) {
                    if (!$phone_missing) {
                        $entity->phone->setValue($phone);
                        $response['changed'] = true;
                    }
                }
                if ($entity->office_id->target_id != $office_id) {
                    $entity->office_id->setValue($office_id);
                    $response['changed'] = true;
                }
                if ($entity->job->value != $job->jobTitleID) {
                    $entity->job->setValue($job->jobTitleID);
                    $response['changed'] = true;
                }
                if ($entity->status->value != $status) {
                    $entity->status->setValue($status);
                    $response['changed'] = true;
                }
                if ($entity->hire_date->value != strtotime($job->startDate)) {
                    $entity->hire_date->setValue(strtotime($job->startDate));
                    $response['changed'] = true;
                }
                $entity->save();
            }
            $response['loaded']++;
            $response['status'] = 'complete';
            if ($response['created'] || $response['changed']) {
                $response['success'] = true;
            }
        }

        if ($response['success']) {
            \Drupal::messenger()->addMessage('Resource import successful for '. $employee->firstName .' '. $employee->lastName .' ('. $employee->employeeID .')');
        } else {
            \Drupal::messenger()->addMessage('No changes to import for '. $employee->firstName .' '. $employee->lastName .' ('. $employee->employeeID .')');
        }

        if ($verbose) {
            echo '<pre>';
            var_dump($response);
            echo '</pre>';
        } else {
            print json_encode($response);
        }
        //return $response;
        exit();
    }


    /**
     * Manually initiate update for each employee
     */
    public function manualEmployeeUpdate()
    {

        $condition = array(
        'hire_date' => 'yes',
        'test_data' => true,
        'office_access' => true,
        'office_id' => 0,
        );
        // Get list of Employees
        $employee_list = $this->employee_controller->queryEmployeeList($condition);
        //var_dump($employee_list);exit();
        $response = array();
        foreach ($employee_list as $key => $employee) {
            //try {
            $response[$key] = $this->importEmployeeSingle($employee->resource_id, false, false);
            //} catch (exception $e) {
            //  drupal_set_message('Resource import unsuccessful: Exception '. $e);
            //}
        }

        echo '<pre>';
        var_dump($response);
        echo '</pre>';
        exit();
    }

    /**
     * Manage Lock file to avoid API server overload
     */
    public function requestImport()
    {

        $response = array(
        'success' => true,
        'message' => 'Import request in progress',
        'pid' => 0,
        );
        // Ensure only one instance of import running
        if (file_exists($this->filename)) {
        
            // Read lock file
            $lock = fopen($this->filename, 'r');
            $lock_pid = fgets($lock);
            fclose($lock);

            if ($lock_pid) {
                // Check if lock_pid is active process
                if (posix_getsid($lock_pid)) {
                    $response['success'] = false;
                    $response['message'] = "process running: ". $lock_pid;
                    print json_encode($response);
                    exit();
                } else {
                    // Ignore
                    $response['message'] = "inactive process: ". $lock_pid;
                }
            }

            // Insert this pid as placeholder for import process
            $pid = getmypid();
            $lock = fopen($this->filename, 'w+');
            fwrite($lock, $pid);
            fclose($lock);

            // Return with pid
            $response['pid'] = $pid;
            $response['sid'] = posix_getsid($pid);
            $response['success'] = true;
        
        } else {
            $response['success'] = false;
            $response['message'] = "lock file does not exist";
        }
      
        print json_encode($response);
        exit();

    }


    // Method: POST, PUT, GET etc
    // Data: array("param" => "value") ==> index.php?param=value
    public function callAPI($method, $apiCall)
    {
      
        $requestPath = '/triunelogistics/api/v1.1/'. $apiCall;
        $url = 'https://selfservice.ascentis.com'. $requestPath;
      
        $clientKey = '9mNQcB2IgxosaZEqzwsMQo';
        $secretKey = 'zQ0ZKYxrhDw++Qovmfw4BA';
        // Timestamp in UTC ISO 8601 format -- 2019-12-11T12:04:20Z
        $timeStamp = date("Y-m-d\TH:i:s\Z", time());
      
        $apiSignature = $this->GenerateSignature($method, $requestPath, $secretKey, $timeStamp);
        $headers = array(
        'Authorization: '. urlencode($clientKey) .":". urlencode($apiSignature),
        'Timestamp: '. $timeStamp,
        );
      
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300000);
      
        // Set Headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
        $response = curl_exec($curl);

        curl_close($curl);

        // Expect JSON response
        $response = json_decode($response);
        // Expect XML response
      
        return $response;
    }
    

    /* Generate the API authorization signature
     * GET | PUT | POST
     * full request path with leading "/" -- /selfservicetrunk/api/v1.1/employee/213
     * secret key value
     * timestamp in UTC ISO 8601 format -- 2015-04-29T15:16:55Z
     */
    static public function GenerateSignature($httpMethod, $requestPath, $secretKey, $timeStamp)
    {
      
        // Build request string
        $request = strtoupper($httpMethod) .' '. strtolower($requestPath) .' '. $timeStamp;
        
        // Create authorization signature using sha1 algorithm
        $signature = hash_hmac('sha1', $request, $secretKey, true);
        
        $hexSignature = base64_encode($signature);

        return $hexSignature;
    }

}
