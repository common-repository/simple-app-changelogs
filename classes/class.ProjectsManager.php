<?php
class Simple_AppChangelog_ProjectsDBManager{
	public $table_id = "sappchangelog_projects";
	public function get_attributes(){
		return array(
			array("title"=>'Name',
				  "name"=>'name',
				  "prop"=>'text NULL DEFAULT NULL'
				 ),
			array("title"=>'Slug',
				  "name"=>'slug',
				  "prop"=>'text NULL DEFAULT NULL'
				 )
		);
	}
	//Check if db table exists (Is installed)
	public function is_installed(){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
			return false;
		} else {
			return true;
		}
	}
	//install the DB table (create it)
	public function install(){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		if ( defined( 'DB_CHARSET' ) ) $charset = DB_CHARSET; else $charset = 'utf8';
		
		$sql = "CREATE TABLE " . $table_name . " (";
		$sql .= "ID mediumint(9) NOT NULL AUTO_INCREMENT,";
		foreach ($this->get_attributes() as $column) {
			$sql .= $column['name']." ".$column['prop'].", ";
		}
		$sql .= " UNIQUE KEY ID (ID) ) CHARACTER SET ".$charset.";";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		//Check if the install suceeded
		if($this->is_installed()) {
			return true;
		} else {
			return false;
		}
	}
	//Add a project to the db
	public function add_project($projectName, $projectSlug){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlAddProject = "INSERT INTO " . $table_name . " (";
		foreach ($this->get_attributes() as $column) {
			$sqlAddProject .= $column['name'].", ";
		}
		$sqlAddProject = substr(trim($sqlAddProject), 0, -1);
		$sqlAddProject .= ") VALUES ('".$projectName."', '".$projectSlug."');";
		$results = $wpdb->query( $sqlAddProject );
		if ($results == 1){
			return true;
		}else{
			return false; 
		}	
	}
	//Remove project from the db
	public function deltete_project($projectID){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlDelte = "DELETE FROM ". $table_name ." WHERE ID = '".$projectID."';";
		$results = $wpdb->query( $sqlDelte );
		if($results == 1){
			return true;
		} else {
			return false;
		}
	}
	//Update project in the db
	public function edit_project($projectID, $NewprojectName, $NewprojectSlug){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlEdit = "UPDATE  ". $table_name ." SET name='".$NewprojectName."', slug='".$NewprojectSlug."'  WHERE ID='".$projectID."';";
		$results = $wpdb->query( $sqlEdit );
		if($results == 1){
			return true;
		} else {
			return false;
		}
	}
	//Check if project with specific id exists
	public function check_project($id){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlLookup = "SELECT * FROM ". $table_name ." WHERE ID='".$id."';";
		$results = $wpdb->query( $sqlLookup );
		if($results >= 1){
			return true;
		} else {
			return false;
		}
	}
        public function check_project_name($name, $slug){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlLookup = "SELECT * FROM ". $table_name ." WHERE name='".$name."';";
                $sqlLookup2 = "SELECT * FROM ". $table_name ." WHERE slug='".$slug."';";
		$results = $wpdb->query( $sqlLookup );
                $results2 = $wpdb->query( $sqlLookup2 );
		if($results >= 1 || $results2 >= 1){
			return true;
		} else {
			return false;
		}
	}
	//Get name from projectID
        public function getProject_name($id){
          	global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlLookup = "SELECT * FROM ". $table_name ." WHERE ID='".$id."';";
                if($results = $wpdb->get_results( $sqlLookup,"ARRAY_A")){
                    return $results; 
		} else {	  
                    return false;
		}
        }
        //Get all projects
        public function getAllProjecs(){
          	global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$sqlLookup = "SELECT * FROM ". $table_name .";";
                if($results = $wpdb->get_results( $sqlLookup,"ARRAY_A")){
                    return $results; 
		} else {	  
                    return false;
		}
        }
	//Uninstall the table
	public function uninstall(){
		
	}
}
?>