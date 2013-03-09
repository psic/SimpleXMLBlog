<? 
require './blog.php';
		
		
		$blog = blog::getInstance();
		//print_r($_REQUEST);

		if ($_SERVER['REQUEST_METHOD']== 'POST'){
		if (!empty($_POST['com']) || !empty($_POST['id'])){
			$com=$_POST['com'];
			$id=$_POST['id'];
			 $mail="";
			 $pseudo="";
			 $nbcom;
			if (!empty($_POST['pseudo']) )
				$pseudo = $_POST['pseudo'];
				
			if (!empty($_POST['mail'])) 
				$mail = $_POST['mail'];
			
			$comm= $blog->formatCom($com,$pseudo,$mail);

			// on enregistre le commentaire dans le fichier
			$blog->enregistre_com($com,$pseudo,$mail,$id);
			$nbcom = $blog->getNbCom($id);
			$json_array = array('com'=>$comm,'id'=>$id,'nbcom'=>$nbcom);
			$json_string = json_encode($json_array);
			echo $json_string;
		}
	}
		else
		{

			if	(!empty($_GET['id'])){
			$id= $_GET['id'];
			$content = $blog->getContent($id);
			$nb_com = $blog->getComSection($id);
			$com = $blog->getCom($id);
				if ($content != false){
					//echo $content;
					$json_array = array('content'=>$content,'nb_com'=>$nb_com,'com'=>$com);
					//$json_array = array('content'=>$content);
					$json_string = json_encode($json_array);
					echo $json_string;
					}
			}
		}
		
	
		
	

?>
