<?php

namespace Drupal\drupal_web_pages\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Renders the Home, About, and FAQ pages.
 */
class PageController extends ControllerBase {

  /**
   * Builds the homepage.
   */
  public function home(): array {
    return [
      '#theme' => 'drupal_web_home',
      '#cache' => ['tags' => ['config:system.site']],
    ];
  }

  /**
   * Builds the About page.
   */
  public function about(): array {
    return [
      '#theme' => 'drupal_web_about',
    ];
  }

  /**
   * Builds the FAQ page.
   */
  public function faqs(): array {
    $faqs = [
      [
        'question' => 'What does this site do?',
        'answer' => 'This is a starter drupal_web build: a working Drupal site running in Docker with a custom theme and three hand-built pages, ready for you to replace with real content.',
      ],
      [
        'question' => 'How do I edit these pages?',
        'answer' => 'The Home, About, and FAQ pages are rendered by the drupal_web_pages module rather than as Drupal content, so edit them at web/modules/custom/drupal_web_pages/src/Controller/PageController.php and the matching Twig templates in the templates/ folder.',
      ],
      [
        'question' => 'Can I turn these into editable Drupal content instead?',
        'answer' => 'Yes — create basic page nodes for About and FAQs in the admin UI, then either point these routes at those nodes or remove the routes entirely and use Drupal\'s built-in page/menu system.',
      ],
      [
        'question' => 'Where do I add more FAQ entries?',
        'answer' => 'Add another array item to the $faqs list in PageController::faqs(). Each entry needs a question and an answer key.',
      ],
      [
        'question' => 'How is the site\'s data stored?',
        'answer' => 'Content and configuration live in the MariaDB database defined in docker-compose.yml, in a Docker volume so it persists across container restarts.',
      ],
    ];

    return [
      '#theme' => 'drupal_web_faqs',
      '#faqs' => $faqs,
    ];
  }

}
