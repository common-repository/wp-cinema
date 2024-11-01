<?php

class WPCinema_fields
{
    //  This class is extended by movie and settings pages

    //  Produce an HTML form from a fieldspec data structure ($fields)
    //  $data is either the result of a POST or retrieved data
    //  We only produce the body of the form - the <form...> and </form>
    //  are produced by calling code.
    //
    function fields2form($data, $fields, $no_end_table = false)
    {
        // TODO: where do we do PHP-side validation?
        // remember javascript validation

	$out = '';
	$out .= "<table class='form-table'>\n";
	foreach ($fields as $key => $field)
	{
	    $out .= "<tr valign='top'>\n";
	    $title = $field['prompt'];
	    $out .= "<th scope='row'>"
		    . "<label for='$key'>$title</label></th>\n";
	    $out .= "<td>";
	    $val = '';
	    // print_r($field); //ZZZ
	    $field = apply_filters('wpcinema_fields_readonly', $field, $key);
	    if (! empty($field['readonly']))
	    {
	        if (isset($data[$key]))
		    $out .= wp_kses_data(esc_html($data[$key]));
		$out .= "</td>\n";
		$out .= "</tr>\n";
		continue;
	    }
	    switch ($field['type'])
	    {
	        case 'textarea':
		   $rows = 5;
		   $cols = 80;
		   if (! empty($field['length']))
		       $rows = $field['length'];
		   if (! empty($field['columns']))
		       $cols = $field['columns'];
		   $out .= "<textarea id='$key' name='$key' rows=$rows "
			   . "cols=$cols>";
		   if (isset($data[$key]))
		       $out .= wp_kses_data(esc_html($data[$key]));
		   $out .= "</textarea>\n";
		   break;

	        case 'checkbox':
		   $isset = empty($data[$key]) ? 0 : 1;
		   $out .= "<input name='$key' id='$key' type='checkbox' "
		           . "value='1' ". checked('1', $isset, false) .">\n";
		   break;

	        case 'date':
		    if (isset($data[$key]) && ($data[$key] == '0000-00-00' ||
		      $data[$key] == '1970-01-01'))
			$data[$key] = '';
		    if (! empty($data[$key]))
			$data[$key] = date('d/m/Y', strtotime($data[$key]));
		    
		    // FALL-THROUGH

	        case 'text':
		default:
		   if (isset($data[$key]))
		   {
		       $val = " value='" . wp_kses_data(esc_html($data[$key]))
		               . "'";
		   }
		   $length = 10;
		   if (! empty($field['length']))
		       $length = $field['length'];
		   $out .= "<input id='$key' name='$key' size='$length'$val>";
		   break;
	    }
	    if (! empty($field['desc']) && trim($field['desc']) != '')
	    {
		$out .= "<br><small>${field['desc']} "
		        . "<font color=silver>[$key]</font></small>\n";
	    }
	    $out .= "</td>\n";
	    $out .= "</tr>\n";
	}
	if (! $no_end_table)
	{
	    $out .= "</table>\n";
	    $out .= "<br />";
	}

	return $out;
    }

};


// end
