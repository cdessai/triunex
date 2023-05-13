<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\JobRateForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Order form.
 *
 * @ingroup triune
 */
class JobRateForm extends ContentEntityForm
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
        
        $jobrate = $this->entity;
        if ($jobrate->isNew()) {
            \Drupal::messenger()->addMessage($this->t('Created jobrate @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved jobrate @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        }
        
        if (!$jobrate) {
            $jobrate = Order::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
            $jobrate->label->setValue($form_state->getValue('label'));
            $jobrate->changed->setValue(time());
        }
        $jobrate->save();

        $form_state->setRedirect('triune.entity.jobrate.collection');
        return;
        
    }
}
?>