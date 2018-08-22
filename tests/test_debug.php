<?php
use PHPUnit\Framework\TestCase;
require 'debug.class.php';
use addph\debug\debug;

class debug_test extends TestCase
{

   static $app_config;

   public function setUp() {

      debug::set_config(
         (object) array(
            'root_dir' => realpath('./'),

            'app_namespace' => 'addph',

            'environment_status' => 'development',
            'version'            => '1.1',
            'developer_ips'      => array(
            ),
            'developer_emails'   => array('albert@add.ph','albertdiones@gmail.com'),
            'content_type' => 'text/plain'
         )
      );
   }

   public function tearDown() {

   }


   /**
    * @test
    * */
   public function test_is_developer() {
      $this->assertEquals(true,debug::is_developer());
   }


   /**
    * @test
    * */
   public function test_var_dump_string() {

      $test_string = "Hello World";

      ob_start();
      debug::var_dump($test_string);
      $result = ob_get_clean();


      $this->assertRegexp('/'.preg_quote($test_string,'/').'/',$result);
   }

}
?>
