<?php if (!defined('APPLICATION')) exit();
/**
 * Bootstrap Late
 *
 * All configurations are loaded, as well as the Application, Plugin and Theme
 * managers.
 */

if (c('Garden.Installed')) {
    $Database = Gdn::database();
    $SQL = $Database->sql();
    $PermissionModel = Gdn::permissionModel();
    $PermissionModel->Database = Gdn::database();
    $PermissionModel->SQL = $SQL;

    // Define some global vanilla permissions
    $PermissionModel->define(['Groups.Group.Add',
        'Groups.Group.Delete',
        'Groups.Group.Edit',
        'Groups.Category.Manage',
        'Groups.Moderation.Manage',
        'Groups.EmailInvitations.Add',
        'Groups.Group.Archive']);

   // TODO: need to be sure that all roles and permissions haven't be changed manually in prod/dev
    updateTopcoderRolePermissions(RoleModel::TOPCODER_ROLES);
   // updateTopcoderRolePermissions(RoleModel::TOPCODER_PROJECT_ROLES);
}
