<?php
/**
 * Controls the error checking for stimuli files
*  Parent class: controlFileSetup handles the reading and stitching together of files
 */
class stimuli extends controlFile
{
    public function errorCheck()
    {
        $this->checkColumns();
        // I will do each check as it's own method
    }
    protected function checkColumns()
    {
        $required = array('Cue', 'Answer');
        $file = 'Stimuli';
        $this->requiredColumns($file, $required);
    }
}