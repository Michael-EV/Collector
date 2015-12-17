<?php
/**
 * System Map
 * 
 * To use this file, map out the directory structure of the program, using keys 
 * as directory or file names, and values as either the contents of a directory,
 * or the label for that directory or file.
 * 
 * Inside the map, certain keys can be a variable using 'Dir Name' as the key, 
 * you can give a label to the directory, using {var}, you can provide a point 
 * for the user to provide their own information (such as the specific trial 
 * type "instruct").
 * 
 * Using {Default Name}, you can create a different type of variable, which 
 * should only need to be set once for the experiment good uses for this include
 * the default procedure file or the default output file This can just be part
 * of a file, such as '{Username}.json' 
 * 
 * More information can be found inside the Pathfinder class.
 * 
 * @see Pathfinder
 */
    
    $systemMap = array (
        'Dir Name' => 'root',
        
        'index.php' => 'index',
        
        'Experiments' => array (
            'Dir Name' => 'Experiments',
            
            'Common' => array (
                'Dir Name' => 'Common',
                
                'Common Settings.ini' => 'Common Settings',
            
                'Ineligible' => array (
                    'Dir Name' => 'Ineligibility Dir',
                ),
                
                'Images' => array (
                    'Dir Name' => 'Images',
                ),
                
                'Audio' => array (
                    'Dir Name' => 'Audio',
                ),
                
                'TrialTypes' => array (
                    'Dir Name' => 'Custom Trial Types',
                    
                    '{var}' => array (
                        'Dir Name' => 'Custom Trial Type Dir',
                        
                        'display.php'   => 'Custom Trial Display',
                        'helper.inc'    => 'Custom Trial Helper',
                        'scoring.php'   => 'Custom Trial Scoring',
                        'script.js'     => 'Custom Trial Script',
                        'style.css'     => 'Custom Trial Style',
                        'validator.php' => 'Custom Trial Validator',
                    ),
                ),
            ),
            
            '{Current Experiment}' => array (
                'Dir Name' => 'Current Experiment',
                
                'index.php' => 'Current Index',
                'Return.php' => 'Current Return',
                
                'Conditions.csv' => 'Conditions',
                'Task Instructions.php' => 'Instructions',
                
                'Settings.ini' => 'Experiment Settings',
                
                'Stimuli' => array (
                    'Dir Name' => 'Stimuli Dir',
                    
                    '{Stimuli}' => 'Stimuli',
                ),
                
                'Procedure' => array (
                    'Dir Name' => 'Procedure Dir',
                    
                    '{Procedure}' => 'Procedure',
                ),
            ),
        ),
        
        'Code' => array (
            'Dir Name' => 'Code',
            
            'Welcome.php'     => 'Welcome',
            'WelcomeBack.php' => 'Welcome Back',
            
            'check.php' => 'Check',
            
            'customFunctions.php' => 'Custom Functions',
                        
            'Done.php' => 'Done',
            
            'errorCheck.php' => 'Error Check',
            
            'Experiment.php' => 'Experiment Page',
            'experiment.require.php' => 'Experiment Require',
            
            'footer.php' => 'Footer',
            
            'Header.php' => 'Header',
            
            'icon.png' => 'Icon',
            
            'initiateCollector' => 'Initiate Collector',
            
            'instructions.php' => 'Instructions Page',
            
            'instructionsRecord.php' => 'Instructions Record',
            
            'login.php' => 'Login',
            
            'trialValidator.require.php' => 'Trial Validator Require',
            
            'nojs.php' => 'No JS',
            
            'shuffleFunctions.php' => 'Shuffle Functions',
            
            'systemMap.php' => 'system map',
            
            
            'css' => array (
                'Dir Name' => 'CSS Dir',
                
                'global.css' => 'Global CSS',
                'jquery-ui.min.css' => 'Jquery UI CSS',
                'jquery-ui-1.10.4.custom.min.css' => 'Jquery UI Custom CSS',
                
                'nojswarning.png' => 'No JS Warning',

                'tools.css' => 'Tools CSS',
                'tutorial.css' => 'Tutorial CSS',
                
                'fonts' => array (
                    'Dir Name' => 'Fonts',
                    
                    // wow there is a lot in here. . .
                ),
                
                'images' => array (
                    'Dir Name' => 'CSS Images',
                    
                    // lots of stuff in here
                ),
            ),

            'classes' => array (
                'Dir Name' => 'Classes',

                'conditionController.class.php'     => 'Conditions Class',
                'controlFile.class.php'             => 'Control Files Class',
                'debugController.class.php'         => 'Debug Class',
                'errorController.class'             => 'Errors Class',
                'Parse.class.php'                   => 'Parse',
                'Pathfinder.class.php'              => 'Pathfinder',
                'procedure.class.php'               => 'Procedure Class',
                'ReturnVisitController.class.php'   => 'Return Class',
                'statusController.class.php'        => 'Status Class',
                'stimuli.class.php'                 => 'Stimuli Class',
                'trialValidator.class.php'          => 'Trial Validator Class',
                'user.class.php'                    => 'Users Class',
            ),
            
            'javascript' => array (
                'Dir Name' => 'JS Dir',
                
                'collector_1.0.0.js' => 'Collector JS',
                
                'jquery.js' => 'Jquery',
                'jquery-ui-1.10.4.custom.min.js' => 'Jquery UI Custom',
                'jquery-ui-1.11.4.min.js' => 'Jquery UI',
                
                'loggingIn.js' => 'Login JS',
                'sha256.js' => 'Sha256 JS',
            ),
            
            'phpbrowscap' => array (
                'Browscap.php' => 'Browscap',
                // more stuff in here
            ),
            
        ),
        
        'Data' => array (
            'Dir Name' => 'Data',
            
            '{Current Experiment}-Data{Data Sub Dir}' => array (
                'Dir Name' => 'Current Data Dir',
                
                'InstructionsData.csv'   => 'Instructions Data',
                'Status_Begin.csv'       => 'Status Begin Data',
                'Status_End.csv'         => 'Status End Data',
                
                'Counter' => array (
                    'Dir Name' => 'Counter Dir',
                    
                    'Counter.csv' => 'Counter',
                ),
                
                'JSON_session' => array (
                    'Dir Name' => 'JSON Dir',
                    
                    '{Username}.json' => 'json',
                ),
                
                'Output' => array (
                    'Dir Name' => 'Output Dir',
                    
                    '{Output}' => 'Experiment Output',
                ),
                
                'validations' => array (
                    'Dir Name' => 'Trial Validations Dir',
                    
                    'scanTime-{Condition Index}.txt' => 'Trial Validation Scan Time',
                ),
            ),
        ),
        
        'Tools' => array (
            'Dir Name' => 'Tools',
            
            // lots of stuff in here
        ),

        'TrialTypes' => array (
            'Dir Name' => 'Trial Types',
            
            '{var}' => array (
                'Dir Name' => 'Trial Type Dir',
                
                'display.php'   => 'Trial Display',
                'helper.php'    => 'Trial Helper',
                'scoring.php'   => 'Trial Scoring',
                'script.js'     => 'Trial Script',
                'style.css'     => 'Trial Style',
                'validator.php' => 'Trial Validator',
            ),
            'defaultTrialHelper.php' => 'default helper',
            'defaultTrialScoring.php' => 'default scoring',
        ),
    );
