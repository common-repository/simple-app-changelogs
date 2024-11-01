<?php
class Simple_AppChangelog_VersionsDBManager{
	public $table_id = "sappchangelog_versions";
	public function get_attributes(){
		return array(
			array("title"=>'Project ID',
				  "name"=>'projectID',
				  "prop"=>'MEDIUMINT(9) NULL DEFAULT NULL'
				 ),
			array("title"=>'Orden',
				  "name"=>'orden',
				  "prop"=>'MEDIUMINT(9) NULL DEFAULT NULL'
				 ),
			array("title"=>'Title',
				  "name"=>'title',
				  "prop"=>'text NULL DEFAULT NULL'
				 ),
			array("title"=>'Description',
				  "name"=>'description',
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
		$sql .= "ID mediumint(9) NOT NULL AUTO_INCREMENT, ";
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
	//Add version
	function add_version($versionArray){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
                $sqlAdd = "INSERT INTO " . $table_name . " (";
		foreach ($this->get_attributes() as $column) {
			$sqlAdd .= $column['name'].", ";
		}
		$sqlAdd = substr(trim($sqlAdd), 0, -1);
                $sqlAdd .= ") VALUES (";
                foreach ($this->get_attributes() as $column) {
			$value = (  isset(  $versionArray[$column['name']] )? $versionArray[$column['name']] :""  );			
			$sqlAdd .= $wpdb->prepare ("%s,", $value);
		}
                $sqlAdd = substr(trim($sqlAdd), 0, -1); 		  
		$sqlAdd .= ");";
                $sqlOrderExists = "SELECT * FROM " . $table_name . " WHERE (projectID=" . $versionArray['projectID'] . " AND orden=".$versionArray['orden'].")";
                $resultOrderExists = $wpdb->query( $sqlOrderExists );
                if($resultOrderExists >= 1){
                    $sqlShiftOrder = "UPDATE " . $table_name . " SET `orden`=`orden`+1 WHERE projectID=" . $versionArray['projectID'];
                    $results2 = $wpdb->query($sqlShiftOrder);
                }
                $results = $wpdb->query( $sqlAdd );
		if ($results == 1){
                    return true;
                }else{
                    return false;
                }
	}
	
	//Edit version
	function edit_version($versionID, $versionArray){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
                $sqlEdit = "UPDATE " . $table_name . " SET ";
                foreach ($versionArray as $column => $value) {			
			$sqlEdit .= $column."=".$wpdb->prepare ("'%s',", $value);
		}
		$sqlEdit = substr(trim($sqlEdit), 0, -1);
		$sqlEdit .= " WHERE ID=".$wpdb->prepare ("'%s',", $versionID);
		$sqlEdit = substr(trim($sqlEdit), 0, -1).";";
                $sqlOrderExists = "SELECT * FROM " . $table_name . " WHERE (projectID=" . $versionArray['projectID'] . " AND orden=".$versionArray['orden'].")";
                $resultOrderExists = $wpdb->query( $sqlOrderExists );
                if($resultOrderExists >= 1){
                    $sqlShiftOrder = "UPDATE " . $table_name . " SET `orden`=`orden`+1 WHERE (projectID=" . $versionArray['projectID'] . " AND orden>=".$versionArray['orden'].")";
                    $results2 = $wpdb->query( $sqlShiftOrder );
                }
		$results = $wpdb->query( $sqlEdit );
		if ($results == 1){
                    return true;
                }else{
                    return false;
                }
	}
	
	//deltete version
	function delete_version($versionID){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$SQLDdelete = "DELETE FROM ". $table_name ." WHERE ID = '".$versionID."';";
		$results = $wpdb->query($SQLDdelete);
		if($results == 1){
                    return true; 
		} else {	  
                    return false;
		}	
	}

	//deltete all version form specific project
	function delete_al_projects_versions($ProjectID){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
		$SQLDdelete = "DELETE FROM ". $table_name ." WHERE projectID = '".$ProjectID."';";
		$results = $wpdb->query($SQLDdelete);
		if($results >= 1){
                    return true; 
		} else {	  
                    return false;
		}	
	}
	//Get all versions from a specific project id
	function getVersions_fromProjectID($projectID){
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_id;
                $sqlGet = "SELECT * FROM " . $table_name . " WHERE projectID='".$projectID."' ORDER BY orden ASC;";
                if($results = $wpdb->get_results( $sqlGet,"ARRAY_A")){
                    return $results; 
		} else {	  
                    return false;
		}
	}
}
?>