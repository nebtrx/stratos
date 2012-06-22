<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parser
 *
 * @author neb
 */


class HtmlParser 
{
    /**
     * INCLUDE split pivot
     */
    const PIVOT_INCLUDED = FALSE;
    
    /**
     * EXCLUDE split pivot
     */
    const PIVOT_EXCLUDED = TRUE;
    
    /**
     * INCLUDE open and close tags
     */
    const TAG_INCLUDED = TRUE;
    
    /**
     * EXCLUDE tag open and close tags
     */
    const TAG_EXCLUDED = FALSE;
    
    /**
     * Returns the portion BEFORE the split pivot
     */
    const BEFORE_PIVOT = TRUE;
    
    /**
     * Return the portion AFTER the split pivot
     */
    const AFTER_PIVOT = FALSE; 

    /**
     * Returns a portion of the string that is either before or after
     * the delineator. The parse is not case sensitive, but the case of
     * the parsed string is not effected.
     * 
     * @param   string  $input_string           Input string to parse.
     * @param   string  $split_pivot            Pivot string (place where split occurs).
     * @param   boolean $return_portion         BEFORE_PIVOT returns the portion before the pivot,
     *                                          AFTER_PIVOT returns the portion after.
     * @param   boolean $pivot_inclusion        Includes/Excludes the split pivot in parsed string.
     * @return  string                          Returns a potion of the string 
     *                                          that is either before or after
     *                                          the delineator. The parse is 
     *                                          not case sensitive, but the case of
     *                                          the parsed string is not effected.
     */
    public static function splitString($input_string, $split_pivot, $return_portion, $pivot_inclusion) 
    {
        # Case insensitive parse, convert string and delineator to lower case
        $low_input_string = strtolower($input_string);
        $pivot = strtolower($split_pivot);          

        # Return text BEFORE the delineator
        if ($return_portion == self::BEFORE_PIVOT) 
        {
            if ($pivot_inclusion == self::PIVOT_EXCLUDED)  // Return text ESCL of the delineator
                $split_here = strpos($low_input_string, $pivot);
            else               // Return text INCL of the delineator
                $split_here = strpos($low_input_string, $pivot) + strlen($pivot);

            $parsed_string = substr($input_string, 0, $split_here);
        }
        # Return text AFTER the delineator
        else 
        {
            if ($pivot_inclusion == self::PIVOT_EXCLUDED)    // Return text ESCL of the delineator
                $split_here = strpos($low_input_string, $pivot) + strlen($pivot);
            else               // Return text INCL of the delineator
                $split_here = strpos($low_input_string, $pivot);

            $parsed_string = substr($input_string, $split_here, strlen($input_string));
        }
        return $parsed_string;
    }
   

    /**
     * Returns a substring of $string delineated by $start and $end
     * The parse is not case sensitive, but the case of the parsed
     * string is not effected.
     * 
     * @param   string  $input_string       Input string to parse.
     * @param   string  $start              Defines the beginning of the sub string.
     * @param   string  $end                Defines the end of the sub string
     * @param   boolean $pivot_inclusion    Includes/Excludes the split pivot in parsed string.
     * @return  string                      Returns a substring of $string delineated by $start and $end
     *                                      The parse is not case sensitive, but the case of the parsed
     *                                      string is not effected.
     */
    public static function returnBetween($input_string, $start, $end, $pivot_inclusion) 
    {
        $temp = self::splitString($input_string, $start, self::AFTER_PIVOT, $pivot_inclusion);
        return self::splitString($temp, $end, self::BEFORE_PIVOT, $pivot_inclusion);
    }

  
    /**
     * Returns an array of strings that exists repeatedly in $string.
     * This function is usful for returning an array that contains
     * links, images, tables or any other data that appears more than
     * once.
     * 
     * @param   string  $input_string   String that contains the tags.
     * @param   string  $open_tag       Name of the open tag (i.e. "<a>").
     * @param   string  $close_tag      Name of the closing tag (i.e. "</title>")    
     * @return  array                   Array containing all the strings matched repeatedly  
     */
    public static function parse2Array($input_string, $open_tag, $close_tag, $tag_inclusion = TRUE)
    {
        preg_match_all("($open_tag(.*)$close_tag)siU", $input_string, $matching_data);
        $matches =  $matching_data[0];
        
        if (!$tag_inclusion) {
            foreach ($matches as &$match)       
            {
                $match = str_replace(array($open_tag, $close_tag), "", $match);
            }
            unset($match);
        }
        return $matches;
    }
    
    /**
     * Returns the value of an attribute in a given tag.
     * 
     * @param   string  $tag        The tag that contains the attribute
     * @param   string  $attribute  The name of the attribute, whose value you seek
     * @return  string              The value of the atribute    
     */
    public static function getAttribute($tag, $attribute) 
    {
        # Use Tidy library to 'clean' input
        $cleaned_html = self::cleanHTML($tag);

        # Remove all line feeds from the string
        $cleaned_html = str_replace("\r", "", $cleaned_html);
        $cleaned_html = str_replace("\n", "", $cleaned_html);

        # Use returnBetween() to find the properly quoted value for the attribute
        return self::returnBetween($cleaned_html, strtoupper($attribute) . "=\"", "\"", self::PIVOT_EXCLUDED);
    }
    
    /**
     * Removes all text between $open_tag and $close_tag
     * 
     * @param   string  $string     The target of your parse.
     * @param   string  $open_tag   The starting delimitor
     * @param   string  $close_tag  The ending delimitor
     * @return  string              The resulting string for removing all text 
     *                              between $open_tag and $close_tag
     */
    public static function remove($string, $open_tag, $close_tag)
    {
        # Get array of things that should be removed from the input string
        $remove_array = self::parse2Array($string, $open_tag, $close_tag);

        # Remove each occurrence of each array element from string;
        for ($i = 0; $i < count($remove_array); $i++)
            $string = str_replace($remove_array, "", $string);

        return $string;
    }

   
    /**
     * Returns a "Cleans-up" (parsable) version raw HTML.
     * 
     * @param   string  $html   Raw HTML.  
     * @return  string          Returns a string of cleaned-up HTML.           
     */
    private static function cleanHTML($html) 
    {
        // Detect if Tidy is in configured
        if (function_exists('tidy_get_release'))
        {
            # Tidy for PHP version 4
            if (substr(phpversion(), 0, 1) == 4) 
            {
                tidy_setopt('uppercase-attributes', TRUE);
                tidy_setopt('wrap', 800);
                tidy_parse_string($html);
                $cleaned_html = tidy_get_output();
            }
            # Tidy for PHP version 5
            if (substr(phpversion(), 0, 1) == 5) 
            {
                $config = array(
                    'uppercase-attributes' => true,
                    'wrap' => 800);
                $tidy = new tidy;
                $tidy->parseString($html, $config, 'utf8');
                $tidy->cleanRepair();
                $cleaned_html = tidy_get_output($tidy);
            }
        } 
        else 
        {
            # Tidy not configured for this computer
            $cleaned_html = $html;
        }
        return $cleaned_html;
    }

}