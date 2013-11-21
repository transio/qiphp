<?php
 /**
 * QiPHP Framework
 * 
 * @filesource
 * @package        qi/log
 * @version        1.1
 * @copyright      (C) 2009, Transio LLC
 * @author         Steven Moseley
 * @link           http://www.qiphp.com/
 * @license        http://www.gnu.org/licenses/gpl.html
 */

class TestMode {
    private $startTime;
    private $startMemory;
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);

    }
    
    public function __toString() {
        // End time for page rendering calculations
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $totalTime = $endTime - $this->startTime;
        $totalMemory = $endMemory - $this->startMemory;
        
        // Buffer test data output
        ob_start();
        $testData = <<<HTML
        <div style="width: 800px; margin: 0 auto; text-align: left"><div style="border: solid 2px #ccc; padding: 20px; -moz-border-radius: 10px; -webkit-border-radius: 10px;">
            <h2 style="color: #ccc">Test Mode</h2>
            <ul>
                <li style="color: #ccc">Start time: {$this->startTime}</li>
                <li style="color: #ccc">End time: {$endTime}</li>
                <li style="color: #ccc">Elapsed time:  {$totalTime}s<br /></li>
                <li style="color: #ccc">Start memory: {$this->startMemory}</li>
                <li style="color: #ccc">End (total) memory: {$endMemory}</li>
                <li style="color: #ccc">QiPHP memory: {$totalMemory}</li>
            </ul>
HTML;
        print($testData);
        
        // Post
        print("<h2 style=\"color: #ccc\">Post Data</h2>");
        print("<pre style=\"color: #ccc\">");
        print_r($_POST);
        print("</pre>");
        
        // Session
        print("<h2 style=\"color: #ccc\">Session Data</h2>");
        print("<pre style=\"color: #ccc\">");
        foreach ($_SESSION as $key => $value) {
            print("<b>[{$key}]</b>\n");
            print_r(Session::getParameter($key));
        }
        print("</pre>");
        
        print("</div></div>");
        
        // Get contents of buffer
        $testData = ob_get_contents();
        
        // End output buffer, return testdata
        ob_end_clean();
        return $testData;
    }
}