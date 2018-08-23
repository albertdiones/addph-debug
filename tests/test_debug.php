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


   /**
    * @test
    * */
   public function test_print_data_string() {

      $label = "message";
      $test_string = "Hello World";

      ob_start();
      debug::print_data($label,$test_string);
      $result = ob_get_clean();


      $this->assertRegexp('/'.preg_quote($label,'/').'/',$result);
      $this->assertRegexp('/'.preg_quote($test_string,'/').'/',$result);
   }

   /**
    * @test
    * */
   public function test_print_data_array() {

      $label = "message";
      $test_strings = array("Hello", "World");

      ob_start();
      debug::print_data($label,$test_strings);
      $result = ob_get_clean();


      $this->assertRegexp('/'.preg_quote($label,'/').'/',$result);
      $this->assertRegexp('/'.preg_quote($test_strings[0],'/').'/',$result);
      $this->assertRegexp('/'.preg_quote($test_strings[1],'/').'/',$result);
   }

}
?>
