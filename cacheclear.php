<?php

namespace CacheClear;

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;


/**
 * CacheClear Class.
 */
class CacheClear {

  // Crawler instance used by class.
  private $client;

  // Dashboard Login URL.
  private $dashboardLoginURL = 'LOGIN_URL_HERE';

  // Dashboard url.
  // The pagination queryString (page) should be present in this url,
  // so you shoud append ?page= in the end its end
  private $dashboardSitesURL = 'DASHBOARD_URL_HERE' . '?page=';


  // ***************************************
  // ***************************************
  // *********      WARNING     ************
  // ***************************************
  // ***************************************
  //
  //  Your credentials.
  //  If you share this script, remember to remove the info below.
  private $accountUsermame = 'YOUR_USERNAME';

  //  Your password should be base64 encoded
  //  You can use this url to encode it: https://www.base64encode.org/
  private $accountPassword = 'YOUR_PASSWORD_BASE_ENCODED';


  /**
   * Class Constructor.
   */
  public function __construct() {
    $this->client = new Client();
  }


  /**
   * Logs messages on screen highlighted.
   */
  private function headerLog($message) {
    print "\n--> $message\n";
  }


  /**
   * Logs messages on screen.
   */
  private function log($message) {
    print "$message\n";
  }


  /**
   * Executes login using the given credentials.
   */
  private function login() {
    $this->headerLog('Executing login');

    $crawler = $this->client->request('GET', $this->dashboardLoginURL);

    $form = $crawler->selectButton('Log in')->form();

    $crawler = $this->client->submit($form, array('name' => $this->accountUsermame, 'pass' => base64_decode($this->accountPassword)));
  }


  /**
   * Go though the given pages clearing sites cache.
   *
   * @param  integer $startPage
   *   First page to start clear caches.
   *
   * @param  boolean|integer $endPage
   *   OPTIONAL.
   *   If empty, only the $startPage will be cleared.
   *   If an integer, caches will be cleared til this page.
   *
   */
  public function clearCaches($startPage = 1, $endPage = FALSE) {
    $this->login();

    $this->headerLog('Starting Clear Caches');

    if ($endPage === FALSE) {
      $endPage = $startPage;
    }

    for ($i=$startPage - 1; $i < $endPage; $i++) {
      $this->headerLog('Clear Cache Page ' . ($i + 1));

      $dashboardURL = $this->dashboardSitesURL . $i;

      $crawler = $this->client->request('GET', $dashboardURL);

      $crawler->filter('a.clear-caches')->each(function ($node) {
        $clearCacheForm = $this->client->click($node->link());

        $form = $clearCacheForm->selectButton('Clear caches')->form();

        $this->log($clearCacheForm->filter('.title-bar h1')->text());

        $dashboardPage = $this->client->submit($form);

        $this->log($dashboardPage->filter('.messages.status')->text() . "\n");
      });
    }
  }

}


/**
 * ================
 * = Clear Caches =
 * ================
 */
$crawler = new CacheClear();
$crawler->clearCaches(1, 10);
