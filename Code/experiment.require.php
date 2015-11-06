<?php
/**
 * get procedure columns for single trial, optionally specifying
 * specific position and post trial number. Post trial columns are
 * transformed into their unposted form (Text instead of Post 1 Text)
 * @param $pos, optional, procedure row, defaults to $_SESSION['Position']
 * @param $post, optional, post trial number (first trial is 0), defaults to $_SESSION['PostNumber']
 * @return array with columns as keys, row fields as values
 * @see getStimuli(), getTrial()
 */
function getProcedure($pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];

    $procRow  = $_SESSION['Procedure'][$pos];
    $procCols = array();

    if ($post === 0) {
        foreach ($procRow as $col => $val) {
            if (substr($col, 0, 5) === 'Post ') {
                continue;
            } else {
                $procCols[$col] = $val;
            }
        }
    } else {
        $colPre = "Post $post ";
        $colPreLen = strlen($colPre);
        
        foreach ($procRow as $col => $val) {
            if (substr($col, 0, $colPreLen) !== $colPre) {
                continue;
            } else {
                $colClean = substr($col, $colPreLen);
                $colClean = trim($colClean); // in case they had "Post 1  Settings"
                $procCols[$colClean] = $val;
            }
        }
    }
    
    // if a post trial item is not set, use the original trial's item
    if (!isset($procCols['Item'])) {
        $procCols['Item'] = $procRow['Item'];
    }
    
    return $procCols;
}
/**
 * Get stimuli from given item
 * @param $item typically, contents of "Item" column in proc file
 * @return array of stimuli, with column values imploded with | if multiple items specified
 * @see getProcedure(), getTrial()
 */
function getStimuli($item) {
    $items = rangeToArray($item);
    $stimRows = array();
    foreach ($items as $i) {
        if(isset($_SESSION['Stimuli'][$i-2])) {
            $stimRows[] = $_SESSION['Stimuli'][$i-2];
        }
    }
    // if no item then populate with nothing
    if ($stimRows === array()) {
        foreach ($_SESSION['Stimuli'][0] as $col => $val) {
            $stimRows[0][$col] = '.';
        }
    }
    $stimCols = array();
    foreach ($stimRows as $row) {
        foreach ($row as $col => $val) {
            $stimCols[$col][] = $val;
        }
    }
    foreach ($stimCols as $col => $val) {
        $stimCols[$col] = implode('|', $val);
    }
    return $stimCols;
}
/**
 * Gets procedure and stimuli data for given procedure row and post trial
 * @param $pos, optional int specifying which row of the procedure file to use
 * @param $post, optional int specifying which post trial to use
 * @return array, with indices "Stimuli" and "Procedure" for array of column => values from those files
 * @see getProcedure(), getStimuli()
 */
function getTrial($pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];
    
    $procedure = getProcedure($pos, $post);
    $stimuli   = getStimuli($procedure['Item']);
    
    return array(
        'Stimuli'   => $stimuli,
        'Procedure' => $procedure
    );
}
/**
 * converts 2d associative array into a 1d array for extract(). Array keys are converted to variable form
 * @param $trial, 2d array to be aliased by extract()
 * @return array with columns converted to lowercase, spaces replaced with underscores
 */
function prepareAliases($trial) {
    $trialValues = array();
    foreach ($trial as $fileType => $row) {
        foreach ($row as $col => $val) {
            $aliasCol = str_replace(' ', '_', strtolower($col));
            // it is possible that overlaps here will occur, if you have
            // something like "Cue" in your stim file and "Post 1 Cue" in
            // your proc file. In that case, an error will be generated,
            // and you only get the first value
            if (isset($trialValues[$aliasCol])) {
                if (stripos($aliasCol, 'shuffle') !== false) continue;  // temp hack until we work out how to handle shuffle cols
                $err = "Overlap with aliases: $aliasCol already defined, $col not used";
                trigger_error($err, E_USER_WARNING);
            } else {
                $trialValues[$aliasCol] = $val;
            }
        }
    }
    return $trialValues;
}
/**
 * Determines which timing to apply to the current trial.
 * @param &$max_time string indicating number of seconds to wait until trial submit, or "user"
 * @param $compTime string or int indicating value to use for $max_time if $max_time === 'computer'
 * @return string class name to add to form
 */
