<?php

namespace Drupal\islandora_solr_facet_pages\Plugin\Block;

use Drupal\islandora\Plugin\Block\AbstractConfiguredBlockBase;
use Drupal\Core\Link;

/**
 * Provides a block to list facet pages.
 *
 * @Block(
 *   id = "islandora-solr-facet-pages",
 *   admin_label = @Translation("Islandora Solr facet pages"),
 *   category = @Translation("Islandora Solr facet pages"),
 * )
 */
class ListFacetPages extends AbstractConfiguredBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = [];
    $fields = $this->configFactory->get('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_fields_data');

    $items = [];
    foreach ($fields as $value) {
      $items[] = [
        '#markup' => Link::createFromRoute(
          $value['label'],
          'islandora_solr_facet_pages.callback',
          ['path' => $value['path']],
          ['attributes' => ['title' => $value['label']]]
        )->toString(),
      ];
    }

    if (!empty($items)) {
      $block = [
        '#theme' => 'item_list',
        '#items' => $items,
        '#list_type' => 'ul',
        '#wrapper_attributes' => [
          'class' => 'islandora-solr-facet-pages-list',
        ],
      ];
    }
    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
