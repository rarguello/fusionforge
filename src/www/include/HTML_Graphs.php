<?php
/**
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * http://sourceforge.net
 *
 * @author		Tim Perdue <tperdue@valnux.com>
 *
 * Ryan T. Sammartino made several changes to make the output XHTML 1.0
 * Transitional.
 */

/*
#######################################################################
#
#      Revision 1.5  1998/11/05 06:15:52  pdavis
#      Added error_reporting setting per Jean-Pierre Arneodo's request.
#      (Though redundant) Added html_graph_init() to initialize vars array.
#
#      Revision 1.4  1998/07/08 05:24:25  pdavis
#      Add double_vertical_graph from Jan Diepens.
#      Added "max" function to find $largest in examples page.
#      Added code to increase values of zero to one.
#      Added double_vertical_graph example
#      Combined all source into one zip.
#
#      Revision 1.3  1998/06/17 23:37:19  pdavis
#      Added mixed color codes and images to double graph.
#
#      Revision 1.2  1998/06/17 21:20:20  pdavis
#      Fixed Background problem, added mixed color codes and graphics.
#
#      Revision 1.1  1998/06/17 15:52:41  pdavis
#      Initial revision
#
#
#######################################################################
#
#     *
#     *  Phil Davis
#     *
#     *  Smyrna, Tennessee  37167  USA
#     *
#     *  pdavis@pobox.com
#     *  http://www.pobox.com/~pdavis/
#     *
#
#     (C) Copyright 1998
#         Phil Davis
#         Printed in the United States of America
#
#     This program is free software; you can redistribute it
#     and/or modify it under the terms of the GNU General
#     Public License version 2 as published by the Free
#     Software Foundation.
#
#     This program is distributed in the hope that it will
#     be useful, but WITHOUT ANY WARRANTY; without even the
#     implied warranty of MERCHANTABILITY or FITNESS FOR A
#     PARTICULAR PURPOSE.  See the GNU General Public License
#     for more details.
#
#     Released under GNU Public License v2.0, available
#     at www.fsf.org.  The author hereby disclaims all
#     warranties relating to this software, express or implied,
#     including with no limitation any implied warranties of
#     merchantability, quality performance, or fitness for a
#     particular purpose. The author and their distributors
#     shall not be liable for any special, incidental,
#     consequential, indirect or similar damages due to loss
#     of data, even if an agent of the author has been found
#     to be the source of loss or damage. In no event shall the
#     author's liability for any damages ever exceed the price
#     paid for the license to use software, regardless of the
#     form of the claim. The person using the software bears all
#     risk as to the quality and performance of the software.
#
#     Swim at your own risk!
#
#     This software program, documentation, accompanying
#     written and disk-based notes and specifications, and all
#     referenced and related program files, screen display
#     renditions, and text files, are the property of the
#     author.
#
#     The authors have done their best to insure that the
#     material found in this document is both useful and
#     accurate. However, please be aware that errors may exist,
#     the author does not make any guarantee concerning the
#     accuracy of the information found here or in the uses
#     to which it may be put.
#
#######################################################################
#
#  About:
#
#  The following PHP3 code provides a nice class interface for
#  html graphs.  It provides a single, reasonably consistent
#  interface for creating HTML based graphs.  The idea behind
#  this code is that the user of the class sets up four or five
#  arrays and pass these to html_graph() which then takes
#  care of all the messy HTML layout.  I am reasonably happy
#  with the outcome of this interface.  The HTML that must be
#  generated for HTML graphs *is* messy, and the interface is
#  very clean and flexible.  I think that once you generate
#  one graph with it, you'll never look at creating HTML graphs
#  the same.  The arrays that must be set up consist of:
#
#       * A names array containing column/row identifiers ($names)
#       * One or two values arrays containg corresponding
#         values to the column/row names ($values & $dvalues)
#       * One or two bars array which also corresponds to the names
#         array.  The values in these arrays are URLS to graphics
#         or color codes starting with a # which will be used to
#         generate the graph bar.  Color codes and graphics may
#         be mixed in the same chart, although color codes can't
#         be used on Vertical charts. ($bars & $dbars)
#       * The heart of customization... a vals array.  If this
#         array isn't created then html_graphs will use all
#         default values for the chart.  Items that are customizable
#         include font styles & colors, backgrounds, graphics,
#         labels, cellspacing, cellpadding, borders, anotations
#         and scaling factor. ($vals)
#
#######################################################################
#
#  Known Bugs:
#
#  * Currently the $vals["background"] tag element doesn't
#    work in Netscape.
#
#######################################################################
#
#  To Do:
#
#  * Would like to make the $vals array to html_graph() completely
#    optional.  Currently it has to at least be an empty array.
#
#######################################################################
#
# Contributors:
#
#  Jan Diepens - Eindhoven University of Technologie
#  Jean-Pierre Arneodo
#
#######################################################################
#
# Contact:
#
# If you have questions, suggestions, bugs, bug fixes, or enhancements
# please send them to pdavis@pobox.com so that they may be wrapped into
# future versions of HTML_Graph.
#
#######################################################################
#
#  Examples:
#
#  See http://www.pobox.com/~pdavis/programs/
#
#######################################################################
*/

