<?php

namespace Drupal\quiz\Controller\Admin;

use Drupal\quiz\Entity\QuizEntity;

class QuizQuestionAdminController {

  private $quiz;

  public function __construct(QuizEntity $quiz) {
    $this->quiz = $quiz;
  }

  public static function staticCallback(QuizEntity $quiz) {
    $obj = new self($quiz);

    module_load_include('pages.inc', 'quiz');

    return array(
        '#type'            => 'vertical_tabs',
        'question_admin'   => array(
            '#type'  => 'fieldset',
            '#title' => t('Manage questions'),
            '#value' => '',
            'links'  => array(
                '#type'        => 'fieldset',
                '#title'       => t('Create new question'),
                '#collapsible' => TRUE,
                '#collapsed'   => TRUE,
                '#value'       => '',
                'links'        => array(
                    '#theme' => 'item_list',
                    '#items' => $obj->getQuestionAddingLinks(),
                ),
            ),
            'form'   => drupal_get_form('quiz_questions_form', $quiz),
        ),
        'global_questions' => array(
            '#type'  => 'fieldset',
            '#title' => t('Question bank'),
            '#value' => views_get_view('quiz_question_bank')->preview(),
        ),
    );
  }

  public function getQuestionAddingLinks() {
    $items = array();

    foreach (quiz_get_question_types() as $type => $info) {
      if (!node_access('create', $type)) {
        continue;
      }

      $text = $info['name'];
      $url = 'node/add/' . str_replace('_', '-', $type);
      $items[] = l($text, $url, array('query' => drupal_get_destination()));
    }

    if (empty($items)) {
      $items[] = t('You have not enabled any question type module or no has permission been given to create any question.');
    }

    return $items;
  }

}
