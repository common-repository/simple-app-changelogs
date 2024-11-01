<?php
add_action('simple_appchangelog_admin_init', 'cgtienda_admin_init');
function simple_appchangelog_admin_init() {
    /*global $sAppChangelog_options;

    if (!isset($sAppChangelog_options)) {
        $sAppChangelog_options = cgtienda_obtener_opciones();
    }*/
    sAppChangelog_versionManager_init();
    sAppChangelog_ProjectManager_init();
}
add_action( 'admin_enqueue_scripts', 'load_admin_style_simple_appChangelog' );
function load_admin_style_simple_appChangelog() {
    wp_enqueue_style( 'sAppChangelog', SIMPLE_APPCHANGELOG_PLUGIN_URL . '/admin/adminCSS.css', false, '1.0.0' );
}
/*
* Añadir el menu a wordpres menu bar
*/
add_action('admin_menu', 'simple_appchangelog_add_pages');

function simple_appchangelog_add_pages() {
    add_menu_page('main', 'Simple App Changelog', 'administrator', 'simple_appchangelog_main_page', '', 'dashicons-media-text');
    add_submenu_page('simple_appchangelog_main_page', __('General','simple-appchangelog-plugin'), __('General','simple-appchangelog-plugin'), 'administrator', 'simple_appchangelog_main_page', 'simple_appChangelog_general_page');
    add_submenu_page('simple_appchangelog_main_page', __('New Project', 'simple-appchangelog-plugin'), __('New Project', 'simple-appchangelog-plugin') ,'administrator', 'simple_appchangelog_newProject_page', 'simple_appChangelog_new_project_page');
}
function simple_appChangelog_general_page(){
    //HTML code starts
    ?>
    <div class="warp">
        <?php
        global $sAppChangelog_versionManager;
        global $sAppChangelog_projectManager;
        
        if(!isset($_GET['ViewVersionProject'])){
            ?>
            <h1>Simple App Changelog - Projects</h1>
            <?php
            //Bulk delete
            if (isset($_POST['_wpnonce_sappchangelog_projectsloolup']) &&
                    wp_verify_nonce($_POST['_wpnonce_sappchangelog_projectsloolup'], 'sappchangelog_projectsloolup') &&
                    (isset($_POST['action']) && $_POST['action'] == 'deleteProject') ||
                    (isset($_POST['action2']) && $_POST['action2'] == 'deleteProject')) {

                $num = 0;
                foreach ($_POST['chk_project'] as $id => $value) {

                    if ($sAppChangelog_projectManager->deltete_project($id)) {
                        $sAppChangelog_versionManager->delete_al_projects_versions($id);
                        $num++;
                    }
                }
                if ($num > 0) {
                    if ($num == 1)
                        echo "<div class='notice notice-success'><p>".__('Project Deleted!', 'simple-appchangelog-plugin')."</div>";
                    else
                        echo "<div class='notice notice-success'><p><strong>" . str_replace("%s", $num , __('Deleted %s Projects!','simple-appchangelog-plugin'))."</strong></p></div>";
                }
             }
            //Edit Project
            if(isset($_POST['EditProjectSubmit']) && isset($_POST['projectTitleEdit_'.$_POST['EditProjectSubmit']]) && isset($_POST['projectSlugEdit_'.$_POST['EditProjectSubmit']])){
                if($sAppChangelog_projectManager->edit_project($_POST['EditProjectSubmit'], $_POST['projectTitleEdit_'.$_POST['EditProjectSubmit']], $_POST['projectSlugEdit_'.$_POST['EditProjectSubmit']])){
                    echo '<div class="notice notice-success"><p>'.__('Project Edited!','simple-appchangelog-plugin').'</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>'.__('There was a problem editing the project!','simple-appchangelog-plugin').'</p></div>';
                }
            }
            //Delete Project
             if(isset($_GET['_wpnonce_simple_appchangelog_delete_project']) && wp_verify_nonce($_GET['_wpnonce_simple_appchangelog_delete_project'],'simple_appchangelog_delete_project') &&
                isset($_GET['action']) && ($_GET['action'] == 'delete-project') && isset($_GET['id'])){

                //Delete project
                if($sAppChangelog_projectManager->deltete_project($_GET['id'])){
                    $sAppChangelog_versionManager->delete_al_projects_versions($_GET['id']);
                    echo '<div class="notice notice-success"><p>'.__('Project Deleted!','simple-appchangelog-plugin').'</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>'.__('There was a problem deleting the project!','simple-appchangelog-plugin').'</p></div>';
                }
            }
            $allProjects = $sAppChangelog_projectManager->getAllProjecs();
            if(empty($allProjects)){
                echo __('There are no project to display!','simple-appchangelog-plugin');
               return; 
            }
        ?>
        <form method="POST" name="ProjectList" target="_self" enctype="multipart/form-data">
        <?php wp_nonce_field('sappchangelog_projectsloolup', '_wpnonce_sappchangelog_projectsloolup'); ?>
        <!-- Action chooser1 -->
            <div class="tablenav">
                <div class="alignleft actions">
                    <select name="action">
                        <option selected="selected" value=""><?php _e('Actions','simple-appchangelog-plugin')?></option>
                        <option value="deleteProject"><?php _e('Delete Project', 'simple-appchangelog-plugin') ?></option>
                    </select>
                    <input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Do action','simple-appchangelog-plugin') ?>"/>
                </div>
                <br/>
            </div>
        <!-- End Action chooser1 -->

        <!-- Start Table -->
            <table cellspacing="0" class="widefat fixed">
                <!-- Table header -->
                <thead>
                    <tr class="thead">
                        <th scope="col" class="check-column" ><input type="checkbox"/></th>
                        <?php 
                        foreach($sAppChangelog_projectManager->get_attributes() as $attributes ){ ?>
                            <th scope="col"><?php echo $attributes['title'] ?></th>
                        <?php  } ?>          
                        <th scope="col"><?php _e('ID','simple-appchangelog-plugin') ?></th>
                        <th scope="col"><?php _e('Options','simple-appchangelog-plugin') ?></th>
                    </tr> 
                </thead>
                <!-- End table header -->
                <!-- Table body -->
                <tbody class="list:user user-list" id="users">
                    <?php
                    $i = 0;
                    foreach($allProjects as $key => $projectArray){ ?>
                    <tr class="<?php if ($i % 2 == 0) echo "alternate"; ?>">
                        <th scope="row" class="check-column">
                            <input type="checkbox" value="1" id="check_<?php echo $i; ?>" name="chk_project[<?php echo $projectArray['ID'] ?>]"/>
                            <input type="hidden" value="<?php echo $projectArray['ID'] ?>" name="projectID[]"/>   
                        </th>
                    <?php foreach($sAppChangelog_projectManager->get_attributes() as $attributes){?>
                        <td><?php
                        if($attributes['name'] == 'name'){ 
                            echo '<a href="?page=simple_appchangelog_main_page&ViewVersionProject='.$projectArray['ID'].'"><strong>'.$projectArray[$attributes['name']]."</strong></a>";
                        } else {
                            echo $projectArray[$attributes['name']];
                        }
                        ?></td>
                    <?php } ?>
                        <td><?php echo $projectArray['ID'] ?></td>
                        <td>
                            <a onclick="document.getElementById('delete_alert_<?php echo $i; ?>').style.display = 'block';return false;" href="#"><?php _e('Delete','simple-appchangelog-plugin') ?></a><br/>
                            <a onclick="document.getElementById('edit_alert_<?php echo $i; ?>').style.display = 'block';return false;" href="#"><?php _e('Edit Project','simple-appchangelog-plugin') ?></a><br/>
                            <a href="?page=simple_appchangelog_main_page&ViewVersionProject=<?php echo $projectArray['ID']; ?>"><?php _e('View/Edit Project Versions','simple-appchangelog-plugin') ?></a><br/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" align="center" width="100%">
                            <div id="delete_alert_<?php echo $i; ?>" style="display:none;" class="sAppChangelog-alert">
                                <?php _e('You are about to delete a project','simple-appchangelog-plugin') ?>
                                <a href="<?php echo wp_nonce_url('?page=simple_appchangelog_main_page&action=delete-project&id=' . $projectArray['ID'], 'simple_appchangelog_delete_project', '_wpnonce_simple_appchangelog_delete_project') ?>"><strong><?php _e('Continue','simple-appchangelog-plugin') ?></strong></a>&nbsp;
                                <a onclick="this.parentNode.style.display = 'none';return false;" href="#"><?php _e('Cancel','simple-appchangelog-plugin') ?></a>
                            </div>
                            <div id="edit_alert_<?php echo $i; ?>" style="display:none;" class="sAppChangelog-alert">
                                <label><?php _e('Project Name','simple-appchangelog-plugin') ?></label><br/>
                                <input type="text" name="projectTitleEdit_<?php echo $projectArray['ID'] ?>" size="100" id="title" spellcheck="true" autocomplete="off" /><br/><br/>
                                <label><?php _e('Project Slug','simple-appchangelog-plugin') ?></label><br/>
                                <input type="text" name="projectSlugEdit_<?php echo $projectArray['ID'] ?>" size="100" id="slug" spellcheck="true" autocomplete="off" /><br/></br>
                                <button type="submit" name="EditProjectSubmit" value="<?php echo $projectArray['ID'] ?>"  class="button-primary"><?php _e('Edit Project', 'simple-appchangelog-plugin') ?></button>
                            </div>
                        </td>
                    </tr>
                    <?php $i++; } ?>
                </tbody>
                <!-- End table body -->
                <!-- Table footer -->
                <tfoot>
                    <tr class="thead">
                        <th scope="col" class="check-column" ><input type="checkbox"/></th>
                        <?php 
                        foreach($sAppChangelog_projectManager->get_attributes() as $attributes ){ ?>
                            <th scope="col"><?php echo $attributes['title'] ?></th>
                        <?php  } ?>
                        <th scope="col"><?php _e('ID','simple-appchangelog-plugin') ?></th>
                        <th scope="col"><?php _e('Options','simple-appchangelog-plugin') ?></th>
                    </tr>
                </tfoot>
                <!-- End table footer -->
            </table>
        <!-- End Table -->

        <!-- Action chooser2-->
            <div class="tablenav">
                <div class="alignleft actions">
                    <select name="action2">
                        <option selected="selected" value=""><?php _e('Actions','simple-appchangelog-plugin')?></option>
                        <option value="deleteProject"><?php _e('Delete Project', 'simple-appchangelog-plugin') ?></option>
                    </select>
                    <input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="<?php _e('Do action','simple-appchangelog-plugin') ?>"/>
                </div>
                <br/>
            </div>
        <!-- End Action chooser2-->
        </form>
        </div>
    <?php
    } else {
        ?>
        <h1>Simple App Changelog - Versions</h1>
        <?php
        //View specific projects Versions
       if(!$sAppChangelog_projectManager->check_project($_GET['ViewVersionProject'])){
            _e('There is no project with this ID','simple-appchangelog-plugin');
            ?>
            <br/><br/>
            <a href="<?php echo get_admin_url('','?page=simple_appchangelog_main_page') ?>" class="button-primary"><?php _e('Return to Project List','simple-appchangelog-plugin') ?></a>
         <?php
        } else {
            if(!isset($_GET['CreateNewVersion'])){
            //Bulk delete
            if (isset($_POST['_wpnonce_sappchangelog_versionloolup']) &&
                    wp_verify_nonce($_POST['_wpnonce_sappchangelog_versionloolup'], 'sappchangelog_versionloolup') &&
                    (isset($_POST['action']) && $_POST['action'] == 'deleteVersion') ||
                    (isset($_POST['action2']) && $_POST['action2'] == 'deleteVersion')) {

                $num = 0;
                foreach ($_POST['chk_version'] as $id => $value) {

                    if ($sAppChangelog_versionManager->delete_version($id)) {
                        $num++;
                    }
                }
                if ($num > 0) {
                    if ($num == 1)
                        echo "<div class='notice notice-success'><p>".__('Version Deleted!', 'simple-appchangelog-plugin')."</div>";
                    else
                        echo "<div class='notice notice-success'><p><strong>" . str_replace("%s", $num , __('Deleted %s Versions!','simple-appchangelog-plugin'))."</strong></p></div>";
                }
            }
            //Edit Version
            if(isset($_POST['_wpnonce_sappchangelog_editVersion_'.$_POST['EditVersionSubmit']]) && isset($_POST['EditVersionSubmit']) && 
                    isset($_POST['versionNameEdit_'.$_POST['EditVersionSubmit']]) && isset($_POST['versionDescriptionEdit_'.$_POST['EditVersionSubmit']])
                    && isset($_POST['versionDescriptionEdit_'.$_POST['EditVersionSubmit']]) && 
                    wp_verify_nonce($_POST['_wpnonce_sappchangelog_editVersion_'.$_POST['EditVersionSubmit']],'sappchangelog_editVersion_'.$_POST['EditVersionSubmit'])){
                if($sAppChangelog_versionManager->edit_version($_POST['EditVersionSubmit'],array('projectID' => $_GET['ViewVersionProject'],
                                                                                              'orden' => $_POST['versionOrderEdit_'.$_POST['EditVersionSubmit']],
                                                                                              'title' => $_POST['versionNameEdit_'.$_POST['EditVersionSubmit']],
                                                                                              'description' => $_POST['versionDescriptionEdit_'.$_POST['EditVersionSubmit']]))){
                    echo '<div class="notice notice-success"><p>'.__('Version edited!','simple-appchangelog-plugin').'</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>'.__('There was a problem editing the version!','simple-appchangelog-plugin').'</p></div>';
                }
            }
            //Delete version
            if(isset($_GET['_wpnonce_simple_appchangelog_delete_version']) && wp_verify_nonce($_GET['_wpnonce_simple_appchangelog_delete_version'],'simple_appchangelog_delete_version') &&
                isset($_GET['action']) && ($_GET['action'] == 'delete-version') && isset($_GET['id'])){
                if($sAppChangelog_versionManager->delete_version($_GET['id'])){
                    echo '<div class="notice notice-success"><p>'.__('Version deleted!','simple-appchangelog-plugin').'</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>'.__('There was a problem deleting the Version!','simple-appchangelog-plugin').'</p></div>';
                }
            }
            $allProjectVersion = $sAppChangelog_versionManager->getVersions_fromProjectID($_GET['ViewVersionProject']);
            $projectName = $sAppChangelog_projectManager->getProject_name($_GET['ViewVersionProject'])['0']['name'];
            if(empty($allProjectVersion)){
                echo __('There are no versions to display!','simple-appchangelog-plugin').'</br>';
                echo '<a href="'. get_admin_url('','?page=simple_appchangelog_main_page&ViewVersionProject='. $_GET['ViewVersionProject'] . '&CreateNewVersion=1').'" class="button-primary">'. __('Create new Version','simple-appchangelog-plugin') .'</a>';
               return; 
            }
            ?>
            <h2><?php $projectName ?></h2>
            <form method="POST" name="ProjectList" target="_self" enctype="multipart/form-data">
                <?php wp_nonce_field('sappchangelog_versionloolup', '_wpnonce_sappchangelog_versionloolup'); ?>
                <!-- OptcionChoser1 -->
                <div class="tablenav">
                    <div class="alignleft actions">
                        <select name="action">
                            <option selected="selected" value=""><?php _e('Actions','simple-appchangelog-plugin')?></option>
                            <option value="deleteVersion"><?php _e('Delete Versions', 'simple-appchangelog-plugin') ?></option>
                        </select>
                        <input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Do action','simple-appchangelog-plugin') ?>"/>
                        <a href="<?php echo get_admin_url('','?page=simple_appchangelog_main_page&ViewVersionProject='. $_GET['ViewVersionProject'] . '&CreateNewVersion=1') ?>" class="button-primary"><?php _e('Create new Version','simple-appchangelog-plugin') ?></a>
                    </div>
                    <br/>
                </div>
                <!-- End OptionChoser1 -->
                <!-- Table -->
                <table cellspacing="0" class="widefat fixed">
                <!-- Table header -->
                <thead>
                    <tr class="thead">
                        <th scope="col" class="check-column" ><input type="checkbox"/></th>
                        <?php 
                        foreach($sAppChangelog_versionManager->get_attributes() as $attributes ){ 
                            if($attributes['name'] != 'projectID'){?>
                            <th scope="col"><?php 
                            echo $attributes['title']
                            ?></th>
                        <?php  } } ?>          
                        <th scope="col"><?php _e('Options','simple-appchangelog-plugin') ?></th>
                    </tr> 
                </thead>
                <!-- End table header -->
                <!-- Table body -->
                <tbody class="list:user user-list" id="users">
                    <?php
                    $i = 0;
                    foreach($allProjectVersion as $key => $versionArray){ ?>
                    <tr class="<?php if ($i % 2 == 0) echo "alternate"; ?>">
                        <th scope="row" class="check-column">
                            <input type="checkbox" value="1" id="check_<?php echo $i; ?>" name="chk_version[<?php echo $versionArray['ID'] ?>]"/>
                            <input type="hidden" value="<?php echo $versionArray['ID'] ?>" name="versionID[]"/>   
                        </th>
                    <?php foreach($sAppChangelog_versionManager->get_attributes() as $attributes){
                        if($attributes['name'] != 'projectID'){?>
                        <td><?php
                            echo $versionArray[$attributes['name']]
                        ?></td>
                        <?php } } ?>
                        <td>
                            <a onclick="document.getElementById('delete_alert_<?php echo $i; ?>').style.display = 'block';return false;" href="#"><?php _e('Delete','simple-appchangelog-plugin') ?></a><br/>
                            <a onclick="document.getElementById('edit_alert_<?php echo $i; ?>').style.display = 'block';return false;" href="#"><?php _e('Edit Version','simple-appchangelog-plugin') ?></a><br/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" align="center" width="100%">
                            <div id="delete_alert_<?php echo $i; ?>" style="display:none;" class="sAppChangelog-alert">
                                <?php _e('You are about to delete a version','simple-appchangelog-plugin') ?>
                                <a href="<?php echo wp_nonce_url('?page=simple_appchangelog_main_page&action=delete-version&id=' . $versionArray['ID']. '&ViewVersionProject='. $_GET['ViewVersionProject'], 'simple_appchangelog_delete_version', '_wpnonce_simple_appchangelog_delete_version') ?>"><strong><?php _e('Continue','simple-appchangelog-plugin') ?></strong></a>&nbsp;
                                <a onclick="this.parentNode.style.display = 'none';return false;" href="#"><?php _e('Cancel','simple-appchangelog-plugin') ?></a>
                            </div>
                            <div id="edit_alert_<?php echo $i; ?>" style="display:none;" class="sAppChangelog-alert">
                                <?php wp_nonce_field('sappchangelog_editVersion_'.$versionArray['ID'], '_wpnonce_sappchangelog_editVersion_'.$versionArray['ID']); ?>
                                <label><?php _e('Version Name','simple-appchangelog-plugin') ?></label><br/>
                                <input type="text" name="versionNameEdit_<?php echo $versionArray['ID'] ?>" size="100" id="title" spellcheck="true" autocomplete="off" value="<?php echo $versionArray['title'] ?>"/><br/><br/>
                                <label><?php _e('Version Descripcion','simple-appchangelog-plugin') ?></label><br/>
                                <?php wp_editor($versionArray['description'], 'versionDescriptionEdit_'.$versionArray['ID'], array('textarea_name' => 'versionDescriptionEdit_'.$versionArray['ID'],
                                'media_buttons' => true,
                                'wpautop' => true,
                                'tinymce' => true)) ?><br/>
                                <label><?php _e('Order','simple-appchangelog-plugin') ?></label>
                                <input type="number" name="versionOrderEdit_<?php echo $versionArray['ID'] ?>" value="<?php echo $versionArray['orden'] ?>"/><br/><br/>
                                <button type="submit" name="EditVersionSubmit" value="<?php echo $versionArray['ID'] ?>"  class="button-primary"><?php _e('Edit Version', 'simple-appchangelog-plugin') ?></button>&nbsp;&nbsp;<a onclick="this.parentNode.style.display = 'none';return false;" href="#" class="button-secondary"><?php _e('Cancel','simple-appchangelog-plugin') ?></a>
                            </div>
                        </td>
                    </tr>
                    <?php $i++; } ?>
                </tbody>
                <!-- End table body -->
                <!-- Table footer -->
                <tfoot>
                    <tr class="thead">
                        <th scope="col" class="check-column" ><input type="checkbox"/></th>
                        <?php 
                        foreach($sAppChangelog_versionManager->get_attributes() as $attributes ){
                            if($attributes['name'] != 'projectID'){?>
                            <th scope="col"><?php
                            echo $attributes['title']
                           ?></th>
                        <?php  }} ?>
                        <th scope="col"><?php _e('Options','simple-appchangelog-plugin') ?></th>
                    </tr>
                </tfoot>
                <!-- End table footer -->
                </table>
                <!-- Table End -->
                
                <!-- OptionChoser2 -->
                <div class="tablenav">
                    <div class="alignleft actions">
                        <select name="action2">
                            <option selected="selected" value=""><?php _e('Actions','simple-appchangelog-plugin')?></option>
                            <option value="deleteVersion"><?php _e('Delete Versions', 'simple-appchangelog-plugin') ?></option>
                        </select>
                        <input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="<?php _e('Do action','simple-appchangelog-plugin') ?>"/>
                        <a href="<?php echo get_admin_url('','?page=simple_appchangelog_main_page&ViewVersionProject='. $_GET['ViewVersionProject'] . '&CreateNewVersion=1') ?>" class="button-primary"><?php _e('Create new Version','simple-appchangelog-plugin') ?></a>
                    </div>
                    <br/>
                </div>
                <!-- End OptionChoser2-->
            </form>
    </div>
        <?php
            } else {
            //Creating new version
                if(isset($_POST['newVersionCreatedInfo']) && isset($_POST['versionName']) && ($_POST['newVersionCreatedInfo'] == $_GET['ViewVersionProject'])
                        && isset($_POST['_wpnonce_sappchangelog_CreateVersion']) && wp_verify_nonce($_POST['_wpnonce_sappchangelog_CreateVersion'],'sappchangelog_CreateVersion')){
                    if($sAppChangelog_versionManager->add_version(array('projectID' => $_GET['ViewVersionProject'],
                                                                     'orden' => 1,
                                                                     'title' => $_POST['versionName'],
                                                                     'description' => $_POST['versionDescription']))){
                        echo '<div class="notice notice-success"><p>'.__('Version Created!','simple-appchangelog-plugin'). '<a href="' .get_admin_url('','?page=simple_appchangelog_main_page&ViewVersionProject='. $_GET['ViewVersionProject'] .''). '"> ' . __('Return','') .'</a></p></div>';
                    } else {
                         echo '<div class="notice notice-error"><p>'.__('There was a problem while creating a new version!','simple-appchangelog-plugin').'</p></div>';                                                
                    }
                     
                }
            ?>
            <form method="POST" name="CreateNewVersion" target="_self" enctype="multipart/form-data">
                <?php wp_nonce_field('sappchangelog_CreateVersion', '_wpnonce_sappchangelog_CreateVersion', false); ?>
                <label><?php _e('Version Name','simple-appchangelog-plugin') ?></label><br/>
                <input type="text" name="versionName" size="100" id="title" spellcheck="true" autocomplete="off"/><br/><br/>
                <label><?php _e('Version Descripcion','simple-appchangelog-plugin') ?></label><br/>
                <?php wp_editor('', 'versionDescription', array('textarea_name' => 'versionDescription',
                                'media_buttons' => true,
                                'wpautop' => true,
                                'tinymce' => true)) ?><br/><br/>
                <p class="submit">
                    <button type="submit" name="newVersionCreatedInfo" value="<?php echo $_GET['ViewVersionProject'] ?>"  class="button-primary" ><?php _e('New Version', 'simple-appchangelog-plugin') ?></button>              
                </p>
            </form>
    </div>
        <?php
            }
        }
    }
    //End Html code
}
function simple_appChangelog_new_project_page(){
    //HTML code starts
    ?>
    <div class="warp">
	<h2>Simple App Changelog - Create a new Project</h2>
        <?php
        if(count($_POST) > 0 && isset($_POST['_wpnonce_sappchangelog_newproject']) && wp_verify_nonce($_POST['_wpnonce_sappchangelog_newproject'], 'sappchangelog_newproject')) {
            if(isset($_POST['Submit'])){
                unset($_POST['Submit']);
                global $sAppChangelog_projectManager;
                $alreadyExsists = $sAppChangelog_projectManager->check_project_name($_POST['projectTitle'],$_POST['projectSlug']);
                if(!$alreadyExsists){
                    if($sAppChangelog_projectManager->add_project($_POST['projectTitle'],$_POST['projectSlug'])){
                        echo '<div class="notice notice-success"><p>'.__('Project created!','simple-appchangelog-plugin').'</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>'.__('There was a problem creating the Project!','simple-appchangelog-plugin').'</p></div>';
                    }
                } else {
                    //Add already exists
                    echo '<div class="notice notice-error"><p>'.__('This project already exists!','simple-appchangelog-plugin').'</p></div>';
                }
            }
        }
    ?>
    <form method="POST" name="CreateNewProject" target="_self" enctype="multipart/form-data">
    <?php wp_nonce_field('sappchangelog_newproject', '_wpnonce_sappchangelog_newproject'); ?>
    <label><?php _e('Project Name','simple-appchangelog-plugin') ?></label><br/>
    <input type="text" name="projectTitle" size="100" id="title" spellcheck="true" autocomplete="off" />
    <br/>
    <br/>
    <label><?php _e('Project Slug','simple-appchangelog-plugin') ?></label><br/>
    <input type="text" name="projectSlug" size="100" id="slug" spellcheck="true" autocomplete="off" />
    <br/>
    <br/>
    <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('New Project', 'simple-appchangelog-plugin') ?>"  class="button-primary" />
    </p>
    </form>
    </div>
    <?php
    //HTML code ends
}
?>