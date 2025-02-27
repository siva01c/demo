<?php

declare(strict_types=1);

namespace Drupal\dhl_location\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * DHL API configuration form.
 */
class DhlApiConfigForm extends ConfigFormBase {

  /**
   * The configuration name.
   */
  public const CONFIG_NAME = 'dhl_location.settings';

  /**
   * Default API endpoint.
   */
  public const DEFAULT_API_ENDPOINT = 'https://api.dhl.com';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [static::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dhl_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(static::CONFIG_NAME);

    $form['api_endpoint'] = [
      '#type' => 'url',
      '#title' => $this->t('API endpoint'),
      '#required' => TRUE,
      '#description' => $this->t('The DHL API server URL.'),
      '#default_value' => $config->get('api_endpoint') ?? self::DEFAULT_API_ENDPOINT,
    ];

    $form['api_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('The API key for DHL API authentication. Find instructions at <a href=":url" target="_blank">DHL Developer Portal</a>.', [
        ':url' => 'https://developer.dhl.com',
      ]),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('api_endpoint', rtrim($form_state->getValue('api_endpoint'), '/'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
