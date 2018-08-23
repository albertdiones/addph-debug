<?php

namespace addph\debug;

/**
 * Abstract mailing debug class
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
ABSTRACT CLASS debug_mail EXTENDS debug {

   /**
    * The var_dump strings
    * @var array
    *
    * @since ADD MVC 0.0
    */
   static $mail_var_dumps      = array();

   /**
    * The var_dump file lines
    *
    * @since ADD MVC 0.0
    */
   static $mail_var_dump_lines = array();

   /**
    * mail_var_dump
    * sends var_dump info to the debug mail
    * @see __destruct()
    * @author albertdiones@gmail.com
    */
   static function var_dump(/* $arg1, $arg2, $argn... */) {

      $args = func_get_args();

      $var_dump           = self::return_var_dump($args);
      $caller_line        = self::caller_file_line();
      self::$mail_var_dumps[]  = "$caller_line\r\n\r\n$var_dump";

      if (!in_array($caller_line,self::$mail_var_dump_lines)) {
         self::$mail_var_dump_lines[] = $caller_line;
      }

      return $args[0];
   }

   /**
    * At the end of the script send all mail var dumps
    */
   static function send() {
      if (self::$mail_var_dumps) {
         $var_dump = self::current_url()."\r\n";
         $var_dump .= implode("\r\n\r\n\r\n\r\n ------------- \r\n\r\n\r\n\r\n",self::$mail_var_dumps);
         $caller_locations = implode(" ",self::$mail_var_dump_lines);
         /**
          * Not html
          */
         mail(self::mail_var_dump_recepient(),"DEBUG ".$caller_locations,$var_dump);
         self::reset();
      }
   }

   /**
    * Returns the mail var dump recepients
    * @return string recepients of debug email
    */
   abstract public function mail_var_dump_recepient();

   /**
    * Resets mail var dumps
    */
   static function reset() {
      self::$mail_var_dumps = array();
      self::$mail_var_dump_lines = array();
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
   static function mysql_error() {
      self::singleton();
      if ($error = mysql_error()) {
         self::mail_var_dump($error);
      }
   }
}
