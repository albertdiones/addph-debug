<?php
namespace addph\debug;
/**
 * Debugging class
 *
 * <code>
 * add_debug::var_dump($_SERVER)
 * </code>
 *
 * @author albertdiones@gmail.com
 *
 * @package ADD MVC Debuggers
 * @since ADD MVC 0.0
 * @version 0.1
 */
ABSTRACT CLASS debug {

   /**
    * (var) dumping flag
    *
    * @var boolean
    *
    */
   protected static $dumping = false;

   /**
   /**
    * @var application's config
    */
   public static $app_config;


   /**
    * Max depth of recursion, to avoid infinite loops on recursive referrences
    *
    */
   public static $max_indentation = 15;

   /**
    * The config object for use on the functions below
    */
   public static function config() {
      if (!isset(static::$app_config)) {
          static::$app_config = (object) array(
              'root_dir' => dirname(dirname(dirname(dirname(dirname(__FILE__)))))
          );
      }
      return static::$app_config;
   }
   /**
    * The config object for use on the functions below
    */
   public static function set_config($config) {
      return static::$app_config = $config;
   }

   /**
    * echo only if IP matched
    *
    * @param string $arg the string to echo
    *
    * @author albertdiones@gmail.com
    * @since ADD MVC 0.0
    */
   static function restricted_echo($arg) {

      if (static::current_user_allowed()) {
         echo $arg;
      }

   }

   public static function is_cli() {
       return php_sapi_name() == "cli";
   }


   /**
    * is_developer()
    *
    * Checks if the user is developer according to his/her IP
    *
    */
   public static function is_developer() {
      if (static::is_cli()) {
         return true;
      }

      # Fix for issue #6
      if (static::current_ip_in_network())
         return true;

      if (isset(static::config()->developer_ips))
         return in_array(static::current_user_ip(), (array) static::config()->developer_ips);
      else
         return false;
   }

   /**
    * ip_in_network(string $ip)
    * Checks if the IP is within the network of the server
    * @param string $ip the IP to check
    */
   public static function ip_in_network($ip) {
      if (preg_match('/^((10\.\d+|192\.168)\.\d+\.\d+|\:\:1|127\.0\.0\.1)$/',$ip)) {
         return true;
      }
      else {
         return false;
      }
   }


   /**
    * bool current_ip_in_network(void)
    * Checks if the current user's ip is in the network
    * @uses ip_in_network
    */
   public static function current_ip_in_network() {
      return static::ip_in_network(static::current_user_ip());
   }


   /**
    * string current_user_ip()
    * Gets the IP of the user
    *
    * @since ADD MVC 0.1
    */
   public static function current_user_ip() {

      $ip_server_var_keys = array(
         'HTTP_CLIENT_IP',
         'HTTP_X_FORWARDED_FOR'
      );

      foreach ($ip_server_var_keys as $key) {
         if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
         }
      }

      return @$_SERVER['REMOTE_ADDR'];

   }

   /**
    * Declare this on extensions to set the allowed IPs or allowed users to see the debug prints
    * @return boolean true if allowed false if not
    * @since ADD MVC 0.0
    */
   static function current_user_allowed() {
      return static::is_developer();
   }




   /**
    * Prints request variables and sessions variables
    * @since ADD MVC 0.0
    */
   static function print_request() {

      $request = array(
         "url" => static::current_url(),
         "get" => $_GET,
         "post" => $_POST,
         "cookie" => $_COOKIE,
         "request" => $_REQUEST,
         "session" => $_SESSION
      );

      static::html_print($request,"Request");
   }

   /**
    * Returns the current url
    *
    * @since ADD MVC 0.0
    */
   static function current_url() {
      if (isset($_SERVER['HTTP_HOST'])) {
         return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      }
      else {
         return null;
      }
   }

   /**
    * Prints/Returns location backtrace
    *
    * @param boolean $return true to return instead of echo
    *
    * @since ADD MVC 0.0
    */
   static function lines_backtrace($return=false) {
      $traces = debug_backtrace();
      $file_lines = array();

      foreach ($traces as $trace) {
         $file_line = array(
            'file' => $trace['file'],
            'line' => $trace['line']
         );
         $file_lines[]  = $file_line;
         if (count(array_keys($file_lines,$file_line))>20) {
            die("Possible infinite loop detected");
         }
      }
      if ($return)
         return $file_lines;
      else {
         foreach ($file_lines as &$file_line) {
            $file_line = implode(":",$file_line);
         }
         static::html_print($file_lines,"Location");
      }
   }

   /**
    * Returns the file and line number of the caller of the function
    * @since ADD MVC 0.0
    */
   static function caller_file_line() {
      $backtrace = static::protected_caller_backtrace();

      if (!$backtrace) {
         return "Unknown Location";
      }

      $file_line = @$backtrace['file'].':'.@$backtrace['line'];

      $config = static::config();

      if (isset($config->root_dir)) {
         $file_line = preg_replace('/^'.preg_quote($config->root_dir,'/').'\//','',$file_line);
      }
      return $file_line;

   }

   /**
    * Returns the debug info of the caller
    * @since ADD MVC 0.7
    */
   public static function caller_backtrace() {
      return static::protected_caller_backtrace();
   }

   /**
    * Gets the caller backtrace
    *
    */
   protected static function protected_caller_backtrace() {
      $backtraces = debug_backtrace(@DEBUG_BACKTRACE_IGNORE_ARGS);

      $caller_backtrace = $backtraces[2];

      return $caller_backtrace;

   }


   /**
    * content_type()
    */
   public static function content_type() {
      if ($config = static::config() && isset($config->content_type)) {
          return $config->content_type;
      }
      else if (static::is_cli()) {
          return "text/plain";
      }
      else if (isset($_SERVER['CONTENT_TYPE'])) {
          return $_SERVER['CONTENT_TYPE'];
      }
      else {
          return "text/html";
      }
   }


   /**
    * XMP Var Dump
    * var_dump with <xmp>
    * @author albertdiones@gmail.com
    * @since ADD MVC 0.0
    */
   static function var_dump() {
      $args = func_get_args();
      if (!$args) {
         $args[0] = static::get_declared_globals();
      }
      $var = call_user_func_array('static::return_var_dump',$args);
      if (static::content_type() == 'text/plain') {
         $output="\r\nFile Line:".static::caller_file_line()."\r\n".$var."\r\n";
      }
      else {
         $output="<div style='clear:both'><b>".static::caller_file_line()."</b><xmp>".$var."</xmp></div>";
      }
      static::restricted_echo($output);
      return $args[0];
   }

   /**
    * return_var_dump()
    * get var dump function
    * @param mixed $args
    * @since ADD MVC 0.0
    */
   final public static function return_var_dump($args) {
      ob_start();
      static::$dumping = true;
      call_user_func_array('var_dump',func_get_args());

      /**
       * Debugging for https://code.google.com/p/add-mvc-framework/issues/detail?id=93
       *
      throw new Exception("test");
      die();
       */

      $var = ob_get_clean();
      static::$dumping = false;
      return $var;
   }



   /**
    * Dumping flag
    *
    */
   public static function dumping() {
      return static::$dumping;
   }

   /**
    * return pretty var dump
    * get var dump function
    *
    *
    *
    * @since ADD MVC 0.0
    */
   public static function return_pretty_var_dump() {
      static $indentation = 0;
      static $indentation_length = 8;
      static $type_value_indentation = 1;
      static $value_indentation = 0;
      static $indentation_char = "\t";
      $dump = "";

      foreach (func_get_args() as $arg) {
         # array
         if (is_array($arg)) {
            if ($arg) {
               $dump .= "{";
               $indentation++;
               $pre_index_string = "* ";
               $max_key_length = max(array_map("strlen",array_keys($arg))) + strlen($pre_index_string);
               $value_indentation = ceil(($max_key_length/$indentation_length)+0.1);
               $current_value_indentation = $value_indentation;
               foreach ($arg as $index => $value) {

                  $index_string = $pre_index_string.$index;
                  $index_value_indentation = $current_value_indentation - floor(strlen($index_string)/$indentation_length);
                  $dump .= "\r\n".str_repeat("$indentation_char",$indentation).$index_string;
                  $dump .= str_repeat("$indentation_char",$index_value_indentation);
                  /**
                   * add_debug::pretty_var_dump() causes infinite loop on self referrences
                   * @see https://code.google.com/p/add-mvc-framework/issues/detail?id=51
                   */
                  if ( is_array( $value ) ) {
                     if (
                        $indentation < static::$max_indentation
                     ) {
                        $dump .= static::return_pretty_var_dump($value);
                     }
                     else {
                        $dump .= "*...*";
                     }
                  }
                  else {
                     $dump .= static::return_pretty_var_dump($value);
                  }
                  $index_value_indentation = 0;
               }
               $value_indentation = 0;
               $dump .= "\r\n";
               $indentation--;
               $dump .= str_repeat("$indentation_char",$indentation)."}\r\n";
            }
            else {
               $dump .= "{}\r\n";
            }
         }
         # String
         else if (is_string($arg)) {
            $type_string = "str(".strlen($arg).")";
            $dump .= $type_string;
            if (strlen($arg) > 70) {
               $indentation_string = str_repeat("$indentation_char",
                  $indentation
                  + $value_indentation
               );
               $dump .= " (word-wrapped)\r\n";
               $dump .= $indentation_string.wordwrap($arg,70,"\r\n".$indentation_string)."\r\n";
            }
            else {
               $indentation_string = str_repeat("$indentation_char",$type_value_indentation - floor(strlen($type_string) / $indentation_length) + 1 );
               $dump .= "$indentation_string\"".$arg."\"";
            }
         }
         else if (is_int($arg) || is_float($arg) || is_bool($arg)) {
            $type_string = gettype($arg);
            $dump .= $type_string ;
            $dump .= str_repeat("$indentation_char",$type_value_indentation - floor(strlen($type_string)/$indentation_length) + 1 );
            if (is_bool($arg)) {
               $dump .= $arg ? "true" : "false";
            }
            else {
               $dump .= $arg;
            }
         }
         else if (is_object($arg)) {
            $type_string = get_class($arg);
            $dump .= $type_string ;
            $dump .= str_repeat("$indentation_char",$type_value_indentation - floor(strlen($type_string)/$indentation_length) + 1 );
            $dump .= static::return_pretty_var_dump(get_object_vars($arg));
         }
         else {
            ob_start();
            call_user_func_array('var_dump',func_get_args());
            $dump = trim(ob_get_clean());
         }
         $dump .= "\r\n";
      }

      return $dump;
   }


   /**
    * make a list out of $arg
    *
    * @param mixed $arg the data to print
    * @param string $name the title of this list
    *
    * @since ADD MVC 0.0
    */
   static function html_print($arg,$name) {
      ob_start();
      if (is_array($arg) || is_object($arg)) {
         echo("<ul class='html_print_ul html_print'><li>
             <b onclick='$(this).parents(\"li:eq(0)\").find(\"ul\").slideToggle()' style='cursor:pointer;text-decoration:underline;'>$name</b> <small>(".gettype($arg)."{".count($arg)."})</small>");

         foreach ($arg as $i=>$value) {
            static::html_print($value,$i);
            unset($value);
         }

      }
      else {
         echo("
           <ul class='html_print_one_item html_print'><li>
           <b>$name</b> <small>(".gettype($arg).")</small>");
         echo(": ");

         if (filter_var($arg,FILTER_VALIDATE_URL)) {
            echo("<a href='".htmlspecialchars($arg)."' target='_blank' >");
            if (preg_match('/(?i)\.(jpg|gif|png)/',$arg))
               echo("<img src='".htmlspecialchars($arg)."' border=0 onmouseover='$(this).css(\"height\",\"\")' onmouseout='$(this).css(\"height\",20)' style='height:20px;color:#888;'/>");
            else
               echo("$arg");
            echo("</a>");
         }
         else
            echo("$arg");
      }
      echo("</li></ul>");
      $output = ob_get_clean();
      static::restricted_echo($output);
   }

   /**
    * evaluates $var and prints $var and the value it returns
    *
    * @param string $var the command to eval
    *
    * @since ADD MVC 0.0
    */
   static function print_eval($var) {
      $var=preg_replace('/\$(\w+)/','$GLOBALS[$1]',$var);
      echo "<b>".htmlspecialchars($var)."</b> ";

      if (strpos($var,"return ")===false)
         $var = eval("return $var;");
      else
         $var = eval($var);

      if (is_string($var)) {
         echo $var."<br />";
      }
      else {
         echo "<xmp>".static::return_var_dump(array($var))."</xmp><br />";
      }

   }

   /**
    * Prints array into a HTML table
    *
    * @param array $array the array to convert
    *
    * @since ADD MVC 0.0
    */
   static function print_array_table($array) {
      if ($array) {
         $fields = array_keys($array[0]);
         echo "<table style='min-width:100%;border:1px solid #ccc;background:#e8e8e8' cellspacing=0 cellpadding=5 >";
         echo "<tr>";
         foreach ($fields as $field) {
            echo "<td style='font-weight:bold;background:e0e0e0;'>$field</td>";
         }
         echo "</tr>";
         $count = 0;
         foreach ($array as $item) {
            if ($count % 2 == 0) {
               $background = "#e0e0e0";
            }
            else {
               $background = "#d8d8d8";
            }
            echo "<tr>";
            foreach ($fields as $field) {
               echo "<td style='border:1px solid #e0e0e0;background:$background'>".$item[$field]."</td>";
            }
            echo "</tr>";
            $count++;
         }
         echo "</table>";
      }
   }

   /**
    * deprecated_file() function
    * Call this on deprecated files
    * @since ADD MVC 0.0
    */
   public static function deprecated_file() {
      $locations = static::location();
      $var_dump = static::return_var_dump($locations);
      mail(static::EMAIL_ADDRESS,"DEPRECATED FILE STILL IN USE ".static::caller_file_line(),$var_dump);
   }

   /**
    * Returns an array of declared globals
    * @since ADD MVC 0.0
    */
   public function get_declared_globals() {
      return array_diff_key(
         $GLOBALS,
         array_flip(
            array(
               '_GET',
               '_POST',
               '_COOKIE',
               '_REQUEST',
               '_SERVER',
               '_FILES',
               '_ENV',
               'php_errormsg',
               'HTTP_RAW_POST_DATA',
               'http_response_header',
               'argc',
               'argv',
               'GLOBALS',
            )
         )
      );
   }


   /**
    * Prints the config value
    *
    * @param string $field
    * @param boolean $boolean
    *
    *
    * @since ADD MVC 0.7.4
    */
   public static function print_config($field, $boolean = false) {
      $value = isset(static::config()->$field) ? static::config()->$field : null;

      $label = "config - $field";

      if ($boolean) {
         $value = (bool)$value;
         $label .= " declared";
      }

      static::print_data($label,$value);
   }

   /**
    * Prints a data with label
    *
    * @param mixed $label
    * @param mixed $value
    * @param boolean $escape (htmlspecialchars) will still escape if passed null, and escape is ignored if the content type is text/plain (could be a problem if we are mistakenly thinking that the content type is text plain!
    *
    * @see return_print_data
    *
    * @since ADD MVC 0.7.4
    */
   public static function print_data($label,$value, $escape = true) {
      static::restricted_echo( static::return_print_data($label, $value, $escape) );
   }


   /**
    * Returns the printable data
    *
    * @param mixed $label
    * @param mixed $value
    * @param boolean $escape (htmlspecialchars) (will still escape if passed null) and escape is ignored if the content type is text/plain (could be a problem if we are mistakenly thinking that the content type is text plain!
    *
    * @since ADD MVC 0.10.4
    */
   public static function return_print_data($label,$value, $escape = true) {
      $smarty_class = isset(static::config()->smarty_class) ? static::config()->smarty_class : 'Smarty';
      $print_data_template = isset(static::config()->print_data_template) ? static::config()->print_data_template : 'debug/print_data.tpl';
      $smarty = new $smarty_class();
      $smarty->addTemplateDir(dirname(__DIR__).'/views');
      $smarty -> assign('content_type',static::content_type());
      $smarty -> assign('print_data_template',$print_data_template);
      $smarty -> assign('label',$label);
      $smarty -> assign('value',$value);
      $smarty -> assign('indentations',0);

      if ($escape === false) {
         $smarty -> assign('escape',false);
      }
      else {
         $smarty -> assign('escape',true);
      }

      return $smarty->fetch($print_data_template);
   }
}