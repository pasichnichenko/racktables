<?php
/*

"Ophandler" in RackTables stands for "operation handler", or a function,
which handles execution of "operation" (in the meaning explained in
navigation.php). Most of the ophandlers are meant to perform one specific
action, for example, to set a name of an object. Each such action often
requires a set of parameters (e. g. ID of the object and the new name),
and it is responsibility of each ophandler function to verify, that all
necessary parameters are provided by the user and have proper values. There
is a number of helper functions to make such verification simpler.

Errors occuring in ophandlers are typically indicated with exceptions of
assorted classes. Namely, an "InvalidRequestArgException" class means, that
at least one of the parameters provided by the user is not acceptable. This
is a "soft" error, which gets displayed in the standard message area of
otherwise usual interface. A different case is "InvalidArgException", which
means, that one of the internal functions detected its argument(s) invalid
or corrupted, and that argument(s) did not come from user's input (and thus
cannot be fixed without fixing a bug in the code). Such "hard" errors don't
get special early handling and end up in the default catching block. The
latter may print a detailed stack trace instead of the interface HTML to
help a developer debug the issue.

As long as an ophandler makes through its request (extracting arguments,
performing validation and actually updating records in the database), it
may queue up messages (often referred to as "green" and "red" bars) by
means of showError() and showSuccess() functions. The messages are not
displayed immediately, because successfull ophandlers are expected to
return only the new URL, where the user will be immediately redirected to
(it is also possible to return an empty string to mean, that the current
logical location remains the same). The page at the "next" location is
supposed to translate message buffer into the standard message area.

A very special case of an ophandler is tableHandler(). This generic
function handles the most trivial actions, which map to a single INSERT,
UPDATE or DELETE SQL statement with a fixed number of arguments. The rules
of argument validation and mapping are listed in $opspec_list (operation
specifications list) array.

*/

// This array is deprecated. Please do not add new message constants to it.
// use the new showError, showWarning, showSuccess functions instead
global $msgcode;
$msgcode = array();

global $opspec_list;
$opspec_list = array();

$opspec_list['rackspace-edit-addRow'] = array
(
	'table' => 'Object',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'objtype_id', 'assertion' => 'uint'),
		array ('url_argname' => 'name', 'table_colname' => 'label', 'assertion' => 'string')
	),
);
$opspec_list['rackspace-edit-updateRow'] = array
(
	'table' => 'Object',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'name', 'table_colname' => 'label', 'assertion' => 'string')
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'row_id', 'table_colname' => 'id', 'assertion' => 'uint')
	),
);
$opspec_list['rackspace-edit-deleteRow'] = array
(
	'table' => 'Object',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'row_id', 'table_colname' => 'id', 'assertion' => 'uint')
	),
);
$opspec_list['object-ports-delPort'] = array
(
	'table' => 'Port',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'port_id', 'table_colname' => 'id', 'assertion' => 'uint'),
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
	),
);
$opspec_list['object-ports-deleteAll'] = array
(
	'table' => 'Port',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
	),
);
$opspec_list['object-log-del'] = array
(
	'table' => 'ObjectLog',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'logid', 'table_colname' => 'id', 'assertion' => 'uint'),
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
	),
);
$opspec_list['ipv4vs-editlblist-delLB'] =
$opspec_list['ipv4rspool-editlblist-delLB'] =
$opspec_list['object-editrspvs-delLB'] = array
(
	'table' => 'IPv4LB',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
		array ('url_argname' => 'pool_id', 'table_colname' => 'rspool_id', 'assertion' => 'uint'),
		array ('url_argname' => 'vs_id', 'assertion' => 'uint'),
	),
);
$opspec_list['ipv4vs-editlblist-updLB'] =
$opspec_list['ipv4rspool-editlblist-updLB'] =
$opspec_list['object-editrspvs-updLB'] = array
(
	'table' => 'IPv4LB',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vsconfig', 'assertion' => 'string0', 'if_empty' => 'NULL'),
		array ('url_argname' => 'rsconfig', 'assertion' => 'string0', 'if_empty' => 'NULL'),
		array ('url_argname' => 'prio', 'assertion' => 'string0', 'if_empty' => 'NULL'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
		array ('url_argname' => 'pool_id', 'table_colname' => 'rspool_id', 'assertion' => 'uint'),
		array ('url_argname' => 'vs_id', 'assertion' => 'uint'),
	),
);
$opspec_list['object-cacti-add'] = array
(
	'table' => 'CactiGraph',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
		array ('url_argname' => 'graph_id', 'assertion' => 'uint'),
		array ('url_argname' => 'caption', 'assertion' => 'string0'),
	),
);
$opspec_list['object-cacti-del'] = array
(
	'table' => 'CactiGraph',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
		array ('url_argname' => 'graph_id', 'assertion' => 'uint'),
	),
);
$opspec_list['ipv4net-properties-editRange'] = array
(
	'table' => 'IPv4Network',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string0'),
		array ('url_argname' => 'comment', 'assertion' => 'string0'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'uint')
	),
);
$opspec_list['ipv6net-properties-editRange'] = array
(
	'table' => 'IPv6Network',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string0'),
		array ('url_argname' => 'comment', 'assertion' => 'string0'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'uint')
	),
);
$opspec_list['ipv4rspool-editrslist-delRS'] = array
(
	'table' => 'IPv4RS',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['ipv4rspool-edit-updIPv4RSP'] = array
(
	'table' => 'IPv4RSPool',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string0', 'if_empty' => 'NULL'),
		array ('url_argname' => 'vsconfig', 'assertion' => 'string0', 'if_empty' => 'NULL'),
		array ('url_argname' => 'rsconfig', 'assertion' => 'string0', 'if_empty' => 'NULL'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'pool_id', 'table_colname' => 'id', 'assertion' => 'uint')
	),
);
$opspec_list['file-edit-updateFile'] = array
(
	'table' => 'File',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'file_name', 'table_colname' => 'name', 'assertion' => 'string'),
		array ('url_argname' => 'file_type', 'table_colname' => 'type', 'assertion' => 'string'),
		array ('url_argname' => 'file_comment', 'table_colname' => 'comment', 'assertion' => 'string0', 'if_empty' => 'NULL'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'file_id', 'table_colname' => 'id', 'assertion' => 'uint')
	),
);
$opspec_list['parentmap-edit-add'] = array
(
	'table' => 'ObjectParentCompat',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'parent_objtype_id', 'assertion' => 'uint'),
		array ('url_argname' => 'child_objtype_id', 'assertion' => 'uint'),
	),
);
$opspec_list['parentmap-edit-del'] = array
(
	'table' => 'ObjectParentCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'parent_objtype_id', 'assertion' => 'uint'),
		array ('url_argname' => 'child_objtype_id', 'assertion' => 'uint'),
	),
);
$opspec_list['portmap-edit-add'] = array
(
	'table' => 'PortCompat',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'type1', 'assertion' => 'uint'),
		array ('url_argname' => 'type2', 'assertion' => 'uint'),
	),
);
$opspec_list['portmap-edit-del'] = array
(
	'table' => 'PortCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'type1', 'assertion' => 'uint'),
		array ('url_argname' => 'type2', 'assertion' => 'uint'),
	),
);
$opspec_list['portifcompat-edit-del'] = array
(
	'table' => 'PortInterfaceCompat',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'iif_id', 'assertion' => 'uint'),
		array ('url_argname' => 'oif_id', 'assertion' => 'uint'),
	),
);
$opspec_list['attrs-editmap-del'] = array
(
	'table' => 'AttributeMap',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'attr_id', 'assertion' => 'uint'),
		array ('url_argname' => 'objtype_id', 'assertion' => 'uint'),
	),
);
$opspec_list['attrs-editattrs-add'] = array
(
	'table' => 'Attribute',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'attr_type', 'table_colname' => 'type', 'assertion' => 'enum/attr_type'),
		array ('url_argname' => 'attr_name', 'table_colname' => 'name', 'assertion' => 'string'),
	),
);
$opspec_list['attrs-editattrs-del'] = array
(
	'table' => 'Attribute',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'attr_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['attrs-editattrs-upd'] = array
(
	'table' => 'Attribute',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'attr_name', 'table_colname' => 'name', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'attr_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['dict-chapters-add'] = array
(
	'table' => 'Chapter',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'chapter_name', 'table_colname' => 'name', 'assertion' => 'string')
	),
);
$opspec_list['chapter-edit-add'] = array
(
	'table' => 'Dictionary',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'chapter_no', 'table_colname' => 'chapter_id', 'assertion' => 'uint'),
		array ('url_argname' => 'dict_value', 'assertion' => 'string'),
	),
);
$opspec_list['chapter-edit-del'] = array
(
	'table' => 'Dictionary',
	'action' => 'DELETE',
	'arglist' => array
	(
		// Technically dict_key is enough to delete, but including chapter_id into
		// WHERE clause makes sure, that the action actually happends for the same
		// chapter, which authorization was granted for.
		array ('url_argname' => 'chapter_no', 'table_colname' => 'chapter_id', 'assertion' => 'uint'),
		array ('url_argname' => 'dict_key', 'assertion' => 'uint'),
	),
);
$opspec_list['tagtree-edit-createTag'] = array
(
	'table' => 'TagTree',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'tag_name', 'table_colname' => 'tag', 'assertion' => 'tag'),
		array ('url_argname' => 'parent_id', 'assertion' => 'uint0', 'if_empty' => 'NULL'),
	),
);
$opspec_list['tagtree-edit-destroyTag'] = array
(
	'table' => 'TagTree',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'tag_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['tagtree-edit-updateTag'] = array
(
	'table' => 'TagTree',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'tag_name', 'table_colname' => 'tag', 'assertion' => 'tag'),
		array ('url_argname' => 'parent_id', 'assertion' => 'uint0', 'if_empty' => 'NULL'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'tag_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['8021q-vstlist-upd'] = array
(
	'table' => 'VLANSwitchTemplate',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vst_descr', 'table_colname' => 'description', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'vst_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['8021q-vdlist-upd'] = array
(
	'table' => 'VLANDomain',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vdom_descr', 'table_colname' => 'description', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['vlandomain-vlanlist-add'] = array
(
	'table' => 'VLANDescription',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'domain_id', 'assertion' => 'uint'),
		array ('url_argname' => 'vlan_id', 'assertion' => 'vlan'),
		array ('url_argname' => 'vlan_type', 'assertion' => 'enum/vlan_type'),
		array ('url_argname' => 'vlan_descr', 'assertion' => 'string0', 'if_empty' => 'NULL'),
	),
);
$opspec_list['vlandomain-vlanlist-del'] = array
(
	'table' => 'VLANDescription',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'domain_id', 'assertion' => 'uint'),
		array ('url_argname' => 'vlan_id', 'assertion' => 'vlan'),
	),
);
$opspec_list['vlan-edit-upd'] = // both locations are using the same tableHandler op
$opspec_list['vlandomain-vlanlist-upd'] = array
(
	'table' => 'VLANDescription',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'vlan_type', 'assertion' => 'enum/vlan_type'),
		array ('url_argname' => 'vlan_descr', 'assertion' => 'string0', 'if_empty' => 'NULL'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'vdom_id', 'table_colname' => 'domain_id', 'assertion' => 'uint'),
		array ('url_argname' => 'vlan_id', 'assertion' => 'vlan'),
	),
);

function buildRedirectURL ($nextpage = NULL, $nexttab = NULL, $moreArgs = array())
{
	global $page, $pageno, $tabno;
	if ($nextpage === NULL)
		$nextpage = $pageno;
	if ($nexttab === NULL)
		$nexttab = $tabno;
	$url = "index.php?page=${nextpage}&tab=${nexttab}";
	if (isset ($page[$nextpage]['bypass']))
		$url .= '&' . $page[$nextpage]['bypass'] . '=' . $_REQUEST[$page[$nextpage]['bypass']];

	if (count ($moreArgs) > 0)
		foreach ($moreArgs as $arg => $value)
			if (is_array ($value))
				foreach ($value as $v)
					$url .= '&' . urlencode ($arg . '[]') . '=' . urlencode ($v);
			elseif ($arg != 'module')
				$url .= '&' . urlencode ($arg) . '=' . urlencode ($value);
	return $url;
}

