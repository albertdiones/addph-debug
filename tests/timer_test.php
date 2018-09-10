<?php
require 'init.php';
require __DIR__.'/../src/timer.class.php';
require 'debug_test.php';
use addph\debug\timer;

class timer_test extends debug_test
{

   /**
    * @test
    * */
   public function test_setup() {
      $timer = timer::start();

      $this->assertInstanceOf('\addph\debug\timer',$timer);
   }
   /**
    * @test
    * */
   public function test_print_lap() {
      $timer = timer::start();
      usleep(120000);
      $timer->lap();

      $label = "Test timer";

      ob_start();
      $timer->print_lap($label);
      $result = ob_get_clean();

      $this->assertRegExp('/'.preg_quote($label,'/').'/',$result);
      $this->assertRegExp('/12/',$result);
   }
   /**
    * @test
    * */
   public function test_print_all_laps() {
      $timer = timer::start();
      usleep(120000);
      $timer->lap();
      usleep(130000);
      $timer->lap();
      usleep(140000);
      $timer->lap();

      ob_start();
      $timer->print_all_laps();
      $result = ob_get_clean();

      $this->assertRegExp('/12/',$result);
      $this->assertRegExp('/13/',$result);
      $this->assertRegExp('/14/',$result);
   }

}
?>
