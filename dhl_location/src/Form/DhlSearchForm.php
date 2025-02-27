<?php

declare(strict_types=1);

namespace Drupal\dhl_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use function call_user_func;

/**
 * Provides a DHL location search form.
 */
class DhlSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dhl_location_search_form';
  }

  /**
   * AJAX submit callback for the search form.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    // Get the controller and call the AJAX callback.
    $controller = \Drupal::service('controller_resolver')
      ->getControllerFromDefinition('\Drupal\dhl_location\Controller\DhlSearchController::ajaxSearchResults');

    return call_user_func($controller);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['location_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Location Information'),
    ];

    $form['location_info']['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => $this->getCountryOptions(),
      '#required' => TRUE,
    ];

    $form['location_info']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#maxlength' => 64,
      '#required' => TRUE,
    ];

    $form['location_info']['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal Code'),
      '#maxlength' => 20,
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => 'dhl-search-results-container',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $form_state->setRedirect('dhl_location.search', [
      'country' => $values['country'],
      'postal_code' => $values['postal_code'],
      'city' => $values['city'],
    ]);
  }

  /**
   * Get country options for select list.
   */
  protected function getCountryOptions() {
    return [
      '' => $this->t('- Select -'),
      'AE' => $this->t('United Arab Emirates'),
      'AF' => $this->t('Afghanistan'),
      'AL' => $this->t('Albania'),
      'AM' => $this->t('Armenia'),
      'AO' => $this->t('Angola'),
      'AR' => $this->t('Argentina'),
      'AT' => $this->t('Austria'),
      'AU' => $this->t('Australia'),
      'BA' => $this->t('Bosnia and Herzegovina'),
      'BB' => $this->t('Barbados'),
      'BD' => $this->t('Bangladesh'),
      'BE' => $this->t('Belgium'),
      'BF' => $this->t('Burkina Faso'),
      'BG' => $this->t('Bulgaria'),
      'BH' => $this->t('Bahrain'),
      'BJ' => $this->t('Benin'),
      'BM' => $this->t('Bermuda'),
      'BN' => $this->t('Brunei Darussalam'),
      'BO' => $this->t('Bolivia'),
      'BR' => $this->t('Brazil'),
      'BS' => $this->t('Bahamas'),
      'BT' => $this->t('Bhutan'),
      'BW' => $this->t('Botswana'),
      'BY' => $this->t('Belarus'),
      'CA' => $this->t('Canada'),
      'CG' => $this->t('Congo'),
      'CH' => $this->t('Switzerland'),
      'CI' => $this->t("Côte d'Ivoire"),
      'CK' => $this->t('Cook Islands'),
      'CL' => $this->t('Chile'),
      'CN' => $this->t('China'),
      'CO' => $this->t('Colombia'),
      'CR' => $this->t('Costa Rica'),
      'CV' => $this->t('Cabo Verde'),
      'CY' => $this->t('Cyprus'),
      'CZ' => $this->t('Czechia'),
      'DE' => $this->t('Germany'),
      'DK' => $this->t('Denmark'),
      'DO' => $this->t('Dominican Republic'),
      'DZ' => $this->t('Algeria'),
      'EC' => $this->t('Ecuador'),
      'EE' => $this->t('Estonia'),
      'EG' => $this->t('Egypt'),
      'ES' => $this->t('Spain'),
      'ET' => $this->t('Ethiopia'),
      'FI' => $this->t('Finland'),
      'FJ' => $this->t('Fiji'),
      'FR' => $this->t('France'),
      'GB' => $this->t('United Kingdom of Great Britain and Northern Ireland'),
      'GE' => $this->t('Georgia'),
      'GF' => $this->t('French Guiana'),
      'GG' => $this->t('Guernsey'),
      'GH' => $this->t('Ghana'),
      'GM' => $this->t('Gambia'),
      'GP' => $this->t('Guadeloupe'),
      'GR' => $this->t('Greece'),
      'GT' => $this->t('Guatemala'),
      'GW' => $this->t('Guinea-Bissau'),
      'HK' => $this->t('Hong Kong'),
      'HN' => $this->t('Honduras'),
      'HR' => $this->t('Croatia'),
      'HT' => $this->t('Haiti'),
      'HU' => $this->t('Hungary'),
      'ID' => $this->t('Indonesia'),
      'IE' => $this->t('Ireland'),
      'IL' => $this->t('Israel'),
      'IN' => $this->t('India'),
      'IQ' => $this->t('Iraq'),
      'IR' => $this->t('Iran'),
      'IS' => $this->t('Iceland'),
      'IT' => $this->t('Italy'),
      'JE' => $this->t('Jersey'),
      'JM' => $this->t('Jamaica'),
      'JO' => $this->t('Jordan'),
      'JP' => $this->t('Japan'),
      'KE' => $this->t('Kenya'),
      'KG' => $this->t('Kyrgyzstan'),
      'KH' => $this->t('Cambodia'),
      'KI' => $this->t('Kiribati'),
      'KM' => $this->t('Comoros'),
      'KP' => $this->t('North Korea'),
      'KR' => $this->t('South Korea'),
      'KV' => $this->t('Kosovo'),
      'KW' => $this->t('Kuwait'),
      'KY' => $this->t('Cayman Islands'),
      'KZ' => $this->t('Kazakhstan'),
      'LA' => $this->t('Laos'),
      'LB' => $this->t('Lebanon'),
      'LK' => $this->t('Sri Lanka'),
      'LR' => $this->t('Liberia'),
      'LS' => $this->t('Lesotho'),
      'LT' => $this->t('Lithuania'),
      'LU' => $this->t('Luxembourg'),
      'LV' => $this->t('Latvia'),
      'MA' => $this->t('Morocco'),
      'MD' => $this->t('Moldova'),
      'MG' => $this->t('Madagascar'),
      'MK' => $this->t('North Macedonia'),
      'ML' => $this->t('Mali'),
      'MM' => $this->t('Myanmar'),
      'MN' => $this->t('Mongolia'),
      'MO' => $this->t('Macao'),
      'MP' => $this->t('Northern Mariana Islands'),
      'MQ' => $this->t('Martinique'),
      'MR' => $this->t('Mauritania'),
      'MT' => $this->t('Malta'),
      'MU' => $this->t('Mauritius'),
      'MV' => $this->t('Maldives'),
      'MW' => $this->t('Malawi'),
      'MX' => $this->t('Mexico'),
      'MY' => $this->t('Malaysia'),
      'MZ' => $this->t('Mozambique'),
      'NA' => $this->t('Namibia'),
      'NG' => $this->t('Nigeria'),
      'US' => $this->t('United States of America'),
    ];
  }

}
