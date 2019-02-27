<?php

namespace Drupal\Islandora_solr_facet_pages\Plugin\Block;

use Drupal\Core\Block\BlockBase;
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
class ListFacetPages extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = [];
    $fields = \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_fields_data');

    $items = [];
    foreach ($fields as $value) {
/**
      $items[] = Link::createFromRoute(
        'islandora_solr_facet_pages.callback',
        ['path' => $value['path']],
        ['attributes' => ['title' => $value['label']]]
      );
*/
      $items[] = [
        '#markup' => Link::createFromRoute(
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
