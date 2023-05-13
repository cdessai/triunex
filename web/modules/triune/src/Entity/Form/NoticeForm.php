<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\NoticeForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Notice form.
 *
 * @ingroup triune
 */
class NoticeForm extends ContentEntityForm
{
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        
        $form = parent::buildForm($form, $form_state);
        $entity = $this->entity;
        
        return $form;
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
        $notice = $this->entity;
        if ($notice->isNew()) {
            \Drupal::messenger()->addMessage($this->t('Created Notice @name', array('@name' => $form_state->getValue('label'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved Notice @name', array('@name' => $form_state->getValue('label'))), 'status');
        }
        
        if (!$notice) {
            $notice = Notice::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'office_id' => $form_state->getValue('office_id'),
                'customer_id' => $form_state->getValue('customer_id'),
                'user_id' => $form_state->getValue('user_id'),
                'employee_id' => $form_state->getValue('employee_id'),
                'type' => $form_state->getValue('type'),
                'status' => $form_state->getValue('status'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
            $notice->label->setValue($form_state->getValue('label'));
            $notice->office_id->setValue($form_state->getValue('office_id'));
            $notice->customer_id->setValue($form_state->getValue('customer_id'));
            $notice->user_id->setValue($form_state->getValue('user_id'));
            $notice->employee_id->setValue($form_state->getValue('employee_id'));
            $notice->type->setValue($form_state->getValue('type'));
            $notice->status->setValue($form_state->getValue('status'));
            $notice->changed->setValue(time());
        }
        $notice->save();

        $form_state->setRedirect('triune.entity.notice.collection');
        return;
        
    }
}
?>