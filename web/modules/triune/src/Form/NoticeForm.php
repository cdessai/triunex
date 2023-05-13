<?php
/**
 * @file
 * Contains \Drupal\triune\Form\NoticeForm.
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
use Drupal\triune\Entity\Notice;
use Drupal\triune\Entity\Office;
use Drupal\triune\Entity\Employee;
use Drupal\triune\Controller\NoticeController;
use Drupal\triune\Controller\CustomerController;

/**
 * Notice form.
 */
class NoticeForm extends FormBase
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
        return 'notice_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $type = 'dnr', $notice_id = 0)
    {
        
        if ($notice_id == 0) {
            $label = '';
            $customer = 0;
            $office_id = $this->user->office_id->target_id;
            $employee_id = 0;
            $employee = null;
            $date = time();
        } else {
            $notice = Notice::load($notice_id);
            $label = $notice->label->value;
            $type = $notice->type->value;
            $customer = $notice->customer_id->target_id;
            $office_id = $notice->office_id->target_id;
            $employee_id = $notice->employee_id->target_id;
            $employee = Employee::load($employee_id);
            $date = $notice->changed->value;
        }
        
        $customer_list = array(0 => '---');
        $data = CustomerController::getCustomerList();
        foreach ($data as $value) {
            $customer_list[$value->id] = $value->label;
        }
        
        $form['notice_id'] = array(
          '#type' => 'hidden',
          '#title' => t('Notice ID'),
          '#required' => true,
          '#default_value' => $notice_id,
        );
        $form['type'] = [
          '#type' => 'select',
          '#title' => $this->t('Type'),
          '#options' => [
            'dnr' => $this->t('Do Not Return'),
            'cr' => $this->t('Check Request'),
            'ar' => $this->t('Accident Report'),
            /*'other' => $this->t('Other'),*/
          ],
          '#default_value' => $type,
        ];
        $form['employee_id'] = [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'triune_employee',
          '#title' => $this->t('Employee'),
          '#description' => $this->t(''),
          '#default_value' => $employee,
          '#tags' => false,
        ];
        
        $form['office_id'] = [
          '#type' => 'select',
          '#title' => $this->t('Office'),
          '#options' => [
            '1' => $this->t('- Any -'),
            '2' => $this->t('Schmidt Office'),
            '3' => $this->t('25th Office'),
            '4' => $this->t('60th Office'),
            '5' => $this->t('Main St'),
            '6' => $this->t('Mirmar Rd'),
            '7' => $this->t('Harvill Ave'),
          ],

          '#default_value' => $office_id,
        ];
        $form['customer_id'] = array(
          '#type' => 'select',
          '#title' => $this->t('Company'),
          '#options' => $customer_list,
          '#default_value' => $customer,
        );
        $form['date'] = array(
          '#type' => 'date',
          '#title' => t('Date'),
          '#required' => true,
          '#default_value' => date('Y-m-d', $date),
        );
        $form['label'] = array(
          '#type' => 'textfield',
          '#title' => t('Note'),
          '#required' => false,
          '#default_value' => $label,
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
      
        // If id is 0 then insert as a new notice.
        if ($form_state->getValue('notice_id') == 0) {
            $notice = Notice::create(
                [
                'user_id' => $this->user->id(),
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'office_id' => $form_state->getValue('office_id'),
                'customer_id' => $form_state->getValue('customer_id'),
                'employee_id' => $form_state->getValue('employee_id'),
                'type' => $form_state->getValue('type'),
                'status' => 'active',
                'changed' => strtotime($form_state->getValue('date')),
                'created' => time(),
                //'changed' => time(),
                ]
            );
          
        } else {
            $notice = Notice::load($form_state->getValue('notice_id'));
            $notice->label->setValue($form_state->getValue('label'));
            $notice->office_id->setValue($form_state->getValue('office_id'));
            $notice->customer_id->setValue($form_state->getValue('customer_id'));
            $notice->employee_id->setValue($form_state->getValue('employee_id'));
            $notice->type->setValue($form_state->getValue('type'));
            $notice->status->setValue('active');
            $notice->changed->setValue(strtotime($form_state->getValue('date')));
            //$notice->changed->setValue(time());
        }
        $notice->save();
        
        
        $form_state->setRedirect('triune.notice.list', ['type' => $form_state->getValue('type')]);
        return;
    }
}
?>