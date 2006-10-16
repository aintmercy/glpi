<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}


// FUNCTIONS Planning


function titleTrackingPlanning(){
	global  $LANG,$CFG_GLPI;

	echo "<div align='center'><table border='0'><tr><td>";
	echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/reservation.png\" alt='' title=''></td><td><b><span class='icon_sous_nav'>".$LANG["planning"][3]."</span>";
	echo "</b></td></tr></table>&nbsp;</div>";
}


function showTrackingPlanningForm($device_type,$id_device){

	global $DB,$CFG_GLPI,$LANG;

	if (!haveRight("comment_all_ticket","1")) return false;

	$query="select * from glpi_reservation_item where (device_type='$device_type' and id_device='$id_device')";

	if ($result = $DB->query($query)) {
		$numrows =  $DB->numrows($result);
		//echo "<form name='resa_form' method='post' action=".$CFG_GLPI["root_doc"]."/front/reservation.php>";
		echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/reservation.php?";
		// Ajouter le mat�iel
		if ($numrows==0){
			echo "id_device=$id_device&amp;device_type=$device_type&amp;add=add\">".$LANG["reservation"][7]."</a>";
		}
		// Supprimer le mat�iel
		else {
			echo "ID=".$DB->result($result,0,"ID")."&amp;delete=delete\">".$LANG["reservation"][6]."</a>";
		}

	}
}


function showAddPlanningTrackingForm($target,$fup,$planID=-1){
	global $LANG,$CFG_GLPI;

	if (!haveRight("comment_all_ticket","1")) return false;

	$split=split(":",$CFG_GLPI["planning_begin"]);
	$global_begin=intval($split[0]);

	$split=split(":",$CFG_GLPI["planning_end"]);
	$global_end=intval($split[0]);

	$planning= new PlanningTracking;

	if ($planID!=-1)
		$planning->getFromDB($planID);
	else {
		$planning->getEmpty();
		$planning->fields["begin"]=date("Y-m-d")." 12:00:00";
		$planning->fields["end"]=date("Y-m-d")." 13:00:00";
	}


	$begin=strtotime($planning->fields["begin"]);
	$end=strtotime($planning->fields["end"]);

	$begin_date=date("Y-m-d",$begin);
	$end_date=date("Y-m-d",$end);
	$begin_hour=date("H:i",$begin);
	$end_hour=date("H:i",$end);

	echo "<div align='center'><form method='post' name='form' action=\"$target\">";
	if ($planID!=-1)
		echo "<input type='hidden' name='ID' value='$planID'>";
	echo "<input type='hidden' name='referer' value='".$_SERVER['HTTP_REFERER']."'>";
	// Ajouter le job
	$followup=new Followup;
	$followup->getfromDB($fup);
	$j=new Job();
	$j->getFromDBwithData($followup->fields['tracking'],0);
	if ($planID==-1){
		if ($j->fields["assign"])
			$planning->fields["id_assign"]=$j->fields["assign"];
	}

	echo "<input type='hidden' name='id_followup' value='$fup'>";
	echo "<input type='hidden' name='id_tracking' value='".$followup->fields['tracking']."'>";

	echo "<table class='tab_cadre' cellpadding='2' width='600'>";
	echo "<tr><th colspan='2'><b>";
	echo $LANG["planning"][7];
	echo "</b></th></tr>";
	echo "<tr class='tab_bg_1'><td>".$LANG["planning"][8].":	</td>";
	echo "<td>";
	echo "<b>".$j->fields['contents']."</b>";
	echo "</td></tr>";
	echo "<tr class='tab_bg_1'><td>".$LANG["planning"][10].":	</td>";
	echo "<td>";
	echo "<b>".$j->computername."</b>";
	echo "</td></tr>";

	if (!haveRight("comment_all_ticket","1"))
		echo "<input type='hidden' name='id_assign' value='".$_SESSION["glpiID"]."'>";
	else {
		echo "<tr class='tab_bg_2'><td>".$LANG["planning"][9].":	</td>";
		echo "<td>";
		dropdownUsers("id_assign",$planning->fields["id_assign"],"own_ticket",-1);
		echo "</td></tr>";
	}


	echo "<tr class='tab_bg_2'><td>".$LANG["search"][8].":	</td><td>";
	showCalendarForm("form","begin_date",$begin_date);
	echo "</td></tr>";

	echo "<tr class='tab_bg_2'><td>".$LANG["reservation"][12].":	</td>";
	echo "<td>";
	dropdownHours("begin_hour",$begin_hour,1);
	echo "</td></tr>";

	echo "<tr class='tab_bg_2'><td>".$LANG["search"][9].":	</td><td>";
	showCalendarForm("form","end_date",$end_date);
	echo "</td></tr>";

	echo "<tr class='tab_bg_2'><td>".$LANG["reservation"][13].":	</td>";
	echo "<td>";
	dropdownHours("end_hour",$end_hour,1);
	echo "</td></tr>";



	if ($planID==-1){
		echo "<tr class='tab_bg_2'>";
		echo "<td colspan='2'  valign='top' align='center'>";
		echo "<input type='submit' name='add_planning' value=\"".$LANG["buttons"][8]."\" class='submit'>";
		echo "</td></tr>\n";
	} else {
		echo "<tr class='tab_bg_2'>";
		echo "<td valign='top' align='center'>";
		echo "<input type='submit' name='delete' value=\"".$LANG["buttons"][6]."\" class='submit'>";
		echo "</td><td valign='top' align='center'>";
		echo "<input type='submit' name='edit_planning' value=\"".$LANG["buttons"][14]."\" class='submit'>";
		echo "</td></tr>\n";
	}

	echo "</table>";
	echo "</form></div>";
}