/*
#######################################################################
#
#  Function:  html_graph($names, $values, $bars, $vals[, $dvalues, $dbars])
#
#   Purpose:  Calls routines to initialize defaults, set up table
#             print data, and close table.
#
# Arguments:
#                   $names - Array of element names.
#                  $values - Array of corresponding values for elements.
#                    $bars - Array of corresponding graphic image names
#                            or color codes (begining with a #) for elements.
#                            Color codes can't be used on vertical charts.
#                 $dvalues - Array of corresponding values for elements.
#                            This set is required only in the double graph.
#                   $dbars - Array of corresponding graphic image names
#                            or color codes (begining with a #) for elements.
#                            This set is required only in the double graph.
#
#                    $vals -  array("vlabel"=>"",
#                                   "hlabel"=>"",
#                                   "type"=>"",
#                                   "cellpadding"=>"",
#                                   "cellspacing"=>"",
#                                   "border"=>"",
#                                   "width"=>"",
#                                   "background"=>"",
#                                   "vfcolor"=>"",
#                                   "hfcolor"=>"",
#                                   "vbgcolor"=>"",
#                                   "hbgcolor"=>"",
#                                   "vfstyle"=>"",
#                                   "hfstyle"=>"",
#                                   "noshowvals"=>"",
#                                   "scale"=>"",
#                                   "namebgcolor"=>"",
#                                   "valuebgcolor"=>"",
#                                   "namefcolor"=>"",
#                                   "valuefcolor"=>"",
#                                   "namefstyle"=>"",
#                                   "valuefstyle"=>"",
#                                   "doublefcolor"=>"")
#
#             Where:
#
#                   vlabel - Vertical Label to apply
#                            default is NUL
#                   hlabel - Horizontal Label to apply
#                            default is NUL
#                     type - Type of graph
#                            0 = horizontal
#                            1 = vertical
#                            2 = double horizontal
#                            3 = double vertical
#                            default is 0
#              cellpadding - Padding for the overall table
#                            default is 0
#              cellspacing - Space for the overall table
#                            default is 0
#                   border - Border size for the overall table
#                            default is 0
#                    width - Width of the overall table
#                            default is NUL
#               background - Background image for the overall table
#                            If this value exists then no BGCOLOR
#                            codes will be added to table elements.
#                            default is NUL
#                  vfcolor - Vertical label font color
#                            default is #000000
#                  hfcolor - Horizontal label font color
#                            default is #000000
#                 vbgcolor - Vertical label background color
#                            Not used if background is set
#                            default is #FFFFFF
#                 hbgcolor - Horizontal label background color
#                            Not used if background is set
#                            default is #FFFFFF
#                  vfstyle - Vertical label font style
#                            default is NUL
#                  hfstyle - Horizontal label font style
#                            default is NUL
#               noshowvals - Don't show numeric value at end of graphic
#                            Boolean value, default is FALSE
#                    scale - Scale values by some number.
#                            default is 1.
#              namebgcolor - Color code for element name cells
#                            Not used if background is set
#                            default is "#000000"
#             valuebgcolor - Color code for value cells
#                            Not used if background is set
#                            default is "#000000"
#               namefcolor - Color code for font of name element
#                            default is "#FFFFFF"
#              valuefcolor - Color code for font of value element
#                            default is "#000000"
#               namefstyle - Style code for font of name element
#                            default is NUL
#              valuefstyle - Style code for font of value element
#                            default is NUL
#             doublefcolor - Color code for font of second element value
#                            default is "#886666"
#
#######################################################################
*/
function html_graph($names, $values, $bars, $vals, $dvalues=0, $dbars=0)
   {
    // Set the error level on entry and exit so as not to interfear
    // with anyone elses error checking.
    $er = error_reporting(1);

    // Set the values that the user didn't
    $vals = hv_graph_defaults($vals);
    start_graph($vals, $names);

    if ($vals["type"] == 0)
       {
        horizontal_graph($names, $values, $bars, $vals);
       }
    elseif ($vals["type"] == 1)
       {
        vertical_graph($names, $values, $bars, $vals);
       }
    elseif ($vals["type"] == 2)
       {
        double_horizontal_graph($names, $values, $bars, $vals, $dvalues, $dbars);
       }
    elseif ($vals["type"] == 3)
       {
        double_vertical_graph($names, $values, $bars, $vals, $dvalues, $dbars);
       }

    end_graph();

    // Set the error level back to where it was.
    error_reporting($er);
   }