function getTrialTiming(&$max_time, $compTime) {
    if (!is_numeric($compTime)) $compTime = 'user';
    $max_time = strtolower($max_time);
    if ($max_time === 'computer' || $max_time === 'default') {
        $max_time = $compTime;
    }
    if (!is_numeric($max_time)) {
        $max_time === 'user';
    }
    // set class for input form (shows or hides 'submit' button)
    if ($max_time == 'user') {
        return 'UserTiming';
    } else {
        return 'ComputerTiming';
    }
}
/**
 * saves array of data into (optionally) specified trial's response array. "Post" prefixes appended appropriately
 * @param $data, the array of responses to save, 1-dimensional, typically $_POST with custom scoring
 * @param $pos,  int, optional, defaults to $_SESSION['Position']
 * @param $post, int, optional, defaults to $_SESSION['PostNumber']
 * @return null
 */
function saveResponses($data, $pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];
    
    if ($post == 0) {
        foreach ($data as $col => $val) {
            $_SESSION['Responses'][$pos][$col] = $val;
        }
    } else {
        foreach ($data as $col => $val) {
            $_SESSION['Responses'][$pos]["Post1_$col"] = $val;
        }
    }
}
/**
 * gets all levels of post trials with valid trial types for row in procedure
 * @param $pos, int, optional, defaults to $_SESSION['Position']
 * @return array of integers specifying which post trials are valid, including 0 for non-post trials
 */
function getValidPostTrials($pos = null) {
    if ($pos === null) $pos = $_SESSION['Position'];
    $procRow = $_SESSION['Procedure'][$pos];
    
    $notTrials  = array('off', 'no', '', 'n/a');
    $validPosts = array();
    $type = $procRow['Trial Type'];
    if (!in_array($type, $notTrials)) {
        $validPosts[] = 0;
    }
    
    $i = 1;
    while (isset($procRow["Post $i Trial Type"])) {
        $nextType = $procRow["Post $i Trial Type"];
        $nextType = strtolower($nextType);
        if (!in_array($nextType, $notTrials)) {
            $validPosts[] = $i;
        }
        ++$i;
    }
    
    return $validPosts;
}
/**
 * finds next post trial after current (or given) trial,
 * or returns false if row has no more valid trials
 * @param $pos,  int, optional, defaults to $_SESSION['Position']
 * @param $post, int, optional, defaults to $_SESSION['PostNumber']
 * @return int|bool, int of next valid post trial level, or false if none exist
 * @see getValidPostTrials()
 */
function getNextPostLevel($pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];
    
    $validPosts  = getValidPostTrials($pos);
    
    foreach ($validPosts as $validPostLevel) {
        if ($validPostLevel > $post) {
            return $validPostLevel;
        }
    }
    
    return false;
}
/**
 * records responses for entire proc row into the output csv
 * @param $extraData array, optional, extra data to add to
 *                   this row of output, using the keys as columns
 * @param $pos int, optional, defaults to $_SESSION['Position']
 *             defines which trial to record
 * @return null
 */
function recordTrial($extraData = array(), $pos = null) {
    #### setting up aliases (for later use)
    global $_PATH;
    if ($pos === null) $pos = $_SESSION['Position'];

    #### Calculating time difference from current to last trial
    $oldTime = $_SESSION['Timestamp'];
    $_SESSION['Timestamp'] = microtime(true);
    $timeDif = $_SESSION['Timestamp'] - $oldTime;
    
    #### Writing to data file
    $data = array(
        'Username'       =>  $_SESSION['Username'],
        'ID'             =>  $_SESSION['ID'],
        'ExperimentName' =>  $_PATH->getDefault('Current Experiment'),
        'Session'        =>  $_SESSION['Session'],
        'Trial'          =>  $pos,
        'Date'           =>  date("c"),
        'TimeDif'        =>  $timeDif,
    );
    
    $addData = array();
    $addData['Cond'] = $_SESSION['Condition'];
    $procRow = $_SESSION['Procedure'][$pos];
    $addData['Proc'] = $procRow;
    $addData['Stim'] = getStimuli($procRow['Item']);
    $postTrials = getValidPostTrials();
    foreach ($postTrials as $i) {
        if ($i == 0) continue;  // already grabbed those items
        if (isset($procRow["Post $i Item"])) {
            $addData["StimPost$i"] = getStimuli($procRow["Post $i Item"]);
        }
    }
    $addData['Resp'] = $_SESSION['Responses'][$pos];
    
    foreach ($addData as $category => $values) {
        foreach ($values as $col => $val) {
            $data[$category . '_' . $col] = $val;
        }
    }
    
    if (!is_array($extraData)) {
        $extraData = array('Extra Data' => (string) $extraData);
    }
    foreach ($extraData as $header => $datum) {
        $data[$header] = $datum;
    }
    
    // record line into output CSV
    arrayToLine($data, $_PATH->get('Experiment Output'));
}
/**
 * gets the next trial, whether its the next post trial or the
 * first trial of the next row. Returns false if no trials left
 * @param $pos,  int, optional, defaults to $_SESSION['Position']
 * @param $post, int, optional, defaults to $_SESSION['PostNumber']
 * @return array|bool, array as returned by getTrial(), or false
 * @see getTrial()
 */
