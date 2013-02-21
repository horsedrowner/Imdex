<?php
spl_autoload_register(function ($class) {
    include 'php/' . $class . '.class.php';
});

function print_nav($imdex, $isAdmin) {
	if ($imdex->CanGoUp()) {
		$parent = htmlspecialchars($imdex->Parent()->Name());
		$url = "../";
		if ($isAdmin)
			$url .= "?manage";

		echo "\n\t\t\t\t<li><a href=\"{$url}\"><i class=\"icon-arrow-up\"></i> {$parent}</a>"
		   . "\n\t\t\t\t<li class=\"divider\">";
	}

	foreach ($imdex->Folders() as $value) {
		$sub = new Imdex($imdex->Path() . DIRECTORY_SEPARATOR . $value);
		$name = htmlspecialchars($value);
		$url = rawurldecode($value) . "/";
		if ($isAdmin)
			$url .= "?manage";

		echo "\n\t\t\t\t<li"; 
		if ($sub->IsEmpty())
			echo " class=\"disabled\"";
		echo "><a href=\"{$url}\"><i class=\"icon-caret-right\"></i> {$name}</a>";
	}
}

function print_thumbs($files, $isAdmin) {
	$chunks = array_chunk($files, 3);
	foreach ($chunks as $row)
	{
		echo "\n\t\t\t<ul class=\"thumbnails\">";
		foreach ($row as $value) {
			$name = htmlspecialchars($value);
			$url = rawurlencode($value);
			echo "\n\t\t\t\t<li class=\"span4\"><a href=\"{$url}\" class=\"thumbnail\">"
			   . "<img src=\"{$url}\" alt=\"{$name}\" title=\"{$name}\"></a>";
		}
		echo "\n\t\t\t</ul>";
	}	
}

$isAdmin = isset($_GET["manage"]);

$requestDir = Path::RemoveQueryString($_SERVER["REQUEST_URI"]);
$imdex = new Imdex($requestDir);

?>
<!DOCTYPE html>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Images in <?php echo htmlspecialchars($imdex->Name());?></title>
<link href="/assets/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="/assets/css/font-awesome.min.css" rel="stylesheet" media="screen">
<link href="/assets/css/base.css" rel="stylesheet" media="screen">
<link href="/assets/css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
<link href="/favicon.ico" type="image/x-icon" rel="shortcut icon">

<div class="navbar navbar-static-top">
	<div class="navbar-inner">
		<a class="brand" href="/"><?php echo htmlspecialchars($imdex->Name());?></a>
		<ul class="nav">
			<li <?php if (!$isAdmin) echo "class=\"active\"";?>><a href="."><i class="icon-folder-open"></i> Browse</a>
		</ul>
		<ul class="nav pull-right">
			<li <?php if ($isAdmin) echo "class=\"active\"";?>><a href="?manage"><i class="icon-wrench"></i> Manage</a>
			<li><a href="https://github.com/horsedrowner/Imdex" target="_blank"><i class="icon-github-alt"></i> GitHub</a>
		</ul>
	</div>
</div>

<div id="container" class="container-fluid">
	<div class="row-fluid">
		<div class="span2">
		<?php if ($imdex->HasFolders() || $imdex->CanGoUp()) { ?> 
			<ul class="nav nav-list well">
				<?php print_nav($imdex, $isAdmin); ?> 
			</ul>
		<?php } else if (!$imdex->HasImages()) { ?> 
			<a class="btn btn-block" href="."><i class="icon-refresh"></i> Refresh</a>
		<?php } ?> 
		</div>
		<div class="span10"> 
		<?php if (!$imdex->IsReal()) { ?> 
			<div class="alert">
				The requested directory does not exist.
			</div>
		<?php } else if ($imdex->HasImages()) { 
			print_thumbs($imdex->Images(), $isAdmin); 
		} ?> 
		</div>
	</div>
</div>

<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="/js/bootstrap.min.js"></script>