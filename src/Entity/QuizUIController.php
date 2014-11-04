<?php

namespace Drupal\quiz\Entity;

use EntityDefaultUIController;

class QuizUiController extends EntityDefaultUIController {

  public function hook_menu() {
    $items = parent::hook_menu();
    $items['admin/content/quiz']['type'] = MENU_LOCAL_TASK;

    $this->addQuizAddLinks($items);
    $this->addQuizCRUDItems($items);
    $this->addQuizTabsItems($items);
    $this->addQuizTakeItems($items);

    return $items;
  }

  private function addQuizCRUDItems(&$items) {
    $items['quiz/%quiz_entity_single'] = array(
        'title callback'   => 'entity_class_label',
        'title arguments'  => array(1),
        'access callback'  => 'quiz_entity_access_callback',
        'access arguments' => array('view'),
        'page callback'    => 'Drupal\quiz\Controller\QuizEntityViewController::staticCallback',
        'page arguments'   => array(1),
    );

    $items['quiz/%quiz_entity_single/view'] = array(
        'title'  => 'View',
        'type'   => MENU_DEFAULT_LOCAL_TASK,
        'weight' => -10,
    );

    // Define menu item structure for /quiz/%/edit
    $items['quiz/%entity_object/edit'] = $items['admin/content/quiz/manage/%entity_object'];
    $items['quiz/%entity_object/edit']['title'] = 'Settings';
    unset($items['quiz/%entity_object/edit']['title callback'], $items['quiz/%entity_object/edit']['title arguments']);
    $items['quiz/%entity_object/edit']['type'] = MENU_LOCAL_TASK;
    $items['quiz/%entity_object/edit']['page arguments'][1] = 1;
    $items['quiz/%entity_object/edit']['access arguments'][2] = 1;

    // Define menu item structure for /quiz/%/delete
    $items['quiz/%entity_object/delete'] = array(
        'load arguments'   => array('quiz_entity'),
        'page callback'    => 'drupal_get_form',
        'page arguments'   => array('quiz_entity_operation_form', 'quiz_entity', 1, 'delete'),
        'access callback'  => 'entity_access',
        'access arguments' => array('delete', 'quiz_entity', 1),
        'file'             => 'includes/entity.ui.inc',
    );
  }

  private function addQuizTabsItems(&$items) {
    // Define menu structure for /quiz/%/revisions
    $items['quiz/%entity_object/revisions'] = array(
        'title'            => 'Revisions',
        'type'             => MENU_LOCAL_TASK,
        'access callback'  => 'entity_access',
        'access arguments' => array('update', 'quiz_entity', 1),
        'page callback'    => 'Drupal\quiz\Controller\Admin\QuizRevisionsAdminController::staticCallback',
        'page arguments'   => array(1),
        'load arguments'   => array('quiz_entity'),
    );

    // Define menu structure for /quiz/%/questions
    $items['quiz/%entity_object/questions'] = array(
        'title'            => 'Questions',
        'type'             => MENU_LOCAL_TASK,
        'access callback'  => 'entity_access',
        'access arguments' => array('update', 'quiz_entity', 1),
        'page callback'    => 'Drupal\quiz\Controller\Admin\QuizQuestionAdminController::staticCallback',
        'page arguments'   => array(1),
        'load arguments'   => array('quiz_entity'),
        'weight'           => 5,
    );

    // Define menu structure for /quiz/%/results
    $items['quiz/%entity_object/results'] = array(
        'title'            => 'Results',
        'type'             => MENU_LOCAL_TASK,
        'access callback'  => 'entity_access',
        'access arguments' => array('update', 'quiz_entity', 1),
        'page callback'    => 'Drupal\quiz\Controller\Admin\QuizResultsAdminController::staticCallback',
        'page arguments'   => array(1),
        'load arguments'   => array('quiz_entity'),
        'weight'           => 6,
    );

    // Define menu structure for /quiz/%/results
    $items['quiz/%entity_object/my-results'] = array(
        'title'            => 'My results',
        'type'             => MENU_LOCAL_TASK,
        'access callback'  => 'entity_access',
        'access arguments' => array('update', 'quiz_entity', 1),
        'page callback'    => 'Drupal\quiz\Controller\QuizMyResultsController::staticCallback',
        'page arguments'   => array(1),
        'load arguments'   => array('quiz_entity'),
        'weight'           => 6,
    );

    $items['quiz/%entity_object/questions/term_ahah'] = array(
        'type'             => MENU_CALLBACK,
        'access callback'  => 'entity_access',
        'access arguments' => array('create', 'quiz_entity', 1),
        'page callback'    => 'Drupal\quiz\Form\QuizCategorizedForm::categorizedTermAhah',
        'load arguments'   => array('quiz_entity'),
    );
  }

  private function addQuizAddLinks(&$items) {
    // Change path from /admin/content/quiz/add -> /quizz/add
    $items['quiz/add'] = $items['admin/content/quiz/add'];
    unset($items['admin/content/quiz/add']);

    // Menu items for /quiz/add/*
    if (($types = quiz_get_types()) && (1 < count($types))) {
      $items['quiz/add'] = array(
          'title'           => 'Add ' . QUIZ_NAME,
          'access callback' => 'quiz_can_create_quiz_entity',
          'page callback'   => 'Drupal\quiz\Controller\QuizEntityAddController::staticCallback',
      );

      foreach (array_keys($types) as $name) {
        $items["quiz/add/{$name}"] = array(
            'title callback'   => 'entity_ui_get_action_title',
            'title arguments'  => array('add', 'quiz_entity'),
            'access callback'  => 'entity_access',
            'access arguments' => array('create', 'quiz_entity'),
            'page callback'    => 'Drupal\quiz\Form\QuizEntityForm::staticCallback',
            'page arguments'   => array('add', $name),
            'file path'        => drupal_get_path('module', 'quiz'),
            'file'             => 'quiz.pages.inc',
        );
      }
    }
  }

  private function addQuizTakeItems(&$items) {
    // Define menu item structure for /quiz/%/take
    $items['quiz/%entity_object/take'] = array(
        'load arguments'   => array('quiz_entity'),
        'page callback'    => 'Drupal\quiz\Controller\QuizTakeController::staticCallback',
        'page arguments'   => array(1),
        'access arguments' => array('access quiz'),
    );

    // Define menu item structure for /quiz/%/take/%
    $items['quiz/%entity_object/take/%question_number'] = array(
        'load arguments'   => array('quiz_entity'),
        'page callback'    => 'Drupal\quiz\Controller\QuizTakeQuestionController::staticCallback',
        'page arguments'   => array(1, 3),
        'access callback'  => 'quiz_access_question',
        'access arguments' => array(1, 3),
    );

    $items['quiz/%entity_object/take/%question_number/feedback'] = array(
        'title'            => 'Feedback',
        'load arguments'   => array('quiz_entity'),
        'page callback'    => 'Drupal\quiz\Controller\QuizQuestionFeedbackController::staticCallback',
        'page arguments'   => array(1, 3),
        'access callback'  => 'quiz_question_feedback_access',
        'access arguments' => array(1, 3),
    );
  }

}
