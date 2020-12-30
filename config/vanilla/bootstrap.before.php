<?php

use Vanilla\Formatting\DateTimeFormatter;

if (!defined('APPLICATION')) exit();

if (!function_exists('formatString')) {
    /**
     * Formats a string by inserting data from its arguments, similar to sprintf, but with a richer syntax.
     *
     * @param string $string The string to format with fields from its args enclosed in curly braces.
     * The format of fields is in the form {Field,Format,Arg1,Arg2}. The following formats are the following:
     *  - date: Formats the value as a date. Valid arguments are short, medium, long.
     *  - number: Formats the value as a number. Valid arguments are currency, integer, percent.
     *  - time: Formats the value as a time. This format has no additional arguments.
     *  - url: Calls url() function around the value to show a valid url with the site.
     * You can pass a domain to include the domain.
     *  - urlencode, rawurlencode: Calls urlencode/rawurlencode respectively.
     *  - html: Calls htmlspecialchars.
     * @param array $args The array of arguments.
     * If you want to nest arrays then the keys to the nested values can be separated by dots.
     * @return string The formatted string.
     * <code>
     * echo formatString("Hello {Name}, It's {Now,time}.", array('Name' => 'Frank', 'Now' => '1999-12-31 23:59'));
     * // This would output the following string:
     * // Hello Frank, It's 12:59PM.
     * </code>
     */
    function formatString($string, $args = []) {
        _formatStringCallback($args, true);
        $result = preg_replace_callback('/{([^\s][^}]+[^\s]?)}/', '_formatStringCallback', $string);
        return $result;
    }
}

