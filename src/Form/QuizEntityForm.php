<?php

namespace Drupal\quiz\Form;

use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Form\QuizEntityForm\FormDefinition;
use Drupal\quiz\Form\QuizEntityForm\FormValidation;

class QuizEntityForm {

  /** @var QuizEntity */
  private $quiz;

  public function __construct(QuizEntity $quiz) {
    $this->quiz = $quiz;
  }

  public static function staticCallback($op, $quiz_type) {
    $quiz = NULL;
    if ($op === 'add') {
      $values['type'] = $quiz_type;
      $quiz = entity_create('quiz_entity', $values);
    }
    return entity_ui_get_form('quiz_entity', $quiz, $op);
  }

  public function get($form, &$form_state, $op) {
    $def = new FormDefinition($this->quiz);
    return $def->get($form, $form_state, $op);
  }

  public function validate($form, &$form_state) {
    if (t('Delete') === $form_state['clicked_button']['#value']) {
      $path = 'admin' === arg(0) ? 'admin/content/quiz/manage/' . $this->quiz->qid . '/delete' : 'quiz/' . $this->quiz->qid . '/delete';
      drupal_goto($path);
    }
    else {
      $validator = new FormValidation($form, $form_state);
      return $validator->validate();
    }
  }

  public function submit($form, &$form_state) {
    /* @var $quiz QuizEntity */
    $quiz = entity_ui_controller('quiz_entity')->entityFormSubmitBuildEntity($form, $form_state);

    // convert formatted text fields
    $quiz->summary_default_format = $quiz->summary_default['format'];
    $quiz->summary_default = $quiz->summary_default['value'];
    $quiz->summary_pass_format = $quiz->summary_pass['format'];
    $quiz->summary_pass = $quiz->summary_pass['value'];

    // convert value from date widget to timestamp
    $quiz->quiz_open = mktime(0, 0, 0, $quiz->quiz_open['month'], $quiz->quiz_open['day'], $quiz->quiz_open['year']);
    $quiz->quiz_close = mktime(0, 0, 0, $quiz->quiz_close['month'], $quiz->quiz_close['day'], $quiz->quiz_close['year']);

    // Enable revision flag.
    if (!empty($form_state['values']['revision'])) {
      $quiz->is_new_revision = TRUE;
    }

    // Add in created and changed times.
    $quiz->save();

    // Use would like remembering settings
    if (!empty($form_state['values']['remember_settings'])) {
      $this->remeberSettings();
    }

    if ('admin' === arg(0)) {
      $form_state['redirect'] = 'admin/content/quiz';
    }

    if (!$form['#quiz']->qid) {
      drupal_set_message(t('You just created a new quiz. Now you have to add questions to it. This page is for adding and managing questions. Here you can create new questions or add some of your already created questions. If you want to change the quiz settings, you can use the "edit" tab.'));
      $form_state['redirect'] = "quiz/" . $quiz->qid . "/questions";
    }

    // If the quiz don't have any questions jump to the manage questions tab.
    $sql = 'SELECT 1 FROM {quiz_relationship} WHERE quiz_vid = :vid LIMIT 1';
    if (!db_query($sql, array(':vid' => $quiz->vid))->fetchField()) {
      $form_state['redirect'] = 'quiz/' . $quiz->qid . '/questions';
    }
  }

  private function remeberSettings() {

  }

}