/*
#######################################################################
#
#  Function:  html_graph_init()
#
#   Purpose:  Sets up the $vals array by initializing all values to
#             null.  Used to avoid warnings from error_reporting being
#             set high.  This routine only needs to be called if you
#             are woried about using uninitialized variables.
#
#   Returns:  The initialized $vals array
#
#######################################################################
*/
function html_graph_init()
   {
    $vals = array("vlabel"=>"",
                  "hlabel"=>"",
                  "type"=>"",
                  "cellpadding"=>"",
                  "cellspacing"=>"",
                  "border"=>"",
                  "width"=>"",
                  "background"=>"",
                  "vfcolor"=>"",
                  "hfcolor"=>"",
                  "vbgcolor"=>"",
                  "hbgcolor"=>"",
                  "vfstyle"=>"",
                  "hfstyle"=>"",
                  "noshowvals"=>"",
                  "scale"=>"",
                  "namebgcolor"=>"",
                  "valuebgcolor"=>"",
                  "namefcolor"=>"",
                  "valuefcolor"=>"",
                  "namefstyle"=>"",
                  "valuefstyle"=>"",
                  "doublefcolor"=>"");

    return($vals);
   }
/*
#######################################################################
#
#  Function:  start_graph($vals, $names)
#
#   Purpose:  Prints out the table header and graph labels.
#
#######################################################################
*/
function start_graph($vals, $names)
   {
    print "<!-- Start Inner Graph Table -->\n\n<table";
    print ' cellpadding="' . $vals["cellpadding"] . '"';
    print ' cellspacing="' . $vals["cellspacing"] . '"';
    print ' border="' . $vals["border"] . '"';

    if ($vals["width"] != 0) { print ' width="' . $vals["width"] . '"'; }
    if ($vals["background"]) { print ' background="' . $vals["background"] . '"'; }

    print '>';

    if (($vals["vlabel"]) || ($vals["hlabel"]))
       {
        if (($vals["type"] == 0) || ($vals["type"] == 2 ))// horizontal chart
           {
            $rowspan = SizeOf($names) + 1;
            $colspan = 3;
           }
        elseif ($vals["type"] == 1 || ($vals["type"] == 3 )) // vertical chart
           {
            $rowspan = 3;
            $colspan = SizeOf($names) + 1;
           }

        print '<tr><td class="align-center" valign="center" ';

        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print 'style="background-color:' . $vals["hbgcolor"] . '"'; }

        print ' colspan="' . $colspan . '">';
		print '<span style="color:' . $vals["hfcolor"] . '; ' . $vals["hfstyle"] . '">';
        print "<strong>" . $vals["hlabel"] . "</strong>";
        print '</span></td></tr>';

        print '<tr><td class="align-center" valign="center" ';

        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print 'style="background-color:' . $vals["vbgcolor"] . '"'; }

        print ' rowspan="' . $rowspan . '">';
        print '<span style="color:' . $vals["vfcolor"] . '; ' . $vals["vfstyle"] . '">';
        print "<strong>" . $vals["vlabel"] . "</strong>";
        print '</span></td>';
       }
   }

