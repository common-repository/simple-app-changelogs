<?php
//Obtain options and if there isent one set, set them; 
/* Comming soon
function sAppChangelog_get_options($default = false){
	global $sAppChangelog_options;	
	$defaul_options = array();
	if($default){
		//Reset the default options
		update_option('sAppChangelog_options', $defaul_options);
		$sAppChangelog_options = $defaul_options;
		return $defaul_options;
	}
	//Do not restore
	$options = get_option('sAppChangelog_options');
	if (isset($options) && !empty($options)){
		return $options;
	} else {
		update_option('sAppChangelog_options', $defaul_options);
		return $defaul_options;
	}
}*/
//VersionManager Init
function sAppChangelog_versionManager_init(){
	global $sAppChangelog_versionManager;
	if(!isset($sAppChangelog_versionManager)){
		$sAppChangelog_versionManager = new Simple_AppChangelog_VersionsDBManager();
	}
	
	if(!$sAppChangelog_versionManager->is_installed()){
		if(!$sAppChangelog_versionManager->install()){
			wp_die(__('There is a problen when insaling the Version Manager DB', 'simple-appchangelog-plugin'));
		}
	}
}

//ProjectManager Init
function sAppChangelog_ProjectManager_init(){
	global $sAppChangelog_projectManager;
	if(!isset($sAppChangelog_projectManager)){
		$sAppChangelog_projectManager = new Simple_AppChangelog_ProjectsDBManager();
	}
	
	if(!$sAppChangelog_projectManager->is_installed()){
		if(!$sAppChangelog_projectManager->install()){
			wp_die(__('There is a problen when insaling the Project Manager DB', 'simple-appchangelog-plugin'));
		}
	}
}

//Shortcode manipulation
function show_appchangelog( $args ) {
    global $sAppChangelog_projectManager;
    global $sAppChangelog_versionManager;
    $args_default = array(
        'projectID' => 1,
    );
    $final_args = shortcode_atts($args_default,$args);
    $projectName = $sAppChangelog_projectManager->getProject_name($final_args['projectID']);
    $arrayVersions = $sAppChangelog_versionManager->getVersions_fromProjectID($final_args['projectID']);
    wp_enqueue_script( 'simple_appchangelog_script_main', SIMPLE_APPCHANGELOG_PLUGIN_URL.'/assets/js/mainJquerry.js', array( 'jquery' ) );
    wp_enqueue_style( 'simple_appchangelog_css_main', SIMPLE_APPCHANGELOG_PLUGIN_URL.'/assets/css/mainCSS.css' );
    $output .='<h3>'.$projectName['name'][0].' '.__('Changelog', 'simple-appchangelog-plugin').'</h3>';
    if (!empty($arrayVersions)){
        foreach($arrayVersions as $value => $versionArray) {
            $output .= '<div id="schangelogtitle-'.$versionArray['ID'].'" class="sAppchangelog_title" original-title="'.__('Click for more infrmation about this version!','simple-appchangelog-plugin').'"><strong>';
            $output .= $versionArray['title'].'</strong></div>';
            
            //boddy of the version
            $output .= '<div id="boddyChangelog_'.$versionArray['ID'].'" class="sAppchangelog_boddy"';
            if($value == '0'){
                $output .= ' style="display: block;">';
            }else {
                $output .= ' style="display: none;">';
            }
            $output .= $versionArray['description'].'</div>';
            $output .= '<hr class="sAppChangelog_hrLine">';
        }
    } else {
        $output .= '<div class="sAppChangelog_error">'.__('No Versions Yet', 'simple-appchangelog-plugin').'</div>';
    }
    return $output;
}

add_shortcode('appchangelog', 'show_appchangelog');
?>