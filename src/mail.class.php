<?php

namespace addph\debug;

/**
 * mailing debug class
 *
 * <code>
 * CLASS debug_mail EXTENDS add_debug_mail {
 *    function mail_var_dump_recepient() {
 *       return 'john@gmail.com, doe@gmail.com';
 *    }
 * }
 * </code>
 * @author albertdiones@gmail.com
 *
 * @package ADD MVC Debuggers
 * @since ADD MVC 0.0
 * @version 0.0
 */
CLASS mail EXTENDS debug {

   /**
    * The var_dump strings
    * @var array
    *
    * @since ADD MVC 0.0
    */
   public $mail_var_dumps      = array();

   /**
    * The var_dump file lines
    *
    * @since ADD MVC 0.0
    */
   public $mail_var_dump_lines = array();

   /**
    * mail callback
    */
   public static $mail_send_callback = "mail";

   /**
    * Singleton var
    */
   public static $singleton;

   private function __construct() {

   }

   public static function singleton() {
      if (!isset(static::$singleton)) {
         static::$singleton = new self();
      }
      return static::$singleton;
   }

   /**
    * mail_var_dump
    * sends var_dump info to the debug mail
    * @see __destruct()
    * @author albertdiones@gmail.com
    */
   static function var_dump(/* $arg1, $arg2, $argn... */) {

      $args = func_get_args();

      call_user_func_array(array(static::singleton(),'dump'),$args);

      return $args[0];
   }

   public function dump() {
      $args = func_get_args();

      $var_dump           = self::return_var_dump($args);
      $caller_line        = self::caller_file_line();
      static::singleton()->mail_var_dumps[]  = "$caller_line\r\n\r\n$var_dump";

      if (!in_array($caller_line,static::singleton()->mail_var_dump_lines)) {
         static::singleton()->mail_var_dump_lines[] = $caller_line;
      }
   }

   /**
    * At the end of the script send all mail var dumps
    */
   public function send() {
      if ($this->mail_var_dumps) {
         $var_dump = self::current_url()."\r\n";
         $var_dump .= implode("\r\n\r\n\r\n\r\n ------------- \r\n\r\n\r\n\r\n",$this->mail_var_dumps);
         $caller_locations = implode(" ",$this->mail_var_dump_lines);
         /**
          * Not html
          *
          * mail()
          *
          */
         call_user_func_array(
            static::$mail_send_callback,
            array(
               $this->mail_var_dump_recepient(),
               "DEBUG ".$caller_locations,$var_dump
            )
         );
         self::reset();
      }
   }

   /**
    * Returns the mail var dump recepients
    * @return string recepients of debug email
    */
   public function mail_var_dump_recepient() {
      return static::config()->developer_emails;
   }

   /**
    * Resets mail var dumps
    */
   public function reset() {
      $this->mail_var_dumps = array();
      $this->mail_var_dump_lines = array();
   }

   /**
    * Magic function __destruct()
    * @see http://www.php.net/manual/en/language.oop5.decon.php#object.destruct
    */
   function __destruct() {
      try {
         self::send();
      }
      catch (e_add $e) {
         $e->handle_exception();
      }
   }

   /**
    * Prints mysql error
    *
    * @deprecated unused
    *
    * @since ADD MVC 0.0
    */
   public function mysql_error() {
      self::singleton();
      if ($error = mysql_error()) {
         self::mail_var_dump($error);
      }
   }
}
