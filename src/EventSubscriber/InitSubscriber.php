<?php

namespace Drupal\islandora_solr_facet_pages\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Includes some files.
 */
class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  /**
   * {@inheritdoc}
   */
  public function onEvent() {
    module_load_include('inc', 'islandora_solr', 'includes/utilities');
    module_load_include('inc', 'islandora_solr', 'includes/query_processor');
  }

}