/*
#######################################################################
#
#  Function:  end_graph()
#
#   Purpose:  Prints out the table footer.
#
#######################################################################
*/
function end_graph()
   {
    print "\n</table>\n\n<!-- end inner graph table -->\n\n";
   }

/*
#######################################################################
#
#  Function:  hv_graph_defaults($vals)
#
#   Purpose:  Sets the default values for the $vals array
#
#######################################################################
*/
function hv_graph_defaults($vals)
   {
    if (!isset($vals["vfcolor"]))      { $vals["vfcolor"]="#000000"; }
    if (!isset($vals["hfcolor"]))      { $vals["hfcolor"]="#000000"; }
    if (!isset($vals["vbgcolor"]))     { $vals["vbgcolor"]="#ffffff"; }
    if (!isset($vals["hbgcolor"]))     { $vals["hbgcolor"]="#ffffff"; }
    if (!isset($vals["cellpadding"]))  { $vals["cellpadding"]=0; }
    if (!isset($vals["cellspacing"]))  { $vals["cellspacing"]=0; }
    if (!isset($vals["border"]))       { $vals["border"]=0; }
    if (!isset($vals["scale"]))        { $vals["scale"]=1; }
    if (!isset($vals["namebgcolor"]))  { $vals["namebgcolor"]="#ffffff"; }
    if (!isset($vals["valuebgcolor"])) { $vals["valuebgcolor"]="#ffffff"; }
    if (!isset($vals["namefcolor"]))   { $vals["namefcolor"]="#000000"; }
    if (!isset($vals["valuefcolor"]))  { $vals["valuefcolor"]="#000000"; }
    if (!isset($vals["doublefcolor"])) { $vals["doublefcolor"]="#886666"; }

    return ($vals);
   }

/*
#######################################################################
#
#  Function:  horizontal_graph($names, $values, $bars, $vals)
#
#   Purpose:  Prints out the actual data for the horizontal chart.
#
#######################################################################
*/
function horizontal_graph($names, $values, $bars, $vals)
   {
    for( $i=0;$i<SizeOf($values);$i++ )
       {
?>

	<tr>
	<td class="align-right" <?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["namebgcolor"] . '"'; }
?>>
		<span style="font-size: -1;color:<?php
			echo $vals["namefcolor"];
		?>;<?php
			echo $vals["namefstyle"];
	echo "\">";
        echo "\n".$names[$i]; ?>
		</span>
	</td>

	<td  align="left" <?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["valuebgcolor"] . '"'; }

	echo ">";

        // Decide if the value in bar is a color code or image.
		if (preg_match("/^#/", $bars[$i]))
           {
?>

		<table align="left" style="background-color:<?php echo $bars[$i] ?>" width="<?php echo $values[$i] * $vals["scale"] ?>">
			<tr><td>&nbsp;</td></tr>
		</table>

<?php
            }
         else
            {
             print '<img src="' . $bars[$i] . '"';
             print ' height="10" width="' . $values[$i] * $vals["scale"] . '" alt= "" />';
            }
        if (! $vals["noshowvals"])
           {
            print '		<em><span style="font-size: -2;color:' . $vals["valuefcolor"] . ';'
            . $vals["valuefstyle"] . '">(';
            print $values[$i] . ")</span></em>";
           }
?>

	</td>
	</tr>
<?php
       } // endfor

   } // end horizontal_graph

/*
#######################################################################
#
#  Function:  vertical_graph($names, $values, $bars, $vals)
#
#   Purpose:  Prints out the actual data for the vertical chart.
#
#######################################################################
*/
function vertical_graph($names, $values, $bars, $vals)
   {
    print "<tr>";

    for( $i=0;$i<SizeOf($values);$i++ )
       {

        print '<td  align="center" valign="bottom" ';

        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["valuebgcolor"] . '"'; }
        print ">";

        if (! $vals["noshowvals"])
           {
            print '<em><span style="font-size: -2;color:' . $vals["valuefcolor"] . ';'
            . $vals["valuefstyle"] . '">(';
            print $values[$i] . ")</span></em><br />";
           }
?>

         <img src="<?php echo $bars[$i] ?>" width="5" height="<?php

        // Values of zero are displayed wrong because a image height of zero
        // gives a strange behavior in Netscape. For this reason the height
        // is set at 1 pixel if the value is zero. - Jan Diepens
        if ($values[$i] != 0)
           {
            echo $values[$i] * $vals["scale"];
           }
        else
           {
            echo "1";
           }
?>" alt="" />

         </td>
<?php
       } // endfor

    print "</tr><tr>";

    for( $i=0;$i<SizeOf($values);$i++ )
       {
?>
        <td class="align-center" valign="top"

<?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["namebgcolor"] . '"'; }
?>
         >
		 <span style="font-size: -1;color:<?php echo $vals["namefcolor"] ?>;<?php echo $vals["namefstyle"] ?>">
         <?php echo $names[$i] ?>
         </span>
        </td>