if (!function_exists('_formatStringCallback')) {
    /**
     * The callback helper for {@link formatString()}.
     *
     * @param array $match Either the array of arguments or the regular expression match.
     * @param bool $setArgs Whether this is a call to initialize the arguments or a matching callback.
     * @return mixed Returns the matching string or nothing when setting the arguments.
     * @access private
     */
    function _formatStringCallback($match, $setArgs = false) {
        static $args = [], $contextUserID = null;
        if ($setArgs) {
            $args = $match;

            if (isset($args['_ContextUserID'])) {
                $contextUserID = $args['_ContextUserID'];
            } else {
                $contextUserID = Gdn::session() && Gdn::session()->isValid() ? Gdn::session()->UserID : null;
            }

            return '';
        }

        $match = $match[1];
        if ($match == '{') {
            return $match;
        }

        // Parse out the field and format.
        $parts = explode(',', $match);
        $field = trim($parts[0]);
        $format = trim(($parts[1] ?? ''));
        $subFormat = isset($parts[2]) ? strtolower(trim($parts[2])) : '';
        $formatArgs = $parts[3] ?? '';

        if (in_array($format, ['currency', 'integer', 'percent'])) {
            $formatArgs = $subFormat;
            $subFormat = $format;
            $format = 'number';
        } elseif (is_numeric($subFormat)) {
            $formatArgs = $subFormat;
            $subFormat = '';
        }

        $value = valr($field, $args, null);
        if ($value === null && !in_array($format, ['url', 'exurl', 'number', 'plural'])) {
            $result = '';
        } else {
            switch (strtolower($format)) {
                case 'date':
                    switch ($subFormat) {
                        case 'short':
                            $result = Gdn_Format::date($value, '%d/%m/%Y');
                            break;
                        case 'medium':
                            $result = Gdn_Format::date($value, '%e %b %Y');
                            break;
                        case 'long':
                            $result = Gdn_Format::date($value, '%e %B %Y');
                            break;
                        default:
                            $result = Gdn_Format::date($value);
                            break;
                    }
                    break;
                case 'html':
                case 'htmlspecialchars':
                    $result = htmlspecialchars($value);
                    break;
                case 'number':
                    if (!is_numeric($value)) {
                        $result = $value;
                    } else {
                        switch ($subFormat) {
                            case 'currency':
                                $result = '$'.number_format($value, is_numeric($formatArgs) ? $formatArgs : 2);
                                break;
                            case 'integer':
                                $result = (string)round($value);
                                if (is_numeric($formatArgs) && strlen($result) < $formatArgs) {
                                    $result = str_repeat('0', $formatArgs - strlen($result)).$result;
                                }
                                break;
                            case 'percent':
                                $result = round($value * 100, is_numeric($formatArgs) ? $formatArgs : 0);
                                break;
                            default:
                                $result = number_format($value, is_numeric($formatArgs) ? $formatArgs : 0);
                                break;
                        }
                    }
                    break;
                case 'plural':
                    if (is_array($value)) {
                        $value = count($value);
                    } elseif (stringEndsWith($field, 'UserID', true)) {
                        $value = 1;
                    }

                    if (!is_numeric($value)) {
                        $result = $value;
                    } else {
                        if (!$subFormat) {
                            $subFormat = rtrim("%s $field", 's');
                        }
                        if (!$formatArgs) {
                            $formatArgs = $subFormat.'s';
                        }

                        $result = plural($value, $subFormat, $formatArgs);
                    }
                    break;
                case 'rawurlencode':
                    $result = rawurlencode($value);
                    break;
                case 'text':
                    $result = Gdn_Format::text($value, false);
                    break;
                case 'time':
                    $result = Gdn_Format::date($value, '%l:%M%p');
                    break;
                case 'url':
                    if (strpos($field, '/') !== false) {
                        $value = $field;
                    }
                    $result = url($value, $subFormat == 'domain');
                    break;
                case 'exurl':
                    if (strpos($field, '/') !== false) {
                        $value = $field;
                    }
                    $result = externalUrl($value);
                    break;
                case 'urlencode':
                    $result = urlencode($value);
                    break;
                case 'gender':
                    // Format in the form of FieldName,gender,male,female,unknown[,plural]
                    if (is_array($value) && count($value) == 1) {
                        $value = array_shift($value);
                    }

                    $gender = 'u';

                    if (!is_array($value)) {
                        $user = Gdn::userModel()->getID($value);
                        if ($user) {
                            $gender = $user->Gender;
                        }
                    } else {
                        $gender = 'p';
                    }

                    switch ($gender) {
                        case 'm':
                            $result = $subFormat;
                            break;
                        case 'f':
                            $result = $formatArgs;
                            break;
                        case 'p':
                            $result = ($parts[5] ?? ($parts[4] ?? false));
                            break;
                        case 'u':
                        default:
                            $result = ($parts[4] ?? false);
                    }

                    break;
                case 'user':
                case 'you':
                case 'his':
                case 'her':
                case 'your':
                    $argsBak = $args;
                    if (is_array($value) && count($value) == 1) {
                        $value = array_shift($value);
                    }

                    if (is_array($value)) {
                        if (isset($value['UserID'])) {
                            $user = $value;
                            $user['Name'] = formatUsername($user, $format, $contextUserID);
                            $result = userAnchor($user);
                        } else {
                            $max = c('Garden.FormatUsername.Max', 5);
                            // See if there is another count.
                            $extraCount = valr($field.'_Count', $args, 0);

                            $count = count($value);
                            $result = '';
                            for ($i = 0; $i < $count; $i++) {
                                if ($i >= $max && $count > $max + 1) {
                                    $others = $count - $i + $extraCount;
                                    $result .= ' '.t('sep and', 'and').' '
                                        .plural($others, '%s other', '%s others');
                                    break;
                                }

                                $iD = $value[$i];
                                if (is_array($iD)) {
                                    continue;
                                }

                                if ($i == $count - 1) {
                                    $result .= ' '.t('sep and', 'and').' ';
                                } elseif ($i > 0) {
                                    $result .= ', ';
                                }

                                $special = [-1 => t('everyone'), -2 => t('moderators'), -3 => t('administrators')];
                                if (isset($special[$iD])) {
                                    $result .= $special[$iD];
                                } else {
                                    $user = Gdn::userModel()->getID($iD);
                                    if ($user) {
                                        $user->Name = formatUsername($user, $format, $contextUserID);
                                        $result .= userAnchor($user);
                                    }
                                }
                            }
                        }
                    } else {
                        $user = Gdn::userModel()->getID($value);
                        if ($user) {
                            // Store this name separately because of special 'You' case.
                            $name = formatUsername($user, $format, $contextUserID);
                            // Manually build instead of using userAnchor() because of special 'You' case.
                            if(function_exists('topcoderRatingCssClass') &&
                                function_exists('topcoderRoleCssStyles')) {
                                $ratingCssClass = topcoderRatingCssClass($user);
                                $roleCssClass = topcoderRoleCssStyles($user);
                                $topcoderStyles =$ratingCssClass.' '.$roleCssClass;
                                $result = anchor(htmlspecialchars($name), userUrl($user), $topcoderStyles);
                            } else {
                                $result = anchor(htmlspecialchars($name), userUrl($user));
                            }

                        } else {
                            $result = '';
                        }
                    }

                    $args = $argsBak;
                    break;
                default:
                    $result = $value;
                    break;
            }
        }
        return $result;
    }
}

