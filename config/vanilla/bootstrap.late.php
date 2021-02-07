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

   // FIX: https://github.com/topcoder-platform/forums/issues/218
   // Create a parent category for Groups if it doesn't exist
   // Make sure that the UrlCode is unique among categories.
   $GroupCategoryExists = Gdn::sql()->select('CategoryID')
        ->from('Category')
        ->where('UrlCode', 'groups')
        ->get()->numRows();
   if($GroupCategoryExists == 0) {
       $data = [
           'Name' => 'Groups',
           'UrlCode' => 'groups',
           'DisplayAs' => 'categories',
           'ParentCategoryID' => -1,
           'AllowDiscussions'=> 0,
       ];
       $date = Gdn_Format::toDateTime();
       $CategoryModel = CategoryModel::instance();
       Gdn::sql()->insert('Category', ['ParentCategoryID' => -1, 'TreeLeft' => 2, 'TreeRight' => 3, 'Depth' => 1, 'InsertUserID' => 1,
           'UpdateUserID' => 1, 'DateInserted' => $date, 'DateUpdated' => $date,
           'Name' => 'Groups', 'UrlCode' => 'groups', 'Description' => '', 'PermissionCategoryID' => -1, 'DisplayAs' => 'Categories',
           'LastDiscussionCommentsDate' => $date]);
       $CategoryModel->rebuildTree();
       $CategoryModel->recalculateTree();
       unset($CategoryModel);
    }


   // Define some permissions for the Vanilla categories.
   // FIX: https://github.com/topcoder-platform/forums/issues/373
   $PermissionModel->define(
        [
            'Vanilla.Discussions.Uploads' => 0,
            'Vanilla.Comments.Uploads' => 0],
        'tinyint',
        'Category',
        'PermissionCategoryID'
    );
}
