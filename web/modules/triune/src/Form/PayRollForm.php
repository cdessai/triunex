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
class PayrollForm extends FormBase
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
        return 'payroll_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $customer = null;
        $customer_list = [];
        $position_code = 'GEN';
        $position_name = 'General Labour';
        $rate = 0;
        
        $form['date'] = [
            '#type' => 'date',
            '#title' => $this->t('Date'),
            '#default_value' => time(),
        ];
        
        $form['customer_id'] = array(
            '#type' => 'select',
            '#title' => $this->t('Customer'),
            '#options' => $customer_list,
            '#default_value' => $customer,
        );

        $form['position_code'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Position Code'),
          '#default_value' => $position_code,
        ];
        
        $form['position_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Position Name'),
            '#default_value' => $position_name,
        ];
        
        $form['rate'] = array(
          '#type' => 'number',
          '#title' => $this->t('Rate'),
          '#default_value' => $rate,
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
        $form_state->setRedirect('triune.payroll.list');
        return;
    }
}