if (!function_exists('dateUpdated')) {
    /**
     *
     * Fixed issues with date format:
     * package/library/core/functions.render.php
     *
     * @param $row
     * @param null $wrap
     * @return string
     */
    function dateUpdated($row, $wrap = null) {
        $result = '';
        $dateUpdated = val('DateUpdated', $row);
        $updateUserID = val('UpdateUserID', $row);

        if ($dateUpdated) {
            $updateUser = Gdn::userModel()->getID($updateUserID);
            $dateUpdatedFormatted = Gdn::getContainer()->get(DateTimeFormatter::class)->formatDate($dateUpdated, false, DateTimeFormatter::FORCE_FULL_FORMAT);
            if ($updateUser) {
                $title = sprintf(t('Edited %s by %s.'), $dateUpdatedFormatted, val('Name', $updateUser));
                $link = userAnchor($updateUser);
                $text =  sprintf(t('edited %s by %s'), $dateUpdatedFormatted, $link);
            } else {
                $title = sprintf(t('Edited %s.'), $dateUpdatedFormatted);
                $text = sprintf(t('edited %s'), $dateUpdatedFormatted);
            }

            $result = ' <span title="'.htmlspecialchars($title).'" class="DateUpdated">'.
                $text.'</span> ';

            if ($wrap) {
                $result = $wrap[0].$result.$wrap[1];
            }
        }

        return $result;
    }
}

if (!function_exists('watchButton')) {
    /**
     *
     * Writes the Watch/watching button
     *
     * @param int $categoryID
     * @return string
     */
    function watchButton($categoryID) {
        $output = ' ';
        $userID = Gdn::session()->UserID;
        $category = CategoryModel::categories($categoryID);

        if ($userID && $category && $category['DisplayAs'] == 'Discussions') {
            $categoryModel = new CategoryModel();
            $hasWatched = $categoryModel->hasWatched($categoryID, $userID);
            $iconTitle = t('Watch');
            $icon = <<<EOT
                <svg xmlns="http://www.w3.org/2000/svg" class="watchButton-icon" viewBox="0 0 16 16" aria-hidden="true">
                    <title>{$iconTitle}</title>  
                    <path d="M7.568,14.317a.842.842,0,0,1-1.684,0,4.21,4.21,0,0,0-4.21-4.21h0a.843.843,0,0,1,0-1.685A5.9,5.9,0,0,1,7.568,14.317Zm4.21,0a.842.842,0,0,1-1.684,0A8.421,8.421,0,0,0,1.673,5.9h0a.842.842,0,0,1,0-1.684,10.1,10.1,0,0,1,10.105,10.1Zm4.211,0a.842.842,0,0,1-1.684,0A12.633,12.633,0,0,0,1.673,1.683.842.842,0,0,1,1.673,0,14.315,14.315,0,0,1,15.989,14.315ZM1.673,16a1.684,1.684,0,1,1,1.684-1.684h0A1.684,1.684,0,0,1,1.673,16Z" transform="translate(0.011 0.001)" style="fill: currentColor;"/>
                </svg>
EOT;

            $text = $hasWatched ? t('Watching') : t('Watch');
            $output .= anchor(
                $icon . $text,
                $hasWatched ? "/category/watched/{$categoryID}/" . Gdn::session()->transientKey() : "/category/watch/{$categoryID}/" . Gdn::session()->transientKey(),
                'Hijack watchButton' . ($hasWatched ? ' TextColor isWatching' : ''),
                ['title' => $text, 'aria-pressed' => $hasWatched ? 'true' : 'false', 'role' => 'button', 'tabindex' => '0']
            );
        }
        return $output;
    }
}

