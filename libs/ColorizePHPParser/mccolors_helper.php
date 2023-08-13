<?php

if ( ! function_exists('mccolors')) {

    /**
     * Minecraft Color Parser for PHP
     * @return string
     */
    
    function mccolors($string) {

        preg_match_all("/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/", $string, $broken_up_strings);
        $return_string = "";
        foreach ($broken_up_strings as $results)
        {
            $ending = '';
            foreach ($results as $individual)
            {
                $code = preg_split("/[&§][0-9a-z]/", $individual);
                preg_match("/[&§][0-9a-z]/", $individual, $prefix);
                if (isset($prefix[0]))
                {
                    $actualcode = substr($prefix[0], 1);
                    switch ($actualcode)
                    {
                        case '0':
                            $return_string = $return_string . '<span class="mc-color mc-0">';
                            $ending = $ending . "</span>";
                            break;

                        case "1":
                            $return_string = $return_string . '<span class="mc-color mc-1">';
                            $ending = $ending . "</span>";
                        break;

                        case "2":
                            $return_string = $return_string . '<span class="mc-color mc-2">';
                            $ending = $ending . "</span>";
                        break;

                        case "3":
                            $return_string = $return_string . '<span class="mc-color mc-3">';
                            $ending = $ending . "</span>";
                        break;

                        case "4":
                            $return_string = $return_string . '<span class="mc-color mc-4">';
                            $ending = $ending . "</span>";
                        break;

                        case "5":
                            $return_string = $return_string . '<span class="mc-color mc-5">';
                            $ending = $ending . "</span>";
                        break;

                        case "6":
                            $return_string = $return_string . '<span class="mc-color mc-6">';
                            $ending = $ending . "</span>";
                        break;

                        case "7":
                            $return_string = $return_string . '<span class="mc-color mc-7">';
                            $ending = $ending . "</span>";
                        break;

                        case "8":
                            $return_string = $return_string . '<span class="mc-color mc-8">';
                            $ending = $ending . "</span>";
                        break;

                        case "9":
                            $return_string = $return_string . '<span class="mc-color mc-9">';
                            $ending = $ending . "</span>";
                        break;

                        case "a":
                            $return_string = $return_string . '<span class="mc-color mc-a">';
                            $ending = $ending . "</span>";
                        break;

                        case "b":
                            $return_string = $return_string . '<span class="mc-color mc-b">';
                            $ending = $ending . "</span>";
                        break;

                        case "c":
                            $return_string = $return_string . '<span class="mc-color mc-c">';
                            $ending = $ending . "</span>";
                        break;

                        case "d":
                            $return_string = $return_string . '<span class="mc-color mc-d">';
                            $ending = $ending . "</span>";
                        break;
                        case "e":
                            $return_string = $return_string . '<span class="mc-color mc-e">';
                            $ending = $ending . "</span>";
                        break;

                        case "f":
                            $return_string = $return_string . '<span class="mc-color mc-f">';
                            $ending = $ending . "</span>";
                        break;

                        case "l":
                            $return_string = $return_string . '<span class="mc-l">';
                            $ending = "</span>" . $ending;
                        break;

                        case "m":
                            $return_string = $return_string . '<span class="mc-m">';
                            $ending = "</span>" . $ending;
                        break;

                        case "n":
                            $return_string = $return_string . '<span class="mc-n">';
                            $ending = "</span>" . $ending;
                        break;

                        case "o":
                            $return_string = $return_string . '<span class="mc-o">';
                            $ending = "</span>" . $ending;
                        break;

                        case "r":
                            $return_string = $return_string . '<span class="mc-r">';
                            $ending = '</span>';
                        break;

                        case 'k':
                            $return_string = $return_string . '<span class="mc-k">';
                            $ending = '</span>';
                        break;
                    }
                    if (isset($code[1]))
                    {
                        $return_string = $return_string . $code[1];
                        if (isset($ending) && strlen($individual) > 2)
                        {
                            $return_string = $return_string . $ending;
                            $ending = '';
                        }
                    }
                }
                else
                {
                    $return_string = $return_string . $individual;
                }

            }
        }

        return $return_string;

    }

}