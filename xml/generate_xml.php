<?
// Gestion multi-serveur de l'application (Premier dispo est pris)
$mysql_server = Array();
$mysql_server[0]["host"] = "localhost";
$mysql_server[0]["user"] = "mhabitat";
$mysql_server[0]["pass"] = "bGu0sD4Yk7";
$mysql_server[0]["base"] = "appmenu";
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
	$xml.= "\t".'<lang slug="'.$rows['slug'].'"><nom>'.$rows['nom'].'</nom><desc><![CDATA['.$rows['description'].']]></desc></lang>'."\n";
}
$xml .= '</langs>';
$fp = fopen("pays.xml", 'w+');
fputs($fp, $xml);
fclose($fp);
echo utf8_decode('-> Génération du xml des langues<br />');

//génération du xml des catégories
$q_cat=mysql_query("SELECT * FROM `categorie` ORDER BY `ordre` ASC");
$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
$xml .= '<categories>'."\n";
$lang="";
while($rows=mysql_fetch_array($q_cat)) {
	$xml.= "\t".'<categorie slug="'.$rows['slug'].'" id="'.$rows['id'].'">'."\n";
	$xml.= "\t\t".'<cat lang="'.$rows['lang'].'">'.$rows['nom'].'</cat>'."\n";
	
	$qry_trad=mysql_query("SELECT * FROM `categorie_trad` WHERE `id_cat`=".$rows['id']);
	while($r_trad=mysql_fetch_array($qry_trad)) {
		$xml.="\t\t".'<cat lang="'.$r_trad['lang'].'">'.$r_trad['trad'].'</cat>'."\n";
	}
	$xml.= "\t".'</categorie>'."\n";
}
echo $xml .= '</categories>';
$fp = fopen("categorie.xml", 'w+');
fputs($fp, $xml);
fclose($fp);
echo utf8_decode('-> Génération du xml des catégories<br />');

//génération du xml des sous catégories
$q_cat=mysql_query("SELECT * FROM `sscategorie` ORDER BY `id_cat`, `ordre` ASC");
$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
$lang=""; $old_cat=-1;
$xml .= '<souscategories>'."\n";		
while($rows=mysql_fetch_array($q_cat)) {
	if($rows['id_cat']!=$old_cat) {
		if($old_cat!=-1) $xml .= '</sscategories>'."\n";
		$xml .= '<sscategories categorie="'.$rows['id_cat'].'">'."\n";		
		$old_cat=$rows['id_cat'];
	}
	$xml.= "\t".'<sscategorie slug="'.$rows['slug'].'" id="'.$rows['id'].'" prix="'.$rows['prix'].'">'."\n";
	$xml.= "\t\t".'<sscat lang="'.$rows['lang'].'">'."\n";
		$xml.="\t\t\t".'<nom>'.stripslashes($rows['nom']).'</nom>'."\n";
		$xml.="\t\t\t".'<desctop><![CDATA['.stripslashes($rows['descriptionTop']).']]></desctop>'."\n";
		$xml.="\t\t\t".'<descbas><![CDATA['.stripslashes($rows['descriptionBas']).']]></descbas>'."\n";
	$xml.="\t\t".'</sscat>'."\n";
	
	$qry_trad=mysql_query("SELECT * FROM `sscategorie_trad` WHERE `id_sscat`=".$rows['id']);
	while($r_trad=mysql_fetch_array($qry_trad)) {
		$xml.="\t\t".'<sscat lang="'.$r_trad['lang'].'">'."\n";
			$xml.="\t\t\t".'<nom>'.stripslashes($r_trad['nom']).'</nom>'."\n";
			$xml.="\t\t\t".'<desctop>'.stripslashes($r_trad['descriptionTop']).'</desctop>'."\n";
			$xml.="\t\t\t".'<descbas>'.stripslashes($r_trad['descriptionBas']).'</descbas>'."\n";
		$xml.="\t\t".'</sscat>'."\n";
	}
	$xml.= "\t".'</sscategorie>'."\n";
}
$xml .= '</sscategories>'."\n";
$xml .= '</souscategories>';
$fp = fopen("sscategorie.xml", 'w+');
fputs($fp, $xml);
fclose($fp);
echo utf8_decode('-> Génération du xml des sous catégories<br />');

//génération du xml des produits
$q_cat=mysql_query("SELECT * FROM `produit` ORDER BY `id_cat`, `id_sscat`, `ordre` ASC");
$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
$lang=""; $old_cat=-1; $old_sscat=-1;
$xml .= '<xmlproduits>'."\n";		
while($rows=mysql_fetch_array($q_cat)) {
	if($rows['id_sscat']!=$old_sscat || $rows['id_cat']!=$old_cat) {
		if($old_sscat!=-1) $xml .= '</produits>'."\n";
		$xml .= '<produits categorie="'.$rows['id_cat'].'" sscategorie="'.$rows['id_sscat'].'">'."\n";		
		$old_sscat=$rows['id_sscat']; $old_cat=$rows['id_cat'];
	}
	$xml.= "\t".'<produit id="'.$rows['id'].'" prix="'.$rows['prix'].'" ph1="'.$rows['photo1'].'" ph2="'.$rows['photo2'].'" ph3="'.$rows['photo3'].'">'."\n";
	if($rows['nom']!="") $xml.= "\t\t".'<trad lang="'.$rows['lang'].'">'.trim(stripslashes($rows['nom'])).'</trad>'."\n";
	
	$qry_trad=mysql_query("SELECT * FROM `produit_trad` WHERE `id_produit`=".$rows['id']);
	while($r_trad=mysql_fetch_array($qry_trad)) {
		$xml.="\t\t".'<trad lang="'.$r_trad['lang'].'">'.trim(stripslashes($r_trad['nom'])).'</trad>'."\n";
	}
	$xml.= "\t".'</produit>'."\n";
}
$xml .= '</produits>'."\n";
$xml .= '</xmlproduits>';
$fp = fopen("produits.xml", 'w+');
fputs($fp, $xml);
fclose($fp);
echo utf8_decode('-> Génération du xml des produits<br />');
?>