if (!function_exists('checkGroupPermission')) {
    /**
     * Check group permission for the current user
     * @param $groupID
     * @param null $category
     * @param null $permissionCategoryID
     * @param null $permission null - any permission for a group
     * @param bool $fullMatch
     * @return bool return true if user has a permission
     */
   function checkGroupPermission($groupID, $category = null , $permissionCategoryID = null , $permission = null, $fullMatch = true) {
       $groupModel = new GroupModel();
       return $groupModel->checkPermission(Gdn::session()->UserID,$groupID, $category,$permissionCategoryID , $permission, $fullMatch);
   }
}

if(!function_exists('updateRolePermissions')) {

    /**
     * Update role permissions
     * @param $roleType
     * @param $roles
     */
    function updateRolePermissions($roleType, $roles)  {
        $RoleModel = new RoleModel();
        $PermissionModel = new PermissionModel();
        // Configure default permission for roles
        $allRoles = $RoleModel->getByType($roleType)->resultArray();
        foreach ($allRoles as $role) {
            $allPermissions = $PermissionModel->getRolePermissions($role['RoleID']);
            foreach ($allPermissions as $permission) {
                $roleName = $role['Name'];
                if (array_key_exists($roleName, $roles)) {
                    $globalRolePermissions = $roles[$roleName];
                    foreach ($globalRolePermissions as $key => $value) {
                        $permission[$key] = $globalRolePermissions[$key];
                    }
                    $PermissionModel->save($permission);
                }
            }
        }
    }
}

if (!function_exists('sortsDropDown')) {
    /**
     * Returns a sorting drop-down menu.
     *
     * @param string $baseUrl Target URL with no query string applied.
     * @param array $filters A multidimensional array of rows with the following properties:
     *     ** 'name': Friendly name for the filter.
     *     ** 'param': URL parameter associated with the filter.
     *     ** 'value': A value for the URL parameter.
     * @param string $extraClasses any extra classes you add to the drop down
     * @param string|null $default The default label for when no filter is active. If `null`, the default label is not added
     * @param string|null $defaultURL URL override to return to the default, unfiltered state.
     * @param string $label Text for the label to attach to the cont
     * @return string
     */
    function sortsDropDown($baseUrl, array $filters = [], $extraClasses = '', $default = null, $defaultUrl = null, $label = 'Sort') {
        $links = [];
        $active =  Gdn::session()->getPreference('CategorySort', null);
        // Translate filters into links.
        foreach ($filters as $filter) {
            // Make sure we have the bare minimum: a label and a URL parameter.
            if (!array_key_exists('name', $filter)) {
                throw new InvalidArgumentException('Sort does not have a name field.');
            }
            if (!array_key_exists('param', $filter)) {
                throw new InvalidArgumentException('Sort does not have a param field.');
            }

            // Prepare for consumption by linkDropDown.
            $query = [$filter['param'] => $filter['value']];
            if (array_key_exists('extra', $filter) && is_array($filter['extra'])) {
                $query += $filter['extra'];
            }
            $url = url($baseUrl . '?' . http_build_query($query));
            $link = [
                'name' => $filter['name'],
                'url' => $url
            ];

            // If we don't already have an active link, and this parameter and value match, this is the active link.
            if ($active === null && Gdn::request()->get($filter['param']) == $filter['value']) {
                $active = $filter['value'];
                $link['active'] = true;
            } else if ($active == $filter['value']){
                $link['active'] = true;
                $active = $filter['value'];
            }

            // Queue up another filter link.
            $links[] = $link;
        }

        if ($default !== null) {
            $default = t('All');
            // Add the default link to the top of the list.
            array_unshift($links, [
                'active' => $active === null,
                'name' => $default,
                'url' => $defaultUrl ?: $baseUrl
            ]);
        }

        // Generate the markup for the drop down menu.
        $output = linkDropDown($links, 'selectBox-following ' . trim($extraClasses), t($label) . ': ');
        return $output;
    }
}

if (!function_exists('categorySorts')) {
    /**
     * Returns category sorting.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function categorySorts($extraClasses = '') {
        if (!Gdn::session()->isValid()) {
            return;
        }

        $baseUrl = preg_replace('/\?.*/', '',  Gdn::request()->getFullPath());
        $transientKey = Gdn::session()->transientKey();
        $filters = [
            [
                'name' => t('New'),
                'param' => 'sort',
                'value' => 'new',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ],

            [
                'name' => t('Old'),
                'param' => 'sort',
                'value' => 'old',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ]
        ];

        $defaultParams = [];
        if (!empty($defaultParams)) {
            $defaultUrl = $baseUrl.'?'.http_build_query($defaultParams);
        } else {
            $defaultUrl = $baseUrl;
        }

        return sortsDropDown(
            $baseUrl,
            $filters,
            $extraClasses,
            null,
            $defaultUrl,
            'Sort'
        );
    }
}

