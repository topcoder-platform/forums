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

    updateRolePermissions(RoleModel::TYPE_GUEST, RoleModel::VANILLA_GUEST_ROLES);

   // TODO: Role permission might be configured manually in the env
   // Before uncommenting the next lines:
   // Check all roles in the env and update all role permissions in RoleModel
   // updateRolePermissions(RoleModel::TYPE_TOPCODER, RoleModel::TOPCODER_ROLES);
   // updateTopcoderRolePermissions(RoleModel::TYPE_TOPCODER,RoleModel::TOPCODER_PROJECT_ROLES);
}
