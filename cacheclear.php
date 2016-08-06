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
  private $dashboardLoginURL;

  // Dashboard url.
  private $dashboardSitesURL;

  private $accountUsermame;

  private $accountPassword;


  /**
   * Class Constructor.
   */
  public function __construct() {

    $this->client = new Client();

    if (!file_exists('settings.json')) {
      $this->headerLog('File settings.json not found.');
      die;
    }

    $settings = json_decode(file_get_contents('settings.json'));

    if (!$this->settingsExists($settings)) {
      $this->headerLog('settings.json file not configured properly.');
      die;
    }

    $this->dashboardLoginURL = $settings->dashboardLoginURL;
    $this->dashboardSitesURL = $settings->dashboardSitesURL . '?page=';
    $this->accountUsermame = $settings->accountUsermame;
    $this->accountPassword = $settings->accountPassword;
  }


  /**
   * Chech if all the settings are configured.
   */
  private function settingsExists($settings) {
    if (empty($settings->dashboardLoginURL) || empty($settings->dashboardSitesURL) || empty($settings->accountUsermame) || empty($settings->accountPassword)) {
      return FALSE;
    }

    return TRUE;
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


  /**
   * Clear pages waiting a given interval.
   *
   * @param  integer $totalPages
   *   Total pages on Dashboard.
   *
   * @param  array $pagesToStop
   *   Pages where the crawler should wait.
   *
   * @param  integer $intervalMinutes
   *   Interval in minutes that the crawler will wait in the given pages.
   *
   * Example:
   *   clearCachesInterval($totalPages = 40, $pagesToStop = array(10, 20, 30), $intervalMinutes = 5)
   *   Will go through the 40 pages stopping on pages 10, 20 and 30 and waiting 5 minutes.
   */
  public function clearCachesInterval($totalPages = 1, $pagesToStop = array(), $intervalMinutes = 0) {

    // Try to remove the max_execution_time limit.
    $setPHPExecutionTime = set_time_limit(0);

    if (!$setPHPExecutionTime) {
      $this->headerLog('Unable to change max_execution_time PHP setting. You may need to edit your php.ini file and set it manually.');
      die;
    }

    // Interval in seconds to be used in sleep function.
    $sleepTime = $intervalMinutes * 60;

    $this->login();

    $this->headerLog('Starting Clear Caches');

    for ($i=0; $i < $totalPages; $i++) {

      if (in_array(($i + 1), $pagesToStop)) {
        $this->log("Script will now wait $intervalMinutes minute(s).\n");
        sleep($sleepTime);
      }

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
//$crawler->clearCaches(1,10);
//$crawler->clearCachesInterval(3, array(2), 1);
