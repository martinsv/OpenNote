<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.2.0
**/

include_once dirname(__FILE__)."/./Common.php";
//OO code
class NoteBook{	
	/**
	 * Constructor 
	 * @param mode - the mode for the reviewer
	 * @param id - the id of and existing class if a subFolder. Default value is null
	 */
	public function NoteBook($id = NULL){
		if($id!=NULL)
			$this->subFolder($id);//TODO
		else
			$this->root();
	}
	
	/**
	 * Displays the root folder contents
	 */
	private function root(){
		echo "	<script type=\"text/javascript\">
					setButton0(\"\");
				   	setButton1(\"New Folder\");
				   	setButton2(\"Find\");
				   	setURL(\"./\",\"OpenNote\");
		   		</script>";
		
		//echo the label for the page
			self::folderTitle("Home",false);
		
		$result = Model::getRootFolder();
		foreach($result as $row)
			echo self::boxFactory($row["id"], "folder green", $row["name"], null,"Folder");
	}
	
	/**
	 * Display sub folder
	 * @param id - the id of the sub folder
	 */
	public function subFolder($id){
		
		$query = Model::getFolder($id);
		
		if(!count($query))
			return;
		
		//echo this foldersID for future reference
			echo sprintf("<folder id=\"folder\" folderID=\"%d\"></list>",$query[0]["id"]);
			
		
		echo sprintf("	<script type=\"text/javascript\">
							setButton0(\"New Note\");
						   	setButton1(\"New Folder\");
						   	setButton2(\"Find\");
						   	setURL(\"index.php?folderID=%d\",\"%s\");
				   		</script>", $query[0]["id"], $query[0]["name"]);
			
		//echo the label for the page
			self::folderTitle($query[0]["name"],true);
		
		//get all the folders
			$folders = Model::getSubFolders($id);
			foreach($folders as $row)
				echo self::boxFactory($row["id"], "folder green", $row["name"], null,"Folder");
		
		//get all the notes
			$folders = Model::getNotesInFolder($id);
			foreach($folders as $row)
				echo self::boxFactory($row["id"], "note", $row["title"], null,null,"Note", $id);
	}

	/**
	 * echos the folder list
	 * @param folderList - a array of all the folders 
	 */
	public static function getFolderList(){
		$root = Model::getRootFolder();
		if(count($root)==0)
			return;
		
		self::startSubTree();
		foreach ($root as $rootFolder)
			self::getChildren($rootFolder["id"],$rootFolder["name"]);
		self::endSubTree();
	}
	
	/**
	 * This method prints the folder passed in and finds all children and prints them
	 * @param folderID - the folder to find children for
	 * @param name - the name of the folder to find children for
	 */
	private static function getChildren($folderID, $name){
		self::startTreeEntry();
		self::treeEntryFactory($folderID, $name); //print self
			
		$result =  Model::getSubFolders($folderID);
		if(count($result)>0){//do I have any children
			self::startSubTree();//great line them up
			
			foreach($result as $child)
				self::getChildren($child["id"],$child["name"]);
			
			self::endSubTree();
		}
		
		self::endTreeEntry();
	}

	/**
	 * creates a new folder
	 * @param parrentID - the id of the parent folder.
	 * @param name - the title of folder
	 */
	public static function newFolder($parentID =null,$name){
		Model::newFolder($parentID, $name);
		
		//update the view
			echo "	<script type=\"text/javascript\">
						getFolderList();
			   		</script>";
		new NoteBook($parentID);
	}
	
	/**
	 * delete a new folder
	 * @param $id - the id of the folder to delete.
	 */
	public static function removeFolder($id){		
		//update the view
			echo "	<script type=\"text/javascript\">
						getFolderList();
			   		</script>";
		new NoteBook(Model::removeFolder($id));
	}
	
	/**
	 * change a folders parrent
	 * @param folderID - the folder id to change the parrent of
	 * @param newParrentID - the new parrent of the folder
	 */
	public static function moveFolder($folderID, $newParrentID){
		Model::moveFolder($newParrentID, $folderID);
	}
	
	/**
	 * @param id - the id of the box
	 * @param color/class - the color/class code to format the box
	 * @param title - the title string
	 * @param desc - the description
	 * @param left - what to display on the left
	 * @param right - what to displayn the rightht
	 * @param $otherID - the folder ID of the note
	 * @return - return a box html to go into the box container
	 */
	private static function boxFactory($id=null, $color=null, $title=null,$desc=null,$left=null,$right=null, $otherID = null){	
		return "
				<div class=\"box $color startHidden\" boxID=\"$id\" otherID=\"$otherID\">
					<h2>
						$title
					</h2>
					<p class=\"box_description\">$desc</p>
					<p class=\"options\">
						$left
						<span class=\"right\">$right</span>
					</p>
				</div>
				
				<script type=\"text/javascript\">
					$(\"[boxID=$id]\").fadeIn(fadeSpeedLong*Math.random()+200);
				</script>";
	}

	/**
	 * @param folderID - the folder id to print the entry for
	 * @param title - the title of the folder to print
	 */
	private static function treeEntryFactory($folderID, $title){
		echo "<a class=\"customButton folder\" folderID=\"$folderID\">$title</a>";
	}		
	
	/**
	 * start a tree entry
	 */
	private static function startTreeEntry(){
		echo "<li>";
	}	
	
	/**
	 * close a entry tag
	 */
	private static function endTreeEntry(){
		echo "</li>";
	}	
	
	/**
	 * start a sub tree
	 */
	private static function startSubTree(){
		echo "<ul>";
	}	
	
	/**
	 * close a sub tree
	 */
	private static function endSubTree(){
		echo "</ul>";
	}	
	
	/**
	 * @param title - the title for the folder
	 * @param remove - weather to show the remove button
	 * echos the title block
	 */
	private static function folderTitle($title, $remove){
		$button="";
		if($remove)
			$button="<button id=\"removeFolder\" class=\"customButton\">X</button>";
		
		echo sprintf(
			"<div id=\"folderTitleBar\">
				<div id=\"folderTitle\">%s</div>
				%s
			</div>",$title,$button);
	}
	
	
	/**
	 * @param searchString - the string to search 
	 * @return - the results of the search
	 */
	public static function search($searchString){	
		echo sprintf("	<script type=\"text/javascript\">
							setButton0(\"\");
						   	setButton1(\"\");
						   	setButton2(\"Find\");
						   	setURL(\"index.php\",\"\");
				   		</script>");			
			
		//echo the label for the page
			self::folderTitle(sprintf("Search Results For: %s",$searchString),false);
		
		//get all the folders
			$folders = Model::searchFolders($searchString);
			foreach($folders as $row)
				echo self::boxFactory($row["id"], "folder green", $row["name"], null,"Folder");
		
		//get all the notes
			$folders = Model::searchNotes($searchString);
			foreach($folders as $row)
				echo self::boxFactory($row["id"], "note", $row["title"], null,null,"Note",$row["folderID"]);
	}
}
?>