<?php
       } // endfor

   } // end vertical_graph

/*
#######################################################################
#
#  Function:  double_horizontal_graph($names, $values, $bars,
#                                     $vals, $dvalues, $dbars)
#
#   Purpose:  Prints out the actual data for the double horizontal chart.
#
#######################################################################
*/
function double_horizontal_graph($names, $values, $bars, $vals, $dvalues, $dbars)
   {
    for( $i=0;$i<SizeOf($values);$i++ )
       {
?>
       <tr>
        <td class="align-right"
<?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["namebgcolor"] . '"'; }
?>
         >
		 <span style="font-size: -1;color:<?php echo $vals["namefcolor"] ?>;<?php echo $vals["namefstyle"] ?>">
         <?php echo $names[$i] ?>
         </span>
        </td>
        <td  align="left"
<?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["valuebgcolor"] . '"'; }
?>
         >
         <table align="left" width="<?php echo $dvalues[$i] * $vals["scale"] ?>">
          <tr><td
<?php
        // Set background to a color if it starts with # or
        // an image otherwise.
		if (preg_match("/^#/", $dbars[$i])) { print 'style="background-color:' . $dbars[$i] . '">'; }
        else { print 'background="' . $dbars[$i] . '">'; }
?>
           <nowrap>
<?php
        // Decide if the value in bar is a color code or image.
		if (preg_match("/^#/", $bars[$i]))
           {
?>
            <table align="left" 
             style="background-color:"<?php echo $bars[$i] ?>"
             width="<?php echo $values[$i] * $vals["scale"] ?>">
             <tr><td>&nbsp</td></tr>
            </table>
<?php
            }
         else
            {
             print '<img src="' . $bars[$i] . '"';
             print ' height="10" width="' . $values[$i] * $vals["scale"] . '" alt="" />';
            }

        if (! $vals["noshowvals"])
           {
            print '<em><span style="font-size: -3:color:' . $vals["valuefcolor"] . ';'
            . $vals["valuefstyle"] . '">(';
            print $values[$i] . ")</span></em>";
           }
?>
           </nowrap>
          </td></tr>
         </table>
<?php
        if (! $vals["noshowvals"])
           {
            print '<em><span style="font-size:-3;color:' . $vals["doublefcolor"] . ';'
            . $vals["valuefstyle"] . '">(';
            print $dvalues[$i] . ")</span></em>";
           }
?>
        </td>
       </tr>
<?php
       } // endfor

   } // end double_horizontal_graph

/*
#######################################################################
#
#  Function:  double_vertical_graph($names, $values, $bars, $vals, $dvalues, $dbars)
#
#   Purpose:  Prints out the actual data for the double vertical chart.
#
#    Author: Jan Diepens
#
#######################################################################
*/
function double_vertical_graph($names, $values, $bars, $vals, $dvalues, $dbars)
   {
   // print "<tr>";

    for( $i=0;$i<SizeOf($values);$i++ )
       {

        print '<td class="align-center" valign="bottom" ';
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["valuebgcolor"] . '"'; }
        print ">";

	print '<table><tr><td class="align-center" valign="bottom" ';

        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["valuebgcolor"] . '"'; }
        print ">";

        if (! $vals["noshowvals"])
           {
            print '<em><span style="font-size:-2;color:' . $vals["valuefcolor"] . ';'
            . $vals["valuefstyle"] . '">(';
            print $values[$i] . ")</span></em><br />";
           }
?>

         <img src="<?php echo $bars[$i] ?>" width="10" height="<?php if ($values[$i]!=0){
		echo $values[$i] * $vals["scale"];
		} else { echo "1";} ?>" alt="" />
         </td><td class="align-center" valign="bottom"