if (!function_exists('discussionSorts')) {
    /**
     * Returns discussions sorting.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function discussionSorts($extraClasses = '') {
        if (!Gdn::session()->isValid()) {
            return;
        }

        $baseUrl = preg_replace('/\?.*/', '',  Gdn::request()->getFullPath());
        $transientKey = Gdn::session()->transientKey();
        $filters = [
            [
                'name' => t('New'),
                'param' => 'sort',
                'value' => 'new',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ],
            [
                'name' => t('Old'),
                'param' => 'sort',
                'value' => 'old',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ]
        ];


        $defaultParams = [];
        if (!empty($defaultParams)) {
            $defaultUrl = $baseUrl.'?'.http_build_query($defaultParams);
        } else {
            $defaultUrl = $baseUrl;
        }

        return sortsDropDown(
            $baseUrl,
            $filters,
            $extraClasses,
            null,
            $defaultUrl,
            'Sort'
        );
    }
}

if (!function_exists('discussionFilters')) {
    /**
     *
     * FIX: https://github.com/topcoder-platform/forums/issues/226
     * The source is package/library/core/functions.render.php
     * Returns discussions filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function discussionFilters($extraClasses = '') {
        if (!Gdn::session()->isValid()) {
            return;
        }

        $baseUrl = preg_replace('/\?.*/', '',  Gdn::request()->getFullPath());
        $transientKey = Gdn::session()->transientKey();
        $filters = [
            [
                'name' => t('All'),
                'param' => 'followed',
                'value' => 0,
                'extra' => ['save' => 1, 'TransientKey' => $transientKey]
            ],
            [
                'name' => t('Following'),
                'param' => 'followed',
                'value' => 1,
                'extra' => ['save' => 1, 'TransientKey' => $transientKey]
            ]
        ];

        $defaultParams = [];
        if (!empty($defaultParams)) {
            $defaultUrl = $baseUrl.'?'.http_build_query($defaultParams);
        } else {
            $defaultUrl = $baseUrl;
        }

        return filtersDropDown(
            $baseUrl,
            $filters,
            $extraClasses,
            null,
            $defaultUrl,
            'View'
        );
    }
}

if (!function_exists('categoryFilters')) {
    /**
     *
     * FIX: https://github.com/topcoder-platform/forums/issues/226
     * The source is package/library/core/functions.render.php
     *
     * Returns category filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function categoryFilters($extraClasses = '') {
        if (!Gdn::session()->isValid()) {
            return;
        }

        $baseUrl = preg_replace('/\?.*/', '',  Gdn::request()->getFullPath());
        $transientKey = Gdn::session()->transientKey();
        $filters = [
            [
                'name' => t('All'),
                'param' => 'followed',
                'value' => 0,
                'extra' => ['save' => 1, 'TransientKey' => $transientKey]
            ],

            [
                'name' => t('Following'),
                'param' => 'followed',
                'value' => 1,
                'extra' => ['save' => 1, 'TransientKey' => $transientKey]
            ]
        ];

        $defaultParams = [];
        if (!empty($defaultParams)) {
            $defaultUrl = $baseUrl.'?'.http_build_query($defaultParams);
        } else {
            $defaultUrl = $baseUrl;
        }

        return filtersDropDown(
            $baseUrl,
            $filters,
            $extraClasses,
            null,
            $defaultUrl,
            'View'
        );
    }
}

