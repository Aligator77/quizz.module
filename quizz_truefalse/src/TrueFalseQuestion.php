<?php

namespace Drupal\quizz_truefalse;

use Drupal\quizz_question\QuestionHandler;

/**
 * Extension of QuizQuestion.
 */
class TrueFalseQuestion extends QuestionHandler {

  protected $body_field_title = 'True/false statement';

  /**
   * {@inheritdoc}
   */
  public function onSave($is_new = FALSE) {
    if ($is_new || $this->question->revision == 1) {
      db_insert('quizz_truefalse')
        ->fields(array(
            'qid'            => $this->question->qid,
            'vid'            => $this->question->vid,
            'correct_answer' => $this->question->correct_answer,
        ))
        ->execute();
    }
    else {
      db_update('quizz_truefalse')
        ->fields(array(
            'correct_answer' => (int) $this->question->correct_answer
        ))
        ->condition('qid', $this->question->qid)
        ->condition('vid', $this->question->vid)
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form) {
    // This space intentionally left blank. :)
  }

  /**
   * Implementation of delete
   *
   * @see QuizQuestion#delete($only_this_version)
   */
  public function delete($only_this_version = FALSE) {
    parent::delete($only_this_version);

    $delete_ans = db_delete('quizz_truefalse_user_answers');
    $delete_ans->condition('question_qid', $this->question->qid);

    $delete_node = db_delete('quizz_truefalse');
    $delete_node->condition('qid', $this->question->qid);

    if ($only_this_version) {
      $delete_ans->condition('question_vid', $this->question->vid);
      $delete_node->condition('vid', $this->question->vid);
    }

    $delete_ans->execute();
    $delete_node->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    if (isset($this->properties)) {
      return $this->properties;
    }
    $props = parent::load();

    $res_a = db_query('SELECT correct_answer '
      . ' FROM {quizz_truefalse} '
      . ' WHERE qid = :qid AND vid = :vid', array(
        ':qid' => $this->question->qid,
        ':vid' => $this->question->vid))->fetchAssoc();

    if (is_array($res_a)) {
      $props = array_merge($props, $res_a);
    }
    $this->properties = $props;
    return $props;
  }

  public function view() {
    $content = parent::view();
    if ($this->viewCanRevealCorrect()) {
      $answer = !empty($this->question->correct_answer) ? t('True') : t('False');
      $content['answers']['#markup'] = '<div class="quiz-solution">' . $answer . '</div>';
      $content['answers']['#weight'] = 2;
    }
    else {
      $content['answers'] = array(
          '#markup' => '<div class="quiz-answer-hidden">' . t('Answer hidden') . '</div>',
          '#weight' => 2,
      );
    }
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnsweringForm(array $form_state = NULL, $result_id) {
    $element = parent::getAnsweringForm($form_state, $result_id);

    // 'tries' is unfortunately required by quiz.module
    $element += array(
        '#type'          => 'radios',
        '#title'         => t('Choose one'),
        '#options'       => array(1 => t('True'), 0 => t('False')),
        '#default_value' => NULL, // prevent default value set to NULL
    );

    if (isset($result_id)) {
      $response = new TrueFalseResponse($result_id, $this->question);
      $default = $response->getResponse();
      $element['#default_value'] = is_null($default) ? NULL : $default;
    }

    return $element;
  }

  /**
   * Question response validator.
   */
  public function validateAnsweringForm(array &$form, array &$form_state = NULL) {
    if (is_null($form_state['values']['question'][$this->question->qid]['answer'])) {
      form_set_error('', t('You must provide an answer.'));
    }
  }

  /**
   * Implementation of getCreationForm
   *
   * @see QuizQuestion#getCreationForm($form_state)
   */
  public function getCreationForm(array &$form_state = NULL) {
    $form['correct_answer'] = array(
        '#type'          => 'radios',
        '#title'         => t('Correct answer'),
        '#options'       => array(
            1 => t('True'),
            0 => t('False'),
        ),
        '#default_value' => isset($this->question->correct_answer) ? $this->question->correct_answer : 1,
        '#required'      => TRUE,
        '#weight'        => -4,
        '#description'   => t('Choose if the correct answer for this question is "true" or "false".')
    );
    return $form;
  }

  /**
   * Implementation of getMaximumScore
   *
   * @see QuizQuestion#getMaximumScore()
   */
  public function getMaximumScore() {
    return 1;
  }

  /**
   * Get the answer to this question.
   *
   * This is a utility function. It is not defined in the interface.
   */
  public function getCorrectAnswer() {
    return db_query('SELECT correct_answer FROM {quizz_truefalse} WHERE qid = :qid AND vid = :vid', array(':qid' => $this->question->qid, ':vid' => $this->question->vid))->fetchField();
  }

}
