<?php

namespace addph\debug\test;

use PHPUnit\Framework\TestCase;
use addph\debug\debug;

ABSTRACT CLASS base EXTENDS TestCase {
   public function setUp() {

      $class_name = static::$debug_class;
      $class_name::set_config(
         (object) array(
            'root_dir' => realpath('./'),

            'developer_ips'      => array(
            ),
            'developer_emails'   => array('albert@add.ph','albertdiones@gmail.com'),
            'content_type' => 'text/plain'
         )
      );
   }

   public function tearDown() {

   }
}