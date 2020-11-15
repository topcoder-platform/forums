<?php if (!defined('APPLICATION')) exit();

if (c('Garden.Installed')) {

    $Database = Gdn::database();
    $SQL = $Database->sql();

    $Construct = $Database->structure();

    // Update Vanilla Role Names
    Gdn::sql()->update('Role')->set('Name', 'Vanilla Member')->where('RoleID', 8)->put();
    Gdn::sql()->update('Role')->set('Name', 'Vanilla Admin')->where('RoleID', 16)->put();
    Gdn::sql()->update('Role')->set('Name', 'Vanilla Moderator')->where('RoleID', 32)->put();


    //
    // Update Vanilla tables
    //

    //Add extra columns in Category : https://github.com/topcoder-platform/forums/issues/178
    $Construct->table('Category');
    $GroupIDExists = $Construct->columnExists('GroupID');
    if(!$GroupIDExists) {
        $Construct->column('GroupID', 'int', true, 'key');
        $Construct->set(false, false);
    }

    // Update data after adding GroupID column: https://github.com/topcoder-platform/forums/issues/178
    Gdn::sql()->query("UPDATE GDN_Category c
        INNER JOIN (select c.CategoryID, g.GroupID from GDN_Category c , GDN_Group g where c.UrlCode like concat(g.ChallengeID,'%')) as src
        ON src.CategoryID = c.CategoryID
        SET c.GroupID = src.GroupID
        WHERE c.GroupID is null");

}