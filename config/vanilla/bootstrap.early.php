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

    // Delete the records with UserID=0 (Guests) from UserRole table
    // FIX: https://github.com/topcoder-platform/forums/issues/108
    Gdn::sql()->delete('UserRole',['UserID' => 0]);

    // FIX: https://github.com/topcoder-platform/forums/issues/227
    // Sorting discussions by last date.
    // The last date can be any of  InsertedDate(discussion), UpdatedDate(discussion), InsertedDate(comment) or UpdatedDate(comment).
    // The new columns will be added to keep the last date and who made changes.
    // By default Vanilla uses only DateInserted in calculated columns.
    // Moreover, the calculated columns (LastCommentID, LastDiscussionID and others) are used in calculations/aggregations.
    // Don't modified default Vanilla calculated columns.
    if(!Gdn::structure()->table('Discussion')->columnExists('LastDiscussionCommentsDate')) {
        Gdn::structure()->table('Discussion')
            ->column('LastDiscussionCommentsDate', 'datetime', true, ['index', 'index.CategoryPages'])
            ->column('LastDiscussionCommentsUserID', 'int', true)
            ->set(false, false);
        // Step 1. Calculate the last date for discussions.
        $query = "UPDATE GDN_Discussion p
            SET p.LastDiscussionCommentsDate = (SELECT greatest(COALESCE(MAX(c.DateInserted), 0), COALESCE(MAX(c.DateUpdated), 0), COALESCE(p.DateUpdated, 0), COALESCE(p.DateInserted,0))
            FROM GDN_Comment c WHERE p.DiscussionID = c.DiscussionID)";
        Gdn::sql()->query($query);

        // Step 2. Update discussions with comments.
        $query = "UPDATE GDN_Discussion p
            SET p.LastDiscussionCommentsUserID = (
                SELECT CASE
                           WHEN COALESCE(p.DateUpdated,p.DateInserted) > COALESCE(c.DateUpdated,c.DateInserted) THEN
                               COALESCE(p.UpdateUserID, p.InsertUserID)
                           ELSE COALESCE(c.UpdateUserID,c.InsertUserID) END AS LastUserID FROM GDN_Comment c
                WHERE  c.DiscussionID =  p.DiscussionID ORDER BY  c.DateUpdated DESC,c.DateInserted DESC LIMIT 1)";
        Gdn::sql()->query($query);

        // Step 3. Update discussions without comments.
        $query ="UPDATE GDN_Discussion p
            SET p.LastDiscussionCommentsUserID = COALESCE(p.UpdateUserID,p.InsertUserID)
            WHERE  p.LastDiscussionCommentsUserID IS NULL";
        Gdn::sql()->query($query);
    }

    // Sorting categories by the last date
    // https://github.com/topcoder-platform/forums/issues/227
    if(!Gdn::structure()->table('Category')->columnExists('LastDiscussionCommentsUserID')) {
        Gdn::structure()->table('Category')
            ->column('LastDiscussionCommentsUserID', 'int', true)
            ->column('LastDiscussionCommentsDiscussionID', 'int', true)
            ->column('LastDiscussionCommentsDate', 'datetime', true)
            ->set(false, false);

        // Step1. Update categories with type 'discussions':
        $query = "UPDATE GDN_Category c
            SET c.LastDiscussionCommentsDate = (SELECT MAX(d.LastDiscussionCommentsDate)FROM GDN_Discussion d WHERE  d.CategoryID =  c.CategoryID),
            c.LastDiscussionCommentsUserID = (SELECT d.LastDiscussionCommentsUserID FROM GDN_Discussion d
            WHERE  d.CategoryID =  c.CategoryID ORDER BY  d.LastDiscussionCommentsDate DESC limit 1),
            c.LastDiscussionCommentsDiscussionID = (SELECT d.DiscussionID FROM GDN_Discussion d
            WHERE  d.CategoryID =  c.CategoryID ORDER BY  d.LastDiscussionCommentsDate DESC limit 1)";
        Gdn::sql()->query($query);


        // Step2. Update all ancestor categories.
        // The MAX category depth is 4 for challenges
        $ancestorQuery = "UPDATE GDN_Category pc, (
            SELECT  c1.ParentCategoryID AS ParentCategoryID, c1.LastDiscussionCommentsDate, c1.LastDiscussionCommentsUserID, c1.LastDiscussionCommentsDiscussionID 
            FROM GDN_Category c1 inner join (SELECT c.ParentCategoryID, MAX(c.LastDiscussionCommentsDate) AS LastDiscussionCommentsDate FROM GDN_Category c 
            GROUP BY c.ParentCategoryID) c2 on c1.ParentCategoryID = c2.ParentCategoryID AND c1.LastDiscussionCommentsDate = c2.LastDiscussionCommentsDate) c3
            SET pc.LastDiscussionCommentsDiscussionID = c3.LastDiscussionCommentsDiscussionID, pc.LastDiscussionCommentsUserID = c3.LastDiscussionCommentsUserID,
            pc.LastDiscussionCommentsDate = c3.LastDiscussionCommentsDate
            WHERE pc.CategoryID = c3.ParentCategoryID AND pc.Depth = %d";

        for ($i = 3; $i > -1; $i--) {
            Gdn::sql()->query(sprintf($ancestorQuery, $i));
        }

        //Step 3. Update categories without discussions.
        $emptyAncestorQuery = "UPDATE GDN_Category p
            SET p.LastDiscussionCommentsDate = COALESCE(p.DateUpdated, p.DateInserted)
            WHERE p.LastDiscussionCommentsDate IS NULL && p.LastDiscussionCommentsUserID IS NULL  &&
            p.LastDiscussionCommentsDiscussionID IS NULL";
        Gdn::sql()->query($emptyAncestorQuery);
    }

    // FIX: https://github.com/topcoder-platform/forums/issues/449
    if(!Gdn::structure()->tableExists('GroupInvitation')) {
        // Group  Invitation Table
        Gdn::structure()->table('GroupInvitation')
            ->primaryKey('GroupInvitationID')
            ->column('GroupID', 'int', false, 'index')
            ->column('Token', 'varchar(32)', false, 'unique')
            ->column('InvitedByUserID', 'int', false, 'index')
            ->column('InviteeUserID', 'int', false, 'index')
            ->column('DateInserted', 'datetime', false, 'index')
            ->column('Status', ['pending', 'accepted', 'declined', 'deleted'])
            ->column('DateAccepted', 'datetime', true)
            ->column('DateExpires', 'datetime')
            ->set(false, false);
    }

    // FIX: https://github.com/topcoder-platform/forums/issues/479
    if(!Gdn::structure()->table('User')->columnExists('CountWatchedCategories')) {
        Gdn::structure()->table('User')
        ->column('CountWatchedCategories', 'int', true)
        ->set(false, false);

        // Set count of Watched Categories for users
        Gdn::sql()->query('update GDN_User u, (select wc.UserID, count(wc.CategoryID) count from GDN_Category c
            join (select distinct um.UserID, SUBSTRING_INDEX(um.Name, ".", -1) as CategoryID from GDN_UserMeta  um  where um.Name LIKE "Preferences.%" and Value = 1) wc
            where wc.CategoryID = c.CategoryID group by wc.UserID) uc
            set u.CountWatchedCategories  = uc.count
            where u.UserID = uc.UserID', 'update');

        Gdn::sql()->query('update GDN_User u set u.CountWatchedCategories  = 0 where u.CountWatchedCategories is null', 'update');
    }

}