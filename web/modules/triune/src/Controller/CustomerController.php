<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\CustomerController.
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
use Drupal\triune\Entity\Customer;
use Drupal\triune\Entity\Office;
use Drupal\Core\Field\BaseFieldDefinition;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Controller routines for triune_customer page routes.
 */
class CustomerController implements ContainerInjectionInterface
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database; 


    /**
     * Constructs a \Drupal\triune\Controller\CustomerController object.
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
    
    
    public function addCustomer()
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        

        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CustomerForm', $this->location_list);
        
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'customer',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function editCustomer($customer_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CustomerForm', $this->location_list, $customer_id);
                
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'customer',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function viewCustomer($customer_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'customer',
            '#markup' => time(),
            '#cache' => ['max-age' => 0],
        );
        
    }
    
    
    
    
    public function deleteCustomer($customer_id)
    {
        
        $customer = Employee::load($customer_id);
        
        if ($customer) {
            $customer->delete();
        }
        
        \Drupal::messenger()->addMessage('Removed Customer');
        
        $path = Url::fromRoute('triune.customer.list')->toString();
        return new RedirectResponse($path);
        
    }
    
    
    
    public function listCustomers()
    {
      
        $condition['test_data'] = $this->user->hasPermission('view_test_data');
        $data = CustomerController::getCustomerList($condition);
      
        foreach ($data as $value) {
            $customer = Customer::load($value->id);
            if ($customer) {
                $customer_list[$customer->id()]['id'] = $customer->id();
                $customer_list[$customer->id()]['name'] = $customer->label->value;
                $emails = json_decode($customer->email->value);
                $customer_list[$customer->id()]['email_1'] = $emails[0];
                $customer_list[$customer->id()]['email_2'] = $emails[1];
                $customer_list[$customer->id()]['email_3'] = $emails[2];
                $customer_list[$customer->id()]['email_4'] = $emails[3];
                $customer_list[$customer->id()]['email_5'] = $emails[4];
                $customer_list[$customer->id()]['address'] = $customer->address->value;
                $office = Office::load($customer->office_id->target_id);
                $customer_list[$customer->id()]['office'] = $office->label->value;
                $customer_list[$customer->id()]['s1_name'] = $customer->s1_name->value;
                $customer_list[$customer->id()]['s2_name'] = $customer->s2_name->value;
                $customer_list[$customer->id()]['s3_name'] = $customer->s3_name->value;
                $customer_list[$customer->id()]['tsc_name'] = $customer->tsc_name->value;
                $customer_list[$customer->id()]['p_name'] = $customer->p_name->value;
                $customer_list[$customer->id()]['s1_phone'] = $customer->s1_phone->value;
                $customer_list[$customer->id()]['s2_phone'] = $customer->s2_phone->value;
                $customer_list[$customer->id()]['s3_phone'] = $customer->s3_phone->value;
                $customer_list[$customer->id()]['tsc_phone'] = $customer->tsc_phone->value;
                $customer_list[$customer->id()]['p_phone'] = $customer->p_phone->value;
            }
        }
      
        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'customer_list',
          '#customer_list' => $customer_list,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );      
      
    }
    
    
    public static function getCustomerList($condition = array())
    {
        $query = \Drupal::database()->select('triune_customer', 'r');
        $query->fields('r', array('id', 'label'));
        if (!$condition['test_data']) {
            $query->condition('r.address', '--testing--', '!=');
        }
        $query->orderBy('label', 'ASC');
        $data = $query->execute()->fetchAll();
        return $data;
    }
    
    
    public static function getCustomerById($customer_id)
    {
      
        $query = \Drupal::database()->select('triune_customer', 'r');
        $query->fields('r', array('id', 'customer_id'));
        $query->condition('r.customer_id', $customer_id);
        $data = $query->execute()->fetch();
        if ($data) {
            return($data->id);
        } else {
            return(false);
        }
      
    }
    
}
