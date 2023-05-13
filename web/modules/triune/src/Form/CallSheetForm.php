<?php
/**
 * @file
 * Contains \Drupal\triune\Form\CallSheetForm.
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
use Drupal\triune\Entity\CallSheet;
use Drupal\triune\Entity\Office;

/**
 * CallSheet form.
 */
class CallSheetForm extends FormBase
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
        return 'callsheet_interface_form';
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $callsheet_id = 0)
    {
        
        if ($callsheet_id == 0) {
            $user_id = $this->user->id();
            $office_id = $this->office->id();
            $date = time();
            //$shift = 0;
            $label = '';
            $notes = '';
          
        } else {
            $callsheet = CallSheet::load($callsheet_id);
            $user_id = $callsheet->getOwnerId();
            $office_id = $callsheet->office_id->target_id;
            $date = $callsheet->date->value;
            //$shift = $callsheet->shift->value;
            $label = $callsheet->label->value;
            $notes = $callsheet->notes->value;
        }
        
        $employee_list = '';
        
        $form['callsheet_id'] = array(
          '#type' => 'hidden',
          '#title' => t('Callsheet ID'),
          '#required' => true,
          '#default_value' => $callsheet_id,
        );
        $form['user_id'] = array(
          '#type' => 'hidden',
          '#title' => t('User ID'),
          '#required' => true,
          '#default_value' => $user_id,
        );
        $form['office_id'] = array(
          '#type' => 'hidden',
          '#title' => t('Office ID'),
          '#required' => true,
          '#default_value' => $office_id,
        );
        $form['label'] = array(
          '#type' => 'textfield',
          '#title' => t('Label'),
          '#required' => true,
          '#default_value' => $label,
        );
        $form['date'] = array(
          '#type' => 'date',
          '#title' => t('Week of'),
          '#required' => true,
          '#default_value' => date('Y-m-d', $date),
          '#min' => date('Y-m-d', 0),
          '#max' => date('Y-m-d', strtotime('+1 Year')),
        );
        $form['notes'] = array(
          '#type' => 'textarea',
          '#title' => t('Notes'),
          '#required' => false,
          '#default_value' => $notes,
          '#maxlength' => 255,
        );
        $form['cancel'] = array(
            '#type' => 'cancel',
            '#value' => t('Cancel'),
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
        //parent::validateForm($form, $form_state);
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
        $date = strtotime($form_state->getValue('date'));
        $next = strtotime('next Monday', $date);
        $last = strtotime('last Monday', $next);

        // If id is 0 then insert as a new callsheet.
        if ($form_state->getValue('callsheet_id') == 0) {
            $callsheet = CallSheet::create(
                [
                'user_id' => $this->user->id(),
                'office_id' => $this->office->id(),
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'date' => $last,
                'notes' => $form_state->getValue('notes'),
                'created' => time(),
                'changed' => time(),
                ]
            );
          
        } else {
            $callsheet = CallSheet::load($form_state->getValue('callsheet_id'));
            $callsheet->label->setValue($form_state->getValue('label'));
            $callsheet->date->setValue($last);
            $callsheet->notes->setValue($form_state->getValue('notes'));
            $callsheet->changed->setValue(time());
        }
      
        $callsheet->save();
      
        $form_state->setRedirect('triune.callsheet.view', ['callsheet_id' => $callsheet->id()]);
        return;
    }
}
?>