<?
require './article.php';
class blog {

	//DONE Faire un Singleton et une classe ajaxrouter qui recupere le requete ajax, fait un getInstance sur le blog et recupere le content d'un article*/
	//DONE finir l'enregistrement des commentaires
	//DONE augementer le nombre de commentaire quand on en rajoute un V1
	//DONE gerer les caracteres hmtl, quote et double quote dans les commentaires V1
	//DONE vider le formulaire apres soumission V1
	//DONE afficher le mail dans les commentaires.
	//DONE Pouvoir "refermer" un commentaire avec une fleche vers le haut, et les reouvrir, etc.. V2
	//==> Mettre un attribut first pour savoir s'il y a besoin de recharger ou pas l'article ou juste de le montrer (unhide)	
	//DONE gerer les DIV et autres  tag html dans les articles  V2 --> markdown
	//GIVEUP gerer les DIV et autres  tag html dans les commentaires V2 --> mardown
	
	//TODO classer les articles par date avant de les afficher V2
	//TODO classer les commentaires par date avant de les afficher V2
	//TODO enregistrer la date et l'heure des commmentaires, et l'afficher aussi V2
	//TODO afficher le nombre de commentaire avec le resume au d�but V2

	//TODO Gere le filtre par TAG V3
	//TODO tester tout �a
	public static $_instance;
	const article_rep ="articles/";
	const com_rep ="comments/";
	private $art_array = array();
	private $tag_array = array();


	private function __clone () {
	}
	private function __construct(){
		$this->xml_parser = xml_parser_create();
		xml_set_object (  $this->xml_parser, $this );
		xml_set_element_handler($this->xml_parser, "startTagArticle", "endTagArticle");

		xml_set_character_data_handler($this->xml_parser, "contentsArticle");

		$dir = opendir(blog::article_rep);
		$i=1;
		while ($f = readdir($dir)) {
			if(is_file(blog::article_rep.$f)) {
				$article = new article();
				$article->init(blog::article_rep.$f,blog::com_rep,$i++);
				array_push($this->art_array,$article);
				if ($article->isVisible() == 'true'){
					foreach ($article->getTags() as $tag){
						if(!in_array($tag,$this->tag_array))
							array_push($this->tag_array,$tag);
					}
				}
			}
		}

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
						myobject = JSON.parse(xmlhttp.responseText);
						$("#" + article).children(".content").append(myobject.content);
						$("#" + article).children(".content").append(myobject.nb_com);
						$("#" + article).children(".comment").append(myobject.com);
	
						}
					}
					xmlhttp.open("GET","ajaxRouteur.php?id=" + id,true);
					xmlhttp.send();
				}

				$(document).ready( function()
				{
					$(".fleche a").click(function()
					{	
						var open = $(this).parent().parent().parent().attr("state");
						var first = $(this).parent().parent().parent().attr("first");	
						var Myid = $(this).parent().parent().parent().attr("id");
							
						if (first == "false"){

							// this aritcle was never open
							if (open == "close"){
				
									// this article is close, open it
									loadXMLDoc(Myid);
									//hide resume
									$(this).parent().parent().parent().children(".resume").hide();
							
									//hide right arrow
									//$(this).parent().parent().children(".fleche").children().remove();
									$(this).parent().parent().children(".fleche").children("[sens=droit]").hide();			
				
									//unhide down arrow to fleche.
									 //$("[id=" + Myid +"]").children(".titre").children(".fleche").append(" <a href=\"#\"><img src=\"./ressource/fleche-bas.png\"/></a>");
									$(this).parent().parent().children(".fleche").children("[sens=bas]").show();					
									
									$("[id=" + Myid +"]").attr("state","open");
									$("[id=" + Myid +"]").attr("first","true");
												
								}
								else{
									// this article is open, close it, hide it, show resume
									// remove down arrow to fleche.
									//$(this).parent().parent().children(".fleche").children().remove();
									$(this).parent().parent().children(".fleche").children("[sens=bas]").hide();
									
									//add right arrow
									// $("[id=" + Myid +"]").children(".titre").children(".fleche").append(" <a href=\"#\"><img src=\"./ressource/fleche-droite.png\"/></a>");
									$(this).parent().parent().children(".fleche").children("[sens=droit]").show();					
				
									//show resume
									$(this).parent().parent().parent().children(".resume").show();
				
									$(this).parent().parent().parent().children(".content").show();
									$(this).parent().parent().parent().children(".comment").show();

									$("[id=" + Myid +"]").attr("state","close");
								}
							}
				        else{
							//this article was open once upon a time,
							if (open == "close"){

									// this article is close, open it, hide resume
									//hide resume
									$(this).parent().parent().parent().children(".resume").hide();
									//remove right arrow
									$(this).parent().parent().children(".fleche").children("[sens=droit]").hide();
											
									//add down arrow to fleche.
									$(this).parent().parent().children(".fleche").children("[sens=bas]").show();	

									$(this).parent().parent().parent().children(".content").show();
									$(this).parent().parent().parent().children(".comment").show();
													
									$("[id=" + Myid +"]").attr("state","open");
								}
							else{	
									// this article is open, close it, hide it, show resume
									// remove down arrow to fleche.
									$(this).parent().parent().children(".fleche").children("[sens=bas]").hide();
													
									//add right arrow
									// $("[id=" + Myid +"]").children(".titre").children(".fleche").append(" <a href=\"#\"><img src=\"./ressource/fleche-droite.png\"/></a>");
									$(this).parent().parent().children(".fleche").children("[sens=droit]").show();					
								
									//show resume
									$(this).parent().parent().parent().children(".resume").show();

									$(this).parent().parent().parent().children(".content").hide();
									$(this).parent().parent().parent().children(".comment").hide();

									$("[id=" + Myid +"]").attr("state","close");
									
							}		
						}
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
				$("#" + myobject.id).parent().next("div").next("div").prepend(myobject.com );
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
