<?php
/**
 * Bel-CMS [Content management system]
 * @version 3.0.0 [PHP8.2]
 * @link https://bel-cms.dev
 * @link https://determe.be
 * @license http://opensource.org/licenses/GPL-3.-copyleft
 * @copyright 2015-2023 Bel-CMS
 * @author as Stive - stive@determe.be
 */

class BelCMS
{
	var $page;

	function __construct()
	{
		if (!session_id()) {
			session_start();
		}
		$this->page = (!isset($_REQUEST['page'])) ? 'home' : $_REQUEST['page'];
		require_once ROOT.'INSTALL'.DS.'includes'.DS.'checkCompatibility.php';
	}

	public function VIEW()
	{
		ob_start();
		require ROOT.'INSTALL'.DS.'pages'.DS.$this->page.'.tpl';
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	public function HTML()
	{
		if ($this->page == 'create_sql') {
			$table = $_REQUEST['table'];
			require_once ROOT.'INSTALL'.DS.'includes'.DS.'tables.php';
			if ($error === true) {
				echo json_encode(array($error));
			} else {
				echo json_encode(array($error));
			}
		} else {
			ob_start("ob_gzhandler");
			?>
			<!DOCTYPE HTML>
			<html lang="fr">
			    <head>
			    	<title>Installation du C.M.S [BEL-CMS]</title>
			        <meta charset="UTF-8">
			        <meta http-equiv="x-ua-compatible" content="ie=edge">
			        <link href="/INSTALL/img/favicon.ico" rel="shortcut icon">
			        <link type="text/plain" rel="author" href="/INSTALL/humans.txt">
			        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
					<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
			        <link rel="stylesheet" href="/INSTALL/css/styles.css">
			        <link rel="stylesheet" href="/assets/plugins/fontawesome-6.1.1/css/all.min.css">
			    </head>
			    <body>
			    	<main>
			    		<h1>Installation de BEL-CMS v3.0.0</h1>
			    		<?=self::VIEW()?>
			    	</main>
			        <script src="/INSTALL/js/jquery.min.js"></script>
			        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
			        <script src="/INSTALL/js/scripts.js"></script>
			        <script src="/assets/plugins/fontawesome-6.1.1/js/all.min.js"></script>
				</body>
			</html>
			<?php
			$buffer = ob_get_contents();
			ob_end_clean();
			return $buffer;
		}
	}

	public static function TABLES () {

		$tables = array(
			'ban',
			'comments',
			'config',
			'config_pages',
			'downloads',
			'downloads_cat',
			'emoticones',
			'events',
			'events_cat',
			'gallery',
			'games',
			'groups',
			'inbox',
			'inbox_msg',
			'interaction',
			'mails_blacklist',
			'maintenance',
			'market',
			'market_cat',
			'newsletter',
			'newsletter_send',
			'newsletter_tpl',
			'page',
			'page_articles',
			'page_articles_cat',
			'page_content',
			'page_forum',
			'page_forum_post',
			'page_forum_posts',
			'page_forum_threads',
			'page_shoutbox',
			'page_survey',
			'page_survey_author',
			'page_survey_quest',
			'page_users',
			'page_users_profils',
			'page_users_social',
			'log',
			'page_team',
			'page_team_users',
			'visitors',
			'widgets'
		);

		return $tables;
	}
}
#########################################
# Debug
#########################################
function debug ($data = null, $exitAfter = false)
{
	echo '<pre>';
		print_r($data);
	echo '</pre>';
	if ($exitAfter === true) {
		exit();
	}
}
function redirect ($url = null, $time = null)
{
	$scriptName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);

	$fullUrl = ($_SERVER['HTTP_HOST'].$scriptName);

	if (!strpos($_SERVER['HTTP_HOST'], $scriptName)) {
		$fullUrl = $_SERVER['HTTP_HOST'].$scriptName.$url;
	}

	if (!strpos($fullUrl, 'http://')) {
		if ($_SERVER['SERVER_PORT'] == 80) {
			$url = 'http://'.$fullUrl;
		} else if ($_SERVER['SERVER_PORT'] == 443) {
			$url = 'https://'.$fullUrl;
		} else {
			$url = 'http://'.$fullUrl;
		}
	}

	$time = (empty($time)) ? 0 : (int) $time * 1000;

	?>
	<script>
	window.setTimeout(function() {
		window.location = '<?php echo $url; ?>';
	}, <?php echo $time; ?>);
	</script>
	<?php
}
function insertUserBDD ()
{
	$sql = array();

	if (!function_exists('password_hash')) {
		require ROOT.'core.'.DS.'password.php';
	}

	$users['username']	= $_POST['username'];
	$users['password']	= password_hash($_POST['password'], PASSWORD_DEFAULT);
	$users['email']		= $_POST['email'];
	$users['hash_key']	= md5(uniqid(rand(), true));
	$users['ip']		= getIp();
	$_SESSION['USER']   = array($users);

	$sql[]  = "INSERT INTO `".$_SESSION['prefix']."page_users` (
				`id` ,
				`username` ,
				`passwordhash` ,
				`email` ,
				`avatar` ,
				`hash_key` ,
				`date_registration` ,
				`last_visit` ,
				`groups` ,
				`main_groups` ,
				`valid` ,
				`ip` ,
				`token`,
				`expire`,
				`gold`
			) VALUES (
				NULL , '".$users['username']."', '".$users['password']."', '".$users['email']."', '', '".$users['hash_key']."', NOW() , NOW() , '1', '1', '1', '".$users['ip']."', '', '0', '1'
			);";

	$sql[]  = "INSERT INTO `".$_SESSION['prefix']."page_users_profils` (
				`id` ,
				`hash_key` ,
				`gender` ,
				`public_mail` ,
				`websites` ,
				`list_ip` ,
				`list_avatar` ,
				`config` ,
				`info_text` ,
				`birthday` ,
				`country` ,
				`hight_avatar` ,
				`friends`
				)
			VALUES (
				NULL , '".$users['hash_key']."', 'unisexual', '', '', '', '', '', '', '".date('Y-m-d')."' , '', '', ''
			);";

	$sql[]  = "INSERT INTO `".$_SESSION['prefix']."page_users_social` (
				`id` ,
				`hash_key` ,
				`facebook` ,
				`linkedin` ,
				`twitter` ,
				`discord` ,
				`pinterest`
				)
			VALUES (
				NULL , '".$users['hash_key']."', '', '', '', '', ''
			);";
			$return = false;
	foreach ($sql as $insert) {
		try {
			$cnx = new PDO('mysql:host='.$_SESSION['host'].';port='.$_SESSION['port'].';dbname='.$_SESSION['dbname'], $_SESSION['username'], $_SESSION['password']);
			$cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$cnx->exec($insert);
			$return = true;
		} catch(PDOException $e) {
			$return = $e->getMessage();
			return $return;
		}
		unset($cnx);
	}
	return $return;
}

function rmAllDir($strDirectory){
	$dir_iterator = new RecursiveDirectoryIterator($strDirectory);
	$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
	foreach($iterator as $fichier){
		$fichier->isDir() ? @rmdir($fichier) : @unlink($fichier);
	}
	@rmdir($strDirectory);
}

function getIp () {
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$return = $_SERVER['HTTP_CLIENT_IP'];
	}
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$return = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$return = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
	}
	if ($return == '::1') {
		$return = '127.0.0.1';
	}
	return $return;
}
?>