if (!function_exists('filtersDropDown')) {
    /**
     * FIX: https://github.com/topcoder-platform/forums/issues/226
     * The source is package/library/core/functions.render.php
     *
     * Returns a filtering drop-down menu.
     *
     * @param string $baseUrl Target URL with no query string applied.
     * @param array $filters A multidimensional array of rows with the following properties:
     *     ** 'name': Friendly name for the filter.
     *     ** 'param': URL parameter associated with the filter.
     *     ** 'value': A value for the URL parameter.
     * @param string $extraClasses any extra classes you add to the drop down
     * @param string|null $default The default label for when no filter is active. If `null`, the default label is "All".
     * @param string|null $defaultURL URL override to return to the default, unfiltered state.
     * @param string $label Text for the label to attach to the cont
     * @return string
     */
    function filtersDropDown($baseUrl, array $filters = [], $extraClasses = '', $default = null, $defaultUrl = null, $label = 'View') {

        $output = '';

        if (c('Vanilla.EnableCategoryFollowing')) {
            $links = [];
            $active = Gdn::session()->getPreference('FollowedCategories', null);

            // Translate filters into links.
            foreach ($filters as $filter) {
                // Make sure we have the bare minimum: a label and a URL parameter.
                if (!array_key_exists('name', $filter)) {
                    throw new InvalidArgumentException('Filter does not have a name field.');
                }
                if (!array_key_exists('param', $filter)) {
                    throw new InvalidArgumentException('Filter does not have a param field.');
                }

                // Prepare for consumption by linkDropDown.
                $value = $filter['value'];
                $query = [$filter['param'] => $value];
                if (array_key_exists('extra', $filter) && is_array($filter['extra'])) {
                    $query += $filter['extra'];
                }
                $url = url($baseUrl.'?'.http_build_query($query));
                $link = [
                    'name' => $filter['name'],
                    'url' => $url
                ];

                // If we don't already have an active link, and this parameter and value match, this is the active link.
                if ($active === null && Gdn::request()->get($filter['param']) == $filter['value']) {
                    $active = $filter['value'];
                    $link['active'] = true;
                } else if ($active == $filter['value']){
                    $link['active'] = true;
                    $active = $filter['value'];
                }

                // Queue up another filter link.
                $links[] = $link;
            }

            if ($default !== null) {
                $default = t('All');

                // Add the default link to the top of the list.
                array_unshift($links, [
                    'active' => $active === null,
                    'name' => $default,
                    'url' => $defaultUrl ?: $baseUrl
                ]);
            }
            // Generate the markup for the drop down menu.
            $output = linkDropDown($links, 'selectBox-following '.trim($extraClasses), t($label).': ');
        }

        return $output;
    }
}

if (!function_exists('filterCountString')) {
    /**
     * This function was moved from 'vanilla/applications/vanilla/views/modules/discussionfilter.php'
     * @param $count
     * @param string $url
     * @param array $options
     * @return string
     */
    function filterCountString($count, $url = '', $options = []) {
        $count = countString($count, $url, $options);
        return $count != '' ? '<span class="Aside">'.$count.'</span>' : '';
    }
}

if (!function_exists('myBookmarksMenuItem')) {
    /**
     *
     *
     * @param $CountBookmarks
     * @return string
     */
    function myBookmarksMenuItem($CountBookmarks) {
        if (!Gdn::session()->isValid()) {
            return '';
        }
        $Bookmarked = t('My Bookmarks');
        $Bookmarked .= FilterCountString($CountBookmarks, '/discussions/UserBookmarkCount');
        $cssClass = 'MyBookmarks';
        $cssClass .= Gdn::controller()->RequestMethod == 'bookmarked' ? ' Active' : '';
        $cssClass .= $CountBookmarks == 0 && Gdn::controller()->RequestMethod != 'bookmarked' ? ' hidden': '';
        return sprintf('<li id="MyBookmarks" class="%s">%s</li>', $cssClass, anchor(sprite('SpBookmarks').$Bookmarked, '/discussions/bookmarked'));
    }
}

if (!function_exists('myDraftsMenuItem')) {
    /**
     *
     *
     * @param $CountDrafts
     * @return string
     */
    function myDraftsMenuItem($CountDrafts) {
        if (!Gdn::session()->isValid()) {
            return '';
        }
        $Drafts = t('My Drafts');
        $Drafts .= FilterCountString($CountDrafts, '/drafts');
        $cssClass = 'MyDrafts';
        $cssClass .= Gdn::controller()->ControllerName == 'draftscontroller' ? ' Active' : '';
        $cssClass .= $CountDrafts == 0 ? ' hidden': '';
        return sprintf('<li id="MyDrafts" class="%s">%s</li>', $cssClass, anchor(sprite('SpMyDrafts').$Drafts, '/drafts'));
    }
}