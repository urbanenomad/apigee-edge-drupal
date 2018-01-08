<?php

namespace Drupal\apigee_edge\Job;

use Apigee\Edge\Api\Management\Entity\Developer;
use Drupal\Core\Database\Connection;

/**
 * A job that synchronizes developers.
 */
class DeveloperSync extends EdgeJob {

  use JobCreatorTrait;

  /**
   * All Apigee Edge accounts.
   *
   * Format: strtolower(email) => email.
   *
   * @var array
   */
  protected $edgeAccounts = [];

  /**
   * All Drupal accounts.
   *
   * Format: strtolower(mail) => mail.
   *
   * @var array
   */
  protected $drupalAccounts = [];

  /**
   * Returns the database connection service.
   *
   * @return \Drupal\Core\Database\Connection
   *   The database connection service.
   */
  protected function getConnection() : Connection {
    return \Drupal::service('database');
  }

  /**
   * Loads all users' emails.
   *
   * @return array
   *   Format: strtolower(mail) => mail
   */
  protected function loadUserEmails() : array {
    $mails = $this->getConnection()->query("
      SELECT DISTINCT u.mail
      FROM {user__roles} r
      JOIN {users_field_data} u ON r.entity_id = u.uid
    ")->fetchCol();

    $accounts = [];
    foreach ($mails as $mail) {
      $accounts[strtolower($mail)] = $mail;
    }

    return $accounts;
  }

  /**
   * Loads all Apigee Edge developers' emails.
   *
   * @return array
   *   Format: strtolower(email) => email
   */
  protected function loadEdgeUserEmails() : array {
    $mails = $this->getConnector()->getDeveloperController()->getEntityIds();

    $accounts = [];
    foreach ($mails as $mail) {
      $accounts[strtolower($mail)] = $mail;
    }

    return $accounts;
  }

  /**
   * {@inheritdoc}
   */
  protected function executeRequest() {
    $this->drupalAccounts = $this->loadUserEmails();
    $this->edgeAccounts = $this->loadEdgeUserEmails();
  }

  /**
   * {@inheritdoc}
   */
  public function execute() : bool {
    parent::execute();

    foreach ($this->edgeAccounts as $search => $mail) {
      if (empty($this->drupalAccounts[$search])) {
        $createUserJob = new CreateUser($mail);
        $createUserJob->setTag($this->getTag());
        $this->scheduleJob($createUserJob);
      }
    }

    foreach ($this->drupalAccounts as $search => $mail) {
      if (empty($this->edgeAccounts[$search])) {
        $jobs = new JobList(TRUE);
        /** @var \Drupal\user\Entity\User $account */
        if (!($account = user_load_by_mail($mail))) {
          $this->recordMessage("User for {$mail} not found.");
          continue;
        }

        $createDeveloperJob = DeveloperCreate::createForUser($account);
        if (!$createDeveloperJob) {
          $this->recordMessage(t('Skipping @mail user, because of incomplete data', [
            '@mail' => $mail,
          ])->render());
          continue;
        }
        $jobs->addJob($createDeveloperJob);

        if ($account->isBlocked()) {
          $jobs->addJob(new DeveloperSetStatus($mail, Developer::STATUS_INACTIVE));
        }

        $jobs->setTag($this->getTag());

        $this->scheduleJob($jobs);
      }
    }

    // Reset these, so they won't be saved to the database, taking up space.
    $this->edgeAccounts = [];
    $this->drupalAccounts = [];

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() : string {
    return t('Synchronizing developers and users')->render();
  }

}
