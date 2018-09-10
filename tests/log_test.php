<?php
require 'init.php';
require __DIR__.'/../src/log.class.php';
require 'debug_test.php';
use addph\debug\log;

class log_test extends debug_test
{

   static $debug_class = '\addph\debug\log';

   /**
    * @test
    * */
   public function test_setup() {
      log::$file = log::config()->root_dir.'/log_test.log.txt';

      $this->assertEquals(log::$file,log::file());
   }

   /**
    * @test
    * */
   public function test_log_string() {
      log::$file = log::config()->root_dir.'/log_test1.log.txt';

       $test_string = "Hello World#".rand(1,9999);

       ob_start();
       log::var_dump($test_string);
       $result = ob_get_clean();
       if ($result) {
           echo "Got output: ". $result;
       }

       $this->assertEmpty($result);


       $this->assertRegexp('/'.preg_quote($test_string,'/').'/',file_get_contents(log::file()));
   }

}
?>
