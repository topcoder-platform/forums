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
        $insertUserID = val('InsertUserID', $row);
        $dateUpdated = val('DateUpdated', $row);
        $updateUserID = val('UpdateUserID', $row);

        if ($dateUpdated) {
            $updateUser = Gdn::userModel()->getID($updateUserID);
            $dateUpdatedFormatted = formatDateCustom($dateUpdated, false);
            if ($updateUser && $insertUserID != $updateUserID) {
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

if (!function_exists('watchIcon')) {
    /**
     *
     * Writes the Watch/watching icon
     *
     * @param int $categoryID
     * @return string
     */
    function watchIcon($hasWatched = false, $title='') {
        if($hasWatched) {
            $icon = <<<EOT
<svg width="21px" height="14px" viewBox="0 0 21 14" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="02-Challenge-Forums" transform="translate(-1261.000000, -328.000000)" fill="#0AB88A" fill-rule="nonzero">
            <path d="M1271.08333,328 C1266.5,328 1262.58583,330.850833 1261,334.875 C1262.58583,338.899167 1266.5,341.75 1271.08333,341.75 C1275.66667,341.75 1279.58083,338.899167 1281.16667,334.875 C1279.58083,330.850833 1275.66667,328 1271.08333,328 Z M1271.08333,339.458333 C1268.55333,339.458333 1266.5,337.405 1266.5,334.875 C1266.5,332.345 1268.55333,330.291667 1271.08333,330.291667 C1273.61333,330.291667 1275.66667,332.345 1275.66667,334.875 C1275.66667,337.405 1273.61333,339.458333 1271.08333,339.458333 Z M1271.08333,332.125 C1269.56167,332.125 1268.33333,333.353333 1268.33333,334.875 C1268.33333,336.396667 1269.56167,337.625 1271.08333,337.625 C1272.605,337.625 1273.83333,336.396667 1273.83333,334.875 C1273.83333,333.353333 1272.605,332.125 1271.08333,332.125 Z" id="Shape"></path>
        </g>
    </g>
</svg>       
EOT;
        } else {
            $icon = <<<EOT
         <svg width="22px" height="22px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="02-Challenge-Forums" transform="translate(-1260.000000, -594.000000)">
            <g id="watch-icon" transform="translate(1260.000000, 594.000000)">
                <polygon id="Path" points="0 0 22 0 22 22 0 22"></polygon>
                <path d="M11,6.41666667 C14.4741667,6.41666667 17.5725,8.36916667 19.085,11.4583333 C17.5725,14.5475 14.4741667,16.5 11,16.5 C7.52583333,16.5 4.4275,14.5475 2.915,11.4583333 C4.4275,8.36916667 7.52583333,6.41666667 11,6.41666667 M11,4.58333333 C6.41666667,4.58333333 2.5025,7.43416667 0.916666667,11.4583333 C2.5025,15.4825 6.41666667,18.3333333 11,18.3333333 C15.5833333,18.3333333 19.4975,15.4825 21.0833333,11.4583333 C19.4975,7.43416667 15.5833333,4.58333333 11,4.58333333 Z M11,9.16666667 C12.265,9.16666667 13.2916667,10.1933333 13.2916667,11.4583333 C13.2916667,12.7233333 12.265,13.75 11,13.75 C9.735,13.75 8.70833333,12.7233333 8.70833333,11.4583333 C8.70833333,10.1933333 9.735,9.16666667 11,9.16666667 M11,7.33333333 C8.72666667,7.33333333 6.875,9.185 6.875,11.4583333 C6.875,13.7316667 8.72666667,15.5833333 11,15.5833333 C13.2733333,15.5833333 15.125,13.7316667 15.125,11.4583333 C15.125,9.185 13.2733333,7.33333333 11,7.33333333 Z" id="Shape" fill="#555555" fill-rule="nonzero"></path>
            </g>
        </g>
    </g>
</svg>
EOT;
        }

        return $icon;
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
    function watchButton($category, $isHijackButton = true) {
        $output = ' ';
        $userID = Gdn::session()->UserID;
        if(is_numeric($category)) {
            $category = CategoryModel::categories($category);
        }

        //if ($userID && $category && $category['DisplayAs'] == 'Discussions') {
        if ($userID && $category) {
            $categoryModel = new CategoryModel();
            $categoryID= val('CategoryID', $category);
            $hasWatched = $categoryModel->hasWatched($categoryID, $userID);

            $text = $hasWatched ? t('Stop watching forum') : t('Watch forum');
            $icon = '<span class="tooltiptext">'.$text.'</span>'. watchIcon($hasWatched);
            $cssClasses =  'watchButton ' . ($hasWatched ? ' isWatching tooltip': 'tooltip');
            if($isHijackButton) {
                $cssClasses = 'Hijack '.$cssClasses;
            }

            $output .= anchor($icon,
                $hasWatched ? "/category/watched/{$categoryID}/" . Gdn::session()->transientKey() : "/category/watch/{$categoryID}/" . Gdn::session()->transientKey(),
                $cssClasses,
                [ 'aria-pressed' => $hasWatched ? 'true' : 'false', 'role' => 'button', 'tabindex' => '0']
            );
        }
        return $output;
    }
}

if (!function_exists('checkGroupPermission')) {
    /**
     * Check group permission for an user
     * @param $userID
     * @param $groupID
     * @param null $categoryID
     * @param null $permissionCategoryID
     * @param null $permission null - any permission for a group
     * @param bool $fullMatch
     * @return bool return true if user has a permission
     */
   function checkGroupPermission($userID,$groupID, $categoryID = null , $permissionCategoryID = null , $permission = null, $fullMatch = true) {
       return GroupModel::checkPermission($userID,$groupID, $categoryID,$permissionCategoryID , $permission, $fullMatch);
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
    function sortsDropDown($preferenceKey, $baseUrl, array $filters = [], $extraClasses = '', $default = null, $defaultUrl = null, $label = 'Sort') {
        $links = [];
        $active =  Gdn::session()->getPreference($preferenceKey, null);
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

        return sortsDropDown('CategorySort',
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
                'name' => t('Most recent'),
                'param' => 'sort',
                'value' => 'new',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ],

            [
                'name' => t('Highest views'),
                'param' => 'sort',
                'value' => 'views',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ],
            [
                'name' => t('Highest responses'),
                'param' => 'sort',
                'value' => 'comments',
                'extra' => ['TransientKey' => $transientKey, 'save' => 1]
            ]
        ];


        $defaultParams = [];
        if (!empty($defaultParams)) {
            $defaultUrl = $baseUrl.'?'.http_build_query($defaultParams);
        } else {
            $defaultUrl = $baseUrl;
        }

        return sortsDropDown('DiscussionSort',
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

if (!function_exists('myWatchingMenuItem')) {
    /**
     *
     *
     * @param $CountBookmarks
     * @return string
     */
    function myWatchingMenuItem($CountWatches) {
        if (!Gdn::session()->isValid()) {
            return '';
        }
        $watching = t('Watching');
        $watching .= FilterCountString($CountWatches, '/watching');
        $cssClass = 'MyWatching';
        Logger::event(
            'topcoder_plugin',
            Logger::DEBUG,
            'myWatchingMenuItem:'.Gdn::controller()->RequestMethod,
            []
        );
        $cssClass .= Gdn::controller()->ControllerName == 'watchingcontroller' ? ' Active' : '';
        $cssClass .= $CountWatches == 0 ? ' hidden': '';
        return sprintf('<li id="MyWatching" class="%s">%s</li>', $cssClass, anchor(sprite('SpBookmarks').$watching, '/watching'));
    }
}

if(!function_exists('writeInlineDiscussionOptions')) {
    function writeInlineDiscussionOptions($discussionRow) {
       $discussionID = val('DiscussionID', $discussionRow);
       Gdn::controller()->EventArguments['RecordID'] = $discussionID;
        //Gdn_Theme::bulletRow();
       echo '<div class="Controls flex">';
       echo '<div class="left">';
       Gdn::controller()->EventArguments['RecordID'] = $discussionID;
       Gdn::controller()->fireEvent('InlineDiscussionOptionsLeft');
       echo '</div>';
       echo '<div class="center"></div>';
       echo '<div class="right">';

       $sender = Gdn::controller();

       $sender->EventArguments['Object'] = $discussionRow;
       $sender->EventArguments['Type'] = 'Discussion';
       $sender->fireEvent('BeforeInlineDiscussionOptions');

       // DropdownModule
       $discussionDropdown = getDiscussionOptionsDropdown($discussionRow);

       // Allow plugins to edit the dropdown.
       $sender->EventArguments['DiscussionOptions'] = &$discussionDropdown ;
       $sender->EventArguments['Discussion'] = $discussionRow;
       $sender->fireEvent('InlineDiscussionOptions');

       $discussionDropdownItems = $discussionDropdown->toArray()['items'];

       unset($discussionDropdownItems['announce']);
       unset($discussionDropdownItems['sink']);
       unset($discussionDropdownItems['close']);
       unset($discussionDropdownItems['dismiss']);
       unset($discussionDropdownItems['move']);
       unset($discussionDropdownItems['tag']);

       if (!empty($discussionDropdownItems) && is_array($discussionDropdownItems)) {
           array_walk($discussionDropdownItems, function(&$value, $key) {
               $anchor = anchor($value['text'], $value['url'], val('cssClass', $value, $key));
               $value = '<span class="" style="">'.$anchor.'</span>';
           });

           echo implode('<span class="MiddleDot">·</span>', $discussionDropdownItems);
       }
       echo '</div>';
       echo '</div>';

    }
}

if(!function_exists('writeInlineCommentOptions')) {
    function writeInlineCommentOptions($comment) {
        $iD = val('CommentID', $comment);
        Gdn::controller()->EventArguments['RecordID'] = $iD;
        //Gdn_Theme::bulletRow();
        echo '<div class="Controls flex">';
        echo '<div class="left"></div>';
        echo '<div class="center"></div>';
        echo '<div class="right">';

        $sender = Gdn::controller();
        $sender->EventArguments['Object'] = $comment;
        $sender->EventArguments['Type'] = 'Comment';
        $sender->fireEvent('BeforeInlineCommentOptions');

        // Write the items.
        $items = getCommentOptions($comment);
        if (!empty($items) && is_array($items)) {
            array_walk($items, function(&$value, $key) {
                $anchor = anchor($value['Label'], $value['Url'], val('Class', $value, $key));
                $value = '<span class="" style="">'.$anchor.'</span>';
            });
            echo implode('<span class="MiddleDot">·</span>', $items);
        }
        echo '</div>';
        echo '</div>';

    }
}

if (!function_exists('discussionUrl')) {
    /**
     * Return a URL for a discussion. This function is in here and not functions.general so that plugins can override.
     *
     * @param object|array $discussion
     * @param int|string $page
     * @param bool $withDomain
     * @return string
     */
    function discussionUrl($discussion, $page = '', $withDomain = true) {
        $discussion = (object)$discussion;
        $name = Gdn_Format::url($discussion->Name);

        // Disallow an empty name slug in discussion URLs.
        if (empty($name)) {
            $name = 'x';
        }

        $result = '/discussion/'.$discussion->DiscussionID.'/'.$name;

        if ($page) {
            //if ($page > 1 || Gdn::session()->UserID) {
                $result .= '/p'.$page;
           // }
        }

        return url($result, $withDomain);
    }
}

if (!function_exists('formatDateCustom')) {
    function formatDateCustom($timestamp, $showDayOfWeek=true) {
        $dateFormat = $showDayOfWeek? '%a, %b %e, %Y': '%b %e, %Y';
        $dateFormatted = Gdn::getContainer()->get(DateTimeFormatter::class)->formatDate($timestamp, false, $dateFormat);
        $timeFormatted = Gdn::getContainer()->get(DateTimeFormatter::class)->formatDate($timestamp, false, '%I:%M %p');
        return sprintf('%1$s at %2$s', $dateFormatted, $timeFormatted);
    }
}
if (!function_exists('authorProfileStats')) {
    function authorProfileStats($user) {
        $countDiscussions = plural( $user->CountDiscussions, '%s Post', '%s Posts');
        $countComments = plural( $user->CountComments, '%s Comment', '%s Comments');
        return '<span class="MItem AuthorProfileStats AuthorProfileStats_'.$user->UserID.'">'.sprintf('%1s %2s', $countDiscussions,$countComments).'</span>';
    }
}
