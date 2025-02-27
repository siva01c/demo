<?php

namespace Drupal\dhl_location;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Service for managing DHL API interactions.
 */
class DhlApiManager {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The key repository.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * Constructs a DhlApiManager object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\key\KeyRepositoryInterface $key_repository
   *   The key repository.
   */
  public function __construct(
    ClientInterface $http_client,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    KeyRepositoryInterface $key_repository,
  ) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
    $this->keyRepository = $key_repository;
  }

  /**
   * Fetches DHL locations based on country and postal code.
   *
   * @param string $country
   *   The country code.
   * @param string $postal_code
   *   The postal code.
   * @param string $city
   *   The city name (optional).
   *
   * @return array
   *   An array of DHL locations or error response.
   */
  public function fetchDhlLocations(string $country, string $postal_code, string $city): array {
    $config = $this->configFactory->get('dhl_location.settings');

    try {
      $api_key = $this->loadApiKey($config->get('api_key'));
      $api_endpoint = $config->get('api_endpoint');

      if (empty($api_key) || empty($api_endpoint)) {
        $this->loggerFactory->get('dhl_location')->error('DHL API configuration is incomplete.');
        return ['error' => 'API configuration is incomplete'];
      }

      $query_params = [
        'countryCode' => $country,
        'postalCode' => $postal_code,
        'addressLocality' => $city,
      ];

      $response = $this->httpClient->request('GET', $api_endpoint . '/location-finder/v1/find-by-address', [
        'headers' => [
          'DHL-API-Key' => $api_key,
          'Accept' => 'application/json',
        ],
        'query' => $query_params,
      ]);

      if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getBody(), TRUE);
        $locations = $this->filterLocations($data['locations']);
        if (!empty($locations)) {
          return $locations;
        }

        return ['error' => 'No locations found'];
      }
      else {
        $this->loggerFactory->get('dhl_location')->error('DHL API returned non-200 status code: @code', [
          '@code' => $response->getStatusCode(),
        ]);

        return ['error' => 'API returned status code: ' . $response->getStatusCode()];
      }
    }
    catch (RequestException $e) {
      $error_message = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
      $status_code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;

      $this->loggerFactory->get('dhl_location')->error('DHL API request failed: @message (Status: @status)', [
        '@message' => $error_message,
        '@status' => $status_code,
      ]);

      return ['error' => 'API request failed: ' . $error_message];
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('dhl_location')->error('Error fetching DHL locations: @message', [
        '@message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return ['error' => 'Service error: ' . $e->getMessage()];
    }
  }

  /**
   * Determines if a location is closed on weekends based on its opening hours.
   *
   * @param array $opening_hours
   *   Array of opening hours data.
   *
   * @return bool
   *   TRUE if closed on weekends, FALSE if open on either Saturday or Sunday.
   */
  protected function isClosedOnWeekends(array $opening_hours): bool {
    $weekend_days = [
      'http://schema.org/Saturday',
      'http://schema.org/Sunday',
    ];

    foreach ($opening_hours as $hours) {
      if (in_array($hours['dayOfWeek'], $weekend_days, TRUE)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * This is a filter for demo purposes. Not suitable for production.
   *
   * @param array $locations
   *   API response with all locations.
   *
   * @return array
   *   The filtered results.
   */
  private function filterLocations(array $locations): array {
    // No odd numbers in their address, no locations closed on weekends.
    $filtered_loc = [];
    foreach ($locations as $location) {
      $closed_on_weekend = $this->isClosedOnWeekends($location['openingHours']);

      // Extract numbers from the street address.
      preg_match_all('/\d+/', $location['place']['address']['streetAddress'], $matches);
      $containsOddNumber = FALSE;
      foreach ($matches[0] as $number) {
        if ($number % 2 !== 0) {
          $containsOddNumber = TRUE;
          break;
        }
      }
      if (!$containsOddNumber && !$closed_on_weekend) {
        $filtered_loc[] = $location;
      }
    }

    return $filtered_loc;
  }

  /**
   * Loads the API key from the key repository.
   *
   * @param string $key_id
   *   The key ID to load.
   *
   * @return string
   *   The API key value.
   *
   * @throws \Exception
   *   If the key cannot be loaded.
   */
  protected function loadApiKey($key_id) {
    if (empty($key_id)) {
      throw new \Exception('API key ID is not configured');
    }

    $key = $this->keyRepository->getKey($key_id);

    if (!$key) {
      throw new \Exception('API key not found: ' . $key_id);
    }

    return $key->getKeyValue();
  }

}
