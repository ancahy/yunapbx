<?php
include_once(dirname(__FILE__).'/../include/db_utils.inc.php');
include_once(dirname(__FILE__).'/../include/smarty_utils.inc.php');
include_once(dirname(__FILE__).'/../include/admin_utils.inc.php');


function Extensions_List_IVRs() {
	session_start();
	$session = &$_SESSION['Extensions_List_IVRs'];
	$smarty  = smarty_init(dirname(__FILE__).'/templates');

	// Init message (Message)
	$Message = $_REQUEST['msg'];

	// Init no element on page (PageSize)
	$PageSize = 50;

	// Init sort order (Order)
	if ($session['Sort'] == $_REQUEST['Sort']) {
		$Order = ($session['Order']=="asc"?"desc":"asc");
	} elseif ($session['Sort'] != $_REQUEST['Sort']) {
		$Order = 'asc';
	}
	$session['Order'] = $Order;

	// Init sort field (Sort)
	if(isset($_REQUEST['Sort'])) {
		$Sort = $_REQUEST['Sort'];
	} else {
		$Sort = 'Extension';
	}
	$session['Sort'] = $Sort;

	// Init listing start (Start)
	if(isset($_REQUEST['Start'])) {
		$Start = $_REQUEST['Start'];
	} else {
		$Start = 0;
	}

	// Init search string (Search)
	if(isset($_REQUEST['Search'])) {
		$Search = $_REQUEST['Search'];
	}

	// Init table fields (Extensions)
	$Extensions = array();
	$query = "
		SELECT
			Extensions.PK_Extension        AS _PK_,
			LPAD(Extension,5,' ')          AS Extension,
			Extensions.Type                AS Type,
			IVR_Menus.Name                 AS Name,
			Ext_IVR.FK_Action              AS FK_Action,
			DateCreated AS DateCreated,
			DATE_FORMAT(DateCreated,'%m/%d/%y, %h:%i %p') AS DateCreated_Formated
		FROM
			Extensions
			LEFT JOIN Ext_IVR   ON Ext_IVR.PK_Extension = Extensions.PK_Extension
			LEFT JOIN IVR_Menus ON Ext_IVR.FK_Menu      = IVR_Menus.PK_Menu
		HAVING
			Type = 'IVR'
			AND
			(Extension LIKE '%$Search%' OR Name LIKE '%$Search%')
		ORDER BY
			$Sort $Order
	";
	// -- LIMIT $Start, $PageSize
	$result = mysql_query($query) or die(mysql_error());

	$Total = mysql_numrows($result);
	$entries_allowed = $PageSize;
	@mysql_data_seek($result, $Start);

	while ($row = mysql_fetch_assoc($result)) {
		$extension = $row;

		$query2  = "SELECT * FROM IVR_Actions WHERE PK_Action = {$extension['FK_Action']} LIMIT 1";
		$result2 = mysql_query($query2) or die(mysql_error().$query2);
		$extension['Action'] = mysql_fetch_assoc($result2);

		$Extensions[] = $extension;

		if (($entries_allowed--) == 1) { break; }
	}

	// Init end record (End)
	$End = count($Extensions) + $Start;

	$smarty->assign('Extensions', $Extensions);
	$smarty->assign('Sort'      , $Sort);
	$smarty->assign('Order'     , $Order);
	$smarty->assign('Start'     , $Start);
	$smarty->assign('End'       , $End);
	$smarty->assign('Total'     , $Total);
	$smarty->assign('PageSize'  , $PageSize);
	$smarty->assign('Search'    , $Search);
	$smarty->assign('Message'   , $Message);
	$smarty->assign('Hilight'   , $_REQUEST['hilight']);

	return $smarty->fetch('Extensions_List_IVRs.tpl');
}

admin_run('Extensions_List_IVRs', 'Admin.tpl');

?>
