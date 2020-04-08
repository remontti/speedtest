<?php
session_start();
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html>
<head>
<title>Testador de Velocidade - Resultados</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<style type="text/css">
	@font-face {
	  font-family: OpenSansBold;
	  src: url(results/OpenSans-Semibold.ttf);
	}
	@font-face {
	  font-family: OpenSans;
	  src: url(results/OpenSans-Light.ttf);
	}
	html,body{
		margin:0;
		padding:0;
		border:none;
		width:100%; 
		min-height:100%;
		background:#FFFFFF;
		color:#424242;
	}
	html{
		font-size: 1em;
		font-family:"OpenSans",sans-serif;
	}
	body{
		box-sizing:border-box;
		width:100%;
		max-width:70em;
		margin:4em auto;
		padding:1em 1em 4em 1em;

	}
	h1,h2,h3,h4,h5,h6{
		font-weight:300;
		margin-bottom: 0.1em;
	}
	h1{
		text-align:center;
	}
	table{
		margin:1em 0;
		width:100%;
		border: 2px solid #E0E0E0;
	}
	tr, th, td {
		border: 1px solid #E0E0E0;
	}
	th {
		width: 6em;
	}
	td {
		word-break: break-all;
	}
</style>
</head>
<body>
<h1>Testador de Velocidade - Resultados</h1>
<?php
include_once("telemetry_settings.php");
require "idObfuscation.php";
if($stats_password=="PASSWORD"){
	?>
		<div align="center">
			<p>Defina uma senha em telemetry_settings.php para ativar o acesso ($stats_password).</p>
		</div>
	<?php
}else if($_SESSION["logged"]===true){
	if($_GET["op"]=="logout"){
		$_SESSION["logged"]=false;
		?><script type="text/javascript">window.location=location.protocol+"//"+location.host+location.pathname;</script><?php
	}else{
		$conn=null;
		if($db_type=="mysql"){
			$conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename);
		}else if($db_type=="sqlite"){
			$conn = new PDO("sqlite:$Sqlite_db_file");
		} else if($db_type=="postgresql"){
			$conn_host = "host=$PostgreSql_hostname";
			$conn_db = "dbname=$PostgreSql_databasename";
			$conn_user = "user=$PostgreSql_username";
			$conn_password = "password=$PostgreSql_password";
			$conn = new PDO("pgsql:$conn_host;$conn_db;$conn_user;$conn_password");
		}else die();
?>
	<form action="stats.php" method="GET" style="text-align: right;">
		<input class="btn btn-danger" type="hidden" name="op" value="logout" />
		<input class="btn btn-danger" type="submit" value="Sair" />
	</form>
	<form action="stats.php" method="GET">
		<h3>Localizar teste</h6>
		<div class="form-row">
			<div class="form-group col-md-4">
				<input type="hidden" name="op" value="id" />
				<input class="form-control" type="text" name="id" id="id" placeholder="Teste ID" value=""/>
			</div>		
			<div class="form-group col-md-4">
				<input class="btn btn-primary" type="submit" value="Buscar" />
				<input class="btn btn-info" type="submit" onclick="document.getElementById('id').value=''" value="Mostrar últimos 100" />
			</div>
		</div>
	</form>
	<?php
		$q=null;
		if($_GET["op"]=="id"&&!empty($_GET["id"])){
			$id=$_GET["id"];
			if($enable_id_obfuscation) $id=deobfuscateId($id);
			if($db_type=="mysql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users where id=?");
				$q->bind_param("i",$id);
				$q->execute();
				$q->store_result();
				$q->bind_result($id,$timestamp,$ip,$ispinfo,$ua,$lang,$dl,$ul,$ping,$jitter,$log,$extra);
			} else if($db_type=="sqlite"||$db_type=="postgresql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users where id=?");
				$q->execute(array($id));
			} else die();
		}else{
			if($db_type=="mysql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users order by timestamp desc limit 0,100");
				$q->execute();
				$q->store_result();
				$q->bind_result($id,$timestamp,$ip,$ispinfo,$ua,$lang,$dl,$ul,$ping,$jitter,$log,$extra);
			} else if($db_type=="sqlite"||$db_type=="postgresql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users order by timestamp desc limit 100");
				$q->execute();
			}else die();
		}
		while(true){
			$id=null; $timestamp=null; $ip=null; $ispinfo=null; $ua=null; $lang=null; $dl=null; $ul=null; $ping=null; $jitter=null; $log=null; $extra=null;
			if($db_type=="mysql"){
				if(!$q->fetch()) break;
			} else if($db_type=="sqlite"||$db_type=="postgresql"){
				if(!($row=$q->fetch())) break;
				$id=$row["id"];
				$timestamp=$row["timestamp"];
				$ip=$row["ip"];
				$ispinfo=$row["ispinfo"];
				$ua=$row["ua"];
				$lang=$row["lang"];
				$dl=$row["dl"];
				$ul=$row["ul"];
				$ping=$row["ping"];
				$jitter=$row["jitter"];
				$log=$row["log"];
				$extra=$row["extra"];
			}else die();
	?>
		<table>
			<tr><th>ID</th><td><?=htmlspecialchars(($enable_id_obfuscation?(obfuscateId($id)." (id: ".$id.")"):$id), ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Data Hora</th><td><?=htmlspecialchars($timestamp, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>IP / ISP Info</th><td><?=$ip ?><br/><?=htmlspecialchars($ispinfo, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Dispositivo</th><td><?=$ua ?><br/><?=htmlspecialchars($lang, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Download</th><td><?=htmlspecialchars($dl, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Upload</th><td><?=htmlspecialchars($ul, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Ping</th><td><?=htmlspecialchars($ping, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Jitter</th><td><?=htmlspecialchars($jitter, ENT_HTML5, 'UTF-8') ?></td></tr>
			<!--<tr><th>Log</th><td><?=htmlspecialchars($log, ENT_HTML5, 'UTF-8') ?></td></tr>
			<tr><th>Extra</th><td><?=htmlspecialchars($extra, ENT_HTML5, 'UTF-8') ?></td></tr>-->
		</table>
	<?php
		}
	?>
<?php
	}
}else{
	if($_GET["op"]=="login"&&$_POST["password"]===$stats_password){
		$_SESSION["logged"]=true;
		?><script type="text/javascript">window.location=location.protocol+"//"+location.host+location.pathname;</script><?php
	}else{
?>
	<form action="stats.php?op=login" method="POST">
		<h3>Login</h3>
		<div class="form-row">
			<div class="form-group col-md-4">
				<input class="form-control" type="password" name="password" placeholder="Senha" value=""/>				
			</div>
			<div class="form-group col-md-4">
				<input class="btn btn-primary" type="submit" value="Entrar" />
			</div>
		</div>
	</form>
<?php
	}
}
?>
</body>
</html>
