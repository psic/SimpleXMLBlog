<?
require './commentaire.php';
class article {

	private $articleSection; // 0 = rien, 1 = resume, 2=content, 3=fichier_comment,4=visible,5=titre,6=tag
	private $resumeArticleCourant;
	private $contentArticleCourrant;
	private $fichier_comArticleCourant;
	private $visibleArticleCourant;
	private $titreArticleCourant;
	private $xml_parser;
	private $comm_rep;
	private $comm;
	private $id;
	private $tag_array=array();
	private $NbCom=0;
	private $com_array = array();
	private $file;

	function __construct(){
		$this->xml_parser = xml_parser_create("UTF-8");
		//xml_parser_set_option($this->xml_parser,XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
		xml_set_object (  $this->xml_parser, $this );
		xml_set_element_handler($this->xml_parser, "startTagArticle", "endTagArticle");
		xml_set_character_data_handler($this->xml_parser, "contentsArticle");
	}


	function init($fic, $comm_rep, $id){
		$this->comm_rep = $comm_rep;
		$fart = fopen($fic,"r");
		$art = fread($fart, filesize($fic));
		$this->file = $fic;
		if(!(xml_parse($this->xml_parser, $art, feof($fart)))){
			die("Error on line " . xml_get_current_line_number($this->xml_parser) . "<BR>". xml_get_error_code($this->xml_parser));
		}
		$this->id = $id;
		if ($this->fichier_comArticleCourant != ''){
			$this->Nbcom =  $this->comm->getNbCom();
		}
		else{
			$this->comm = new commentaire();
			$this->Nbcom =  $this->comm->getNbCom();
		}
		xml_parser_free($this->xml_parser);

		fclose($fart);

	}

	function startTagArticle($parser, $data){
		switch ($data){
			case "RESUME":
				$this->articleSection = 1;
				break;
			case "CONTENT":
				$this->articleSection = 2;
				break;
			case "FILE_COMMENTS":
				$this->articleSection = 3;
				break;
			case "VISIBLE":
				$this->articleSection = 4;
				break;
			case "TITRE":
				$this->articleSection = 5;
				break;
			case "TAG":
				$this->articleSection = 6;
				break;
			default:
				$this->articleSection = 0;
				break;
		}
	}

	function contentsArticle($parser, $data){
		//echo $this->titreArticleCourant . ' ' . $this->articleSection . '<BR>';
		if ($this->articleSection == 1){
			$this->resumeArticleCourant .= $data;
		}
		if ($this->articleSection == 2){
			$this->contentArticleCourrant .= $data;
		}
		if ($this->articleSection == 3){
			$this->fichier_comArticleCourant = $data;
			$this->comm = new commentaire();
			$this->comm->init($this->comm_rep.$data);
		}
		if ($this->articleSection == 4){
			$this->visibleArticleCourant =  $data;
		}
		if ($this->articleSection == 5){
			$this->titreArticleCourant .= $data;
		}
		if ($this->articleSection == 6){
			array_push($this->tag_array,$data);
		}
	}

	function endTagArticle($parser, $data){
		switch ($data){
			case "RESUME":
				$this->articleSection = 0;
				break;
			case "CONTENT":
				$this->articleSection = 0;
				break;
			case "FILE_COMMENTS":
				$this->articleSection = 0;
				break;
			case "VISIBLE":
				$this->articleSection = 0;
				break;
			case "TITRE":
				$this->articleSection = 0;
				break;
			case "TAG":
				$this->articleSection = 0;
				break;
			default:
				$this->articleSection = 0;
				break;
		}
	}

