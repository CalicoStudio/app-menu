<?
// Gestion multi-serveur de l'application (Premier dispo est pris)
$mysql_server = Array();
$mysql_server[0]["host"] = "localhost";
$mysql_server[0]["user"] = "mhabitat";
$mysql_server[0]["pass"] = "bGu0sD4Yk7";
$mysql_server[0]["base"] = "mhabitat";
$mysql_server[1]["host"] = "localhost";
$mysql_server[1]["user"] = "root";
$mysql_server[1]["pass"] = "";
$mysql_server[1]["base"] = "appmenu";

foreach ($mysql_server as $connect) {
	if (@mysql_connect($connect["host"],$connect["user"],$connect["pass"])) {
		$G_mysql_server = $connect["host"];
        $select_base=@mysql_select_db($connect["base"]);
		break;
	}
}
mysql_query("SET NAMES UTF8");

if (!$select_base) { // Si échec
	echo "<font color=\"#CC0000\" face=\"Verdana\"><b>Erreur de connexion !</b></font><br />\n";
	echo "<font face=\"Verdana\"><b>Motorisation Habitat</b></font><br />\n";
	echo "Merci de pr&eacute;venir <a href=\"mailto:support@studio-calico.fr\">Calico</a> qu'une erreur intervient lors de la connexion à la base <b>$base</b></font>";
	exit();
}

//génération du xml des langues
$q_lang=mysql_query("SELECT * FROM `langue` ORDER BY `nom` ASC");
$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
$xml .= '<langs>'."\n";
while($rows=mysql_fetch_array($q_lang)) {
	$xml.= "\t".'<lang slug="'.$rows['slug'].'">'.$rows['nom'].'</lang>'."\n";
}
$xml .= '</langs>';
$fp = fopen("pays.xml", 'w+');
fputs($fp, $xml);
fclose($fp);
echo utf8_decode('-> Génération du xml des langues<br />');
?>