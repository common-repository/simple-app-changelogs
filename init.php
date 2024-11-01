<?php
//Init all component
add_action('init', 'sAppChangelog_init');
function sAppChangelog_init(){
	/* Comming soon
         * global $sAppChangelog_options;
	if(!isset($sAppChangelog_options)){
		$sAppChangelog_options = sAppChangelog_get_options();
	}*/
	sAppChangelog_versionManager_init();
	sAppChangelog_ProjectManager_init();
}
?>