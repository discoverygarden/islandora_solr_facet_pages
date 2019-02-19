<?php /**
 * @file
 * Contains \Drupal\islandora_solr_facet_pages\EventSubscriber\InitSubscriber.
 */

namespace Drupal\islandora_solr_facet_pages\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent() {
    // Include islandora_solr common.inc.
    module_load_include('inc', 'islandora_solr', 'includes/utilities');
    // Include islandora solr query_processor.inc
    module_load_include('inc', 'islandora_solr', 'includes/query_processor');
  }

}