$msgcode['addPortForwarding']['OK'] = 48;
function addPortForwarding ()
{
	assertUIntArg ('object_id');
	assertIPv4Arg ('localip');
	assertIPv4Arg ('remoteip');
	assertUIntArg ('localport');
	assertStringArg ('proto');
	assertStringArg ('description', TRUE);
	$remoteport = isset ($_REQUEST['remoteport']) ? $_REQUEST['remoteport'] : '';
	if (!strlen ($remoteport))
		$remoteport = $_REQUEST['localport'];

	newPortForwarding
	(
		$_REQUEST['object_id'],
		$_REQUEST['localip'],
		$_REQUEST['localport'],
		$_REQUEST['remoteip'],
		$remoteport,
		$_REQUEST['proto'],
		$_REQUEST['description']
	);

	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delPortForwarding']['OK'] = 49;
function delPortForwarding ()
{
	assertUIntArg ('object_id');
	assertIPv4Arg ('localip');
	assertIPv4Arg ('remoteip');
	assertUIntArg ('localport');
	assertUIntArg ('remoteport');
	assertStringArg ('proto');

	deletePortForwarding
	(
		$_REQUEST['object_id'],
		$_REQUEST['localip'],
		$_REQUEST['localport'],
		$_REQUEST['remoteip'],
		$_REQUEST['remoteport'],
		$_REQUEST['proto']
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updPortForwarding']['OK'] = 51;
function updPortForwarding ()
{
	assertUIntArg ('object_id');
	assertIPv4Arg ('localip');
	assertIPv4Arg ('remoteip');
	assertUIntArg ('localport');
	assertUIntArg ('remoteport');
	assertStringArg ('proto');
	assertStringArg ('description');

	updatePortForwarding
	(
		$_REQUEST['object_id'],
		$_REQUEST['localip'],
		$_REQUEST['localport'],
		$_REQUEST['remoteip'],
		$_REQUEST['remoteport'],
		$_REQUEST['proto'],
		$_REQUEST['description']
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addPortForObject']['OK'] = 48;
function addPortForObject ()
{
	assertStringArg ('port_name', TRUE);
	genericAssertion ('port_l2address', 'l2address0');
	genericAssertion ('port_name', 'string');
	commitAddPort
	(
		$_REQUEST['object_id'],
		trim ($_REQUEST['port_name']),
		$_REQUEST['port_type_id'],
		trim ($_REQUEST['port_label']),
		trim ($_REQUEST['port_l2address'])
	);
	return showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['port_name']));
}

$msgcode['editPortForObject']['OK'] = 6;
function editPortForObject ()
{
	global $sic;
	assertUIntArg ('port_id');
	assertUIntArg ('port_type_id');
	assertStringArg ('reservation_comment', TRUE);
	genericAssertion ('l2address', 'l2address0');
	genericAssertion ('name', 'string');
	commitUpdatePort ($sic['object_id'], $sic['port_id'], $sic['name'], $sic['port_type_id'], $sic['label'], $sic['l2address'], $sic['reservation_comment']);
	return showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
}

$msgcode['linkPortForObject']['OK'] = 8;
function linkPortForObject ()
{
	assertUIntArg ('port_id');
	assertUIntArg ('remote_port_id');
	assertStringArg ('cable', TRUE);

	// FIXME: ensure, that at least one of these ports belongs to the current object
	linkPorts ($_REQUEST['port_id'], $_REQUEST['remote_port_id'], $_REQUEST['cable']);
	$port_info = getPortInfo ($_REQUEST['port_id']);
	return showFuncMessage
	(
		__FUNCTION__,
		'OK',
		array
		(
			formatPortLink ($port_info['id'], $port_info['name'], NULL, NULL),
			formatLinkedPort ($port_info),
		)
	);
}

$msgcode['addMultiPorts']['OK'] = 10;
function addMultiPorts ()
{
	assertStringArg ('format');
	assertStringArg ('input');
	assertStringArg ('port_type');
	$format = $_REQUEST['format'];
	$port_type = $_REQUEST['port_type'];
	$object_id = $_REQUEST['object_id'];
	// Input lines are escaped, so we have to explode and to chop by 2-char
	// \n and \r respectively.
	$lines1 = explode ("\n", $_REQUEST['input']);
	foreach ($lines1 as $line)
	{
		$parts = explode ('\r', $line);
		reset ($parts);
		if (!strlen ($parts[0]))
			continue;
		else
			$lines2[] = rtrim ($parts[0]);
	}
	$ports = array();
	foreach ($lines2 as $line)
	{
		switch ($format)
		{
			case 'fisxii':
				$words = explode (' ', preg_replace ('/[[:space:]]+/', ' ', $line));
				list ($slot, $port) = explode ('/', $words[0]);
				$ports[] = array
				(
					'name' => "e ${slot}/${port}",
					'l2address' => $words[8],
					'label' => "slot ${slot} port ${port}"
				);
				break;
			case 'c3600asy':
				$words = explode (' ', preg_replace ('/[[:space:]]+/', ' ', trim (substr ($line, 3))));
/*
How Async Lines are Numbered in Cisco 3600 Series Routers
http://www.cisco.com/en/US/products/hw/routers/ps274/products_tech_note09186a00801ca70b.shtml

Understanding 16- and 32-Port Async Network Modules
http://www.cisco.com/en/US/products/hw/routers/ps274/products_tech_note09186a00800a93f0.shtml
*/
				$async = $words[0];
				$slot = floor (($async - 1) / 32);
				$octalgroup = floor (($async - 1 - $slot * 32) / 8);
				$cable = $async - $slot * 32 - $octalgroup * 8;
				$og_label[0] = 'async 0-7';
				$og_label[1] = 'async 8-15';
				$og_label[2] = 'async 16-23';
				$og_label[3] = 'async 24-31';
				$ports[] = array
				(
					'name' => "async ${async}",
					'l2address' => '',
					'label' => "slot ${slot} " . $og_label[$octalgroup] . " cable ${cable}"
				);
				break;
			case 'fiwg':
				$words = explode (' ', preg_replace ('/[[:space:]]+/', ' ', $line));
				$ifnumber = $words[0] * 1;
				$ports[] = array
				(
					'name' => "e ${ifnumber}",
					'l2address' => "${words[8]}",
					'label' => "${ifnumber}"
				);
				break;
			case 'ssv1':
				$words = explode (' ', $line);
				if (!strlen ($words[0]) or !strlen ($words[1]))
					continue;
				$ports[] = array
				(
					'name' => $words[0],
					'l2address' => $words[1],
					'label' => ''
				);
				break;
			default:
				throw new InvalidRequestArgException ('format', $format);
				break;
		}
	}
	// Create ports, if they don't exist.
	$added_count = $updated_count = $error_count = 0;
	foreach ($ports as $port)
	{
		$port_ids = getPortIDs ($object_id, $port['name']);
		if (!count ($port_ids))
		{
			commitAddPort ($object_id, $port['name'], $port_type, $port['label'], $port['l2address']);
			$added_count++;
		}
		elseif (count ($port_ids) == 1) // update only single-socket ports
		{
			commitUpdatePort ($object_id, $port_ids[0], $port['name'], $port_type, $port['label'], $port['l2address']);
			$updated_count++;
		}
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($added_count, $updated_count, $error_count));
}

$msgcode['addBulkPorts']['OK'] = 82;
function addBulkPorts ()
{
	assertStringArg ('port_type_id');
	assertStringArg ('port_name');
	assertStringArg ('port_label', TRUE);
	assertUIntArg ('port_numbering_start', TRUE);
	assertUIntArg ('port_numbering_count');
	
	$object_id = $_REQUEST['object_id'];
	$port_name = $_REQUEST['port_name'];
	$port_type_id = $_REQUEST['port_type_id'];
	$port_label = $_REQUEST['port_label'];
	$port_numbering_start = $_REQUEST['port_numbering_start'];
	$port_numbering_count = $_REQUEST['port_numbering_count'];
	
	$added_count = $error_count = 0;
	if(strrpos($port_name, "%u") === false )
		$port_name .= '%u';
	for ($i=0,$c=$port_numbering_start; $i<$port_numbering_count; $i++,$c++)
	{
		commitAddPort ($object_id, @sprintf($port_name,$c), $port_type_id, @sprintf($port_label,$c), '');
		$added_count++;
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($added_count, $error_count));
}

$msgcode['updIPv4Allocation']['OK'] = 51;
function updIPv4Allocation ()
{
	assertIPv4Arg ('ip');
	assertUIntArg ('object_id');
	assertStringArg ('bond_name', TRUE);
	genericAssertion ('bond_type', 'enum/inet4alloc');

	updateBond ($_REQUEST['ip'], $_REQUEST['object_id'], $_REQUEST['bond_name'], $_REQUEST['bond_type']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updIPv6Allocation']['OK'] = 51;
function updIPv6Allocation ()
{
	$ipv6 = assertIPv6Arg ('ip');
	assertUIntArg ('object_id');
	assertStringArg ('bond_name', TRUE);
	genericAssertion ('bond_type', 'enum/inet6alloc');

	updateIPv6Bond ($ipv6, $_REQUEST['object_id'], $_REQUEST['bond_name'], $_REQUEST['bond_type']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delIPv4Allocation']['OK'] = 49;
function delIPv4Allocation ()
{
	assertIPv4Arg ('ip');
	assertUIntArg ('object_id');

	unbindIpFromObject ($_REQUEST['ip'], $_REQUEST['object_id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delIPv6Allocation']['OK'] = 49;
function delIPv6Allocation ()
{
	assertUIntArg ('object_id');
	$ipv6 = assertIPv6Arg ('ip');
	unbindIPv6FromObject ($ipv6, $_REQUEST['object_id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addIPv4Allocation']['OK'] = 48;
$msgcode['addIPv4Allocation']['ERR1'] = 170;
function addIPv4Allocation ()
{
	assertIPv4Arg ('ip');
	assertUIntArg ('object_id');
	assertStringArg ('bond_name', TRUE);
	genericAssertion ('bond_type', 'enum/inet4alloc');

	// Strip masklen.
	$ip = preg_replace ('@/[[:digit:]]+$@', '', $_REQUEST['ip']);
	if  (getConfigVar ('IPV4_JAYWALK') != 'yes' and NULL === getIPv4AddressNetworkId ($ip))
		return showFuncMessage (__FUNCTION__, 'ERR1', array ($ip));
	
	bindIpToObject ($ip, $_REQUEST['object_id'], $_REQUEST['bond_name'], $_REQUEST['bond_type']);
	$address = getIPv4Address ($ip);
	if ($address['reserved'] == 'yes' or strlen ($address['name']))
	{
		$release = getConfigVar ('IPV4_AUTO_RELEASE');
		if ($release >= 1)
			$address['reserved'] = 'no';
		if ($release >= 2)
			$address['name'] = '';
		updateAddress ($ip, $address['name'], $address['reserved']);
	}
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addIPv6Allocation']['OK'] = 48;
$msgcode['addIPv6Allocation']['ERR1'] = 170;
function addIPv6Allocation ()
{
	assertUIntArg ('object_id');
	assertStringArg ('bond_name', TRUE);
	genericAssertion ('bond_type', 'enum/inet6alloc');

	// Strip masklen.
	$ipv6 = new IPv6Address;
	if (! $ipv6->parse (preg_replace ('@/\d+$@', '', $_REQUEST['ip'])))
		throw new InvalidRequestArgException('ip', $_REQUEST['ip'], 'parameter is not a valid ipv6 address');

	if  (getConfigVar ('IPV4_JAYWALK') != 'yes' and NULL === getIPv6AddressNetworkId ($ipv6))
		return showFuncMessage (__FUNCTION__, 'ERR1', array ($ip));

	bindIPv6ToObject ($ipv6, $_REQUEST['object_id'], $_REQUEST['bond_name'], $_REQUEST['bond_type']);
	$address = getIPv6Address ($ipv6);
	if ($address['reserved'] == 'yes' or strlen ($address['name']))
	{
		$release = getConfigVar ('IPV4_AUTO_RELEASE');
		if ($release >= 1)
			$address['reserved'] = 'no';
		if ($release >= 2)
			$address['name'] = '';
		updateAddress ($ipv6, $address['name'], $address['reserved']);
	}
	return showFuncMessage (__FUNCTION__, 'OK');
}

function addIPv4Prefix ()
{
	assertStringArg ('range');
	assertStringArg ('name', TRUE);

	$is_bcast = isset ($_REQUEST['is_bcast']) ? $_REQUEST['is_bcast'] : 'off';
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	global $sic;
	$vlan_ck = empty ($sic['vlan_ck']) ? NULL : $sic['vlan_ck'];
	$net_id = createIPv4Prefix ($_REQUEST['range'], $sic['name'], $is_bcast == 'on', $taglist, $vlan_ck);
	showSuccess
	(
		'IP network <a href="' .
		makeHref (array ('page' => 'ipv4net', 'tab' => 'default', 'id' => $net_id)) .
		'">' . $_REQUEST['range'] . '</a> has been created'
	);
}

function addIPv6Prefix ()
{
	assertStringArg ('range');
	assertStringArg ('name', TRUE);

	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	$is_connected = isset ($_REQUEST['is_connected']) ? ($_REQUEST['is_connected'] == 'on') : FALSE;
	global $sic;
	$vlan_ck = empty ($sic['vlan_ck']) ? NULL : $sic['vlan_ck'];
	$net_id = createIPv6Prefix ($_REQUEST['range'], $sic['name'], $is_connected, $taglist, $vlan_ck);
	showSuccess
	(
		'IP network <a href="' .
		makeHref (array ('page' => 'ipv6net', 'tab' => 'default', 'id' => $net_id)) .
		'">' . $_REQUEST['range'] . '</a> has been created'
	);
}

$msgcode['delIPv4Prefix']['OK'] = 49;
function delIPv4Prefix ()
{
	assertUIntArg ('id');
	destroyIPv4Prefix ($_REQUEST['id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delIPv6Prefix']['OK'] = 49;
function delIPv6Prefix ()
{
	assertUIntArg ('id');
	destroyIPv6Prefix ($_REQUEST['id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['editAddress']['OK'] = 51;
function editAddress ()
{
	assertStringArg ('name', TRUE);

	if (isset ($_REQUEST['reserved']))
		$reserved = $_REQUEST['reserved'];
	else
		$reserved = 'off';
	updateAddress ($_REQUEST['ip'], $_REQUEST['name'], $reserved == 'on' ? 'yes' : 'no');
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['editv6Address']['OK'] = 51;
function editv6Address ()
{
	$ipv6 = assertIPArg ('ip');
	assertStringArg ('name', TRUE);

	if (isset ($_REQUEST['reserved']))
		$reserved = $_REQUEST['reserved'];
	else
		$reserved = 'off';
	updateAddress ($ipv6, $_REQUEST['name'], $reserved == 'on' ? 'yes' : 'no');
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['createUser']['OK'] = 5;
function createUser ()
{
	assertStringArg ('username');
	assertStringArg ('realname', TRUE);
	assertStringArg ('password');
	$username = $_REQUEST['username'];
	$password = sha1 ($_REQUEST['password']);
	commitCreateUserAccount ($username, $_REQUEST['realname'], $password);
	if (isset ($_REQUEST['taglist']))
		produceTagsForLastRecord ('user', $_REQUEST['taglist']);
	return showFuncMessage (__FUNCTION__, 'OK', array ($username));
}

$msgcode['updateUser']['OK'] = 6;
function updateUser ()
{
	genericAssertion ('user_id', 'uint');
	assertStringArg ('username');
	assertStringArg ('realname', TRUE);
	assertStringArg ('password');
	$username = $_REQUEST['username'];
	$new_password = $_REQUEST['password'];
	$userinfo = spotEntity ('user', $_REQUEST['user_id']);
	// Update user password only if provided password is not the same as current password hash.
	if ($new_password != $userinfo['user_password_hash'])
		$new_password = sha1 ($new_password);
	commitUpdateUserAccount ($_REQUEST['user_id'], $username, $_REQUEST['realname'], $new_password);
	return showFuncMessage (__FUNCTION__, 'OK', array ($username));
}

$msgcode['updateDictionary']['OK'] = 51;
function updateDictionary ()
{
	global $sic;
	assertUIntArg ('dict_key');
	assertStringArg ('dict_value');
	// this request must be built with chapter_no
	usePreparedUpdateBlade
	(
		'Dictionary',
		array ('dict_value' => $sic['dict_value']),
		array
		(
			'chapter_id' => getBypassValue(),
			'dict_key' => $sic['dict_key'],
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updateChapter']['OK'] = 51;
function updateChapter ()
{
	assertUIntArg ('chapter_no');
	assertStringArg ('chapter_name');
	global $sic;
	usePreparedUpdateBlade
	(
		'Chapter',
		array
		(
			'name' => $sic['chapter_name'],
		),
		array
		(
			'id' => $sic['chapter_no'],
			'sticky' => 'no', // note this constant, it protects system chapters
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delChapter']['OK'] = 49;
function delChapter ()
{
	assertUIntArg ('chapter_no');
	commitDeleteChapter ($_REQUEST['chapter_no']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['supplementAttrMap']['OK'] = 48;
$msgcode['supplementAttrMap']['ERR1'] = 154;
function supplementAttrMap ()
{
	assertUIntArg ('attr_id');
	assertUIntArg ('objtype_id');
	$attrMap = getAttrMap();
	if ($attrMap[$_REQUEST['attr_id']]['type'] != 'dict')
		$chapter_id = NULL;
	else
	{
		try
		{
			assertUIntArg ('chapter_no');
		}
		catch (InvalidRequestArgException $e)
		{
			return showFuncMessage (__FUNCTION__, 'ERR1', array ('chapter not selected'));
		}
		$chapter_id = $_REQUEST['chapter_no'];
	}
	commitSupplementAttrMap ($_REQUEST['attr_id'], $_REQUEST['objtype_id'], $chapter_id);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['clearSticker']['OK'] = 49;
function clearSticker ()
{
	global $sic;
	assertUIntArg ('attr_id');
	if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $sic['attr_id']))))
		commitUpdateAttrValue (getBypassValue(), $sic['attr_id']);
	else
	{
		$oldvalues = getAttrValues (getBypassValue());
		showError ('Permission denied, "' . $oldvalues[$sic['attr_id']]['name'] . '" left unchanged');
	}
}

$msgcode['updateObjectAllocation']['OK'] = 63;
function updateObjectAllocation ()
{
	global $remote_username, $sic;
	if (!isset ($_REQUEST['got_atoms']))
	{
		unset($_GET['page']);
		unset($_GET['tab']);
		unset($_GET['op']);
		unset($_POST['page']);
		unset($_POST['tab']);
		unset($_POST['op']);
		return buildRedirectURL (NULL, NULL, $_REQUEST);
	}
	$object_id = getBypassValue();
	$changecnt = 0;
	// Get a list of all of this object's parents,
	// then trim the list to only include parents which are racks
	$objectParents = getEntityRelatives('parents', 'object', $object_id);
	$parentRacks = array();
	foreach ($objectParents as $parentData)
		if ($parentData['entity_type'] == 'rack')
			$parentRacks[] = $parentData['entity_id'];
	$workingRacksData = array();
	foreach ($_REQUEST['rackmulti'] as $cand_id)
	{
		if (!isset ($workingRacksData[$cand_id]))
		{
			$rackData = spotEntity ('rack', $cand_id);
			amplifyCell ($rackData);
			$workingRacksData[$cand_id] = $rackData;
		}
		// It's zero-U mounted to this rack on the form, but not in the DB.  Mount it.
		if (isset($_REQUEST["zerou_${cand_id}"]) && !in_array($cand_id, $parentRacks))
		{
			$changecnt++;
			commitLinkEntities ('rack', $cand_id, 'object', $object_id);
		}
		// It's not zero-U mounted to this rack on the form, but it is in the DB.  Unmount it.
		if (!isset($_REQUEST["zerou_${cand_id}"]) && in_array($cand_id, $parentRacks))
		{
			$changecnt++;
			commitUnlinkEntities ('rack', $cand_id, 'object', $object_id);
		}
	}

	foreach ($workingRacksData as &$rd)
		applyObjectMountMask ($rd, $object_id);

	$oldMolecule = getMoleculeForObject ($object_id);
	foreach ($workingRacksData as $rack_id => $rackData)
	{
		if (! processGridForm ($rackData, 'F', 'T', $object_id))
			continue;
		$changecnt++;
		// Reload our working copy after form processing.
		$rackData = spotEntity ('rack', $cand_id);
		amplifyCell ($rackData);
		applyObjectMountMask ($rackData, $object_id);
		$workingRacksData[$rack_id] = $rackData;
	}
	if ($changecnt)
	{
		// Log a record.
		$newMolecule = getMoleculeForObject ($object_id);
		usePreparedInsertBlade
		(
			'MountOperation', 
			array
			(
				'object_id' => $object_id,
				'old_molecule_id' => count ($oldMolecule) ? createMolecule ($oldMolecule) : NULL,
				'new_molecule_id' => count ($newMolecule) ? createMolecule ($newMolecule) : NULL,
				'user_name' => $remote_username,
				'comment' => empty ($sic['comment']) ? NULL : $sic['comment'],
			)
		);
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($changecnt));
}

$msgcode['updateObject']['OK'] = 51;
function updateObject ()
{
	genericAssertion ('num_attrs', 'uint0');
	genericAssertion ('object_name', 'string0');
	genericAssertion ('object_label', 'string0');
	genericAssertion ('object_asset_no', 'string0');
	genericAssertion ('object_comment', 'string0');
	genericAssertion ('object_type_id', 'uint');
	if (array_key_exists ('object_has_problems', $_REQUEST) and $_REQUEST['object_has_problems'] == 'on')
		$has_problems = 'yes';
	else
		$has_problems = 'no';
	$object_id = getBypassValue();

	global $dbxlink, $sic;
	$dbxlink->beginTransaction();
	commitUpdateObject
	(
		$object_id,
		$_REQUEST['object_name'],
		$_REQUEST['object_label'],
		$has_problems,
		$_REQUEST['object_asset_no'],
		$_REQUEST['object_comment']
	);
	// Update optional attributes
	$oldvalues = getAttrValues ($object_id);
	for ($i = 0; $i < $_REQUEST['num_attrs']; $i++)
	{
		genericAssertion ("${i}_attr_id", 'uint');
		$attr_id = $_REQUEST["${i}_attr_id"];
		if (! array_key_exists ($attr_id, $oldvalues))
			throw new InvalidRequestArgException ('attr_id', $attr_id, 'malformed request');
		$value = $_REQUEST["${i}_value"];

		# Delete attribute and move on, when the field is empty or if the field
		# type is a dictionary and it is the "--NOT SET--" value of 0.
		if ($value == '' || ($oldvalues[$attr_id]['type'] == 'dict' && $value == 0))
		{
			if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
				commitUpdateAttrValue ($object_id, $attr_id);
			else
				showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
			continue;
		}

		// The value could be uint/float, but we don't know ATM. Let SQL
		// server check this and complain.
		assertStringArg ("${i}_value");
		switch ($oldvalues[$attr_id]['type'])
		{
			case 'uint':
			case 'float':
			case 'string':
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'dict':
				$oldvalue = $oldvalues[$attr_id]['key'];
				break;
			default:
		}
		if ($value === $oldvalue) // ('' == 0), but ('' !== 0)
			continue;
		if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
			commitUpdateAttrValue ($object_id, $attr_id, $value);
		else
			showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
	}
	$object = spotEntity ('object', $object_id);
	if ($sic['object_type_id'] != $object['objtype_id'])
	{
		if (! array_key_exists ($sic['object_type_id'], getObjectTypeChangeOptions ($object_id)))
			throw new InvalidRequestArgException ('new type_id', $sic['object_type_id'], 'incompatible with requested attribute values');
		usePreparedUpdateBlade ('RackObject', array ('objtype_id' => $sic['object_type_id']), array ('id' => $object_id));
	}
	// Invalidate thumb cache of all racks objects could occupy.
	foreach (getResidentRacksData ($object_id, FALSE) as $rack_id)
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	$dbxlink->commit();
	return showFuncMessage (__FUNCTION__, 'OK');
}

function addMultipleObjects()
{
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	$max = getConfigVar ('MASSCOUNT');
	for ($i = 0; $i < $max; $i++)
	{
		if (!isset ($_REQUEST["${i}_object_type_id"]))
			return showError ('Submitted form is invalid at line ' . ($i + 1));

		// set to empty values for virtual objects
		if (isset ($_REQUEST['virtual_objects']))
		{
			$_REQUEST["${i}_object_label"] = '';
			$_REQUEST["${i}_object_asset_no"] = '';
		}

		assertUIntArg ("${i}_object_type_id", TRUE);
		assertStringArg ("${i}_object_name", TRUE);
		assertStringArg ("${i}_object_label", TRUE);
		assertStringArg ("${i}_object_asset_no", TRUE);
		$name = $_REQUEST["${i}_object_name"];

		// It's better to skip silently, than to print a notice.
		if ($_REQUEST["${i}_object_type_id"] == 0)
			continue;
		try
		{
			$object_id = commitAddObject
			(
				$name,
				$_REQUEST["${i}_object_label"],
				$_REQUEST["${i}_object_type_id"],
				$_REQUEST["${i}_object_asset_no"],
				$taglist
			);
			$info = spotEntity ('object', $object_id);
			amplifyCell ($info);
			showSuccess ("added object " . formatPortLink ($info['id'], $info['dname'], NULL, NULL));
		}
		catch (RTDatabaseError $e)
		{
			showError ("Error creating object '$name': " . $e->getMessage());
			continue;
		}
	}
}

function addLotOfObjects()
{
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	assertUIntArg ('global_type_id', TRUE);
	assertStringArg ('namelist', TRUE);
	$global_type_id = $_REQUEST['global_type_id'];
	if ($global_type_id == 0 or !strlen ($_REQUEST['namelist']))
		return showError ('Incomplete form has been ignored. Cheers.');
	else
	{
		// The name extractor below was stolen from ophandlers.php:addMultiPorts()
		$names1 = explode ("\n", $_REQUEST['namelist']);
		$names2 = array();
		foreach ($names1 as $line)
		{
			$parts = explode ('\r', $line);
			reset ($parts);
			if (!strlen ($parts[0]))
				continue;
			else
				$names2[] = rtrim ($parts[0]);
		}
		foreach ($names2 as $name)
			try
			{
				$object_id = commitAddObject ($name, NULL, $global_type_id, '', $taglist);
				$info = spotEntity ('object', $object_id);
				amplifyCell ($info);
				showSuccess ("added object " . formatPortLink ($info['id'], $info['dname'], NULL, NULL));
			}
			catch (RTDatabaseError $e)
			{
				showError ("Error creating object '$name': " . $e->getMessage());
				continue;
			}
	}
}

$msgcode['deleteObject']['OK'] = 7;
function deleteObject ()
{
	assertUIntArg ('object_id');
	$oinfo = spotEntity ('object', $_REQUEST['object_id']);

	$racklist = getResidentRacksData ($_REQUEST['object_id'], FALSE);
	commitDeleteObject ($_REQUEST['object_id']);
	foreach ($racklist as $rack_id)
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	return showFuncMessage (__FUNCTION__, 'OK', array ($oinfo['dname']));
}

$msgcode['resetObject']['OK'] = 57;
function resetObject ()
{
	$racklist = getResidentRacksData (getBypassValue(), FALSE);
	commitResetObject (getBypassValue());
	foreach ($racklist as $rack_id)
		usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['useupPort']['OK'] = 49;
function useupPort ()
{
	assertUIntArg ('port_id');
	commitUpdatePortComment ($_REQUEST['port_id'], '');
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updateUI']['OK'] = 51;
function updateUI ()
{
	assertUIntArg ('num_vars');

	for ($i = 0; $i < $_REQUEST['num_vars']; $i++)
	{
		assertStringArg ("${i}_varname");
		assertStringArg ("${i}_varvalue", TRUE);
		$varname = $_REQUEST["${i}_varname"];
		$varvalue = $_REQUEST["${i}_varvalue"];

		// If form value = value in DB, don't bother updating DB
		if (!isConfigVarChanged($varname, $varvalue))
			continue;
		// any exceptions will be handled by process.php
		setConfigVar ($varname, $varvalue, TRUE);
	}
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['saveMyPreferences']['OK'] = 51;
function saveMyPreferences ()
{
	assertUIntArg ('num_vars');

	for ($i = 0; $i < $_REQUEST['num_vars']; $i++)
	{
		assertStringArg ("${i}_varname");
		assertStringArg ("${i}_varvalue", TRUE);
		$varname = $_REQUEST["${i}_varname"];
		$varvalue = $_REQUEST["${i}_varvalue"];

		// If form value = value in DB, don't bother updating DB
		if (!isConfigVarChanged($varname, $varvalue))
			continue;
		setUserConfigVar ($varname, $varvalue);
	}
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['resetMyPreference']['OK'] = 51;
function resetMyPreference ()
{
	assertStringArg ("varname");
	resetUserConfigVar ($_REQUEST["varname"]);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['resetUIConfig']['OK'] = 57;
function resetUIConfig()
{
	setConfigVar ('MASSCOUNT','8');
	setConfigVar ('MAXSELSIZE','30');
	setConfigVar ('ROW_SCALE','2');
	setConfigVar ('PORTS_PER_ROW','12');
	setConfigVar ('IPV4_ADDRS_PER_PAGE','256');
	setConfigVar ('DEFAULT_RACK_HEIGHT','42');
	setConfigVar ('DEFAULT_SLB_VS_PORT','');
	setConfigVar ('DEFAULT_SLB_RS_PORT','');
	setConfigVar ('DETECT_URLS','no');
	setConfigVar ('RACK_PRESELECT_THRESHOLD','1');
	setConfigVar ('DEFAULT_IPV4_RS_INSERVICE','no');
	setConfigVar ('AUTOPORTS_CONFIG','4 = 1*33*kvm + 2*24*eth%u;15 = 1*446*kvm');
	setConfigVar ('SHOW_EXPLICIT_TAGS','yes');
	setConfigVar ('SHOW_IMPLICIT_TAGS','yes');
	setConfigVar ('SHOW_AUTOMATIC_TAGS','no');
	setConfigVar ('DEFAULT_OBJECT_TYPE','4');
	setConfigVar ('IPV4_AUTO_RELEASE','1');
	setConfigVar ('SHOW_LAST_TAB', 'no');
	setConfigVar ('EXT_IPV4_VIEW', 'yes');
	setConfigVar ('TREE_THRESHOLD', '25');
	setConfigVar ('IPV4_JAYWALK', 'no');
	setConfigVar ('ADDNEW_AT_TOP', 'yes');
	setConfigVar ('IPV4_TREE_SHOW_USAGE', 'yes');
	setConfigVar ('PREVIEW_TEXT_MAXCHARS', '10240');
	setConfigVar ('PREVIEW_TEXT_ROWS', '25');
	setConfigVar ('PREVIEW_TEXT_COLS', '80');
	setConfigVar ('PREVIEW_IMAGE_MAXPXS', '320');
	setConfigVar ('VENDOR_SIEVE', '');
	setConfigVar ('IPV4LB_LISTSRC', '{$typeid_4}');
	setConfigVar ('IPV4OBJ_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8} or {$typeid_12} or {$typeid_445} or {$typeid_447} or {$typeid_798} or {$typeid_1504}');
	setConfigVar ('IPV4NAT_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8} or {$typeid_798}');
	setConfigVar ('ASSETWARN_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8}');
	setConfigVar ('NAMEWARN_LISTSRC','{$typeid_4} or {$typeid_7} or {$typeid_8}');
	setConfigVar ('RACKS_PER_ROW','12');
	setConfigVar ('FILTER_PREDICATE_SIEVE','');
	setConfigVar ('FILTER_DEFAULT_ANDOR','or');
	setConfigVar ('FILTER_SUGGEST_ANDOR','yes');
	setConfigVar ('FILTER_SUGGEST_TAGS','yes');
	setConfigVar ('FILTER_SUGGEST_PREDICATES','yes');
	setConfigVar ('FILTER_SUGGEST_EXTRA','no');
	setConfigVar ('DEFAULT_SNMP_COMMUNITY','public');
	setConfigVar ('IPV4_ENABLE_KNIGHT','yes');
	setConfigVar ('TAGS_TOPLIST_SIZE','50');
	setConfigVar ('TAGS_QUICKLIST_SIZE','20');
	setConfigVar ('TAGS_QUICKLIST_THRESHOLD','50');
	setConfigVar ('ENABLE_MULTIPORT_FORM', 'no');
	setConfigVar ('DEFAULT_PORT_IIF_ID', '1');
	setConfigVar ('DEFAULT_PORT_OIF_IDS', '1=24; 3=1078; 4=1077; 5=1079; 6=1080; 8=1082; 9=1084');
	setConfigVar ('IPV4_TREE_RTR_AS_CELL', 'yes');
	setConfigVar ('PROXIMITY_RANGE', 0);
	setConfigVar ('IPV4_TREE_SHOW_VLAN', 'yes');
	setConfigVar ('VLANSWITCH_LISTSRC', '');
	setConfigVar ('VLANIPV4NET_LISTSRC', '');
	setConfigVar ('DEFAULT_VDOM_ID', '');
	setConfigVar ('DEFAULT_VST_ID', '');
	setConfigVar ('STATIC_FILTER', 'yes');
	setConfigVar ('8021Q_DEPLOY_MINAGE', '300');
	setConfigVar ('8021Q_DEPLOY_MAXAGE', '3600');
	setConfigVar ('8021Q_DEPLOY_RETRY', '10800');
	setConfigVar ('8021Q_WRI_AFTER_CONFT_LISTSRC', 'false');
	setConfigVar ('8021Q_INSTANT_DEPLOY', 'no');
	setConfigVar ('CDP_RUNNERS_LISTSRC', '');
	setConfigVar ('LLDP_RUNNERS_LISTSRC', '');
	setConfigVar ('SHRINK_TAG_TREE_ON_CLICK', 'yes');
	setConfigVar ('MAX_UNFILTERED_ENTITIES', '0');
	setConfigVar ('SYNCDOMAIN_MAX_PROCESSES', '0');
	setConfigVar ('PORT_EXCLUSION_LISTSRC', '{$typeid_3} or {$typeid_10} or {$typeid_11} or {$typeid_1505} or {$typeid_1506}');
	setConfigVar ('FILTER_RACKLIST_BY_TAGS', 'yes');
	setConfigVar ('SSH_OBJS_LISTSRC', 'none');
	setConfigVar ('TELNET_OBJS_LISTSRC', 'none');
	setConfigVar ('SYNC_802Q_LISTSRC', '');
	setConfigVar ('QUICK_LINK_PAGES', '');
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addRealServer']['OK'] = 48;
// Add single record.
function addRealServer ()
{
	assertIPv4Arg ('remoteip');
	assertStringArg ('rsport', TRUE);
	assertStringArg ('rsconfig', TRUE);
	addRStoRSPool
	(
		getBypassValue(),
		$_REQUEST['remoteip'],
		$_REQUEST['rsport'],
		getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'),
		$_REQUEST['rsconfig']
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addRealServers']['OK'] = 37;
$msgcode['addRealServers']['ERR1'] = 131;
// Parse textarea submitted and try adding a real server for each line.
function addRealServers ()
{
	assertStringArg ('format');
	assertStringArg ('rawtext');
	$ngood = 0;
	$rsconfig = '';
	// Keep in mind, that the text will have HTML entities (namely '>') escaped.
	foreach (explode ("\n", dos2unix ($_REQUEST['rawtext'])) as $line)
	{
		if (!strlen ($line))
			continue;
		$match = array ();
		switch ($_REQUEST['format'])
		{
			case 'ipvs_2': // address and port only
				if (!preg_match ('/^  -&gt; ([0-9\.]+):([0-9]+) /', $line, $match))
					continue;
				addRStoRSPool (getBypassValue(), $match[1], $match[2], getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), '');
				break;
			case 'ipvs_3': // address, port and weight
				if (!preg_match ('/^  -&gt; ([0-9\.]+):([0-9]+) +[a-zA-Z]+ +([0-9]+) /', $line, $match))
					continue;
				addRStoRSPool (getBypassValue(), $match[1], $match[2], getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), 'weight ' . $match[3]);
				break;
			case 'ssv_2': // IP address and port
				if (!preg_match ('/^([0-9\.]+) ([0-9]+)$/', $line, $match))
					continue;
				addRStoRSPool (getBypassValue(), $match[1], $match[2], getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), '');
				break;
			case 'ssv_1': // IP address
				if (!preg_match ('/^([0-9\.]+)$/', $line, $match))
					continue;
				addRStoRSPool (getBypassValue(), $match[1], 0, getConfigVar ('DEFAULT_IPV4_RS_INSERVICE'), '');
				break;
			default:
				return showFuncMessage (__FUNCTION__, 'ERR1');
		}
		$ngood++;
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

$msgcode['addVService']['OK'] = 48;
function addVService ()
{
	assertIPv4Arg ('vip');
	assertUIntArg ('vport');
	genericAssertion ('proto', 'enum/ipproto');
	assertStringArg ('name', TRUE);
	assertStringArg ('vsconfig', TRUE);
	assertStringArg ('rsconfig', TRUE);
	usePreparedExecuteBlade
	(
		'INSERT INTO IPv4VS (vip, vport, proto, name, vsconfig, rsconfig) VALUES (INET_ATON(?), ?, ?, ?, ?, ?)',
		array
		(
			$_REQUEST['vip'],
			$_REQUEST['vport'],
			$_REQUEST['proto'],
			!mb_strlen ($_REQUEST['name']) ? NULL : $_REQUEST['name'],
			!strlen ($_REQUEST['vsconfig']) ? NULL : $_REQUEST['vsconfig'],
			!strlen ($_REQUEST['rsconfig']) ? NULL : $_REQUEST['rsconfig'],
		)
	);
	produceTagsForLastRecord ('ipv4vs', isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array());
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['deleteVService']['OK'] = 49;
function deleteVService ()
{
	assertUIntArg ('vs_id');
	commitDeleteVS ($_REQUEST['vs_id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updateSLBDefConfig']['OK'] = 43;
function updateSLBDefConfig ()
{
	commitUpdateSLBDefConf
	(
		array
		(
			'vs' => $_REQUEST['vsconfig'],
			'rs' => $_REQUEST['rsconfig'],
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updateRealServer']['OK'] = 51;
function updateRealServer ()
{
	assertUIntArg ('rs_id');
	assertIPv4Arg ('rsip');
	assertStringArg ('rsport', TRUE);
	assertStringArg ('rsconfig', TRUE);
	commitUpdateRS (
		$_REQUEST['rs_id'],
		$_REQUEST['rsip'],
		$_REQUEST['rsport'],
		$_REQUEST['rsconfig']
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updateVService']['OK'] = 51;
function updateVService ()
{
	assertUIntArg ('vs_id');
	assertIPv4Arg ('vip');
	assertUIntArg ('vport');
	genericAssertion ('proto', 'enum/ipproto');
	assertStringArg ('name', TRUE);
	assertStringArg ('vsconfig', TRUE);
	assertStringArg ('rsconfig', TRUE);
	commitUpdateVS (
		$_REQUEST['vs_id'],
		$_REQUEST['vip'],
		$_REQUEST['vport'],
		$_REQUEST['proto'],
		$_REQUEST['name'],
		$_REQUEST['vsconfig'],
		$_REQUEST['rsconfig']
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addLoadBalancer']['OK'] = 48;
function addLoadBalancer ()
{
	assertUIntArg ('pool_id');
	assertUIntArg ('object_id');
	assertUIntArg ('vs_id');
	assertStringArg ('vsconfig', TRUE);
	assertStringArg ('rsconfig', TRUE);
	assertStringArg ('prio', TRUE);

	addLBtoRSPool (
		$_REQUEST['pool_id'],
		$_REQUEST['object_id'],
		$_REQUEST['vs_id'],
		$_REQUEST['vsconfig'],
		$_REQUEST['rsconfig'],
		$_REQUEST['prio']
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addRSPool']['OK'] = 48;
function addRSPool ()
{
	assertStringArg ('name');
	assertStringArg ('vsconfig', TRUE);
	assertStringArg ('rsconfig', TRUE);
	commitCreateRSPool
	(
		$_REQUEST['name'],
		$_REQUEST['vsconfig'],
		$_REQUEST['rsconfig'],
		isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array()
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['deleteRSPool']['OK'] = 49;
function deleteRSPool ()
{
	assertUIntArg ('pool_id');
	commitDeleteRSPool ($_REQUEST['pool_id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updateRSInService']['OK'] = 26;
function updateRSInService ()
{
	assertUIntArg ('rscount');
	$orig = spotEntity ('ipv4rspool', getBypassValue());
	amplifyCell ($orig);
	$ngood = 0;
	for ($i = 1; $i <= $_REQUEST['rscount']; $i++)
	{
		$rs_id = $_REQUEST["rsid_${i}"];
		if (isset ($_REQUEST["inservice_${i}"]) and $_REQUEST["inservice_${i}"] == 'on')
			$newval = 'yes';
		else
			$newval = 'no';
		if ($newval != $orig['rslist'][$rs_id]['inservice'])
		{
			commitSetInService ($rs_id, $newval);
			$ngood++;
		}
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

$msgcode['importPTRData']['OK'] = 26;
$msgcode['importPTRData']['ERR'] = 141;
// FIXME: check, that each submitted address belongs to the prefix we
// are operating on.
function importPTRData ()
{
	assertUIntArg ('addrcount');
	$nbad = $ngood = 0;
	for ($i = 0; $i < $_REQUEST['addrcount']; $i++)
	{
		$inputname = "import_${i}";
		if (!isset ($_REQUEST[$inputname]) or $_REQUEST[$inputname] != 'on')
			continue;
		assertIPv4Arg ("addr_${i}");
		assertStringArg ("descr_${i}", TRUE);
		assertStringArg ("rsvd_${i}");
		// Non-existent addresses will not have this argument set in request.
		$rsvd = 'no';
		if ($_REQUEST["rsvd_${i}"] == 'yes')
			$rsvd = 'yes';
		if (updateAddress ($_REQUEST["addr_${i}"], $_REQUEST["descr_${i}"], $rsvd) == '')
			$ngood++;
		else
			$nbad++;
	}
	if (!$nbad)
		return showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
	else
		return showFuncMessage (__FUNCTION__, 'ERR', array ($nbad, $ngood));
}

$msgcode['generateAutoPorts']['OK'] = 21;
function generateAutoPorts ()
{
	$object = spotEntity ('object', getBypassValue());
	executeAutoPorts ($object['id'], $object['objtype_id']);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL (NULL, 'ports');
}

$msgcode['saveEntityTags']['OK'] = 43;
function saveEntityTags ()
{
	global $pageno, $etype_by_pageno;
	if (!isset ($etype_by_pageno[$pageno]))
		throw new RackTablesError ('key not found in etype_by_pageno', RackTablesError::INTERNAL);
	$realm = $etype_by_pageno[$pageno];
	$entity_id = getBypassValue();
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	rebuildTagChainForEntity ($realm, $entity_id, buildTagChainFromIds ($taglist), TRUE);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['rollTags']['OK'] = 67;
$msgcode['rollTags']['ERR'] = 149;
function rollTags ()
{
	assertStringArg ('sum', TRUE);
	assertUIntArg ('realsum');
	if ($_REQUEST['sum'] != $_REQUEST['realsum'])
		return showFuncMessage (__FUNCTION__, 'ERR');
	// Even if the user requested an empty tag list, don't bail out, but process existing
	// tag chains with "zero" extra. This will make sure, that the stuff processed will
	// have its chains refined to "normal" form.
	$extratags = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	$n_ok = 0;
	// Minimizing the extra chain early, so that tag rebuilder doesn't have to
	// filter out the same tag again and again. It will have own noise to cancel.
	$extrachain = getExplicitTagsOnly (buildTagChainFromIds ($extratags));
	foreach (listCells ('rack', getBypassValue()) as $rack)
	{
		if (rebuildTagChainForEntity ('rack', $rack['id'], $extrachain))
			$n_ok++;
		amplifyCell ($rack);
		foreach ($rack['mountedObjects'] as $object_id)
			if (rebuildTagChainForEntity ('object', $object_id, $extrachain))
				$n_ok++;
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($n_ok));
}

$msgcode['changeMyPassword']['OK'] = 51;
$msgcode['changeMyPassword']['ERR1'] = 150;
$msgcode['changeMyPassword']['ERR2'] = 151;
$msgcode['changeMyPassword']['ERR3'] = 152;
function changeMyPassword ()
{
	global $remote_username, $user_auth_src;
	if ($user_auth_src != 'database')
		return showFuncMessage (__FUNCTION__, 'ERR1');
	assertStringArg ('oldpassword');
	assertStringArg ('newpassword1');
	assertStringArg ('newpassword2');
	$remote_userid = getUserIDByUsername ($remote_username);
	$userinfo = spotEntity ('user', $remote_userid);
	if ($userinfo['user_password_hash'] != sha1 ($_REQUEST['oldpassword']))
		return showFuncMessage (__FUNCTION__, 'ERR2');
	if ($_REQUEST['newpassword1'] != $_REQUEST['newpassword2'])
		return showFuncMessage (__FUNCTION__, 'ERR3');
	commitUpdateUserAccount ($remote_userid, $userinfo['user_name'], $userinfo['user_realname'], sha1 ($_REQUEST['newpassword1']));
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['saveRackCode']['OK'] = 43;
$msgcode['saveRackCode']['ERR1'] = 154;
function saveRackCode ()
{
	assertStringArg ('rackcode');
	// For the test to succeed, unescape LFs, strip CRs.
	$newcode = dos2unix ($_REQUEST['rackcode']);
	$parseTree = getRackCode ($newcode);
	if ($parseTree['result'] != 'ACK')
		return showFuncMessage (__FUNCTION__, 'ERR1', array ($parseTree['load']));
	saveScript ('RackCode', $newcode);
	saveScript ('RackCodeCache', base64_encode (serialize ($parseTree)));
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['setPortVLAN']['ERR'] = 164;
// This handler's context is pre-built, but not authorized. It is assumed, that the
// handler will take existing context and before each commit check authorization
// on the base chain plus necessary context added.
function setPortVLAN ()
{
	assertUIntArg ('portcount');
	try
	{
		$data = getSwitchVLANs ($_REQUEST['object_id']);
	}
	catch (RTGatewayError $re)
	{
		return showFuncMessage (__FUNCTION__, 'ERR', array ($re->getMessage()));
	}
	list ($vlanlist, $portlist) = $data;
	// Here we just build up 1 set command for the gateway with all of the ports
	// included. The gateway is expected to filter unnecessary changes silently
	// and to provide a list of responses with either error or success message
	// for each of the rest.
	$nports = $_REQUEST['portcount'];
	$prefix = 'set ';
	$setcmd = '';
	for ($i = 0; $i < $nports; $i++)
	{
		genericAssertion ('portname_' . $i, 'string');
		genericAssertion ('vlanid_' . $i, 'string');
		if ($_REQUEST['portname_' . $i] != $portlist[$i]['portname'])
			throw new InvalidRequestArgException ('portname_' . $i, $_REQUEST['portname_' . $i], 'expected to be ' . $portlist[$i]['portname']);
		if
		(
			$_REQUEST['vlanid_' . $i] == $portlist[$i]['vlanid'] ||
			$portlist[$i]['vlanid'] == 'TRUNK'
		)
			continue;
		$portname = $_REQUEST['portname_' . $i];
		$oldvlanid = $portlist[$i]['vlanid'];
		$newvlanid = $_REQUEST['vlanid_' . $i];
		if
		(
			!permitted (NULL, NULL, NULL, array (array ('tag' => '$fromvlan_' . $oldvlanid), array ('tag' => '$vlan_' . $oldvlanid))) or
			!permitted (NULL, NULL, NULL, array (array ('tag' => '$tovlan_' . $newvlanid), array ('tag' => '$vlan_' . $newvlanid)))
		)
		{
			showOneLiner (159, array ($portname, $oldvlanid, $newvlanid));
			continue;
		}
		$setcmd .= $prefix . $portname . '=' . $newvlanid;
		$prefix = ';';
	}
	// Feed the gateway and interpret its (non)response.
	if ($setcmd == '')
		showOneLiner (201);
	else
	{
		try
		{
			setSwitchVLANs ($_REQUEST['object_id'], $setcmd); // shows messages by itself
		}
		catch (RTGatewayError $e)
		{
			showFuncMessage (__FUNCTION__, 'ERR', array ($e->getMessage()));
		}
	}
}

function submitSLBConfig ()
{
	showNotice ("You should redefine submitSLBConfig ophandler in your local extension to install SLB config");
}

$msgcode['addRack']['OK'] = 48;
$msgcode['addRack']['ERR2'] = 172;
function addRack ()
{
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	if (isset ($_REQUEST['got_data']))
	{
		assertStringArg ('name');
		assertUIntArg ('height1');
		assertStringArg ('asset_no', TRUE);
		$rack_id = commitAddObject (NULL, $_REQUEST['name'], 1560, $_REQUEST['asset_no'], $taglist);

		// Update the height
		commitUpdateAttrValue ($rack_id, 27, $_REQUEST['height1']);

		// Link it to the row
		commitLinkEntities ('object', $_REQUEST['row_id'], 'object', $rack_id);

		return showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
	}
	elseif (isset ($_REQUEST['got_mdata']))
	{
		assertUIntArg ('height2');
		assertStringArg ('names', TRUE);
		// copy-and-paste from renderAddMultipleObjectsForm()
		$names1 = explode ("\n", $_REQUEST['names']);
		$names2 = array();
		foreach ($names1 as $line)
		{
			$parts = explode ('\r', $line);
			reset ($parts);
			if (!strlen ($parts[0]))
				continue;
			else
				$names2[] = rtrim ($parts[0]);
		}
		foreach ($names2 as $cname)
		{
			$rack_id = commitAddObject (NULL, $cname, 1560, NULL, $taglist);

			// Update the height
			commitUpdateAttrValue ($rack_id, 27, $_REQUEST['height2']);

			// Link it to the row
			commitLinkEntities ('object', $_REQUEST['row_id'], 'object', $rack_id);
		}
	}
	else
		return showFuncMessage (__FUNCTION__, 'ERR2');
}

$msgcode['deleteRack']['OK'] = 6;
$msgcode['deleteRack']['ERR1'] = 206;
function deleteRack ()
{
	assertUIntArg ('rack_id');
	$rackData = spotEntity ('rack', $_REQUEST['rack_id']);
	amplifyCell ($rackData);
	if (count ($rackData['mountedObjects']))
		return showFuncMessage (__FUNCTION__, 'ERR1');
	commitDeleteObject ($_REQUEST['rack_id']);
	showFuncMessage (__FUNCTION__, 'OK', array ($rackData['name']));
	return buildRedirectURL ('rackspace', 'default');
}

$msgcode['updateRack']['OK'] = 6;
function updateRack ()
{
	assertUIntArg ('row_id');
	assertStringArg ('name');
	assertUIntArg ('height');
	$has_problems = (isset ($_REQUEST['has_problems']) and $_REQUEST['has_problems'] == 'on') ? 'yes' : 'no';
	assertStringArg ('asset_no', TRUE);
	assertStringArg ('comment', TRUE);

	$rack_id = getBypassValue();
	usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $rack_id));
	commitUpdateRack ($rack_id, $_REQUEST['row_id'], $_REQUEST['name'], $_REQUEST['height'], $has_problems, $_REQUEST['asset_no'], $_REQUEST['comment']);

	// Update optional attributes
	$oldvalues = getAttrValues ($rack_id);
	$num_attrs = isset ($_REQUEST['num_attrs']) ? $_REQUEST['num_attrs'] : 0;
	for ($i = 0; $i < $num_attrs; $i++)
	{
		assertUIntArg ("${i}_attr_id");
		$attr_id = $_REQUEST["${i}_attr_id"];

		// Skip the 'height' attribute as it's already handled by commitUpdateRack
		if ($attr_id == 27)
			continue;

		// Field is empty, delete attribute and move on. OR if the field type is a dictionary and it is the --NOT SET-- value of 0
		if (!strlen ($_REQUEST["${i}_value"]) || ($oldvalues[$attr_id]['type']=='dict' && $_REQUEST["${i}_value"] == 0))
		{
			commitUpdateAttrValue ($rack_id, $attr_id);
			continue;
		}

		// The value could be uint/float, but we don't know ATM. Let SQL
		// server check this and complain.
		assertStringArg ("${i}_value");
		$value = $_REQUEST["${i}_value"];
		switch ($oldvalues[$attr_id]['type'])
		{
			case 'uint':
			case 'float':
			case 'string':
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'dict':
				$oldvalue = $oldvalues[$attr_id]['key'];
				break;
			default:
		}
		if ($value === $oldvalue) // ('' == 0), but ('' !== 0)
			continue;
		commitUpdateAttrValue ($rack_id, $attr_id, $value);
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($_REQUEST['name']));
}

function updateRackDesign ()
{
	$rackData = spotEntity ('rack', getBypassValue());
	amplifyCell ($rackData);
	applyRackDesignMask($rackData);
	markupObjectProblems ($rackData);
	if (processGridForm ($rackData, 'A', 'F'))
		showSuccess ("Saved successfully");
	else
		showNotice ("Nothing saved");
}

function updateRackProblems ()
{
	$rackData = spotEntity ('rack', getBypassValue());
	amplifyCell ($rackData);
	applyRackProblemMask($rackData);
	markupObjectProblems ($rackData);
	if (processGridForm ($rackData, 'F', 'U'))
		showSuccess ("Saved successfully");
	else
		showNotice ("Nothing saved");
}

function querySNMPData ()
{
	genericAssertion ('ver', 'uint');
	$snmpsetup = array ();
	switch ($_REQUEST['ver'])
	{
	case 1:
		genericAssertion ('community', 'string');
		$snmpsetup['community'] = $_REQUEST['community'];
		break;
	case 23:
		assertStringArg ('sec_name');
		assertStringArg ('sec_level');
		assertStringArg ('auth_protocol');
		assertStringArg ('auth_passphrase', TRUE);
		assertStringArg ('priv_protocol');
		assertStringArg ('priv_passphrase', TRUE);

		$snmpsetup['sec_name'] = $_REQUEST['sec_name'];
		$snmpsetup['sec_level'] = $_REQUEST['sec_level'];
		$snmpsetup['auth_protocol'] = $_REQUEST['auth_protocol'];
		$snmpsetup['auth_passphrase'] = $_REQUEST['auth_passphrase'];
		$snmpsetup['priv_protocol'] = $_REQUEST['priv_protocol'];
		$snmpsetup['priv_passphrase'] = $_REQUEST['priv_passphrase'];
		break;
	default:
		throw new InvalidRequestArgException ('ver', $_REQUEST['ver']);
	}
	doSNMPmining (getBypassValue(), $snmpsetup); // shows message by itself
}

$msgcode['linkEntities']['OK'] = 51;
function linkEntities ()
{
	assertStringArg ('parent_entity_type');
	assertUIntArg ('parent_entity_id');
	assertStringArg ('child_entity_type');
	assertUIntArg ('child_entity_id');
	usePreparedInsertBlade
	(
		'EntityLink',
		array
		(
			'parent_entity_type' => $_REQUEST['parent_entity_type'],
			'parent_entity_id' => $_REQUEST['parent_entity_id'],
			'child_entity_type' => $_REQUEST['child_entity_type'],
			'child_entity_id' => $_REQUEST['child_entity_id'],
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['unlinkEntities']['OK'] = 49;
function unlinkEntities ()
{
	assertUIntArg ('link_id');
	commitUnlinkEntitiesByLinkID ($_REQUEST['link_id']);
	return showFuncMessage (__FUNCTION__,  'OK');
}

$msgcode['addFileWithoutLink']['OK'] = 5;
// File-related functions
function addFileWithoutLink ()
{
	assertStringArg ('comment', TRUE);

	// Make sure the file can be uploaded
	if (get_cfg_var('file_uploads') != 1)
		throw new RackTablesError ('file uploads not allowed, change "file_uploads" parameter in php.ini', RackTablesError::MISCONFIGURED);

	$fp = fopen($_FILES['file']['tmp_name'], 'rb');
	global $sic;
	commitAddFile ($_FILES['file']['name'], $_FILES['file']['type'], $fp, $sic['comment']);
	if (isset ($_REQUEST['taglist']))
		produceTagsForLastRecord ('file', $_REQUEST['taglist']);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($_FILES['file']['name'])));
}

$msgcode['addFileToEntity']['OK'] = 5;
function addFileToEntity ()
{
	global $pageno, $etype_by_pageno;
	if (!isset ($etype_by_pageno[$pageno]))
		throw new RackTablesError ('key not found in etype_by_pageno', RackTablesError::INTERNAL);
	$realm = $etype_by_pageno[$pageno];
	assertStringArg ('comment', TRUE);

	// Make sure the file can be uploaded
	if (get_cfg_var('file_uploads') != 1)
		throw new RackTablesError ('file uploads not allowed, change "file_uploads" parameter in php.ini', RackTablesError::MISCONFIGURED);

	$fp = fopen($_FILES['file']['tmp_name'], 'rb');
	global $sic;
	commitAddFile ($_FILES['file']['name'], $_FILES['file']['type'], $fp, $sic['comment']);
	usePreparedInsertBlade
	(
		'FileLink',
		array
		(
			'file_id' => lastInsertID(),
			'entity_type' => $realm,
			'entity_id' => getBypassValue(),
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($_FILES['file']['name'])));
}

$msgcode['linkFileToEntity']['OK'] = 71;
function linkFileToEntity ()
{
	assertUIntArg ('file_id');
	global $pageno, $etype_by_pageno, $sic;
	if (!isset ($etype_by_pageno[$pageno]))
		throw new RackTablesError ('key not found in etype_by_pageno', RackTablesError::INTERNAL);

	$fi = spotEntity ('file', $sic['file_id']);
	usePreparedInsertBlade
	(
		'FileLink',
		array
		(
			'file_id' => $sic['file_id'],
			'entity_type' => $etype_by_pageno[$pageno],
			'entity_id' => getBypassValue(),
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($fi['name'])));
}

$msgcode['replaceFile']['OK'] = 7;
$msgcode['replaceFile']['ERR2'] = 201;
function replaceFile ()
{
	// Make sure the file can be uploaded
	if (get_cfg_var('file_uploads') != 1)
		throw new RackTablesError ('file uploads not allowed, change "file_uploads" parameter in php.ini', RackTablesError::MISCONFIGURED);
	$shortInfo = spotEntity ('file', getBypassValue());

	if (FALSE === $fp = fopen ($_FILES['file']['tmp_name'], 'rb'))
		return showFuncMessage (__FUNCTION__, 'ERR2');
	commitReplaceFile ($shortInfo['id'], $fp);

	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($shortInfo['name'])));
}

$msgcode['unlinkFile']['OK'] = 72;
function unlinkFile ()
{
	assertUIntArg ('link_id');
	commitUnlinkFile ($_REQUEST['link_id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['deleteFile']['OK'] = 7;
function deleteFile ()
{
	assertUIntArg ('file_id');
	$shortInfo = spotEntity ('file', $_REQUEST['file_id']);
	commitDeleteFile ($_REQUEST['file_id']);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($shortInfo['name'])));
}

$msgcode['updateFileText']['OK'] = 6;
$msgcode['updateFileText']['ERR1'] = 179;
$msgcode['updateFileText']['ERR2'] = 155;
function updateFileText ()
{
	assertStringArg ('mtime_copy');
	assertStringArg ('file_text', TRUE); // it's Ok to save empty
	$shortInfo = spotEntity ('file', getBypassValue());
	if ($shortInfo['mtime'] != $_REQUEST['mtime_copy'])
		return showFuncMessage (__FUNCTION__, 'ERR1');
	global $sic;
	commitReplaceFile ($shortInfo['id'], $sic['file_text']);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($shortInfo['name'])));
}

$msgcode['addIIFOIFCompat']['OK'] = 48;
function addIIFOIFCompat ()
{
	assertUIntArg ('iif_id');
	assertUIntArg ('oif_id');
	commitSupplementPIC ($_REQUEST['iif_id'], $_REQUEST['oif_id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['addIIFOIFCompatPack']['OK'] = 37;
function addIIFOIFCompatPack ()
{
	genericAssertion ('standard', 'enum/wdmstd');
	genericAssertion ('iif_id', 'iif');
	global $wdm_packs, $sic;
	$ngood = 0;
	foreach ($wdm_packs[$sic['standard']]['oif_ids'] as $oif_id)
	{
		commitSupplementPIC ($sic['iif_id'], $oif_id);
		$ngood++;
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

$msgcode['delIIFOIFCompatPack']['OK'] = 38;
function delIIFOIFCompatPack ()
{
	genericAssertion ('standard', 'enum/wdmstd');
	genericAssertion ('iif_id', 'iif');
	global $wdm_packs, $sic;
	$ngood = 0;
	foreach ($wdm_packs[$sic['standard']]['oif_ids'] as $oif_id)
	{
		usePreparedDeleteBlade ('PortInterfaceCompat', array ('iif_id' => $sic['iif_id'], 'oif_id' => $oif_id));
		$ngood++;
	}
	return showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
}

$msgcode['addOIFCompatPack']['OK'] = 21;
function addOIFCompatPack ()
{
	genericAssertion ('standard', 'enum/wdmstd');
	global $wdm_packs;
	$oifs = $wdm_packs[$_REQUEST['standard']]['oif_ids'];
	foreach ($oifs as $oif_id_1)
	{
		$args = $qmarks = array();
		$query = 'REPLACE INTO PortCompat (type1, type2) VALUES ';
		foreach ($oifs as $oif_id_2)
		{
			$qmarks[] = '(?, ?)';
			$args[] = $oif_id_1;
			$args[] = $oif_id_2;
		}
		$query .= implode (', ', $qmarks);
		usePreparedExecuteBlade ($query, $args);
	}
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delOIFCompatPack']['OK'] = 21;
function delOIFCompatPack ()
{
	genericAssertion ('standard', 'enum/wdmstd');
	global $wdm_packs;
	$oifs = $wdm_packs[$_REQUEST['standard']]['oif_ids'];
	foreach ($oifs as $oif_id_1)
		foreach ($oifs as $oif_id_2)
			if ($oif_id_1 != $oif_id_2) # leave narrow-band mapping intact
				usePreparedDeleteBlade ('PortCompat', array ('type1' => $oif_id_1, 'type2' => $oif_id_2));
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['add8021QOrder']['OK'] = 48;
function add8021QOrder ()
{
	assertUIntArg ('vdom_id');
	assertUIntArg ('object_id');
	assertUIntArg ('vst_id');
	global $sic, $pageno;
	fixContext();
	if ($pageno != 'object')
		spreadContext (spotEntity ('object', $sic['object_id']));
	if ($pageno != 'vst')
		spreadContext (spotEntity ('vst', $sic['vst_id']));
	assertPermission();
	usePreparedExecuteBlade
	(
		'INSERT INTO VLANSwitch (domain_id, object_id, template_id, last_change, out_of_sync) ' .
		'VALUES (?, ?, ?, NOW(), "yes")',
		array ($sic['vdom_id'], $sic['object_id'], $sic['vst_id'])
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['del8021QOrder']['OK'] = 49;
function del8021QOrder ()
{
	assertUIntArg ('object_id');
	assertUIntArg ('vdom_id');
	assertUIntArg ('vst_id');
	global $sic, $pageno;
	fixContext();
	if ($pageno != 'object')
		spreadContext (spotEntity ('object', $sic['object_id']));
	if ($pageno != 'vst')
		spreadContext (spotEntity ('vst', $sic['vst_id']));
	assertPermission();
	usePreparedDeleteBlade ('VLANSwitch', array ('object_id' => $sic['object_id']));
	$focus_hints = array
	(
		'prev_objid' => $_REQUEST['object_id'],
		'prev_vstid' => $_REQUEST['vst_id'],
		'prev_vdid' => $_REQUEST['vdom_id'],
	);
	showFuncMessage (__FUNCTION__, 'OK');
	return buildRedirectURL (NULL, NULL, $focus_hints);
}

$msgcode['createVLANDomain']['OK'] = 48;
function createVLANDomain ()
{
	assertStringArg ('vdom_descr');
	global $sic;
	usePreparedInsertBlade
	(
		'VLANDomain',
		array
		(
			'description' => $sic['vdom_descr'],
		)
	);
	usePreparedInsertBlade
	(
		'VLANDescription',
		array
		(
			'domain_id' => lastInsertID(),
			'vlan_id' => VLAN_DFL_ID,
			'vlan_type' => 'compulsory',
			'vlan_descr' => 'default',
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['destroyVLANDomain']['OK'] = 49;
function destroyVLANDomain ()
{
	assertUIntArg ('vdom_id');
	global $sic;
	usePreparedDeleteBlade ('VLANDomain', array ('id' => $sic['vdom_id']));
	return showFuncMessage (__FUNCTION__, 'OK');
}

function save8021QPorts ()
{
	global $sic;
	assertUIntArg ('mutex_rev', TRUE); // counts from 0
	assertStringArg ('form_mode');
	if ($sic['form_mode'] != 'save' and $sic['form_mode'] != 'duplicate')
		throw new InvalidRequestArgException ('form_mode', $sic['form_mode']);
	$extra = array();

	// prepare the $changes array
	$changes = array();
	switch ($sic['form_mode'])
	{
	case 'save':
		assertUIntArg ('nports');
		if ($sic['nports'] == 1)
		{
			assertStringArg ('pn_0');
			$extra = array ('port_name' => $sic['pn_0']);
		}
		for ($i = 0; $i < $sic['nports']; $i++)
		{
			assertStringArg ('pn_' . $i);
			assertStringArg ('pm_' . $i);
			// An access port only generates form input for its native VLAN,
			// which we derive allowed VLAN list from.
			$native = isset ($sic['pnv_' . $i]) ? $sic['pnv_' . $i] : 0;
			switch ($sic["pm_${i}"])
			{
			case 'trunk':
#				assertArrayArg ('pav_' . $i);
				$allowed = isset ($sic['pav_' . $i]) ? $sic['pav_' . $i] : array();
				break;
			case 'access':
				if ($native == 'same')
					continue 2;
				assertUIntArg ('pnv_' . $i);
				$allowed = array ($native);
				break;
			default:
				throw new InvalidRequestArgException ("pm_${i}", $_REQUEST["pm_${i}"], 'unknown port mode');
			}
			$changes[$sic['pn_' . $i]] = array
			(
				'mode' => $sic['pm_' . $i],
				'allowed' => $allowed,
				'native' => $native,
			);
		}
		break;
	case 'duplicate':
		assertStringArg ('from_port');
#			assertArrayArg ('to_ports');
		$before = getStored8021QConfig ($sic['object_id'], 'desired');
		if (!array_key_exists ($sic['from_port'], $before))
			throw new InvalidArgException ('from_port', $sic['from_port'], 'this port does not exist');
		foreach ($sic['to_ports'] as $tpn)
			if (!array_key_exists ($tpn, $before))
				throw new InvalidArgException ('to_ports[]', $tpn, 'this port does not exist');
			elseif ($tpn != $sic['from_port'])
				$changes[$tpn] = $before[$sic['from_port']];
		break;
	}
	apply8021qChangeRequest ($sic['object_id'], $changes, TRUE, $sic['mutex_rev']);
	return buildRedirectURL (NULL, NULL, $extra);
}

$msgcode['bindVLANtoIPv4']['OK'] = 48;
function bindVLANtoIPv4 ()
{
	assertUIntArg ('id'); // network id
	global $sic;
	commitSupplementVLANIPv4 ($sic['vlan_ck'], $sic['id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['bindVLANtoIPv6']['OK'] = 48;
function bindVLANtoIPv6 ()
{
	assertUIntArg ('id'); // network id
	global $sic;
	commitSupplementVLANIPv6 ($sic['vlan_ck'], $_REQUEST['id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['unbindVLANfromIPv4']['OK'] = 49;
function unbindVLANfromIPv4 ()
{
	assertUIntArg ('id'); // network id
	global $sic;
	commitReduceVLANIPv4 ($sic['vlan_ck'], $sic['id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['unbindVLANfromIPv6']['OK'] = 49;
function unbindVLANfromIPv6 ()
{
	assertUIntArg ('id'); // network id
	global $sic;
	commitReduceVLANIPv6 ($sic['vlan_ck'], $sic['id']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['process8021QSyncRequest']['OK'] = 63;
$msgcode['process8021QSyncRequest']['ERR'] = 191;
function process8021QSyncRequest ()
{
	// behave depending on current operation: exec8021QPull or exec8021QPush
	global $sic, $op;
	if (FALSE === $done = exec8021QDeploy ($sic['object_id'], $op == 'exec8021QPush'))
		return showFuncMessage (__FUNCTION__, 'ERR');
	return showFuncMessage (__FUNCTION__, 'OK', array ($done));
}

$msgcode['process8021QRecalcRequest']['CHANGED'] = 87;
function process8021QRecalcRequest ()
{
	assertPermission (NULL, NULL, NULL, array (array ('tag' => '$op_recalc8021Q')));
	$counters = recalc8021QPorts (getBypassValue());
	if ($counters['ports'])
		return showFuncMessage (__FUNCTION__, 'CHANGED', array ($counters['ports'], $counters['switches']));
	else
		return showNotice ('No changes were made');
}

$msgcode['resolve8021QConflicts']['OK'] = 63;
$msgcode['resolve8021QConflicts']['ERR1'] = 179;
$msgcode['resolve8021QConflicts']['ERR2'] = 109;
function resolve8021QConflicts ()
{
	global $sic, $dbxlink;
	assertUIntArg ('mutex_rev', TRUE); // counts from 0
	assertUIntArg ('nrows');
	// Divide submitted radio buttons into 3 groups:
	// left (saved version wins)
	// asis (ignore)
	// right (running version wins)
	$F = array();
	for ($i = 0; $i < $sic['nrows']; $i++)
	{
		if (!array_key_exists ("i_${i}", $sic))
			continue;
		// let's hope other inputs are in place
		switch ($sic["i_${i}"])
		{
		case 'left':
		case 'right':
			$F[$sic["pn_${i}"]] = array
			(
				'mode' => $sic["rm_${i}"],
				'allowed' => $sic["ra_${i}"],
				'native' => $sic["rn_${i}"],
				'decision' => $sic["i_${i}"],
			);
			break;
		default:
			// don't care
		}
	}
	$dbxlink->beginTransaction();
	try
	{
		if (NULL === $vswitch = getVLANSwitchInfo ($sic['object_id'], 'FOR UPDATE'))
			throw new InvalidArgException ('object_id', $sic['object_id'], 'VLAN domain is not set for this object');
		if ($vswitch['mutex_rev'] != $sic['mutex_rev'])
			throw new InvalidRequestArgException ('mutex_rev', $sic['mutex_rev'], 'expired form (table data has changed)');
		$D = getStored8021QConfig ($vswitch['object_id'], 'desired');
		$C = getStored8021QConfig ($vswitch['object_id'], 'cached');
		$R = getRunning8021QConfig ($vswitch['object_id']);
		$plan = get8021QSyncOptions ($vswitch, $D, $C, $R['portdata']);
		$ndone = 0;
		foreach ($F as $port_name => $port)
		{
			if (!array_key_exists ($port_name, $plan))
				continue;
			elseif ($plan[$port_name]['status'] == 'merge_conflict')
			{
				// for R neither mutex nor revisions can be emulated, but revision change can be
				if (!same8021QConfigs ($port, $R['portdata'][$port_name]))
					throw new InvalidRequestArgException ("port ${port_name}", '(hidden)', 'expired form (switch data has changed)');
				if ($port['decision'] == 'right') // D wins, frame R by writing value of R to C
				{
					upd8021QPort ('cached', $vswitch['object_id'], $port_name, $port);
					$ndone++;
				}
				elseif ($port['decision'] == 'left') // R wins, cross D up
				{
					upd8021QPort ('cached', $vswitch['object_id'], $port_name, $D[$port_name]);
					$ndone++;
				}
				// otherwise there was no decision made
			}
			elseif
			(
				$plan[$port_name]['status'] == 'delete_conflict' or
				$plan[$port_name]['status'] == 'martian_conflict'
			)
				if ($port['decision'] == 'left')
				{
					// confirm deletion of local copy
					del8021QPort ($vswitch['object_id'], $port_name);
					$ndone++;
				}
				// otherwise ignore a decision, which doesn't address a conflict
		}
	}
	catch (InvalidRequestArgException $e)
	{
		$dbxlink->rollBack();
		return showFuncMessage (__FUNCTION__, 'ERR1');
	}
	catch (Exception $e)
	{
		$dbxlink->rollBack();
		return showFuncMessage (__FUNCTION__, 'ERR2');
	}
	$dbxlink->commit();
	return showFuncMessage (__FUNCTION__, 'OK', array ($ndone));
}

$msgcode['addVLANSwitchTemplate']['OK'] = 48;
function addVLANSwitchTemplate()
{
	assertStringArg ('vst_descr');
	global $sic;
	usePreparedInsertBlade
	(
		'VLANSwitchTemplate',
		array
		(
			'description' => $sic['vst_descr'],
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['delVLANSwitchTemplate']['OK'] = 49;
function delVLANSwitchTemplate()
{
	assertUIntArg ('vst_id');
	global $sic;
	usePreparedDeleteBlade ('VLANSwitchTemplate', array ('id' => $sic['vst_id']));
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['cloneVST']['OK'] = 48;
function cloneVST()
{
	assertUIntArg ('mutex_rev', TRUE);
	assertUIntArg ('from_id');
	$src_vst = spotEntity ('vst', $_REQUEST['from_id']);
	amplifyCell ($src_vst);
	commitUpdateVSTRules (getBypassValue(), $_REQUEST['mutex_rev'], $src_vst['rules']);
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['updVSTRule']['OK'] = 43;
function updVSTRule()
{
	// this is used for making throwing an invalid argument exception easier.
	function updVSTRule_get_named_param ($name, $haystack, &$last_used_name)
	{
		$last_used_name = $name;
		return isset ($haystack[$name]) ? $haystack[$name] : NULL;
	}

	global $port_role_options, $sic;
	assertUIntArg ('mutex_rev', TRUE);
	genericAssertion ('template_json', 'json');
	$data = json_decode ($sic['template_json'], TRUE);
	$rule_no = 0;
	try
	{
		$last_field = '';
		foreach ($data as $rule)
		{
			$rule_no++;
			if
			(
				! isInteger (updVSTRule_get_named_param ('rule_no', $rule, $last_field))
				or ! isPCRE (updVSTRule_get_named_param ('port_pcre', $rule, $last_field))
				or NULL === updVSTRule_get_named_param ('port_role', $rule, $last_field)
				or ! array_key_exists (updVSTRule_get_named_param ('port_role', $rule, $last_field), $port_role_options)
				or NULL ===  updVSTRule_get_named_param ('wrt_vlans', $rule, $last_field)
				or ! preg_match ('/^[ 0-9\-,]*$/',  updVSTRule_get_named_param ('wrt_vlans', $rule, $last_field))
				or NULL ===  updVSTRule_get_named_param ('description', $rule, $last_field)
			)
				throw new InvalidRequestArgException ($last_field, $rule[$last_field], "rule #$rule_no");
		}
		commitUpdateVSTRules ($_REQUEST['vst_id'], $_REQUEST['mutex_rev'], $data);
	}
	catch (Exception $e)
	{
		// Every case, which is soft-processed in process.php, will have the working copy available for a retry.
		if ($e instanceof InvalidRequestArgException or $e instanceof RTDatabaseError)
			$_SESSION['vst_edited'] = $data;
		throw $e;
	}
	return showFuncMessage (__FUNCTION__, 'OK');
}

$msgcode['importDPData']['OK'] = 44;
function importDPData()
{
	global $sic, $dbxlink;
	assertUIntArg ('nports');
	$nignored = $ndone = 0;
	$POIFC = getPortOIFCompat();
	for ($i = 0; $i < $sic['nports']; $i++)
		if (array_key_exists ("do_${i}", $sic))
		{
			$params = array();
			assertStringArg ("ports_${i}");
			foreach (explode (',', $_REQUEST["ports_${i}"]) as $item)
			{
				$pair = explode (':', $item);
				if (count ($pair) != 2)
					continue;
				$params[$pair[0]] = $pair[1];
			}
			if (! isset ($params['a_id']) || ! isset ($params['b_id']) ||
				! intval ($params['a_id']) || ! intval ($params['b_id']))
				throw new InvalidArgException ("ports_${i}", $_REQUEST["ports_${i}"], "can not unpack port ids");
			
			$porta = getPortInfo ($params['a_id']);
			$portb = getPortInfo ($params['b_id']);
			if
			(
				$porta['linked'] or
				$portb['linked'] or
				($porta['object_id'] != $sic['object_id'] and $portb['object_id'] != $sic['object_id'])
			)
			{
				$nignored++;
				continue;
			}
			$oif_a = intval (@$params['a_oif']); // these parameters are optional
			$oif_b = intval (@$params['b_oif']);
			
			$dbxlink->beginTransaction();
			if ($oif_a || $oif_b)
				try
				{
					if ($oif_a)
					{
						commitUpdatePortOIF ($params['a_id'], $oif_a);
						$porta['oif_id'] = $oif_a;
					}
					if ($oif_b)
					{
						commitUpdatePortOIF ($params['b_id'], $oif_b);
						$portb['oif_id'] = $oif_b;
					}
				}
				catch (RTDatabaseError $e)
				{
					$dbxlink->rollBack();
					$nignored++;
					continue;
				}

			foreach ($POIFC as $item)
				if ($item['type1'] == $porta['oif_id'] and $item['type2'] == $portb['oif_id'])
				{
					linkPorts ($params['a_id'], $params['b_id']);
					$ndone++;
					$dbxlink->commit();
					continue 2; //next port
				}
			$dbxlink->rollback();
		}
	return showFuncMessage (__FUNCTION__, 'OK', array ($nignored, $ndone));
}

function addObjectlog ()
{
	assertStringArg ('logentry');
	global $remote_username, $sic;
	$object_id = isset($sic['object_id']) ? $sic['object_id'] : $sic['rack_id'];
	usePreparedExecuteBlade ('INSERT INTO ObjectLog SET object_id=?, user=?, date=NOW(), content=?', array ($object_id, $remote_username, $sic['logentry']));
	showSuccess ('Log entry added');
}

function saveQuickLinks()
{
	genericAssertion ('page_list', 'array');
	if (is_array ($_REQUEST['page_list']))
	{
		setUserConfigVar ('QUICK_LINK_PAGES', implode(',', $_REQUEST['page_list']));	
		showSuccess ('Quick links list is saved');
	}
}

function getOpspec()
{
	global $pageno, $tabno, $op, $opspec_list;
	if (!array_key_exists ($pageno . '-' . $tabno . '-' . $op, $opspec_list))
		throw new RackTablesError ('key not found in opspec_list', RackTablesError::INTERNAL);
	$ret = $opspec_list[$pageno . '-' . $tabno . '-' . $op];
	if
	(
		!array_key_exists ('table', $ret)
		or !array_key_exists ('action', $ret)
		// add further checks here
	)
		throw new RackTablesError ('malformed array structure in opspec_list', RackTablesError::INTERNAL);
	return $ret;
}

function unlinkPort ()
{
	assertUIntArg ('port_id');
	commitUnlinkPort ($_REQUEST['port_id']);
	showSuccess ("Port unlinked successfully");
}

function clearVlan()
{
	assertStringArg ('vlan_ck');
	list ($vdom_id, $vlan_id) = decodeVLANCK ($_REQUEST['vlan_ck']);
	
	$n_cleared = 0;
	foreach (getVLANConfiguredPorts ($_REQUEST['vlan_ck']) as $object_id => $portnames)
	{
		$D = getStored8021QConfig ($object_id);
		$changes = array();
		foreach ($portnames as $pn)
		{
			$conf = $D[$pn];
			$conf['allowed'] = array_diff ($conf['allowed'], array ($vlan_id));
			if ($conf['mode'] == 'access')
				$conf['mode'] = 'trunk';
			if ($conf['native'] == $vlan_id)
				$conf['native'] = 0;
			$changes[$pn] = $conf;
		}
		$n_cleared += apply8021qChangeRequest ($object_id, $changes, FALSE);
	}
	if ($n_cleared > 0)
		showSuccess ("VLAN $vlan_id removed from $n_cleared ports");
}

function deleteVlan()
{
	assertStringArg ('vlan_ck');
	$confports = getVLANConfiguredPorts ($_REQUEST['vlan_ck']);
	if (! empty ($confports))
		throw new RackTablesError ("You can not delete vlan which has assosiated ports");
	list ($vdom_id, $vlan_id) = decodeVLANCK ($_REQUEST['vlan_ck']);
	usePreparedDeleteBlade ('VLANDescription', array ('domain_id' => $vdom_id, 'vlan_id' => $vlan_id));
	showSuccess ("VLAN $vlan_id has been deleted");
	return buildRedirectURL ('vlandomain', 'default', array ('vdom_id' => $vdom_id));
}

function tableHandler()
{
	$opspec = getOpspec();
	global $sic;
	$columns = array();
	foreach (array ('arglist', 'set_arglist', 'where_arglist') as $listname)
	{
		if (! array_key_exists ($listname, $opspec))
			continue;
		foreach ($opspec[$listname] as $argspec)
		{
			genericAssertion ($argspec['url_argname'], $argspec['assertion']);
			// "table_colname" is normally used for an override, if it is not
			// set, use the URL argument name
			$table_colname = array_key_exists ('table_colname', $argspec) ?
				$argspec['table_colname'] :
				$argspec['url_argname'];
			$arg_value = $sic[$argspec['url_argname']];
			if
			(
				($argspec['assertion'] == 'uint0' and $arg_value == 0)
				or ($argspec['assertion'] == 'string0' and $arg_value == '')
			)
				switch (TRUE)
				{
				case !array_key_exists ('if_empty', $argspec): // no action requested
					break;
				case $argspec['if_empty'] == 'NULL':
					$arg_value = NULL;
					break;
				default:
					throw new InvalidArgException ('opspec', '(malformed array structure)', '"if_empty" not recognized');
				}
			$columns[$listname][$table_colname] = $arg_value;
		}
	}
	switch ($opspec['action'])
	{
	case 'INSERT':
		usePreparedInsertBlade ($opspec['table'], $columns['arglist']);
		break;
	case 'DELETE':
		$conjunction = array_key_exists ('conjunction', $opspec) ? $opspec['conjunction'] : 'AND';
		usePreparedDeleteBlade ($opspec['table'], $columns['arglist'], $conjunction);
		break;
	case 'UPDATE':
		usePreparedUpdateBlade
		(
			$opspec['table'],
			$columns['set_arglist'],
			$columns['where_arglist'],
			array_key_exists ('conjunction', $opspec) ? $opspec['conjunction'] : 'AND'
		);
		break;
	default:
		throw new InvalidArgException ('opspec/action', $opspec['action']);
	}
	showOneLiner (51);
}

?>