function getNextTrial($pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];
    
    $nextPost = getNextPostLevel($pos, $post);
    
    if ($nextPost === false) {
        $nextPos  = $pos+1;
        $nextPost = 0;
    } else {
        $nextPos  = $pos;   // same proc row, different post trial
    }
    
    if (!isset($_SESSION['Procedure'][$nextPos])) return false;
    
    return getTrial($nextPos, $nextPost);
}
/**
 * adds images from next trial to a hidden div
 * @param $pos,  int, optional, defaults to $_SESSION['Position']
 * @param $post, int, optional, defaults to $_SESSION['PostNumber']
 * @return bool true if next trial exists, false if next does not exist
 * @see getNextTrial()
 */
function precacheNext($pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];
    
    $nextTrial = getNextTrial($pos, $post);
    
    if ($nextTrial === false) return false;
    
    echo '<div class="precachenext">';
    
    foreach ($nextTrial['Stimuli'] as $col => $val) {
        if (show($val) !== $val) {
            echo show($val);
        }
    }
    
    echo '</div>';
    
    return true;    // just return true to mark success
}
/**
 * checks if its any trials exist, and if not, redirects to Done
 * @return null, will exit out if no future trial found
 * @see getTrialTrial()
 */
function checkIfDone() {
    // can do some error/fraud detection here
    global $_PATH;
    if (getNextTrial() === false) {
        $_SESSION['finishedTrials'] = true;
        header('Location: ' . $_PATH->get('Done'));
        exit;
    }
}
/**
 * shows all available info for trial, helpful if trial fails
 * @param $pos,  int, optional, defaults to $_SESSION['Position']
 * @param $post, int, optional, defaults to $_SESSION['PostNumber']
 * @return null
 * @see getTrial()
 */
function showTrialDiagnostics($pos = null, $post = null) {
    if ($pos  === null) $pos  = $_SESSION['Position'];
    if ($post === null) $post = $_SESSION['PostNumber'];
    // clean the arrays used so that they output strings, not code
    $clean_session      = arrayCleaner($_SESSION);
    $clean_currentTrial = arrayCleaner(getTrial($pos, $post));
    echo '<div class=diagnostics>'
        .    '<h2>Diagnostic information</h2>'
        .    '<ul>'
        .        '<li> Condition #: '              . $clean_session['Condition']['Number']                . '</li>'
        .        '<li> Condition Stimuli File:'    . $clean_session['Condition']['Stimuli']               . '</li>'
        .        '<li> Condition Procedure File: ' . $clean_session['Condition']['Procedure']             . '</li>'
        .        '<li> Condition description: '    . $clean_session['Condition']['Condition Description'] . '</li>'
        .    '</ul>'
        .    '<ul>'
        .        '<li> Trial Number: '         . $currentPos                                  . '</li>'
        .        '<li> Trial Type: '           . $trialType                                   . '</li>'
        .        '<li> Trial max time: '       . $clean_currentTrial['Procedure']['Max Time'] . '</li>'
        .        '<li> Trial Time (seconds): ' . $maxTime                                     . '</li>'
        .    '</ul>'
        .    '<ul>'
        .        '<li> Cue: '    . show($cue)    . '</li>'
        .        '<li> Answer: ' . show($answer) . '</li>'
        .    '</ul>';
    readable($currentTrial,         "Information loaded about the current trial");
    readable($_SESSION['Trials'],   "Information loaded about the entire experiment");
    echo '</div>';
}
