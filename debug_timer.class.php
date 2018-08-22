<?php
namespace addph\debug;

/**
 * Debug timer class
 * Probably shouldn't be abstract
 *
 * <code>
 * CLASS debug_timer EXTENDS add_debug_timer {
 * }
 *
 * $timer = debug_timer::start("Start");
 * sleep(3);
 * $timer->lap("After 3 seconds");
 * sleep(5);
 * $timer->lap("After 5 seconds");
 * $timer->print_all_laps();
 * </code>
 *
 *
 * @todo remove the extra micro times that the functions are using
 * @author albertdiones@gmail.com
 *
 * @package ADD MVC Debuggers
 * @since ADD MVC 0.0
 * @version 0.0
 */
ABSTRACT CLASS debug_timer EXTENDS debug {

   /**
    * The start timestamp of the timer
    *
    * @since ADD MVC 0.0
    */
   protected $start_timestamp;

   /**
    * The timestamp of the laps
    *
    * @since ADD MVC 0.0
    */
   protected $lap_timestamps = array();

   /**
    * Construct the timer and start it
    *
    * @param $label the label of the point
    *
    * @since ADD MVC 0.0
    */
   protected function __construct($label) {

      $this->start_timestamp = microtime(true);

      $this->lap($label);

   }

   /**
    * Construct and start the timer
    *
    * @param $label the label of the point
    *
    * @since ADD MVC 0.0
    * @version 0.1
    */
   public static function start($label=null) {
      return new static($label);
   }

   /**
    * Add a lap with $label
    *
    * @param string $label the description of where the function is called (eg. "before mysql query" )
    *
    * @since ADD MVC 0.0
    * @version 0.1
    */
   public function lap($label=NULL) {
      $timestamp = microtime(true);

      if (!$label)
         $label = static::caller_file_line();

      $this->lap_timestamps[] = array(
            'label' => $label,
            'timestamp' => $timestamp,
         );

      return $timestamp - $this->start_timestamp;
   }


  /**
   * Add a lap with $label and print the details
   * @param string $label the description of the line where it is placed (eg. "after mysql query" )
   * @author albertdiones@gmail.com
   *
   * @since ADD MVC 0.0
   * @version 0.1
   */
   public function print_lap($label=NULL) {

      $lap_difference = $this->lap($label);

      $lap1 = array_shift(array_slice($this->lap_timestamps,0,1));
      $lap2 = array_shift(array_slice($this->lap_timestamps,-1,1));

      return $this->print_lap_difference($lap1, $lap2);
   }


   /**
    * Print lap details from lap1 to lap2
    *
    *
    * @see $this->print_lap()
    *
    * @param array $lap1
    * @param array $lap2
    *
    * @since ADD MVC 0.7.2
    */
   public function print_lap_difference($lap1, $lap2) {

      $lap_difference = $lap2['timestamp'] - $lap1['timestamp'];

      self::print_data("Time elapse - $lap1[label] -- $lap2[label]",$lap_difference);

   }


   /**
    * print_data extended
    *
    * @param float $label the microsecond time
    * @param float $lap_difference
    *
    * @since ADD MVC 0.7.4
    */
   public static function print_data($label,$lap_difference,$escape=false) {
      $diff = static::us_diff_readable_format($lap_difference);
      if (add::content_type() == 'text/html') {
         $diff = static::us_diff_html($diff,$lap_difference);
      }
      else {
         if ($lap_difference > 1) {
            $diff = "*$diff*";
            if ($lap_difference > 10) {
               $diff = "*$diff*";
            }
         }
      }
      parent::print_data($label,$diff,$escape);
   }

   /**
    * Print all laps
    *
    * @since ADD MVC 0.7.2
    */
   public function print_all_laps() {

      $laps = $this->lap_timestamps;

      $previous_lap = array_shift($laps);

      foreach ($laps as $lap) {
         static::print_lap_difference($previous_lap, $lap);
         $previous_lap = $lap;
      }

   }

   /**
    * Microseconds Diff Readable Format
    * @param int $microseconds the microseconds
    * @since ADD MVC 0.0
    */
   public static function us_diff_readable_format($microseconds) {
      $difference_ms = $microseconds*1000;
      return "$difference_ms milliseconds";
   }


   /**
    * Microseconds diff html style
    *
    * @param string $string
    * @param float $second_difference
    *
    * @since ADD MVC 0.4
    */
   public static function us_diff_html($string, $second_difference) {

      if (add::content_type() == 'text/plain') {
         return "$string";
      }

      $styles_array = array();

      if ($second_difference > 1) {
         $styles_array[] = 'color:red';
         if ($second_difference > 10) {
            $styles_array[] = 'font-weight:bold';
         }
      }
      else if ($second_difference < 1) {
         $styles_array[] = 'color:green';
      }

      $styles = implode(';',$styles_array);
      return "<span style='$styles'>$string</span>";
   }
}