<?php

namespace Drupal\quiz\Helper;

use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Helper\HookImplementation\HookEntityInfo;
use Drupal\quiz\Helper\HookImplementation\HookFieldExtraFields;
use Drupal\quiz\Helper\HookImplementation\HookMenu;
use Drupal\quiz\Helper\HookImplementation\HookQuizFinished;
use Drupal\quiz\Helper\HookImplementation\HookQuizScored;
use Drupal\quiz\Helper\HookImplementation\HookUserCancel;

class HookImplementation {

  private $hookMenu;
  private $hookEntityInfo;
  private $hookFieldExtraFields;
  private $hookQuizFinished;
  private $hookQuizScored;
  private $hookUserCancel;

  /**
   * @return HookMenu
   */
  public function getHookMenu() {
    if (null === $this->hookMenu) {
      $this->hookMenu = new HookMenu();
    }
    return $this->hookMenu;
  }

  public function setHookMenu($hookMenu) {
    $this->hookMenu = $hookMenu;
    return $this;
  }

  /**
   * @return HookEntityInfo
   */
  public function getHookEntityInfo() {
    if (null === $this->hookEntityInfo) {
      $this->hookEntityInfo = new HookEntityInfo();
    }
    return $this->hookEntityInfo;
  }

  public function setHookEntityInfo($hookEntityInfo) {
    $this->hookEntityInfo = $hookEntityInfo;
    return $this;
  }

  /**
   * @return HookFieldExtraFields
   */
  public function getHookFieldExtraFields() {
    if (null === $this->hookFieldExtraFields) {
      $this->hookFieldExtraFields = new HookFieldExtraFields();
    }
    return $this->hookFieldExtraFields;
  }

  public function setHookFieldExtraFields($hookFieldExtraFields) {
    $this->hookFieldExtraFields = $hookFieldExtraFields;
    return $this;
  }

  /**
   * @param QuizEntity $quiz
   * @param array $score
   * @param array $session_data
   * @return HookQuizFinished
   */
  public function getHookQuizFinished($quiz, $score, $session_data) {
    if (NULL === $this->hookQuizFinished) {
      $this->hookQuizFinished = new HookQuizFinished($quiz, $score, $session_data);
    }
    return $this->hookQuizFinished;
  }

  public function setHookQuizFinished($hookQuizFinished) {
    $this->hookQuizFinished = $hookQuizFinished;
    return $this;
  }

  /**
   * @return HookQuizScored
   */
  public function getHookQuizScored($quiz, $score, $result_id) {
    if (NULL === $this->hookQuizScored) {
      $this->hookQuizScored = new HookQuizScored($quiz, $score, $result_id);
    }
    return $this->hookQuizScored;
  }

  public function setHookQuizScored($hookQuizScored) {
    $this->hookQuizScored = $hookQuizScored;
    return $this;
  }

  /**
   * @return HookUserCancel
   */
  public function getHookUserCancel($account, $method) {
    if (NULL === $this->hookUserCancel) {
      $this->hookUserCancel = new HookUserCancel($account, $method);
    }
    return $this->hookUserCancel;
  }

  public function setHookUserCancel($hookUserCancel) {
    $this->hookUserCancel = $hookUserCancel;
    return $this;
  }

}
