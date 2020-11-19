<?php if (!defined('APPLICATION')) exit();

if (c('Garden.Installed')) {

    // Update Vanilla Role Names
    Gdn::sql()->update('Role')->set('Name', 'Vanilla Member')->where('RoleID', 8)->put();
    Gdn::sql()->update('Role')->set('Name', 'Vanilla Admin')->where('RoleID', 16)->put();
    Gdn::sql()->update('Role')->set('Name', 'Vanilla Moderator')->where('RoleID', 32)->put();


    //
    // Update Vanilla tables and data
    //

    //Add extra columns in Category : https://github.com/topcoder-platform/forums/issues/178
    if(!Gdn::structure()->table('Category')->columnExists('GroupID')) {
        Gdn::structure()->table('Category')
            ->column('GroupID', 'int', true, 'key')
            ->set(false, false);

        // Update data after adding GroupID column: https://github.com/topcoder-platform/forums/issues/178
        Gdn::sql()->query("UPDATE GDN_Category c
        INNER JOIN (SELECT c.CategoryID, g.GroupID FROM GDN_Category c , GDN_Group g WHERE c.UrlCode LIKE concat(g.ChallengeID,'%')) AS src
        ON src.CategoryID = c.CategoryID
        SET c.GroupID = src.GroupID
        WHERE c.GroupID IS NULL");
    }



    // Add the column Type in Group : https://github.com/topcoder-platform/forums/issues/133
    if(! Gdn::structure()->table('Group')->columnExists('Privacy')) {
        if(Gdn::structure()->table('Group')->renameColumn('Type', 'Privacy')) {

            // Reset the internal state of this object so that it can be reused.
            Gdn::structure()->reset();

            Gdn::structure()->table('Group')
            ->column('Type', ['challenge', 'regular'], true)
            ->set(false, false);

            // Update existing data, all groups with ChallengeID will have the type 'challenge'
            Gdn::sql()->query("UPDATE GDN_Group g
                SET g.Type = CASE WHEN g.ChallengeID IS NOT NULL THEN 'challenge'
                ELSE 'regular' END");

            Gdn::structure()->table('Group')
            ->column('Type', ['challenge', 'regular'], false)
            ->set(false, false);
        }
    }

    // Add the column Archived in Group : https://github.com/topcoder-platform/forums/issues/136
    if(!Gdn::structure()->table('Group')->columnExists('Archived')) {
        Gdn::structure()->table('Group')
            ->column('Archived', 'tinyint(1)', '0')
            ->set(false, false);
    }
}