<?php
         // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["valuebgcolor"] . '"'; }
        print ">";

        if (! $vals["noshowvals"])
           {
            print '<em><span style="font-size:-2;color:' . $vals["doublefcolor"] . ';'
            . $vals["valuefstyle"] . '">(';
            print $dvalues[$i] . ")</span></em><br />";
           }
?>

         <img src="<?php echo $dbars[$i] ?>" width="10" height="<?php if ($dvalues[$i]!=0){
		echo $dvalues[$i] * $vals["scale"];
		} else { echo "1";} ?>" alt="" />
         </td></tr></table>
	 </td>
<?php
       } // endfor

    print "</tr><tr>";

    for( $i=0;$i<SizeOf($values);$i++ )
       {
?>
        <td class="align-center" valign="top"

<?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) { print ' style="background-color:' . $vals["namebgcolor"] . '"'; }
?>
         >
		 <span style="font-size:-1;color:<?php echo $vals["namefcolor"] ?>;<?php echo $vals["namefstyle"] ?>">
         <?php echo $names[$i] ?>
         </span>
        </td>
<?php
       } // endfor

   } // end double_vertical_graph



/*
#######################################################################
#
#  Function:  horizontal_absolute_multi_graph($names, $multi_rows,
#                                             $colors, $vals,
#                                             $additive)
#     $multi_rows - array of arrays of values (may be seen as
#             array of columns - column for first color, for second, etc.)
#             $colors - array of color names or codes
#	  $additive - treat data as absolute values (will be
#             differentiated for drawing, and hence should be non-decreasing
#             sequence) or additive (just stick one on another).
#
#   Purpose:  Prints out the actual data for the horizontal chart of
#             bars with multiple sections
#
#######################################################################
*/
function horizontal_multisection_graph($names, $multi_rows, $colors, $vals, $additive=false)
   {
    $subbars_num=SizeOf($multi_rows);
    for( $i=0;$i<SizeOf($names);$i++ )
       {
?>

	<tr>
	<td class="align-right" <?php
        // If a background was choosen don't print cell BGCOLOR
        if (! $vals["background"]) {
			print ' style="background-color:' . $vals["namebgcolor"] . '"';
		}
?>>
		<span style="font-size:-1;color:<?php echo $vals["namefcolor"]; ?>;<?php echo $vals["namefstyle"]; ?>">
<?php
        echo "\n".$names[$i]; ?>
		</span>
	</td>

	<td  align="left" <?php
        // If a background was choosen don't print cell BGCOLOR
		if (! $vals["background"]) {
			print ' style="background-color:' . $vals["valuebgcolor"] . '"';
		}
		echo ">";

        echo '<table align="left"><tr>'."\n";
        $prev_val=0;
        $shown=0;
		for( $j=0;$j<$subbars_num;$j++ ) {
        	$width=$multi_rows[$j][$i];
            if (!$additive) $width-=$prev_val;
            if ($width<=0 && ($j!=$subbars_num-1 || $shown)) continue;
            // make sure that we show at least stump, but only one
            $shown=1;
            $prev_val=$multi_rows[$j][$i];
            $pix_width=$width * $vals["scale"];
            echo "<td style=\"background-color:".$colors[$j]."\" width=\"".$pix_width."\">&nbsp;</td>";
        }
        echo '</tr></table>';

        if (! $vals["noshowvals"]) {
            print '		<em><span style="font-size:-2;color:' . $vals["valuefcolor"] . ';'
                . $vals["valuefstyle"] . '">&nbsp;(';
        	for( $j=0;$j<SizeOf($multi_rows);$j++ ) {
                        if ($j) print "/";
                        print $multi_rows[$j][$i];
                }
                print ")</span></em>";
           }
?>

	</td>
	</tr>
<?php
       } // endfor

   } // end horizontal_graph

function graph_calculate_scale($multi_rows,$width) {
	$max_value=0;
        $rows_num=count($multi_rows);

        for ($row_i = 0; $row_i < $rows_num; $row_i++) {
                $row=$multi_rows[$row_i];
               	$counter=count($row);

		for ($i = 0; $i < $counter; $i++) {
			if ($row[$i] > $max_value) {
				$max_value=$row[$i];
			}
		}
        }

	if ($max_value < 1) {
		$max_value=1;
	}

	$scale=($width/$max_value);
        return $scale;
}

?>