function showPlanning($who,$when,$type){
	global $LANG,$CFG_GLPI,$DB;

	if (!haveRight("show_planning","1")&&!haveRight("show_all_planning","1")) return false;

	// Define some constants

	$date=split("-",$when);
	$time=mktime(0,0,0,$date[1],$date[2],$date[0]);

	// Check bisextile years
	list($current_year,$current_month,$current_day)=split("-",$when);
	if (($current_year%4)==0) $feb=29; else $feb=28;
	$nb_days= array(31,$feb,31,30,31,30,31,31,30,31,30,31);
	// Begin of the month
	$begin_month_day=strftime("%w",mktime(0,0,0,$current_month,1,$current_year));
	if ($begin_month_day==0) $begin_month_day=7;
	$end_month_day=strftime("%w",mktime(0,0,0,$current_month,$nb_days[$current_month-1],$current_year));
	// Day of the week
	$dayofweek=date("w",$time);
	// Cas du dimanche
	if ($dayofweek==0) $dayofweek=7;




	// Print Headers
	echo "<div align='center'><table class='tab_cadre_fixe'>";
	// Print Headers
	echo "<tr>";
	switch ($type){
		case "month":
		case "week":
			for ($i=1;$i<=7;$i++){
				echo "<th width='12%'>".$LANG["calendarDay"][$i%7]."</th>";
			}
			break;
		case "day":
			echo "<th width='12%'>".$LANG["calendarDay"][$dayofweek%7]."</th>";
			break;
	}
	echo "</tr>";

	// Get begin and duration
	$begin=0;
	$end=0;
	switch ($type){
		case "month":
			$begin=strtotime($current_year."-".$current_month."-01 00:00:00");
			$end=$begin+DAY_TIMESTAMP*$nb_days[$current_month-1];
			break;
		case "week":
			$begin=$time+mktime(0,0,0,0,1,0)-mktime(0,0,0,0,$dayofweek,0);
			$end=$begin+WEEK_TIMESTAMP;
			break;
		case "day":
			$add="";
			$begin=$time;
			$end=$begin+DAY_TIMESTAMP;
			break;
	}
	$begin=date("Y-m-d H:i:s",$begin);
	$end=date("Y-m-d H:i:s",$end);

	// Get items to print
	$ASSIGN="";
	if ($who!=0)
		$ASSIGN="id_assign='$who' AND";

	// ---------------Tracking

	$query="SELECT * from glpi_tracking_planning WHERE $ASSIGN (('$begin' <= begin AND '$end' >= begin) OR ('$begin' < end AND '$end' >= end) OR (begin <= '$begin' AND end > '$begin') OR (begin <= '$end' AND end > '$end')) ORDER BY begin";

	$result=$DB->query($query);

	$fup=new Followup();
	$job=new Job();

	$interv=array();
	$i=0;
	if ($DB->numrows($result)>0)
		while ($data=$DB->fetch_array($result)){
			$fup->getFromDB($data["id_followup"]);
			$job->getFromDBwithData($fup->fields["tracking"],0);

			$interv[$data["begin"]."$$$".$i]["id_followup"]=$data["id_followup"];
			$interv[$data["begin"]."$$$".$i]["id_tracking"]=$fup->fields["tracking"];
			$interv[$data["begin"]."$$$".$i]["id_assign"]=$data["id_assign"];
			$interv[$data["begin"]."$$$".$i]["ID"]=$data["ID"];
			if (strcmp($begin,$data["begin"])>0)
				$interv[$data["begin"]."$$$".$i]["begin"]=$debut;
			else $interv[$data["begin"]."$$$".$i]["begin"]=$data["begin"];
			if (strcmp($end,$data["end"])<0)
				$interv[$data["begin"]."$$$".$i]["end"]=$fin;
			else $interv[$data["begin"]."$$$".$i]["end"]=$data["end"];
			$interv[$data["begin"]."$$$".$i]["content"]=resume_text($job->fields["contents"],$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$$".$i]["device"]=$job->computername;
			$interv[$data["begin"]."$$$".$i]["status"]=$job->fields["status"];
			$interv[$data["begin"]."$$$".$i]["priority"]=$job->fields["priority"];
			$i++;
		}
	
	// ---------------reminder 

	$query2="SELECT * from glpi_reminder WHERE rv='1' AND (author='$who' OR type='public')  AND (('$begin' <= begin AND '$end' >= begin) OR ('$begin' < end AND '$end' >= end) OR (begin <= '$begin' AND end > '$begin') OR (begin <= '$end' AND end > '$end')) ORDER BY begin";
	
	$result2=$DB->query($query2);


	$remind=new Reminder();

	if ($DB->numrows($result2)>0)
		while ($data=$DB->fetch_array($result2)){

			$interv[$data["begin"]."$$".$i]["id_reminder"]=$data["ID"];
			if (strcmp($begin,$data["begin"])>0)
				$interv[$data["begin"]."$$".$i]["begin"]=$debut;
			else $interv[$data["begin"]."$$".$i]["begin"]=$data["begin"];
			if (strcmp($end,$data["end"])<0)
				$interv[$data["begin"]."$$".$i]["end"]=$fin;
			else $interv[$data["begin"]."$$".$i]["end"]=$data["end"];

			$interv[$data["begin"]."$$".$i]["title"]=resume_text($data["title"],$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$".$i]["text"]=resume_text($data["text"],$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$".$i]["author"]=$data["author"];
			$interv[$data["begin"]."$$".$i]["type"]=$data["type"];

			$i++;
		}

	ksort($interv);

	// Display Items
	$tmp=split(":",$CFG_GLPI["planning_begin"]);
	$hour_begin=$tmp[0];
	$tmp=split(":",$CFG_GLPI["planning_end"]);
	$hour_end=$tmp[0];

	switch ($type){
		case "week":
			for ($hour=$hour_begin;$hour<=$hour_end;$hour++){
				echo "<tr>";
				for ($i=1;$i<=7;$i++){
					echo "<td class='tab_bg_3' width='12%' valign='top' >";
					echo "<b>".display_time($hour).":00</b><br>";
					
					// From midnight
					if ($hour==$hour_begin){
						$begin_time=date("Y-m-d H:i:s",strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP);
					} else {
						$begin_time=date("Y-m-d H:i:s",strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP+$hour*HOUR_TIMESTAMP);
					}
					// To midnight
					if($hour==$hour_end){
						$end_time=date("Y-m-d H:i:s",strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP+24*HOUR_TIMESTAMP);
					} else {
						$end_time=date("Y-m-d H:i:s",strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP+($hour+1)*HOUR_TIMESTAMP);
					}
					
					reset($interv);
					while ($data=current($interv)){
						if ($data["begin"]<$end_time&&$data["begin"]>=$begin_time){
							displayPlanningItem($data,$who);
							unset($interv[key($interv)]);
						} else {
							next($interv);
						}
					}
					echo "</td>";
				}
	
				echo "</tr>\n";
	
			}

			break;
		case "day":
			for ($hour=$hour_begin;$hour<=$hour_end;$hour++){
				echo "<tr>";
				$end_time=date("Y-m-d H:i:s",strtotime($when)+($hour+1)*HOUR_TIMESTAMP);
				echo "<td class='tab_bg_3' width='12%' valign='top' >";
				echo "<b>".display_time($hour).":00</b><br>";
				reset($interv);
				while ($data=current($interv)){
					if ($data["begin"]<$end_time||$hour==$hour_end){
						displayPlanningItem($data,$who,1);
						unset($interv[key($interv)]);
					} else {
						next($interv);
					}
				}
				echo "</td>";
				echo "</tr>";
			}
			break;
		case "month":
			echo "<tr class='tab_bg_3'>";
			// Display first day out of the month
			for ($i=1;$i<$begin_month_day;$i++){
				echo "<td style='background-color:#ffffff'>&nbsp;</td>";
			}
			// Print real days
			if ($current_month<10&&strlen($current_month)==1) $current_month="0".$current_month;
			
			$begin_time=strtotime($begin);
			$end_time=strtotime($end);
		
			for ($time=$begin_time;$time<$end_time;$time+=DAY_TIMESTAMP){

				// Add 6 hours for midnight problem
				$day=date("d",$time+6*HOUR_TIMESTAMP);

				echo "<td  valign='top' height='100'  class='tab_bg_3'>";
				echo "<table align='center' ><tr><td align='center' ><span style='font-family: arial,helvetica,sans-serif; font-size: 14px; color: black'>".$day."</span></td></tr>";

				echo "<tr class='tab_bg_3'>";
				echo "<td class='tab_bg_3' width='12%' valign='top' >";
				$begin_day=date("Y-m-d H:i:s",$time);
				$end_day=date("Y-m-d H:i:s",$time+DAY_TIMESTAMP);
				reset($interv);
				while ($data=current($interv)){
					if ($data["begin"]<$end_day&&$data["begin"]>=$begin_day){
						displayPlanningItem($data,$who);
						unset($interv[key($interv)]);
					} else {
						next($interv);
					}
				}

				echo "</td>";
	
				echo "</tr>";
				echo "</table>";
				echo "</td>";
				
				// Add break line
				if (($day+$begin_month_day)%7==1)	{
					echo "</tr>";
					if ($day!=$nb_days[$current_month-1]){
						echo "<tr>";
					}
				}

			}
			if ($end_month_day!=0){
				for ($i=0;$i<7-$end_month_day;$i++) 	{
					echo "<td style='background-color:#ffffff'>&nbsp;</td>";
				}
			}
			echo "</tr>";


			break;

	}
	
	echo "</table></div>";

}

function displayPlanningItem($val,$who,$complete=0){
	global $CFG_GLPI,$LANG;

	$author="";  // variable pour l'affichage de l'auteur ou non
	$img="rdv_private.png"; // variable par defaut pour l'affichage de l'icone du reminder

	echo "<div style=' margin:auto; text-align:center; border:1px dashed #cccccc; background-color: #d7d7d2; font-size:9px; width:80%;'>";
	$rand=mt_rand();
	if(isset($val["id_tracking"])){  // show tracking

		echo "<img src='".$CFG_GLPI["root_doc"]."/pics/rdv_interv.png' alt=''>&nbsp;";


		echo "<a href='".$CFG_GLPI["root_doc"]."/front/tracking.form.php?ID=".$val["id_tracking"]."'";
		if (!$complete){
			echo "onmouseout=\"cleanhide('content_tracking_".$val["ID"].$rand."')\" onmouseover=\"cleandisplay('content_tracking_".$val["ID"].$rand."')\"";
		}
		echo ">";
		echo date("H:i",strtotime($val["begin"]))." -> ".date("H:i",strtotime($val["end"])).": ".$val["device"];
		echo "&nbsp;<img src=\"".$CFG_GLPI["root_doc"]."/pics/".$val["status"].".png\" alt='".getStatusName($val["status"])."' title='".getStatusName($val["status"])."'>";
		if ($who==0){
			echo "<br>";
			echo $LANG["planning"][9]." ".getUserName($val["id_assign"]);
		} 
		echo "</a>";
		if ($complete){
			echo "<br>";
			echo "<strong>".$LANG["joblist"][2].":</strong> ".getPriorityName($val["priority"])."<br>";
			echo "<strong>".$LANG["joblist"][6].":</strong><br>".$val["content"];
		} else {
			echo "<div class='over_link' id='content_tracking_".$val["ID"].$rand."'><strong>".$LANG["joblist"][2].":</strong> ".getPriorityName($val["priority"])."<br>";
			echo "<strong>".$LANG["joblist"][6].":</strong><br>".$val["content"]."</div>";
		}

	}else{  // show Reminder
		if ($val["type"]=="public"){
			$author="<br>".$LANG["planning"][9]." : ".getUserName($val["author"]);
			$img="rdv_public.png";
		} 
		echo "<img src='".$CFG_GLPI["root_doc"]."/pics/".$img."' alt=''>&nbsp;";
		echo "<a href='".$CFG_GLPI["root_doc"]."/front/reminder.form.php?ID=".$val["id_reminder"]."'";
			if (!$complete){
			echo "onmouseout=\"cleanhide('content_reminder_".$val["id_reminder"].$rand."')\" onmouseover=\"cleandisplay('content_reminder_".$val["id_reminder"].$rand."')\"";
		}
		echo ">";
		echo date("H:i",strtotime($val["begin"]))." -> ".date("H:i",strtotime($val["end"])).": ".$val["title"];
		echo $author;
		echo "</a>";
		if ($complete){
			echo "<br>";
			echo $val["text"];
		} else {
			echo "<div class='over_link' id='content_reminder_".$val["id_reminder"].$rand."'>".$val["text"]."</div>";
		}

		echo "";
	}
	echo "</div><br>";

}


function display_time($time){

	$time=round($time);
	if ($time<10&&strlen($time)) return "0".$time;
	else return $time;

}


function ShowPlanningCentral($who){

	global $DB,$CFG_GLPI,$LANG;

	if (!haveRight("show_planning","1")) return false;

	$when=strftime("%Y-%m-%d");
	$debut=$when;

	// followup
	$ASSIGN="";
	if ($who!=0)
		$ASSIGN="id_assign='$who' AND";


	$INTERVAL=" 1 DAY "; // we want to show planning of the day

	$query="SELECT * from glpi_tracking_planning WHERE $ASSIGN (('".$debut."' <= begin AND adddate( '". $debut ."' , INTERVAL $INTERVAL ) >= begin) OR ('".$debut."' < end AND adddate( '". $debut ."' , INTERVAL $INTERVAL ) >= end) OR (begin <= '".$debut."' AND end > '".$debut."') OR (begin <= adddate( '". $debut ."' , INTERVAL $INTERVAL ) AND end > adddate( '". $debut ."' , INTERVAL $INTERVAL ))) ORDER BY begin";


	$result=$DB->query($query);

	$fup=new Followup();
	$job=new Job();

	$interv=array();
	$i=0;
	if ($DB->numrows($result)>0)
		while ($data=$DB->fetch_array($result)){
			$fup->getFromDB($data["id_followup"]);
			$job->getFromDBwithData($fup->fields["tracking"],0);

			$interv[$data["begin"]."$$".$i]["id_tracking"]=$fup->fields["tracking"];
			$interv[$data["begin"]."$$".$i]["begin"]=$data["begin"];
			$interv[$data["begin"]."$$".$i]["end"]=$data["end"];
			$interv[$data["begin"]."$$".$i]["content"]=resume_text($job->fields["contents"],$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$".$i]["device"]=$job->computername;
			$i++;
		}


	// reminder 
	$read_public="";
	if (haveRight("reminder_public","r")) $read_public=" OR type='public' ";

	$query2="SELECT * from glpi_reminder WHERE rv='1' AND (author='$who' $read_public)    AND (('".$debut."' <= begin AND adddate( '". $debut ."' , INTERVAL $INTERVAL ) >= begin) OR ('".$debut."' < end AND adddate( '". $debut ."' , INTERVAL $INTERVAL ) >= end) OR (begin <= '".$debut."' AND end > '".$debut."') OR (begin <= adddate( '". $debut ."' , INTERVAL $INTERVAL ) AND end > adddate( '". $debut ."' , INTERVAL $INTERVAL ))) ORDER BY begin";

	$result2=$DB->query($query2);


	$remind=new Reminder();

	$i=0;
	if ($DB->numrows($result2)>0)
		while ($data=$DB->fetch_array($result2)){
			$remind->getFromDB($data["ID"]);


			$interv[$data["begin"]."$$".$i]["id_reminder"]=$remind->fields["ID"];
			$interv[$data["begin"]."$$".$i]["begin"]=$data["begin"];
			$interv[$data["begin"]."$$".$i]["end"]=$data["end"];
			$interv[$data["begin"]."$$".$i]["title"]=resume_text($remind->fields["title"],$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$".$i]["text"]=resume_text($remind->fields["text"],$CFG_GLPI["cut"]);

			$i++;
		}



	ksort($interv);

	echo "<table class='tab_cadre' width='80%'><tr><th colspan='3'><a href='".$CFG_GLPI["root_doc"]."/front/planning.php'>".$LANG["planning"][15]."</a></th></tr><tr><th>".$LANG["buttons"][33]."</th><th>".$LANG["buttons"][32]."</th><th>".$LANG["joblist"][6]."</th></tr>";
	if (count($interv)>0){
		foreach ($interv as $key => $val){

			echo "<tr class='tab_bg_1'>";
			echo "<td>";		
			echo date("H:i",strtotime($val["begin"]));
			echo "</td>";
			echo "<td>";
			echo date("H:i",strtotime($val["end"]));
			echo "</td>";
			if(isset($val["id_tracking"])){
				echo "<td>".$val["device"]."<a href='".$CFG_GLPI["root_doc"]."/front/tracking.form.php?ID=".$val["id_tracking"]."'>";
				echo ": ".resume_text($val["content"],125)."</a>";
			}else{
				echo "<td><a href='".$CFG_GLPI["root_doc"]."/front/reminder.form.php?ID=".$val["id_reminder"]."'>".$val["title"]."";
				echo "</a>: ".resume_text($val["text"],125);
			}

			echo "</td></tr>";

		}

	}
	echo "</table>";

}
















//*******************************************************************************************************************************
// *********************************** Implementation ICAL ***************************************************************
//*******************************************************************************************************************************


/**
 * Generate URL for ICAL
 *
 *  
 * @param $who 
 * @Return Nothing (display function)
 *
 **/      
function urlIcal ($who) {

	global  $CFG_GLPI, $LANG;

	echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/planning.ical.php?uID=$who\"><span style='font-size:10px'>-".$LANG["planning"][12]."</span></a>";
	echo "<br>";

	// Todo recup l'url complete de glpi proprement, ? nouveau champs table config ?
	echo "<a href=\"webcal://".$_SERVER['HTTP_HOST'].$CFG_GLPI["root_doc"]."/front/planning.ical.php?uID=$who\"><span style='font-size:10px'>-".$LANG["planning"][13]."</span></a>";

}


/**
 * Convert date mysql to timestamp
 * 
 * @param $date  date in mysql format
 * @Return timestamp
 *
 **/      
function date_mysql_to_timestamp($date){
	if (!preg_match('/(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)/', $date, $r)){
		return false;
	}

	return mktime($r[4], $r[5], $r[6], $r[2], $r[3], $r[1] );
}


/**
 * Convert timestamp to date in ical format
 * 
 * @param $date  timestamp
 * @Return date in ical format
 *
 **/      
function date_ical($date) {
	return date("Ymd\THis", date_mysql_to_timestamp($date));
}



/**
 *
 * Generate header for ical file
 * 
 * @param $name 
 * @Return $debut_cal  
 *
 **/      
function debutIcal($name) {

	global  $CFG_GLPI, $LANG;

	$debut_cal = "BEGIN:VCALENDAR\n";
	$debut_cal .= "VERSION:2.0\n";

	if ( ! empty ( $CFG_GLPI["version"]) ) {
		$debut_cal.= "PRODID:-//GLPI-Planning-".$CFG_GLPI["version"]."\n";
	} else {
		$debut_cal.= "PRODID:-//GLPI-Planning-UnknownVersion\n";
	}

	$debut_cal.= "METHOD:PUBLISH\n"; // Outlook want's this in the header, why I don't know...
	$debut_cal .= "X-WR-CALNAME ;VALUE=TEXT:$name\n";

	//   $debut_cal .= "X-WR-RELCALID:n";
	//   $debut_cal .= "X-WR-TIMEZONE:US/Pacific\n";
	$debut_cal .= "CALSCALE:GREGORIAN\n\n";
	return (string) $debut_cal;
}


/**
 *  Generate ical body file
 *  
 * @param $who
 * @Return $debutcal $event $fincal
 **/      
function generateIcal($who){

	global  $DB,$CFG_GLPI, $LANG;

	// export job
	$query="SELECT * from glpi_tracking_planning WHERE id_assign=$who";

	$result=$DB->query($query);

	$job=new Job();
	$fup=new Followup();
	$interv=array();
	$i=0;
	if ($DB->numrows($result)>0)
		while ($data=$DB->fetch_array($result)){

			$fup->getFromDB($data["id_followup"]); 
			$job->getFromDBwithData($fup->fields["tracking"],0);

			$interv[$data["begin"]."$$".$i]["id_tracking"]=$data['id_followup'];
			$interv[$data["begin"]."$$".$i]["id_assign"]=$data['id_assign'];
			$interv[$data["begin"]."$$".$i]["ID"]=$data['ID'];
			$interv[$data["begin"]."$$".$i]["begin"]=$data['begin'];
			$interv[$data["begin"]."$$".$i]["end"]=$data['end'];
			//$interv[$i]["content"]=substr($job->contents,0,$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$".$i]["content"]=substr($job->fields['contents'],0,$CFG_GLPI["cut"]);
			$interv[$data["begin"]."$$".$i]["device"]=$job->computername;
			$i++;
		}


	// reminder 

	$query2="SELECT * from glpi_reminder WHERE rv='1' AND (author='$who' OR type='public')";

	$result2=$DB->query($query2);


	$remind=new Reminder();

	$i=0;
	if ($DB->numrows($result2)>0)
		while ($data=$DB->fetch_array($result2)){
			$remind->getFromDB($data["ID"]);


			$interv[$data["begin"]."$$".$i]["id_reminder"]=$remind->fields["ID"];
			$interv[$data["begin"]."$$".$i]["begin"]=$data["begin"];
			$interv[$data["begin"]."$$".$i]["end"]=$data["end"];
			$interv[$data["begin"]."$$".$i]["title"]=$remind->fields["title"];
			$interv[$data["begin"]."$$".$i]["content"]=$remind->fields["text"];

			$i++;
		}

	$debutcal="";
	$event="";
	$fincal="";


	ksort($interv);

	if (count($interv)>0) {

		$debutcal=debutIcal(getUserName($who));

		foreach ($interv as $key => $val){

			$event .= "BEGIN:VEVENT\n";

			if(isset($val["id_tracking"])){
				$event.="UID:Job#".$val["id_tracking"]."\n";
			}else{
				$event.="UID:Event#".$val["id_reminder"]."\n";
			}		

			$event.="DTSTAMP:".date_ical($val["begin"])."\n";

			$event .= "DTSTART:".date_ical($val["begin"])."\n";

			$event .= "DTEND:".date_ical($val["end"])."\n";

			if(isset($val["id_tracking"])){
				$event .= "SUMMARY:".$LANG["planning"][8]." # ".$val["id_tracking"]." ".$LANG["planning"][10]." # ".$val["device"]."\n";
			}else{
				$event .= "SUMMARY:".$val["title"]."\n";
			}

			$event .= "DESCRIPTION:".$val["content"]."\n";

			//todo recup la cat�orie d'intervention.
			//$event .= "CATEGORIES:".$val["categorie"]."\n";
			if(isset($val["id_tracking"])){
				$event .= "URL:".$CFG_GLPI["url_base"]."/index.php?redirect=tracking_".$val["id_tracking"]."\n";
			}

			$event .= "END:VEVENT\n\n";
		}
		$fincal= "END:VCALENDAR\n";	
	}

	return utf8_decode($debutcal.$event.$fincal);

}


?>
