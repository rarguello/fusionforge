#!/usr/local/bin/php -q
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require ('squal_pre.php');    


/*
//FIRST TIME THIS SCRIPT IS RUN - YOU MAY NEED TO RUN THIS QUERY FIRST

//nightly aggregation query
DROP TABLE trove_agg;
CREATE TABLE trove_agg AS
	SELECT tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name,
		g.status, g.register_time, g.short_description,
		project_weekly_metric.percentile, project_weekly_metric.ranking
        FROM groups g
        LEFT JOIN project_weekly_metric USING (group_id) ,
        trove_group_link tgl
        WHERE
        tgl.group_id=g.group_id
        AND (g.is_public=1)
        AND (g.type=1)
        AND (g.status='A')
	ORDER BY trove_cat_id ASC, ranking ASC;

CREATE INDEX troveagg_trovecatid ON trove_agg(trove_cat_id);
create index troveagg_trovecatid_ranking ON trove_agg(trove_cat_id,ranking);

DROP TABLE trove_treesums;
CREATE TABLE "trove_treesums" (
        "trove_treesums_id" serial primary key,
        "trove_cat_id" integer DEFAULT '0' NOT NULL,
        "limit_1" integer DEFAULT '0' NOT NULL,
        "subprojects" integer DEFAULT '0' NOT NULL
);

*/

/*

	Rebuild the trove_agg table, which saves us
	from doing really expensive queries in trove
	each time of the trove map is viewed

*/

db_begin(SYS_DB_TROVE);

db_query("DELETE FROM trove_agg;", -1, 0, SYS_DB_TROVE);

$sql="INSERT INTO trove_agg
        SELECT 
            tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name, g.status, g.register_time, g.short_description, 
            project_weekly_metric.percentile, project_weekly_metric.ranking 
        FROM groups g
        LEFT JOIN project_weekly_metric USING (group_id) , 
        trove_group_link tgl 
        WHERE 
        tgl.group_id=g.group_id 
        AND (g.is_public=1) 
        AND (g.type=1) 
        AND (g.status='A') 
        ORDER BY trove_cat_id ASC, ranking ASC;";

db_query($sql, -1, 0, SYS_DB_TROVE);
echo db_error(SYS_DB_TROVE);

db_commit(SYS_DB_TROVE);

/*

Calculate the number of projects under each category

Do this by first running an aggregate query in the database,
then putting that into two associative arrays.

Start at the top of the trove tree and recursively go down 
the tree, building a third associative array which contains
the count of projects under each category

Then iterate through that third array and insert the results into the
database inside of a transaction

*/

$cat_counts = array();
$parent_list = array();

$res=db_query("
	SELECT trove_cat.trove_cat_id,trove_cat.parent,count(groups.group_id) AS count
	FROM  trove_cat LEFT JOIN trove_group_link ON
		trove_cat.trove_cat_id=trove_group_link.trove_cat_id
	LEFT JOIN groups ON
		groups.group_id=trove_group_link.group_id
	WHERE (groups.status='A' OR groups.status IS NULL)
	AND ( groups.type='1' OR groups.status IS NULL)
	AND ( groups.is_public='1' OR groups.is_public IS NULL)
	GROUP BY trove_cat.trove_cat_id,trove_cat.parent
", -1, 0, SYS_DB_TROVE);

$rows = db_numrows($res);

for ($i=0; $i<$rows; $i++) {

	$cat_counts[db_result($res,$i,'trove_cat_id')][0]=db_result($res,$i,'parent');
	$cat_counts[db_result($res,$i,'trove_cat_id')][1]=db_result($res,$i,'count');

	$parent_list[db_result($res,$i,'parent')][]=db_result($res,$i,'trove_cat_id');

}

$sum_totals=array();

function get_trove_sub_projects($cat_id) {
	global $cat_counts,$sum_totals,$parent_list;

	//number of groups that were in this trove_cat
	$count=$cat_counts[$cat_id][1];

	//number of children of this trove_cat
	$rows=count( $parent_list[$cat_id] );

	for ($i=0; $i<$rows; $i++) {
		$count += get_trove_sub_projects( $parent_list[$cat_id][$i] );
	}
	$sum_totals["$cat_id"]=$count;
	return $count;
}

//start the recursive function at the top of the trove tree
$res2=db_query("SELECT trove_cat_id FROM trove_cat WHERE parent=0", -1, 0, SYS_DB_TROVE);

for ($i=0; $i< db_numrows($res2); $i++) {
	get_trove_sub_projects( db_result($res2,$i,0) );
}

db_begin(SYS_DB_TROVE);
db_query("DELETE FROM trove_treesums", -1, 0, SYS_DB_TROVE);
echo db_error(SYS_DB_TROVE);
//echo "<TABLE>";
while (list($k,$v) = each($sum_totals)) {
	$res = db_query("
		INSERT INTO trove_treesums (trove_cat_id,subprojects) 
		VALUES ($k,$v)
	", -1, 0, SYS_DB_TROVE);
	if (!$res || db_affected_rows($res)!=1) {
		echo db_error(SYS_DB_TROVE);
	}
//	echo "<TR><TD>$k</TD><TD>$v</TD></TR>\n";

}
//echo "</TABLE>";

db_commit(SYS_DB_TROVE);

if (db_error(SYS_DB_TROVE)) {
	echo "Error: ".db_error(SYS_DB_TROVE);
}

?>
