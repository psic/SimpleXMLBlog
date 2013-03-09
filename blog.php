<?
require './article.php';
class blog {
	
//DONE Faire un Singleton et une classe ajaxrouter qui recupere le requete ajax, fait un getInstance sur le blog et recupere le content d'un article*/
//DONE finir l'enregistrement des commentaires
//DONE augementer le nombre de commentaire quand on en rajoute un V1
//DONE gerer les caracteres hmtl, quote et double quote dans les commentaires V1
//DONE vider le formulaire apres soumission V1
//DONE afficher le mail dans les commentaires.

//TODO Pouvoir "refermer" un commentaire avec une fleche vers le haut, et les reouvrir, etc.. V2
//TODO gerer les DIV et autres  tag html dans les articles  V2
//TODO gerer les DIV et autres  tag html dans les commentaires V2
//TODO classer les articles par date avant de les afficher V2
//TODO classer les commentaires par date avant de les afficher V2
//TODO enregistrer la date et l'heure des commmentaires, et l'afficher aussi V2
//TODO afficher le nombre de commentaire avec le resume au début V2

//TODO Gere le filtre par TAG V3
//TODO tester tout ça
	public static $_instance;	
	const article_rep ="articles/";	
	const com_rep ="comments/";	
	private $art_array = array();
	private $tag_array = array();
	
	
	private function __clone () {}
	private function __construct(){
	$this->xml_parser = xml_parser_create(); 
	xml_set_object (  $this->xml_parser, $this );
	xml_set_element_handler($this->xml_parser, "startTagArticle", "endTagArticle"); 

	xml_set_character_data_handler($this->xml_parser, "contentsArticle"); 

	$dir = opendir(blog::article_rep);	
	$i=1;
	while ($f = readdir($dir)) {
		if(is_file(blog::article_rep.$f)) {
	/**	echo "<li>Nom : ".$f;
		echo "<li>Taille : ".filesize(blog::article_rep.$f)." octets";
		echo "<li>Création : ".(filectime(blog::article_rep.$f));
		echo "<li>Modification : ".(filemtime(blog::article_rep.$f));
		echo "<li>Dernier accès : ".(fileatime(blog::article_rep.$f));
		echo "<br><br>";
		**/ 
		$article = new article();
		$article->init(blog::article_rep.$f,blog::com_rep,$i++);
		//$this->article(blog::article_rep.$f);
		array_push($this->art_array,$article);
		if ($article->isVisible() == 'true'){
			foreach ($article->getTags() as $tag){
				if(!in_array($tag,$this->tag_array))
					array_push($this->tag_array,$tag);
			}
		}
	   }
	}
	
	//$this->affiche(); 
	
  }
	
	 public static function getInstance () {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();
 
        return self::$_instance;
    }
 
	
	
	public function affiche(){
	echo '<html><link rel="stylesheet" href="css/blog.css" type="text/css" /><body>';
	echo $this->js();
	echo '<DIV class="blog"><DIV class="global">';
		foreach ($this->art_array as $article)
		{
			$article->affiche();	
		}
		echo '</DIV>';	
		echo '<DIV class="tag"><UL>';
		foreach ($this->tag_array as $tag){
			echo '<LI>'. $tag .'</LI>';
		}				
		echo '</UL></DIV>';
		echo '</DIV>';
    echo '</body></html>';
	}
	
	function getArticleContent($id){
		foreach ($this->art_array as $article)
		{
			if ($article->id == $id)
				return $article->getArticleContent();	
		}
	}
	
	function commentaires($fic){
	}
	
	function ajouterCommentaire(){
	
	}
	
	static function getSection(){
	 return $this->articleSection;
	}
	
	public function getContent($id)
	{
		foreach ($this->art_array as $article){
			if ($article->getId() == $id)
				return $article->getArticleContent();
		}
		return false;
	}
	
	public function getComSection($id){
		foreach ($this->art_array as $article){
			if ($article->getId() == $id)
				return $article->getComSection();
		}	
	}
	
	public function getNbCom($id){
		foreach ($this->art_array as $article){
			if ($article->getId() == $id)
				return $article->getNbCom();
		}	
	}
	
	public function formatCom($com,$pseudo,$mail){
		return commentaire::formatCom($com,$pseudo,$mail);
	}
	
	public function getCom($id){
		foreach ($this->art_array as $article){
			if ($article->getId() == $id)
				return $article->getCom();
		}
		
	}
	
	public function enregistre_com($com, $pseudo, $mail, $id){
		$article_trouve;	
		foreach ($this->art_array as $article)
		{
			if ($article->getId() == $id){
				$article_trouve = $article;
				break;
			}
		}
		$article->enregistre_com($com,$pseudo,$mail);		
	}
	
	function dd($date) {
		return date("d/m/Y H:i:s",$date);
	}
	
	function js(){
		return '<script src="js/jquery-1.6.4.min.js" type="text/javascript"></script>
		<script type="text/javascript">
		
		function loadXMLDoc(id)
		{
			var xmlhttp;
			article = id;
		if (window.XMLHttpRequest)
		{// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else
		{	// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				//document.getElementById(id).innerHTML=xmlhttp.responseText;
				var myobject ;
				//alert(xmlhttp.responseText);
				myobject = JSON.parse(xmlhttp.responseText);
				//$("#" + article).parent().next("div").append(xmlhttp.request.responseText);
				//alert(myobject.content);
				//alert(myobject.com);
				$("#" + article).parent().next("div").append(myobject.content);
				$("#" + article).parent().next("div").append(myobject.nb_com);
				$("#" + article).parent().next("div").next("div").append(myobject.com);

			}
		}
		xmlhttp.open("GET","ajaxRouteur.php?id=" + id,true);
		xmlhttp.send();
		}
		
		$(document).ready( function()
		{ 
		 $(".resume a").click(function()
		   {	 	
			 loadXMLDoc(this.id);
			 $(this).parent().hide(); 	
		   });
	}); 
		
		
		function enregistre_com(com,pseudo,mail,id){
			var xmlhttp;

		if (!pseudo){
				alert ("pseudo obligatoire!");
				return;
			}
		if (!com){
			alert ("commentaire obligatoire!");
			return;
		}
				
		if (window.XMLHttpRequest)
		{// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp=new XMLHttpRequest();
		}
		else
		{	// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				var myobject ;
				myobject = JSON.parse(xmlhttp.responseText);
				//alert(myobject.com);
				$("#" + myobject.id).parent().next("div").next("div").prepend(myobject.com );
				//var nbcom = $("#" + myobject.id).closest(".article").find(".nb_com").html();
				//alert(myobject.nbcom);
				$("#" + myobject.id).closest(".article").find(".nb_com").replaceWith(myobject.nbcom);
			}
		}
		xmlhttp.open("POST","ajaxRouteur.php",true);
		var params = "id=" + id + "&com=" + encodeURIComponent(com) + "&pseudo=" + encodeURIComponent(pseudo)+ "&mail=" + encodeURIComponent(mail);
		//Send the proper header information along with the request
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send(params);
		
		}

		
	</script>';
		
		
		
	}	
	
		
}




?>
