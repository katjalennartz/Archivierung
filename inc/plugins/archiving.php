<?php
if (!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// $plugins->add_hook("admin_formcontainer_output_row", "archiving_box");
// $plugins->add_hook("admin_forum_management_edit_commit", "archiving_commit");

function archiving_info()
{
	return array(
		'name'		=> 'Automatische Archivierung',
		'description'	=> 'Ermöglicht es Admins einzustellen in welchen Archivbereich das Thema standardmäßig verschoben wird',
		'author'	=> 'aheartforspinach',
		'authorsite'	=> 'https://github.com/aheartforspinach',
		'version'	=> '1.0',
		'compatibility' => '18*'
	);
}

function archiving_install()
{
	global $db;

	$db->write_query("ALTER TABLE " . TABLE_PREFIX . "forums ADD archiving_active TINYINT(1) NOT NULL DEFAULT '0';");
	$db->write_query("ALTER TABLE " . TABLE_PREFIX . "forums ADD archiving_defaultArchive INT(32) NOT NULL DEFAULT '0';");
	$db->write_query("ALTER TABLE " . TABLE_PREFIX . "forums ADD archiving_isVisibleForUser TINYINT(1) NOT NULL DEFAULT '0';");
	$db->write_query("ALTER TABLE " . TABLE_PREFIX . "forums ADD archiving_inplay TINYINT(1) NOT NULL DEFAULT '0';");

	$insert_array = array(
		'title'        => 'archivingButton',
		'template'    => $db->escape_string('<a href="misc.php?action=archiving&fid={$fid}&tid={$tid}" title="Thema archivieren"><i class="fas fa-archive"></i></a>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'        => 'archivingSubmitSite',
		'template'    => $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->archiving_submitpage_title}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<form action="misc.php?action=archiving" method="post">
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
		<td class="thead" colspan="2"><strong>{$lang->archiving_submitpage_title}</strong></td>
		</tr>
			<tr><td>
				<center>{$infoText}</center></td>
			</tr>
		</table>
		<br />
		<div align="center"><input type="submit" class="button" name="submit" value="{$lang->archiving_submitpage_submit}" /></div>
		<input type="hidden" name="action" value="archiving" />
		<input type="hidden" name="tid" value="{$tid}" />
		<input type="hidden" name="old_fid" value="{$old_fid}" />
		<input type="hidden" name="new_fid" value="{$new_fid}" />
		</form>
		{$footer}
		</body>
		</html>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	rebuild_settings();
}

function archiving_is_installed()
{
	global $db;
	if ($db->field_exists('archiving_defaultArchive', 'forums')) {
		return true;
	}
	return false;
}

function archiving_uninstall()
{
	global $db;
	if ($db->field_exists('archiving_active', 'forums'))
		$db->drop_column('forums', 'archiving_active');

	if ($db->field_exists('archiving_defaultArchive', 'forums'))
		$db->drop_column('forums', 'archiving_defaultArchive');

	if ($db->field_exists('archiving_isVisibleForUser', 'forums'))
		$db->drop_column('forums', 'archiving_isVisibleForUser');

	if ($db->field_exists('archiving_inplay', 'forums'))
		$db->drop_column('forums', 'archiving_inplay');


	rebuild_settings();
}

function archiving_activate()
{
	include MYBB_ROOT . "/inc/adminfunctions_templates.php";
	find_replace_templatesets("forumdisplay_thread", "#" . preg_quote('{$thread[\'multipage\']}') . "#i", '{$thread[\'multipage\']} {$archivingButton}');
}

function archiving_deactivate()
{
	include MYBB_ROOT . "/inc/adminfunctions_templates.php";
	find_replace_templatesets("forumdisplay_thread", "#" . preg_quote('{$archivingButton}') . "#i", '', 0);
}

$plugins->add_hook('admin_formcontainer_output_row', 'archiving_editForumBox');
function archiving_editForumBox($args)
{
	global $lang, $form_container, $form, $mybb, $db;

	$lang->load('archiving');

	if ($args['title'] == $lang->misc_options && $lang->misc_options) {
		$data = $db->fetch_array($db->simple_select('forums', 'archiving_active, archiving_isVisibleForUser, archiving_inplay, archiving_defaultArchive', 'fid = ' . $mybb->get_input('fid')));
		$formcontent = array(
			$lang->archiving_active . ':<br/>' . $form->generate_yes_no_radio('archive_active', $data['archiving_active']),
			'<br><br><br>' . $lang->archiving_standardArchive . '<br />' . $form->generate_forum_select('archive_forum', (int)$data['archiving_defaultArchive'], array('id' => 'archive_forum')),
			'<br>' . $lang->archiving_editableUser . ':<br/>' . $form->generate_yes_no_radio('archive_editableUser', $data['archiving_isVisibleForUser'],  array('id' => 'archive_editableUser')),
			'<br><br><br>' . $lang->archive_inplayArchive . ':<br />' . $form->generate_yes_no_radio('archive_inplayArchive', $data['archiving_inplay']),
		);

		$args['content'] .= $form_container->output_row('Archivierungsoptionen', '', "<div class=\"forum_archiving_bit\">" . implode("</div><div class=\"forum_archiving_bit\">", $formcontent) . "</div>");
	}


	return $args;
}

$plugins->add_hook('admin_forum_management_edit_commit', 'archiving_commit');
function archiving_commit()
{
	global $mybb, $cache, $db, $fid;
	$update_array = array(
		'archiving_active' => $mybb->get_input('archive_active'),
		'archiving_defaultArchive' => $mybb->get_input('archive_forum'),
		'archiving_isVisibleForUser' => $mybb->get_input('archive_editableUser'),
		'archiving_inplay' => $mybb->get_input('archive_inplayArchive')
	);

	$db->update_query('forums', $update_array, 'fid = ' . $fid);
	$cache->update_forums();
}

//Button in Forenansicht anzeigen
$plugins->add_hook('forumdisplay_thread', 'archiving_forumdisplay_thread');
function archiving_forumdisplay_thread()
{
	global $mybb, $archivingButton, $db, $fid, $templates, $thread;

	$tid = $thread['tid'];
	$settings = $db->fetch_array($db->simple_select('forums', 'archiving_active, archiving_isVisibleForUser, archiving_inplay', 'fid = ' . $fid));

	$archivingButton = '';
	//TODO: accountswitcher berücksichtigen
	if ($settings['archiving_active']) {
		if ($settings['archiving_isVisibleForUser']) {
			if ($settings['archiving_inplay']) { //Berücksichtigung von anderen Szenenteilnehmern
				if (strstr($thread['partners'], (string) $mybb->user['uid'])) {
					$archivingButton = eval($templates->render('archivingButton'));
				}
			} elseif ($thread['uid'] ==  $mybb->user['uid']) {
				$archivingButton = eval($templates->render('archivingButton'));
			}
		} elseif ($mybb->usergroup['canmodcp'] == 1) {
			// $archivingButton = eval($templates->render('archivingButton'));
		}
	}
}

$plugins->add_hook('misc_start', 'archiving_misc');
function archiving_misc()
{
	global $lang, $db, $mybb, $templates, $theme, $headerinclude, $header, $footer, $cache;
	$lang->load('archiving');
	if ($mybb->input['action'] == 'archiving') {
		//Annahme von Bestätigung
		if (isset($_POST['submit'])) {
			$old_fid = $_POST['old_fid'];
			$new_fid = $_POST['new_fid'];
			var_dump($old_fid);
			$update_array = array(
				'fid' => $new_fid
			);

			$db->update_query('posts', $update_array, 'tid = ' . $_POST['tid']);
			$db->update_query('threads', $update_array, 'tid = ' . $_POST['tid']);
			require_once MYBB_ROOT . "inc/functions_rebuild.php";
			rebuild_forum_counters($old_fid);
			rebuild_forum_counters($new_fid);

			redirect('forumdisplay.php?fid=' . $_POST['aid'], $lang->archiving_submitpage_success);
		}

		if (!isset($mybb->input['fid'])) //wenn Zugriff ohne fid ->  von außen
			error_no_permission();


		$tid = $mybb->get_input('tid');
		$old_fid = $mybb->get_input('fid');
		$settings = $db->fetch_array($db->simple_select('forums', 'archiving_inplay, archiving_defaultArchive', 'fid = ' . $old_fid));
		$threadName = $db->fetch_array($db->simple_select('threads', 'subject', 'tid = ' . $tid))['subject'];

		if ($settings['archiving_inplay']) { //wenn inplay nach richtiger kategorie suchen
			$ipdate = $db->fetch_array($db->simple_select('threads', 'ipdate', 'tid = ' . $mybb->get_input('tid')))['ipdate'];
			$archiveName = getMonthName(date('m', $ipdate)) . ' ' . date('Y', $ipdate);
			$new_fid = $db->fetch_array($db->simple_select('forums', 'fid', 'name = "' . $archiveName . '"'))['fid'];

			if ($new_fid == null) {
				$new_fid = $settings['archiving_defaultArchive'];
			}
		} else {
			$archiveName = $db->fetch_array($db->simple_select('forums', 'name', 'fid = ' . $settings['archiving_defaultArchive']))['name'];
			$new_fid = $settings['archiving_defaultArchive'];
		}

		$infoText = $lang->sprintf($lang->archiving_submitpage_text, $threadName, $archiveName);

		eval("\$page = \"" . $templates->get('archivingSubmitSite') . "\";");
		output_page($page);
	}
}

function getMonthName($month)
{
	if ($month == '01') {
		return 'Januar';
	} elseif ($month == '02') {
		return 'Februar';
	} elseif ($month == '03') {
		return 'März';
	} elseif ($month == '04') {
		return 'April';
	} elseif ($month == '05') {
		return 'Mai';
	} elseif ($month == '06') {
		return 'Juni';
	} elseif ($month == '07') {
		return 'Juli';
	} elseif ($month == '08') {
		return 'August';
	} elseif ($month == '09') {
		return 'September';
	} elseif ($month == '10') {
		return 'Oktober';
	} elseif ($month == '11') {
		return 'November';
	} elseif ($month == '12') {
		return 'Dezember';
	}
}

