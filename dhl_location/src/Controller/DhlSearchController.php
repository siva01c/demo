<?php

declare(strict_types=1);

namespace Drupal\dhl_location\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\dhl_location\DhlApiManager;
use Drupal\dhl_location\Form\DhlSearchForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for DHL location details.
 */
class DhlSearchController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The DHL API manager service.
   *
   * @var \Drupal\dhl_location\DhlApiManager
   */
  protected $dhlApiManager;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a DhlSearchController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\dhl_location\DhlApiManager $api_manager
   *   The DHL API manager service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    RequestStack $request_stack,
    LoggerChannelFactoryInterface $logger_factory,
    DhlApiManager $api_manager,
    FormBuilderInterface $form_builder,
    RendererInterface $renderer,
  ) {
    $this->requestStack = $request_stack;
    $this->loggerFactory = $logger_factory;
    $this->dhlApiManager = $api_manager;
    $this->formBuilder = $form_builder;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('request_stack'),
      $container->get('logger.factory'),
      $container->get('dhl_location.api_manager'),
      $container->get('form_builder'),
      $container->get('renderer')
    );
  }

  /**
   * Search form and rendered results.
   *
   * @return array
   *   Render array.
   */
  public function searchForLocation(): array {
    $build = [
      'search_form' => [
        '#type' => 'container',
        '#attributes' => ['id' => 'dhl-search-form-container'],
        'form' => $this->formBuilder->getForm(DhlSearchForm::class),
      ],
      'search_results' => [
        '#type' => 'container',
        '#attributes' => ['id' => 'dhl-search-results-container'],
      ],
    ];

    return $build;
  }

  /**
   * Ajax callback for displaying filtered API results.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The Ajax response containing the filtered results.
   */
  public function ajaxSearchResults() {
    $response = new AjaxResponse();
    $request = $this->requestStack->getCurrentRequest();
    $country = $request->request->get('country');
    $postal_code = $request->request->get('postal_code');
    $city = $request->request->get('city');

    $locations = $this->dhlApiManager->fetchDhlLocations($country, $postal_code, $city);

    $results = [
      '#theme' => 'dhl_location_results',
      '#items' => $this->formatLocations($locations),
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    $rendered_results = $this->renderer->render($results);

    $response->addCommand(new ReplaceCommand(
      '#dhl-search-results-container',
      $rendered_results
    ));

    return $response;
  }

  /**
   * Formats location data for display.
   *
   * @param array $locations
   *   The array of location data from the API.
   *
   * @return array
   *   The formatted location data as render arrays.
   */
  private function formatLocations(array $locations): array {
    return array_map(function ($location) {
      // Format each location for display.
      return [
        '#type' => 'container',
        '#attributes' => ['class' => ['dhl-location-item']],
        'details' => [
          '#markup' => '<pre>' . Yaml::encode($location) . '</pre>',
        ],
      ];
    }, $locations);
  }

}
