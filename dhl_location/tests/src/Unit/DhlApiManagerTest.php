<?php

namespace Drupal\Tests\dhl_location\Unit;

use Drupal\dhl_location\DhlApiManager;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\key\KeyRepositoryInterface;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests the DHL API Manager.
 *
 * @group dhl_location
 */
class DhlApiManagerTest extends TestCase {
  use ProphecyTrait;

  /**
   * The DHL API manager.
   *
   * @var \Drupal\dhl_location\DhlApiManager
   */
  private $dhlApiManager;

  /**
   * The reflection method for filterLocations.
   *
   * @var \ReflectionMethod
   */
  private $filterLocationsMethod;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Mock the dependencies.
    $httpClient = $this->createMock(ClientInterface::class);
    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);

    // Correctly mock the KeyRepositoryInterface (not KeyValueStoreInterface).
    $keyRepository = $this->createMock(KeyRepositoryInterface::class);

    // Instantiate the real service.
    $this->dhlApiManager = new DhlApiManager(
      $httpClient,
      $configFactory,
      $loggerFactory,
      $keyRepository
    );

    // Use reflection to make the private method accessible.
    $reflectionClass = new \ReflectionClass($this->dhlApiManager);
    $this->filterLocationsMethod = $reflectionClass->getMethod('filterLocations');
    $this->filterLocationsMethod->setAccessible(TRUE);
  }

  /**
   * Tests the filterLocations method.
   */
  public function testFilterLocations(): void {
    $testLocations = [
      [
        'url' => '/locations/PRG013',
        'location' => [
          'ids' => [['locationId' => 'PRG013', 'provider' => 'express']],
          'type' => 'servicepoint',
        ],
        'name' => 'Test Store - closed on weekend',
        'distance' => 747,
        'place' => [
          'address' => ['streetAddress' => 'Vodickova 34, Pasaz Egap'],
        ],
        'openingHours' => [
          ['dayOfWeek' => 'http://schema.org/Monday'],
          ['dayOfWeek' => 'http://schema.org/Tuesday'],
        ],
      ],
      [
        'url' => '/locations/8006-13511000',
        'location' => [
          'ids' => [['locationId' => '8006-13511000', 'provider' => 'parcel']],
          'type' => 'servicepoint',
        ],
        'name' => 'Test Store - show',
        'distance' => 721,
        'place' => [
          'address' => ['streetAddress' => 'Jungmannova 22'],
        ],
        'openingHours' => [
          ['dayOfWeek' => 'http://schema.org/Saturday'],
          ['dayOfWeek' => 'http://schema.org/Sunday'],
        ],
      ],
      [
        'url' => '/locations/8006-13441000',
        'location' => [
          'ids' => [['locationId' => '8016-13511000', 'provider' => 'parcel']],
          'type' => 'servicepoint',
        ],
        'name' => 'Test Store - odd',
        'place' => [
          'address' => ['streetAddress' => 'Jungmannova 1'],
        ],
        'openingHours' => [
          ['dayOfWeek' => 'http://schema.org/Saturday'],
          ['dayOfWeek' => 'http://schema.org/Sunday'],
        ],
      ],
    ];

    // Test filtering for parcel provider.
    $parcelLocations = $this->filterLocationsMethod->invoke(
      $this->dhlApiManager,
      $testLocations
    );

    // Assert that only parcel locations remain.
    $this->assertCount(1, $parcelLocations);
    $this->assertEquals('Test Store - show', $parcelLocations[0]['name']);
  }

}
