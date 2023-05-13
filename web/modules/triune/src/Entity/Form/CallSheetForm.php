<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\CallSheetForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\triune\Entity\CallSheet;

/**
 * CallSheet form.
 *
 * @ingroup triune
 */
class CallSheetForm extends ContentEntityForm
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
        
        $callsheet = $this->entity;
        $is_new = $callsheet->isNew();
        
        if (!$callsheet) {
            var_dump($form_state->getValue('office_id'));exit();
            $callsheet = CallSheet::create(
                [
                'user_id' => $form_state->getValue('user_id'),
                'office_id' => $form_state->getValue('office_id'),
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'notes' => $form_state->getValue('notes'),
                'date' => $form_state->getValue('date'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
            $callsheet->label->setValue($form_state->getValue('label'));
            $callsheet->notes->setValue($form_state->getValue('notes'));
            $callsheet->date->setValue($form_state->getValue('date'));
            $callsheet->office_id = $form_state->getValue('office_id')[0];
            $callsheet->changed->setValue(time());
        }
        
        $callsheet->save();
        
        if ($is_new) {
            \Drupal::messenger()->addMessage($this->t('Created Call Sheet @name', array('@name' => $form_state->getValue('label'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved Call Sheet @name', array('@name' => $form_state->getValue('label'))), 'status');
        }
        
        $form_state->setRedirect('triune.entity.callsheet.collection');
        return;
        
    }
}
?>