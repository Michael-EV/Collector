<?php adminOnly();

/*
	GUI

	Collector
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

    // start the session, load our custom functions, and create $_PATH
    //require '../Code/initiateCollector.php';
    $title = 'Collector GUI';
    require $_PATH->get('Header');
		require ('guiFunctions.php'); // these need to be incorporated with other functions. 
		require('guiClasses.php');
		
?>

<style>
    body { color: black; background-color: white; }
    #header { font-size: 180%; text-align: center; margin: 10px 0 40px; }
    form {
        text-align: center;
        margin: 30px;
    }
    .tableArea {
        display: inline-block;
        width: 50%;
        box-sizing: border-box;
        padding: 10px 30px;
        vertical-align: top;
    }
	textarea { border: none; }
</style>


<?php
	
	$thisDirInfo = new csvDirInfo(); // calling in class for directory information
	$studySheetsInfo = new csvSheetsInfo(); // calling in class for sheets information
	
	//identifying file name
	if (isset($_POST['csvPostName'])){
		$thisDirInfo->studyDir="../Experiments/".$_POST['csvPostName'];		
		$_SESSION['thisDir']=$thisDirInfo->studyDir;//redundant??
		$_SESSION['studyName']=$_POST['csvPostName'];
	} else {
		if(isset($_SESSION['studyName'])){
			$thisDirInfo->studyDir=$_SESSION['thisDir'];
			$thisDirInfo->studyName=$_SESSION['studyName'];
		}
	}
	
	if(!file_exists("$thisDirInfo->studyDir/name.txt")){ //create name.txt for first run
		file_put_contents("$thisDirInfo->studyDir/name.txt",$_SESSION['studyName']);
	}
	
	if(isset($_POST['csvSelected'])){ //to allow reference to session if post doesn't exist (although may want to check if this can be tidier in future).
		$_SESSION['csvSelected']=$_POST['csvSelected'];
	}
	
	//updating study name
	$thisDirInfo->studyName=file_get_contents("$thisDirInfo->studyDir/name.txt");

	// get a list of all the filenames to allow checking for duplications
	$branches = scandir("../Experiments");
	$listStudyNames=array();
	foreach($branches as $branch){
		if(file_exists("../Experiments/$branch/name.txt")){
			array_push($listStudyNames,file_get_contents("../Experiments/$branch/name.txt"));
		}
	}
	$listStudyNamesJson=json_encode($listStudyNames);
	
	if(!isset($_SESSION['csvSelected'])){ //i.e. if this page has just been opened
		$studySheetsInfo->thisSheetName='Conditions';
		$studySheetsInfo->thisSheetFilename='Conditions.csv';
		$studySheetsInfo->thisSheetFolder='';
	} else { // checking whether browsing to "Conditions.csv";
		if(strcmp($_SESSION['csvSelected'],'Conditions.csv,')==0){
			$studySheetsInfo->thisSheetName='Conditions';
			$studySheetsInfo->thisSheetFolder='';
			$studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/Conditions.csv";
		}	else {
			$studySheetsInfo->postSheetInfo($_SESSION['csvSelected']);	
		}	
	}
	
	if(isset($_POST['Delete'])){//something is being deleted
		unlink ("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");
		$studySheetsInfo->thisSheetName='Conditions';
		$studySheetsInfo->thisSheetFolder='';
		$studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/Conditions.csv";
	}
	
	// updating study name - doesn't need a save to do this
	if(isset($_POST['currStudyName'])){
		file_put_contents($thisDirInfo->studyDir.'/name.txt',$_POST['currStudyName']);
		$thisDirInfo->studyName=file_get_contents($thisDirInfo->studyDir.'/name.txt');
	}
	
	// List csv files in the directories
	$studySheetsInfo->stimSheets=getCsvsInDir($thisDirInfo->studyDir.'/Stimuli/');
	$studySheetsInfo->procSheets=getCsvsInDir($thisDirInfo->studyDir.'/Procedure/');
	
	if(isset($_POST['newSheet'])){	//code for creating a new CSV sheet
		$newName=0;
		$newNo=0;
		switch ($_POST['newSheet']){
			case "stim":
				//identify what novel filename needs to be					
				while ($newName==0){
					$newNo++;
					if(!in_array("Stimuli$newNo.csv",$studySheetsInfo->stimSheets)){
						$newName=1;
					}					
				}
				$studySheetsInfo->thisSheetName="Stimuli$newNo";
				$studySheetsInfo->thisSheetFolder="Stimuli";
				$studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";
				copy("../Experiments/New Experiment/Stimuli/Stimuli.csv","$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");
				break;		
			case "proc":
				//identify what novel filename needs to be					
				while ($newName==0){
					$newNo++;
					if(!in_array("Procedure$newNo.csv",$studySheetsInfo->procSheets)){
						$newName=1;
					}					
				}		
				$studySheetsInfo->thisSheetName="Procedure$newNo";
				$studySheetsInfo->thisSheetFolder="Procedure";
				$studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";
				copy("../Experiments/New Experiment/Procedure/Procedure.csv","$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");
				break;
		}
	}
	
	if (isset($_POST['Save'])){ //Saving whichever csv you are currently working on
		// renaming file if the user renamed it
		if (strcmp($studySheetsInfo->thisSheetName,'Conditions')==0){
			// skip this renaming process
		} else {
			if (strcmp($_POST['eventName'],$studySheetsInfo->thisSheetName)!=0){
				$illegalChars=array('	',' ','.');
				foreach ($illegalChars as $illegalChar){
					$_POST['eventName']=str_ireplace($illegalChar,'',$_POST['eventName']);
				}
				$newFile=$thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFolder.'/'.$_POST['eventName'].'.csv';
				$originalFile=$thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFilename;
				copy($originalFile,$newFile);
				unlink($originalFile);
				$studySheetsInfo->thisSheetName=$_POST['eventName'];
				// do not change $studySheetsInfo->thisSheetFolder it's the same folder
				$studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";				
			}
		}
		// converting raw table data into usable array
		//removing symbols
		$stimTableArray=str_replace('[','',$_POST['stimTableInput']);
		$stimTableArray=str_replace('"','',$stimTableArray);
		$stimTableArray=str_replace('null','',$stimTableArray);
		$stimTableArray=explode(']',$stimTableArray); // exploding into an array
		$stimKeys=explode(',',$stimTableArray[0]);	
		for ($i=1; $i<=count($stimTableArray); $i++){
			$stimTableArray[$i]=explode(',',$stimTableArray[$i]);
			if (empty($stimTableArray[$i][0])){
				unset($stimTableArray[$i][0]);
			}
			if (count($stimTableArray[$i])==count($stimKeys)){
				$stimTableArray[$i]=array_combine($stimKeys,$stimTableArray[$i]);
			} else {
				unset ($stimTableArray[$i]);
			}
		}	
		//Save table as a .csv file;
		$fp = fopen("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename", 'w');
		$stimTableArray[0]=explode(',',$stimTableArray[0]);
		fputcsv($fp, $stimTableArray[0]);
		for($i=1; $i<count($stimTableArray); $i++){
			fputcsv($fp, $stimTableArray[$i]);
		}
		fclose($fp);	
	}			
	
	// extract table from csv file
	$stimuli   = csv_to_array("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");
	$stimKeys = array_keys($stimuli[0]);	
	$stimData = array(array_keys(reset($stimuli)));
	foreach ($stimuli as $row) {
		$stimData[] = array_values($row);
	}
	$stimData = json_encode($stimData);			
	
	//list all csv files - should this be a function within $studySheetsInfo?
	$studySheetsInfo->stimSheets=getCsvsInDir($thisDirInfo->studyDir.'/Stimuli/');
	$studySheetsInfo->procSheets=getCsvsInDir($thisDirInfo->studyDir.'/Procedure/');
	$sheetsList=array();
	foreach($studySheetsInfo->stimSheets as $stimSheet){
		array_push($sheetsList,$stimSheet);
	}
	foreach($studySheetsInfo->procSheets as $procSheet){
		array_push($sheetsList,$procSheet);
	}
	$jsonSheets=json_encode($sheetsList);
	
?>
<form action="index.php" method="post">
	<textarea name="currentGuiSheetPage" style="display:none">indexGui</textarea>
	<button class="collectorButton"> return to index </button>
</form>
<form action='index.php' method='post'>
	<textarea name="currentGuiSheetPage" style="display:none">sheetsEditor</textarea>
	<h1>
		<textarea id="currStudyName" name="currStudyName" style="color:#069;" rows="1"
		onkeyup="checkName()"><?=$thisDirInfo->studyName?></textarea>
	</h1>
	<span>
		<button class="collectorButton" id="stimButton"> list of stimuli </button>
		<button name="newSheet" value="stim" class="collectorButton" id="newStimButton"> new stimuli sheet </button>
		<button name="newSheet" value="proc" class="collectorButton" id="newProcButton"> new procedure sheet </button>
	</span>
	<br>	
	<br>
	<div>
		<?php
		
		
		echo '<select name="csvSelected" title="[filename],[folder]">';
		
			if (!$studySheetsInfo->thisSheetFilename==''){ //if nature is redundant? 
				echo"<option name='csvSelected' value='$studySheetsInfo->thisSheetName.csv,$studySheetsInfo->thisSheetFolder'>$studySheetsInfo->thisSheetName.csv,$studySheetsInfo->thisSheetFolder</option>";			
			} 
			if('Conditions' != $studySheetsInfo->thisSheetName){
				echo "<option name='csvSelected' value='Conditions.csv,'>Conditions.csv,</option>";
			}			
			foreach ($studySheetsInfo->procSheets as $procFile){
				if($procFile != "$studySheetsInfo->thisSheetName.csv"){
					echo "<option name='csvSelected' value='$procFile,Procedure'>$procFile,Procedure</option>";
				}	
			}			
			foreach ($studySheetsInfo->stimSheets as $stimFile){
				if($stimFile != "$studySheetsInfo->thisSheetName.csv"){
					echo "<option name='csvSelected' value='$stimFile,Stimuli'>$stimFile,Stimuli</option>";	
				}
			}
	?>		
		</select>
		<button type='submit' class='collectorButton' value='Select'>Select</button>
	</div>		
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<link rel="stylesheet" href="guiSheets/handsontables/handsontables.full.css">
	<script src="guiSheets/handsontables/handsontables.full.js"></script>

	<?php
		if (strcmp("$studySheetsInfo->thisSheetName.csv","Conditions.csv")==0){ ?>
			<h2 title="You cannot edit the Conditions.csv filename or delete the file.">Conditions.csv</h2>
			<?php		
		}	else {
			?>
			
			<h2>
				<textarea id="sheetName" name="eventName" style="color:#069;" rows="1" onkeyup="checkSheetName()"><?=$studySheetsInfo->thisSheetName?></textarea>
			</h2>
			<?php 
			}
			?>	

	<div>   
		
	<?php
		// doing this in PHP to prevent whitespace
		echo '<div id="stimArea" class="tableArea">'
		   .         '<div id="stimTable" class="expTable"></div>'
		   .     '</div>'
		   . '</div>';
	?>

		<br>
	
	<input id="saveButton" type="button" class="collectorButton" value="Save">	
	<button id="submitButton" type="submit" name="Save" class="collectorButton" style="display:none"></button> 

	<?php
		if (strcmp("$studySheetsInfo->thisSheetName.csv",'Conditions.csv')!=0){  ?>
			<input type="button" id="deleteButton" name="Delete" class="collectorButton" value="Delete?">	
			<button id="deleteActivate" type="submit" name="Delete" class="collectorButton" value="Delete" style="display:none">No text needed</button>
		<?php		
		}	
	?>

	</div>

	<input type="hidden" name="stimTableInput">
	
</form>


<script type="text/javascript">
//importing json encoded lists from php
listStudyNames=<?=$listStudyNamesJson?>;
listSheetsNames=<?=$jsonSheets?>;

//removing the current study's name from the list (because this list is to prevent duplication)
studyIndex=listStudyNames.indexOf(currStudyName.value);
listStudyNames.splice(studyIndex,1);

// Checks for preventing repeating study names
var revertStudyName=currStudyName.value;
function checkName(){
	// check if member of array
	if($.inArray(currStudyName.value,listStudyNames)!=-1){
		alert("This is the same name of another study, reverting to unique name");
		currStudyName.value=revertStudyName;
	} else{
		revertStudyName=currStudyName.value;
	}
}


//checks for preventing repeating sheet names

//removing the current study's name from the list (because this list is to prevent duplication)
if (typeof sheetName !== 'undefined'){
	sheetIndex=listSheetsNames.indexOf(sheetName.value+'.csv');
	listSheetsNames.splice(sheetIndex,1);
	var revertSheetName=sheetName.value;
	function checkSheetName(){
		potentialSheetName=sheetName.value+'.csv';
		// check if member of array
		if($.inArray(potentialSheetName,listSheetsNames)!=-1){
			alert("This is the same name of another sheet, reverting to unique name");
			sheetName.value=revertSheetName;
		} else{
			revertSheetName=sheetName.value; // could delete to revert to name when page loaded
		}
		//put in a check to see if there are any illegal symbols here in future version - this is currently being checked after saving	
	}	
}

var stimTable;
    function isTrialTypeHeader(colHeader) {
        var isTrialTypeCol = false;
        
        if (colHeader === 'Trial Type') isTrialTypeCol = true;
        
        if (   colHeader.substr(0, 5) === 'Post '
            && colHeader.substr(-11)  === ' Trial Type'
        ) {
            postN = colHeader.substr(5, colHeader.length - 16);
            postN = parseInt(postN);
            if (!isNaN(postN) && postN != 0) {
                isTrialTypeCol = true;
            }
        }
        
        return isTrialTypeCol;
    }
    function isNumericHeader(colHeader) {
        var isNum = false;
        if (colHeader.substr(-4) === 'Item')     isNum = true;
        if (colHeader.substr(-8) === 'Max Time') isNum = true;
        if (colHeader.substr(-8) === 'Min Time') isNum = true;
        return isNum;
    }
    function isShuffleHeader(colHeader) {
        var isShuffle = false;
        if (colHeader.indexOf('Shuffle') !== -1) isShuffle = true;
        return isShuffle;
    }
    function firstRowRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        td.style.fontWeight = 'bold';
        if (value == '') {
            $(td).addClass("htInvalid");
        }
    }
    function numericRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (isNaN(value) || value === '') {
            td.style.background = '#D8F9FF';
        }
    }
    function shuffleRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (value === '') {
            td.style.background = '#DDD';
        } else if (
            typeof value === 'string' 
         && (   value.indexOf('#') !== -1
             || value.toLowerCase() === 'off'
            )
        ) {
            td.style.background = '#DDD';
        }
    }
    function trialTypesRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
        if (value === 'Nothing' || value === '') {
            if (instance.getDataAtCell(0,col) === 'Trial Type') {
                $(td).addClass("htInvalid");
            } else {
                td.style.background = '#DDD';
            }
        }
    }
    function updateDimensions(hot, addWidth, addHeight) {
        var addW = addWidth  || 0;
        var addH = addHeight || 0;
        
        var container   = hot.container;
        var thisSizeBox = $(container).find(".wtHider");
        
        var thisWidth  = thisSizeBox.width()+22+addW;
        var thisHeight = thisSizeBox.height()+22+addH;
        
        var thisArea = $(container).closest(".tableArea");
        
        thisWidth  = Math.min(thisWidth,  thisArea.width());
        thisHeight = Math.min(thisHeight, 600);
        
        hot.updateSettings({
            width:  1000, //thisWidth,
            height: thisHeight
        });
    }
    function updateDimensionsDelayed(hot, addWidth, addHeight) {
        updateDimensions(hot, addWidth, addHeight);
        setTimeout(function() {
            updateDimensions(hot);
        }, 0);
    }
    function createHoT(container, data) {
        var table = new Handsontable(container, {
            data: data,
            width: 1,
            height: 1,
			
            afterChange: function(changes, source) {
                updateDimensions(this);	
				
				var middleColEmpty=0;
				var middleRowEmpty=0;
				var postEmptyCol=0; //identify if there is a used col after empty one
				var postEmptyRow=0; // same for rows

				//identify if repetition has occurred and adjusting value
				var topRow=[];
				for (var k=0; k<this.countCols()-1; k++){
					var cellValue=this.getDataAtCell(0,k);
					topRow[k]=this.getDataAtCell(0,k);
					for (l=0; l<k; l++){
						if (this.getDataAtCell(0,k)==this.getDataAtCell(0,l)){
							alert ('repetition has occurred!');
							this.setDataAtCell(0,k,this.getDataAtCell(0,k)+'*');
						}
					}
									
				}
				
				//Removing Empty middle columns
				for (var k=0; k<this.countCols()-1; k++){
					if (this.isEmptyCol(k)){
						if (middleColEmpty==0){
							middleColEmpty=1;
						}
					}						
					if (!this.isEmptyCol(k) & middleColEmpty==1){
						postEmptyCol =1;
						alert ("You have an empty column in the middle - Being removed from table!");
						this.alter("remove_col",k-1); //delete column that is empty						
					}						
				}
				
				//Same thing for rows
				for (var k=0; k<this.countRows()-1; k++){
					if (this.isEmptyRow(k)){
						if (middleRowEmpty==0){
							middleRowEmpty=1;
						}
					}						
					if (!this.isEmptyRow(k) & middleRowEmpty==1){
						postEmptyRow =1;
						alert ("You have an empty row in the middle - Being removed from table!");
						this.alter("remove_row",k-1); //delete column that is empty
					}						
				}				
				if(postEmptyCol != 1 ){
					while(this.countEmptyCols()>1){  
						this.alter("remove_col",this.countCols); //delete the last col
					}
				}
				if(postEmptyRow != 1){
					while(this.countEmptyRows()>1){  
						this.alter("remove_row",this.countRows);//delete the last row
					}
				}
            },
            afterInit: function() {
                updateDimensions(this);
            },
            afterCreateCol: function() {
                updateDimensionsDelayed(this, 55, 0);
            },
            afterCreateRow: function() {
                updateDimensionsDelayed(this, 0, 28);
            },
            afterRemoveCol: function() {
                updateDimensionsDelayed(this);
            },
            afterRemoveRow: function() {
                updateDimensionsDelayed(this);
            },
            rowHeaders: false,
            contextMenu: true,
            cells: function(row, col, prop) {
                var cellProperties = {};
                
                if (row === 0) {
                    // header row
                    cellProperties.renderer = firstRowRenderer;
                } else {
                    var thisHeader = this.instance.getDataAtCell(0,col);
                    if (typeof thisHeader === 'string' && thisHeader != '') {
                        if (isTrialTypeHeader(thisHeader)) {
                            cellProperties.type = 'dropdown';
                            cellProperties.source = trialTypes;
                            cellProperties.renderer = trialTypesRenderer;
                        } else {
                            cellProperties.type = 'text';
                            if (isNumericHeader(thisHeader)) {
                                cellProperties.renderer = numericRenderer;
                            } else if (isShuffleHeader(thisHeader)) {
                                cellProperties.renderer = shuffleRenderer;
                            } else {
                                cellProperties.renderer = Handsontable.renderers.TextRenderer;
                            }
                        }
                    } else {
                        cellProperties.renderer = Handsontable.renderers.TextRenderer;
                    }
                }                
                return cellProperties;
            },
            minSpareCols: 1,
            minSpareRows: 1,
            manualColumnFreeze: true,
            fixedRowsTop: 0,
			colHeaders: false,
			cells: function (row, col, prop) {
			}
        });
        return table;
    }
    
	var stimContainer = document.getElementById("stimTable");
	var stimData = <?= $stimData ?>;
	stimTable = createHoT(stimContainer, stimData);
    
    // limit resize events to once every 100 ms
    var resizeTimer;
    
    $(window).resize(function() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function() {
            updateDimensions(stimTable);
        }, 100);
    });
   
$("#submitButton").on("click", function() {
	$("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
});

$("#stimButton").on("click", function() {
	var myWindow = window.open("guiSheets/stimList.php", "", "width=800, height=600");
});

$("#newStimButton").on("click", function() {
	alert("Creating new Stimuli sheet");
});

$("#newProcButton").on("click", function() {
	alert("Creating new Procedure sheet");
});

$("#deleteButton").on("click", function() {
	delConf=confirm("Are you SURE you want to delete this file?");
	if (delConf== true){
		document.getElementById('deleteActivate').click();
	}	
});

$("#saveButton").on("click", function() { //final checks before saving
	//are there too many empty column headers?
  emptyHeadCols=0;
	for(i=0; i<stimTable.countCols();i++){
		if(stimTable.getDataAtCell(0,i)==''){
			emptyHeadCols++;
		}
	}
	if(emptyHeadCols>1){
		alert("You have an empty header - will not save. Fix before trying to save again.");
	} else {
		$('#submitButton').click();
	}
});

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
        case 's':
            event.preventDefault();
            alert('Saving');
			stimTable.deselectCell();			
			$("#saveButton").click();
            break;
        case 'd':
            event.preventDefault();
			$("#deleteButton").click();
            break;
        }
    }
});
</script>