	function affiche(){

		if ($this->visibleArticleCourant == 'true')
		{
			echo '<BR><DIV class="article" state="close" first="false" id="'. $this->id . '">';
			echo '<DIV class="titre">';
			echo $this->titreArticleCourant;
			echo '<DIV class="fleche">';
			echo ' <a  href="#" sens="droit"><img src="./ressource/fleche-droite.png"/></a>';
			echo ' <a  href="#" style="display: none;" sens="bas"><img src="./ressource/fleche-bas.png"/></a>';
			echo '</DIV>';
			echo '</DIV><BR>';
			echo '<DIV class="resume">';
			echo $this->resumeArticleCourant ;
			//echo ' <a id="'. $this->id . '" href="#"><img src="./ressource/fleche-droite.png"/></a>';
			echo '</DIV>';
			echo '<DIV class="content"></DIV>';
			echo '<DIV class="comment"></DIV>';
			echo '</DIV>';
		}
	}

	function getArticleContent(){
		return $this->contentArticleCourrant;
	}
	function getNbCom(){
		return $this->comm->getNbCom();
	}

	function getComSection(){
		//return $this->Nbcom;
		return $this->comm->getComSection();
	}

	function getCom(){
		return $this->comm->getCom();
	}

	function getId(){
		return $this->id;
	}

	function getTags()
	{
		return $this->tag_array;
	}


	function isVisible(){
		if($this->visibleArticleCourant == 'true')
			return true;
		else return false;
	}

	function enregistre_com($com,$pseudo,$mail){
		if ($this->fichier_comArticleCourant == "")
		{// pas de fichier commentaire : on le cree et le met dans le fichier article
			$this->create_com_file();
		}
		// on enregistre le commentaire dans le fichier comm et dans le tableau de commm
		$this->write_com($com,$pseudo,$mail);
	}

	private function create_com_file(){
		//On cree le fichier com
		$nom_fichier = $this->uniquename();
		$ourFileHandle = fopen($this->comm_rep . $nom_fichier, 'a+') or die("Impossible d'ouvrir ou de creer le fichier de commentaire");
		fclose($ourFileHandle);
		$this->fichier_comArticleCourant = $nom_fichier;
		// mettre le nom de fichier  de comm dans le fichier article <FILE_COMMENTS>
		$xdoc = new DOMDocument();
		$xdoc->load($this->file);
		$file_comTag = $xdoc->getElementsByTagName('FILE_COMMENTS');
		if ($file_comTag->length == 0)
		{
			$xdoc->appendChild($xdoc->createElement('FILE_COMMENTS',$this->fichier_comArticleCourant));
		}
		else{
			$file_comTag->item(0)->nodeValue = $this->fichier_comArticleCourant;
			$xdoc->save($this->file);
		}
	}

	private function write_com($comm,$pseudo,$mail){
		$com="";
		//	$ourFileHandle = fopen($this->comm_rep . $this->fichier_comArticleCourant, 'a') or die("Impossible d'ouvrir ou de creer le fichier de commentaire");
		$xdoc = new DOMDocument();
		$xdoc->load($this->comm_rep . $this->fichier_comArticleCourant);
		$rootTag = $xdoc->getElementsByTagName('XML');
		$com .= "<COM><AUTEUR>";
		if ($pseudo !="")
			$com .= "<PSEUDO>" . $pseudo . "</PSEUDO>";
		if ($mail !="")
			$com .= "<MAIL>" . $mail . "</MAIL>";
		$com .= "</AUTEUR>";
		$date = date('d-M-Y G:i:s');
		$com .= "<DATE>" . $date ."</DATE>";
		if ($comm != ""){
			$com .= "\n<CONTENT>" . $comm . "</CONTENT></COM>\n";
			$this->comm->addCom($com,$pseudo,$mail,$date);
			$xcomdoc = new DOMDocument();
			$xcomdoc->loadXML($com,LIBXML_DTDATTR);
			$comNode = $xcomdoc->getElementsByTagName('COM');
			$comNode = $xdoc->importNode($comNode->item(0),true);
			$rootTag->item(0)->appendChild($comNode);
			$xdoc->save($this->comm_rep . $this->fichier_comArticleCourant);
			//fwrite($ourFileHandle, $com);
		}
		//fclose($ourFileHandle);
	}


	private function uniquename(){
		$return='';
		for ($i=0;$i<7;$i++){
			$return.=chr(rand(97,122));
		}
		$return="$return-".time().".xml";
		return $return;
	}
}
?>
