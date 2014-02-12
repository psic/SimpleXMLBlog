# What is SimpleXMLBlog ?

This is a bunch of php classes. They provide a blog without no database needs (kind of NoSQL) in order to be easy to install and manage.
All your blog entries and comments are stored in XML files. Blog entries use markdown language.

# Why ?

I will explain

# Demo

I will show!

# How does it work ?

## Installing this blog

Use it as a php class. See below for the simplest sample :

        <html>
    	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    	</head>
    	<body>
    		<? 
    		include './blog.php';
    		$blog = blog::getInstance();
    		$blog->affiche();
    		?>
    	</body>
    	</html>
    	
The blog entries and the comments are stored as XML files. You can change the directory where they are stored in the blog.php file.
    	
    const article_rep ="articles/";
	const com_rep ="comments/";
	
A css file, blog.css is available also.

## Writing your blog

Each of your blog entry is a XML file. See sample below.
        
        <?xml version="1.0" encoding="utf-8"?>
        <XML>
        	<TITRE>My first blog entry</TITRE>
        	<RESUME>this is a short version.</RESUME>
        	<CONTENT>Ceci l'aut est effectivement mon premier article de blog
        		et c'est un test
        	</CONTENT>
        	<FILE_COMMENTS>comments1.xml</FILE_COMMENTS>
        	<VISIBLE>true</VISIBLE>
        	<DATE>28-01-2014</DATE>
        	<TAG>Cool</TAG>
        	<TAG>first</TAG>
        </XML>

Add a new entry ==> create a new file, and it's done. You can use markdown language in the content of your entry. This blog uses php-markdown to handle it.
For each entry, you need to specify a summary because the blog only shows a summary of each entry on first sight, a content (the blog entry itself), a file where the comments will be stored, a date ( to get entries filtered by date) and as many tags as you want.


# Features

## Done

* Parse entry and comments 
* Can add comments
* Can open and close blog entry
* Use markdown 
* Sort entries by date
* Sort comments by date
* Show comments date

## To be done

* Show numbre of commments with the summary (V2)
* Use Tag Filter (V2)
* Use Date Filter (V2)

for all comments send at psic_at_free_fr