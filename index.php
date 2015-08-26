<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell


    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
 */
    require 'Code/initiateCollector.php';
    
    if ($_PATH->getDefault('Current Experiment') !== null) {
        $exp = $_PATH->getDefault('Current Experiment');
        header('Location: ' . $_PATH->get('Current Experiment'));
        exit;
    }
    
    // get possible experiments to choose from
    $experiments     = array();
    $experimentsScan = scandir($_PATH->get('Experiments'));
    foreach ($experimentsScan as $exp) {
        if (   $exp === '.' 
            || $exp === '..'
            || $exp === 'Common'
        ) {
            // do nothing
        } else {
            $experiments[$exp] = $_PATH->get('Experiments') . '/' . $exp;
        }
    }
    
    $title = 'Collector Homepage';
    require $_PATH->get('Header');
?>
    <h1>Collector</h1>
    <h2>A program for running experiments on the web</h2>
    
    <p>Welcome to the Collector. If you would like to begin an experiment,
         click on one of the links below.</p>
    
    <ul>
    <?php
        foreach ($experiments as $name => $path) {
            echo '<li> <a href="' . $path . '">' . $name . '</a> </li>';
        }
    ?>
    </ul>
    
    <p>Otherwise, you can access one of the other tools 
         <a href="<?= $_PATH->get('Tools') ?>">here</a>.</p>
<?php
    require $_PATH->get('Footer');
