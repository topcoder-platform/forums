<?php if (!defined('APPLICATION')) exit();

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
                            if(function_exists('topcoderRatingCssClass')) {
                                $ratingCssClass = topcoderRatingCssClass($user->Name);
                                $result = anchor(htmlspecialchars($name), userUrl($user), $ratingCssClass);
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