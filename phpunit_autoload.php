<?php

/**
 * @file
 * Class autoloader.
 */

spl_autoload_register(function ($class) {
  if (substr($class, 0, strlen('Drupal\\webform_d7_to_d8\\')) == 'Drupal\\webform_d7_to_d8\\') {
    $class = preg_replace('/^Drupal\\\\webform_d7_to_d8\\\\/', '', $class);
    $path = 'src/' . str_replace('\\', '/', $class) . '.php';
    require_once $path;
  }
});
