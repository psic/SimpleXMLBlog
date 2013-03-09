<?
class commentaire {

	private $commentaireSection; // 0 = rien, 1 = com, 2=auteur, 3=pseudo,4=mail, 5=date, 6=content
	private $pseudoCommentaireCourant;
	private $contentCommentaireCourant;
	private $mailCommentaireCourant;
	private $dateCommentaireCourant;
	private $xml_parser;
	private $nb_com=0;
	private $comm_array = array();


	function __construct(){
	$this->xml_parser = xml_parser_create(); 
	xml_set_object (  $this->xml_parser, $this );
	xml_set_element_handler($this->xml_parser, "startTagCommentaire", "endTagCommentaire"); 
	xml_set_character_data_handler($this->xml_parser, "contentsCommentaire"); 
	} 
	

	function init($fic){
	$fcomm = fopen($fic,"r");
	 if(filesize($fic)>0) {
		$comm = fread($fcomm, filesize($fic));
		if(!(xml_parse($this->xml_parser, $comm, feof($fcomm)))){ 
		die("Error on line " . xml_get_current_line_number($this->xml_parser)); 
		}
		//xml_parse($this->xml_parser, $art);
		//echo xml_error_string(xml_get_error_code($this->xml_parser));
		xml_parser_free($this->xml_parser); 
	}
	else
	{
		fclose($fcomm);
		$fcomm = fopen($fic,"w");
		fwrite($fcomm, '<?xml version="1.0" encoding="utf-8"?><XML></XML>');	
	}
	fclose($fcomm); 
		
	}
	 
	function startTagCommentaire($parser, $data){ 
		switch ($data){
			case "COM" :
				$this->pseudoCommentaireCourant ="";
				$this->contentCommentaireCourant ="";
				$this->mailCommentaireCourant ="";
				break;
			case "PSEUDO":
				$this->commentaireSection = 3;
				break;
			case "MAIL":
				$this->commentaireSection = 4;
				break;
			case "DATE":
				$this->commentaireSection = 5;
				break;
			case "CONTENT":
				$this->commentaireSection = 6;
				break;
			default:
				$this->commentaireSection = 0;
				break;
		} 
	} 

	function contentsCommentaire($parser, $data){ 
		if ($this->commentaireSection == 3){
			$this->pseudoCommentaireCourant .= $data;
		}
		if ($this->commentaireSection == 4){
			$this->mailCommentaireCourant .= $data;
		}
		if ($this->commentaireSection == 5){
			$this->dateCommentaireCourant = $data;
		}
		if ($this->commentaireSection == 6){
			$this->contentCommentaireCourant .= $data;
		}
	}
	 

	function endTagcommentaire($parser, $data){ 
    	switch ($data){
			case "PSEUDO":
				$this->commentaireSection = 0;
				break;
			case "CONTENT":
				$this->commentaireSection = 0;
				break;
			case "MAIL":
				$this->commentaireSection = 0;
				break;
			case "DATE":
				$this->commentaireSection = 0;
				break;
			case "COM":
				$com_courant = array('pseudo'=>$this->pseudoCommentaireCourant,
									 'content'=>$this->contentCommentaireCourant,
									 'mail'=>$this->mailCommentaireCourant,
									 'date'=>$this->dateCommentaireCourant);
				array_push($this->comm_array,$com_courant);
				
				$this->pseudoCommentaireCourant ="";
				$this->contentCommentaireCourant ="";
				$this->mailCommentaireCourant ="";
				
				$this->nb_com ++;				
				$this->commentaireSection = 0;
 
				break;
			default:
				$this->commentaireSection = 0;
				break;
		} 
	}
	
	function affiche(){
		echo '<BR>';
		echo $this->nb_com . ' commentaire(s)';
		foreach ($this->comm_array as $com){
			echo '<BR>-------------------------------------<BR>';
			echo $com['content'];
			echo '<BR>';
			echo 'par ' . $com['pseudo'] . ' / ' . $com['mail'];
		
		}
	}
	
	function getCom(){
		$comms= "";
		foreach ($this->comm_array as $com){

			$comms = $comms . '<BR><DIV class="un_com">';
			$comms = $comms . $com['content'];
			$comms = $comms . '<BR><BR><DIV class="auteur_com">';
			$comms = $comms . 'par ' . $com['pseudo'];
			if ($com['mail']!="") 
				$comms = $comms . ' / ' . $com['mail'];
			$comms = $comms . '</DIV></DIV>';

			 
			 //$comms = $comms . $this->formatCom($com['content'],$com['pseudo'],$com['mail']);
			
		}
		//$comms = $comms . '</DIV>';
		return $comms;
	
	}
	
	static function formatCom($com,$pseudo,$mail){
			$comms ="";
			$comms = $comms . '<BR><DIV class="un_com">';
			$comms = $comms . $com;
			$comms = $comms . '<BR><BR><DIV class="auteur_com">';
			$comms = $comms . 'par ' . $pseudo;
			if ($mail!=null ||$mail!="") $comms = $comms . ' / ' . $mail;
			 $comms = $comms . '</DIV></DIV>';
			return  $comms  ;
	}
	
	function getComSection(){
	 return   "<BR></BR>".$this->getNbCom() . $this->getComForm();
	}
	
	function getComForm(){
		$form = "
		<div class='form'>
		<form method='post' action=''>
		<div class='form_text'>
		<textarea name='com' id='com' rows='4' cols='122'>... ici j'écris mon commentaire ...</textarea>
		</div>
		pseudo : <input type='text' name='pseudo' id='pseudo'/> mail : <input type='text' name='mail' id='mail'/>  	
		<input type='button' value='Commenter' onClick='enregistre_com(this.form.com.value,this.form.pseudo.value,this.form.mail.value,$(this).closest(\".article\").find(\".resume a\").attr(\"id\")); this.form.reset();' />
		</div></form></div>";
		return $form;
	}	
	function getNbCom(){
		if ($this->nb_com >1)
			return '<DIV class="nb_com">'. $this->nb_com . ' commentaires</DIV>' ;
		elseif ($this->nb_com == 1)
			return '<DIV class="nb_com">'. $this->nb_com . ' commentaire</DIV>';
		else
			return '<DIV class="nb_com"> Pas de commentaire</DIV>' ;
	}
	
	public function addCom($com,$pseudo,$mail,$date){
		$com_courant = array('pseudo'=>$pseudo,
							 'content'=>$com,
							 'mail'=>$mail,
							 'date'=>$date);
				array_push($this->comm_array,$com_courant);
				$this->nb_com ++;				 
	}
}
?>
