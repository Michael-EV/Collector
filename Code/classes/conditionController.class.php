<?php
/**
 * Controls the selecting, assigning, and returning of condition information
 * Also controls manipulation of the login counter (becasue it is needed to condition cycle)
 * Upon creation of a new instance of this object the Conditions.csv file is loaded
 * Once $this->assignCondition() has been run you can use the following public methods
 * to query information about the assigned conditions
 *
 * --Public Methods
 *     - $this->stimuli()       :   @return 'Stimuli' cell string
 *     - $this->procedure()     :   @return 'Procedure' cell string
 *     - $this->description()   :   @return 'Condition Description' cell string
 *     - $this->notes()         :   @return 'Condition Notes' cell string
 *     - $this->get()           :   @return  keyed array of assigned condition (row)
 */
class conditionController
{
    protected $selection;                 // condition selected from $_GET
    protected $location;                  // how to get to Conditions.csv
    protected $logLocation;               // path to login counter
    protected $showFlagged;
    protected $ConditionsCSV;             // GetFromFile() load of Conditiions.csv
    protected $assignedCondition = false; // tells whether a conditions has been assigned or not
    protected $userCondition;             // array (keys by column) of the assigned conditon
    protected $errObj;                    // name of error handler object


    /**
     * This class needs the following information to function
     * @param string            $conditionsLoc relative path to Conditions.csv
     * @param string            $counterDir    relative path to the directory where the counter is held
     * @param string            $logLocation   relative path to the login counter file
     * @param errorController   $errorHandler  object that will capture and log
     */
    public function __construct($conditionsLoc, $logLocation, $showFlagged = false, errorController $errorHandler)
    {
        $this->errObj   = $errorHandler;
        $this->location = $conditionsLoc;
        $this->logLocation = $logLocation;
        $this->showFlagged = $showFlagged;
        $this->makeCounterDir($logLocation);
        $this->loadConditons();
    }
    /**
     * Save GetFromFile() results of Conditions.csv into the object
     */
    protected function loadConditons()
    {
        $this->conditionsExists();
        $this->ConditionsCSV = getFromFile($this->location, false);
        $this->requiredColumns();
    }
    protected function makeCounterDir($logLocation)
    {
        $dir = dirname($logLocation);
        if (!is_dir($dir)) {
            mkdir($dir,  0777,  true);
        }
    }
    /**
     * Saves the condition selection made on index.php
     * Pulls input from a $_GET
     */
    public function selectedCondition($selection)
    {
        $selection = filter_var($selection, FILTER_SANITIZE_STRING);
        if (is_numeric($selection) OR ($selection == 'Auto')) {
            $this->selection = $selection;
        } else {
            $msg = "Your condition selection: $selection is not valid";
            $this->errObj->add($msg, true);
        }
    }
    /**
     * Assigns participant conditon and updates login counter so the next
     * participant will not be assigned the same condiiton
     */
    public function assignCondition()
    {
        $validConds = $this->removeOffConditions();
        if ($this->selection == 'Auto') {
            $log = $this->getLogVal();
            $index = $log % count($validConds);
            $this->userCondition = $validConds[$index];
            $this->incrementLog($log);
            $this->assignedCondition = $index;
        } else {
            $index = $this->selection;
            if (isset($validConds[$index])) {
                $this->userCondition = $validConds[$index];
                $this->assignedCondition = $index;
            }
        }        
    }
    protected function getLogVal()
    {
        $logPath = $this->logLocation;
        if (file_exists($logPath)) {
            $handle   = fopen($logPath, "r");
            $logCount = fgets($handle);
            fclose($handle);
            return $logCount;
        } else {
            return 0;
        }
    }
    protected function incrementLog($oldVal)
    {
        $newVal = $oldVal + 1;
        $handle = fopen($this->logLocation, "w");
        fputs($handle, $newVal);
        fclose($handle);
    }
    protected function removeOffConditions()
    {
        if ($this->showFlagged === true) {
            return $this->ConditionsCSV;
        }
        $on = array();
        foreach ($this->ConditionsCSV as $row) {
            if ($row['Condition Description'][0] === '#') {
                continue;
            } else {
                $on[] = $row;
            }
        }
        return $on;
    }
    /**
     * Send it the array from a row of a Conditions.csv read
     * Must be formatted as a getFromFile() array
     * e.g., = array("Number"=> 1, "Stimuli"=>'something.csv',...)
     */
    public function overrideCondition($array)
    {
        if(is_array($array)) {
            $this->userCondition = $array;
            $this->assignedCondition = 'overridden-' . microtime(true);
        }
    }
    /**
     * Debug method for checking what this class does
     */
    public function info()
    {
        // echo '<div>Selected condition: ' . $this->selection . '<ol>';
        echo "<div>Selected contion: $this->selection <ol>";
        foreach ($this->ConditionsCSV as $pos => $row) {
            echo "<li>
                      <strong>stim:</strong>{$row['Stimuli']}<br>
                      <strong>proc:</strong>{$row['Procedure']}
                  </li>";
        }
        echo '</ol></div>';
    }
    /**
     * Makes sure the conditions file can be found.
     * If not found then send a showstopper to $errors
     * @see $this->__construct()
     * @param string $location path to Conditions.csv
     */
    protected function conditionsExists()
    {
        
        if (!FileExists($this->location)) {
            $msg = "Cannot find Conditions.csv at $this->location";
            $this->errObj->add($msg, true);
        }
    }
    
    protected function requiredColumns()
    {
        
        $requiredColumns = array('Number', 'Stimuli', 'Procedure');
        foreach ($requiredColumns as $pos => $col) {
            if(!isset($this->ConditionsCSV[0][$col])) {
                $msg = "Your Conditions.csv file is missing the $col column";
                $this->errObj->add($msg, true);
            }
        }
    }
    /**
     * Once a condition has been assigned this will return the stimuli file string
     * @return string contents of 'Stimuli' column
     */
    public function stimuli()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Stimuli'];
        }
    }
    /**
     * Once a condition has been assigned this will return the procedure file string
     * @return string contents of 'Procedure' column
     */
    public function procedure()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Procedure'];
        }
    }
    /**
     * Once a condition has been assigned this will return the 'Condition Description' string
     * @return string contents of 'Condition Description' column
     */
    public function description()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Condition Description'];
        }
    }
    /**
     * Once a condition has been assigned this will return the 'Condition Notes' string
     * @return string contents of 'Condition Notes' column
     */
    public function notes()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Condition Notes'];
        }
    }
    /**
     * Once a condition has been assigned this will return the array for the assigned row
     * @return array keyed by column names
     */
    public function get()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition;
        }
    }
    /**
     * Gets the index, which is sort of the row number, of the assigned condition
     * @return bool|int|string false if not set, int if assigned typically, string if overridden
     */
    public function getAssignedIndex() {
        return $this->assignedCondition;
    }
    /**
     * Allows you to change the default error handler object, $errors, to one of your choosing
     * @param  string $varName will look for variable with the name of the string contents
     * for example, 'mikey' would cause the errors to be reported to `global $mikey`
     */
    public function changeErrorHandler(errorController $newErrHandler)
    {
        $this->errObj = $newErrHandler;
    }
}
?>