<?php
use PHPUnit\Framework\TestCase;
require 'src/mail.class.php';
require 'debug_test.php';
use addph\debug\mail as debug_mail;

class mail_test extends debug_test
{

   public static $debug_class = '\addph\debug\mail';
   
   public static $mail_arguments;

   public function setUp() {
      debug_mail::set_config(
         (object) array(
            'root_dir' => realpath('./'),

            'developer_ips'      => array(
            ),
            'developer_emails'   => array('albert@add.ph','albertdiones@gmail.com'),
            'content_type' => 'text/plain'
         )
      );

      debug_mail::$mail_send_callback = array(__CLASS__,"mail");
   }

   public function tearDown() {

   }

   public static function mail() {
      static::$mail_arguments = func_get_args();
   }

   /**
    * @test
    * */
   public function test_setup() {
      $debug_mail = debug_mail::singleton();

      $test_strings = array("Hello", "World");

      $debug_mail->var_dump($test_strings);

      $debug_mail->send();

      $this->assertNotCount(0,static::$mail_arguments);

      $this->assertRegexp('/'.preg_quote($test_strings[0],'/').'/',static::$mail_arguments[2]);
      $this->assertRegexp('/'.preg_quote($test_strings[1],'/').'/',static::$mail_arguments[2]);

   }

}
?>
