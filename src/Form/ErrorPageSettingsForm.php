<?php

namespace Drupal\apigee_edge\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Provides a form for saving the error page title and content.
 */
class ErrorPageSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apigee_edge_error_page_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'apigee_edge.error_page',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('apigee_edge.error_page');

    $form['error_page'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('The displayed title and content on the error page.'),
      '#collapsible' => FALSE,
    ];

    $form['error_page']['error_page_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error page title'),
      '#default_value' => $config->get('error_page_title'),
    ];

    $form['error_page']['error_page_content'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Error page content'),
      '#format' => $config->get('error_page_content.format'),
      '#default_value' => $config->get('error_page_content.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('apigee_edge.error_page')
      ->set('error_page_title', $form_state->getValue('error_page_title'))
      ->set('error_page_content.format', $form_state->getValue(['error_page_content', 'format']))
      ->set('error_page_content.value', $form_state->getValue(['error_page_content', 'value']))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
