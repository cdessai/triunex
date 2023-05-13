<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\JobRateController.
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
use Drupal\triune\Entity\JobRate;
use Drupal\triune\Entity\Office;
use Drupal\Core\Field\BaseFieldDefinition;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Controller routines for triune_jobrate page routes.
 */
class JobRateController implements ContainerInjectionInterface
{
    
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database; 


    /**
     * Constructs a \Drupal\triune\Controller\JobRateController object.
     *
     * @param \Drupal\Core\Database\Connection $database
     *     The database connection.
     */
    public function __construct(Connection $database, AccountInterface $account)
    {
        
        $this->database = $database;
        $this->user = User::load($account->id());

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
    
    
    public function addJobRate()
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        

        $form = \Drupal::formBuilder()->getForm('\Drupal\triune\Form\PayRollForm');
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'jobrate_add',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
        );
        
    }
    
    
    public function editJobRate($customer_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        $form = \Drupal::formBuilder()->getForm('Drupal\triune\Form\CustomerForm', $this->location_list, $customer_id);
                
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'jobrate',
            '#form' => $form,
            '#cache' => ['max-age' => 0,],
            '#markup' => time(),
        );
        
    }
    
    
    public function viewJobRate($customer_id)
    {
        
        \Drupal::service('page_cache_kill_switch')->trigger();
        
        /** 
         * Send data/calculations to twig...
         */
        return array(
            '#theme' => 'jobrate',
            '#markup' => time(),
            '#cache' => ['max-age' => 0],
        );
        
    }
    
    
    
    
    public function deleteJobRate($customer_id)
    {
        
        $customer = Employee::load($customer_id);
        
        if ($customer) {
            $customer->delete();
        }
        
        \Drupal::messenger()->addMessage('Removed Customer');
        
        $path = Url::fromRoute('triune.jobrate.list')->toString();
        return new RedirectResponse($path);
        
    }
    
    
    
    public function listJobRates()
    {
      
        $condition['test_data'] = $this->user->hasPermission('view_test_data');
        $data = JobRateController::getJobRateList($condition);
        // dump($data);die;

        foreach ($data as $value) {
            $jobrate = JobRate::load($value->id);
            if ($jobrate) {
                $jobrate_list[$jobrate->id()]['id'] = $jobrate->id();
                // $customer_list[$customer->id()]['name'] = $customer->label->value;
                // $emails = json_decode($customer->email->value);
                // $customer_list[$customer->id()]['email_1'] = $emails[0];
                // $customer_list[$customer->id()]['email_2'] = $emails[1];
                // $customer_list[$customer->id()]['email_3'] = $emails[2];
                // $customer_list[$customer->id()]['email_4'] = $emails[3];
                // $customer_list[$customer->id()]['email_5'] = $emails[4];
                // $customer_list[$customer->id()]['address'] = $customer->address->value;
                // $office = Office::load($customer->office_id->target_id);
                // $customer_list[$customer->id()]['office'] = $office->label->value;
                // $customer_list[$customer->id()]['s1_name'] = $customer->s1_name->value;
                // $customer_list[$customer->id()]['s2_name'] = $customer->s2_name->value;
                // $customer_list[$customer->id()]['s3_name'] = $customer->s3_name->value;
                // $customer_list[$customer->id()]['tsc_name'] = $customer->tsc_name->value;
                // $customer_list[$customer->id()]['p_name'] = $customer->p_name->value;
                // $customer_list[$customer->id()]['s1_phone'] = $customer->s1_phone->value;
                // $customer_list[$customer->id()]['s2_phone'] = $customer->s2_phone->value;
                // $customer_list[$customer->id()]['s3_phone'] = $customer->s3_phone->value;
                // $customer_list[$customer->id()]['tsc_phone'] = $customer->tsc_phone->value;
                // $customer_list[$customer->id()]['p_phone'] = $customer->p_phone->value;
            }
        }
      
        /** 
         * Send data to twig
         */
        return array(
          '#theme' => 'jobrate_list',
          '#jobrate_list' => $jobrate_list,
          '#cache' => ['max-age' => 0,],
          '#markup' => time(),
        );      
      
    }
    
    
    public static function getJobRateList($condition = array())
    {
        $query = \Drupal::database()->select('triune_jobrate', 'j');
        $query->fields('j', array('id', 'label'));
        if (!$condition['test_data']) {
            $query->condition('j.pay_rate', '--testing--', '!=');
        }
        $query->orderBy('label', 'ASC');
        $data = $query->execute()->fetchAll();
        return $data;
    }
    
    
    public static function getCustomerById($customer_id)
    {
      
        $query = \Drupal::database()->select('triune_jobrate', 'j');
        $query->fields('j', array('id', 'customer_id'));
        $query->condition('j.customer_id', $customer_id);
        $data = $query->execute()->fetch();
        if ($data) {
            return($data->id);
        } else {
            return(false);
        }
      
    }
    
}
