<?php
/*
Plugin Name: YouTube Poster
Plugin URI: http://www.BlogsEye.com
Description: Reads a YouTube rss feed and posts the videos as blog entries
Version: 0.7
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// this will not run, cannot activate and should not even load if the php version is not 5.0 or greater
if (phpversion()<"5") 
wp_die( sprintf('Your server is running PHP version %1$s but the youtube-poster plugin requires at least %2$s.', phpversion(), '5.0' ));

// need to have simplexml as a loaded extension or we can't run
if ( !extension_loaded('simplexml') )
 	wp_die('Your PHP installation appears to be missing the SimpleXML extension which is required by the youtube-poster plugin.');





  
/************************************************************
*	kpg_YouTube_poster_admin_menu()
*	Adds the admin menu
*************************************************************/
function kpg_YouTube_poster_admin_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('YouTube Poster', 'YouTube Poster', 'manage_options', 'YouTubePoster','kpg_YouTube_poster_control');
	}
}
function kpg_YouTube_poster_dopostings_menu() {
	// the dopostings is only done if the user has entered his YouTube access key
	$ytOptions=get_option('kpg_YouTube_poster_options');
	if ($ytOptions==null) $ytOptions=array();
	$ytkey='';
	if (array_key_exists('ytkey',$ytOptions)) $ytkey=$ytOptions['ytkey'];
	if ($ytkey=='') return;
	add_posts_page('Add YouTube Posts', 'Add YouTube Posts', 'manage_options','AddYouTubePosts', 'kpg_add_YouTube_dopostings_control');
}


// add the the options to the admin menu
add_action('admin_menu', 'kpg_YouTube_poster_admin_menu');
add_action('admin_menu', 'kpg_YouTube_poster_dopostings_menu');

// uninstall routines

function kpg_YouTube_poster_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	delete_option('kpg_YouTube_poster_options'); 
	return;
}
if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'kpg_YouTube_poster_uninstall');
}

// need to ask users for their YouTube key so that they can access YouTube
function kpg_YouTube_poster_control() {
    if (phpversion()<'5') {
		echo "You need PHP version 5 and SimpleXML to run this plugin<br/>";
		return;
	}
	$mess="";
	$ytOptions=get_option('kpg_YouTube_poster_options');
	if ($ytOptions==null) $ytOptions=array();
	$ytkey='';
	// check to see if there is a form POST to this function
	if (array_key_exists('kg_ytkey',$_POST)) {
		$ytkey=$_POST['kg_ytkey'];
		$ytOptions['ytkey']=$ytkey;
		update_option('kpg_YouTube_poster_options', $ytOptions);
		$mess="<h4>Changes Saved</h4>";
	} else {
	}
	if (array_key_exists('ytkey',$ytOptions)) $ytkey=$ytOptions['ytkey'];
?>
<div class="wrap">
<h2>YouTube-Poster Options </h2>
<?php echo $mess; ?>	
<form method="post" action="" name="DOIT" >
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="submit,kpg_ytkey" />

<?php wp_nonce_field('update-options'); ?>
<p>
In order to access the YouTube videos you need to have a YouTube developer key. <a href="http://code.google.com/apis/youtube/2.0/reference.html" target="_blank">You can get one at 
YouTube.</a>
</p>
<p>
Enter your YouTube Developer Key: <input name="kg_ytkey" type="text" value="<?php echo $ytkey; ?>" />
</p>

<p class="submit">
<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
<?PHP

	if (array_key_exists('ytkey',$ytOptions)) {
	$bname=urlencode(get_bloginfo('name'));
	$burl=urlencode(get_bloginfo('url'));
	$bdesc=urlencode(get_bloginfo('description'));
?>
<p>
Now that you have entered your developer key you can post YouTube videos to your blog. The entry is under Posts on the admin menu.
</p>
<hr/>
<h3>If you like this plugin, why not try out these other interesting plugins.</h3>
<?php
// list of plugins
$p=array(
"facebook-open-graph-widget"=>"The easiest way to add a Facebook Like buttons to your blog' sidebar",
"threat-scan-plugin"=>"Check your blog for virus, trojans, malicious software and other threats",
"open-in-new-window-plugin"=>"Keep your surfers. Open all external links in a new window",
"youtube-poster-plugin"=>"Automagically add YouTube videos as posts. All from inside the plugin. Painless, no heavy lifting.",
"permalink-finder"=>"Never get a 404 again. If you have restructured or moved your blog, this plugin will find the right post or page every time",
);
  $f=$_SERVER["REQUEST_URI"];
  // get the php out
  $ff=explode('page=',$f);
  $f=$ff[1];
  $ff=explode('/',$f);
  $f=$ff[0];
  foreach ($p as $key=>$data) {
	if ($f!=$key) { 
	$kk=urlencode($key);
		?><p>&bull;<span style="font-weight:bold;"> <?PHP echo $key ?>: </span> <a href="plugin-install.php?tab=plugin-information&plugin=<?PHP echo $kk ?>&TB_iframe=true&width=640&height=669">Install Plugin</a> - <span style="font-style:italic;font-weight:bold;"><?PHP echo $data ?></span></p><?PHP 
	}
  }
?>


<hr/>
<p>
<?PHP
	}
?>

<br/>
Version 0.5 April 1, 2010

</div>
		
<?PHP
}
// now the rest of the logic
function kpg_add_YouTube_dopostings_control() {
    if (phpversion()<'5') {
		echo "You need PHP version 5 and SimpleXML to run this plugin<br/>";
		return;
	}
	// in control there are three separate sections
    // call each (just to keep the structure here easy to maintain)
	// just a quick check to keep out the riff-raff
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	// look for parameters
	
	$kg_date="";
	$kg_post="";
	$kg_publish="";
	$ytOptions=get_option('kpg_YouTube_poster_options');
	if ($ytOptions==null) {
		$ytOptions=array();
	}
	$ytkey='';
	if (array_key_exists('ytkey',$ytOptions)) $ytkey=$ytOptions['ytkey'];
	if ($ytkey=='') return;
	if (array_key_exists('kg_date',$_POST)) $kg_date=$_POST['kg_date'];
	if (array_key_exists('kg_post',$_POST)) $kg_post=$_POST['kg_post'];
	if (array_key_exists('kg_publish',$_POST)) $kg_publish=$_POST['kg_publish'];
	//echo "In control 1: $kp_date, $kg_post, $kg_publish<br/>"; 
	if ($kg_date!=""||$kg_post!=""||$kg_publish!="") {
		$ytOptions['kg_date']=$kg_date;
		$ytOptions['kg_post']=$kg_post;
		$ytOptions['kg_publish']=$kg_publish;
		update_option('kpg_YouTube_poster_options', $ytOptions);
	}
	if (array_key_exists('kg_date',$ytOptions)) $kg_date=$ytOptions['kg_date'];
	if (array_key_exists('kg_post',$ytOptions)) $kg_post=$ytOptions['kg_post'];
	if (array_key_exists('kg_publish',$ytOptions)) $kg_publish=$ytOptions['kg_publish'];
	//echo "In control 2: $kp_date, $kg_post, $kg_publish<br/>"; 

	// check to see whare we are:
	// each form has a different submit button
	$sub1=''; // search form button
	if (array_key_exists('kg_sub1',$_POST)) $sub1=$_POST['kg_sub1'];
	// the next form has an array of YouTube Poster
	$sub2=''; // post form button
	if (array_key_exists('kg_sub2',$_POST)) $sub2=$_POST['kg_sub2'];
	// everybody uses $ytkey
	if ($sub1!='') {
		// if sub1 is pressed we have a list of postings that need to be seleted
		kpg_add_YouTube_select($ytkey,$kg_date,$kg_post,$kg_publish);
	} else if ($sub2!='') {
		// if sub2 is pressed we have an array of YouTube posts to insert.
		kpg_add_YouTube_dopostings($ytkey,$kg_date,$kg_post,$kg_publish);
	}
	// in any case we can do the YouTube sarch
	kpg_add_YouTube_search();
	
}

function kpg_add_YouTube_dopostings($ytkey,$kg_date,$kg_post,$kg_publish) {
	// parameters are kept in an array of arrays
	// now get any post options to see if the user has been trying to do some posts
	// there are two possible forms. One has the array of items to post to pages.
	// one form has the search parameters.
	// process the array coming in from form2
	

	$addme=$_POST['kg_addme'];
	$posts=$_POST['kg_posts'];
	$excerpts=$_POST['kg_excerpts'];
	$vidcount=count($addme);
	for ($k=0;$k<count($addme);$k++) {
		$j=$addme[$k];
		// this is where we insert the post
		$post=$posts[$j];
		$post=gzinflate(base64_decode($post)); 
		$post=unserialize($post);
		// we now have a post. see if we can put it into the database
		// see if the url exists
		if ($kg_date=='now') {
			unset($post['post_date']);
		}
		if ($kg_publish!='publish') {
			$post['post_status']='draft';
		}
		if ($kg_post!='post') {
			$post['post_type']='page';
		}
		
		wp_insert_post($post);
		echo "Added: ".$post['post_title']."<br/>";
	}
	echo "<h4>added $vidcount videos</h4>";
	echo "<hr/>";	
}

function kpg_add_YouTube_search() {
// display the form to select YouTubes
	$wpcats = get_categories('hide_empty=0'); // Wordpress category list
	
	$ytcats=array(
		'Film'=>'Film &amp; Animation',
		'Autos'=>'Autos &amp; Vehicles',
		'Music'=>'Music',
		'Animals'=>'Pets &amp; Animals',
		'Sports'=>'Sports',
		'Travel'=>'Travel &amp; Events',
		'Games'=>'Gaming',
		'Comedy'=>'Comedy',
		'People'=>'People &amp; Blogs',
		'News'=>'News & Politics',
		'Entertainment'=>'Entertainment',
		'Education'=>'Education',
		'Howto'=>'Howto &amp; Style',
		'Nonprofit'=>'Nonprofits &amp; Activism',
		'Tech'=>'Science &amp; Technology'
	);
	$ytOptions=get_option('kpg_YouTube_poster_options');
	if ($ytOptions==null) $ytOptions=array();

	$searchHistory=array();
	if (array_key_exists('searchHistory',$ytOptions)) $searchHistory=$ytOptions['searchHistory'];

?>
<div class="wrap">
<h2>YouTube-posts Options </h2>
<form method="post" action="" name="DOIT3" >
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="Sub1,kpg_yt_wpcat,kpg_yt_ytcat,kpg_yt_q" />
<input type="hidden" name="yt_add_action" id="yt_add_action" value="" />

 YouTube category: <select name="kg_ycat">
<?php
	foreach($ytcats as $key=>$value) {
		echo "<option value=\"$key\" >$value</option>";
    }
?>		
</select>	
<br/>
Wordpress Post Categorey <select name="kg_cat">
<?php
	foreach($wpcats as $key=>$value) {
		//$cat->category_nicename
		//$cat->cat_name
		//$cat->cat_ID
		$nname=$value->category_nicename;
		$cid=$value->cat_ID;
		echo "<option value=\"$cid\" >$nname</option>";
    }
?>		
</select>	
<br/>
Search String <input type="text" name="kg_search"/></td>

<p class="submit">
<input type="submit" name="kg_sub1" class="button-primary" value="Search for Tubes" />
</p>
</form>
<?php
if (count($searchHistory)>0) {
?>
<hr/>
<script type="text/javascript">
	// <!--

function setSearch() {
	var sel2=document.DOIT2.kg_history.selectedIndex;
	if (sel2>=0) {
		// need to get the value
		var v2=document.DOIT2.kg_history[sel2].value;
		var a2=v2.substr(0,v2.indexOf('|'));
		v2=v2.substr(v2.indexOf('|')+1);
		var b2=v2.substr(0,v2.indexOf('|'));
		v2=v2.substr(v2.indexOf('|')+1);
		var c2=v2;
		document.DOIT3.kg_search.value=a2;
		var j=0;
		for (j=0;j<document.DOIT3.kg_cat.length;j++) {
			if (document.DOIT3.kg_cat[j].value==b2) document.DOIT3.kg_cat.selectedIndex=j;
		}
		for (j=0;j<document.DOIT3.kg_ycat.length;j++) {
			if (document.DOIT3.kg_ycat[j].value==c2) {
				document.DOIT3.kg_ycat.selectedIndex=j;
			}
		}
	}
	return false;
}
	// -->
</script>
<?PHP
	// display the form
    if (count($searchHistory)>0) {
?>
<p>You can select a previous search here.<br/>
<form method="post" action="" name="DOIT2" >
<select name="kg_history" onchange="setSearch();">
<option value='||'>  </option><?PHP
foreach ($searchHistory as $key=>$sh) {
	$search=$sh['search'];
	$search=str_replace("'",'&apos;',$search);
	$cat=$sh['cat'];
	$ycat=$sh['ycat'];
	$kk=$search.'|'.$cat.'|'.$ycat; // fix for history bug - take out someday
	if ($search!='') 
		echo "<option value='$kk'>$cat, $ycat, $search</option>";
}
?>
</select>
</p>

</form>
<hr/>
<?php
	}
}
?>
</div>
<?PHP
}


// this goes out and gets the YouTube list and displays the choices on the screen
function kpg_add_YouTube_select($ytkey,$kg_date,$kg_post,$kg_publish) {
	// we have been hit from the search button
	
	$ytOptions=get_option('kpg_YouTube_poster_options');
	
	$searchHistory=array();
	if (array_key_exists('searchHistory',$ytOptions)) $searchHistory=$ytOptions['searchHistory'];
	$searches=array();
	if (array_key_exists('searches',$ytOptions)) $searches=$ytOptions['searches'];
	// get the search values from post
	$search=''; // search keywords
	$cat=''; // blog catagory
	$ycat=''; // YouTube catagory
	if (array_key_exists('kg_search',$_POST)) $search=$_POST['kg_search'];
	if (array_key_exists('kg_cat',$_POST)) $cat=$_POST['kg_cat'];
	if (array_key_exists('kg_ycat',$_POST)) $ycat=$_POST['kg_ycat'];

	
	$search=stripslashes($search);
	$cat=stripslashes($cat);
	$ycat=stripslashes($ycat);
	// save this search in the stored array of prior searches
	// create a key for the search
	$skey=$search.'|'.$cat.'|'.$ycat;

	if (!array_key_exists($skey,$searchHistory) && $search!='') {
		// add the key to beginning of the array
		$x=array();
		$x['search']=$search;
		$x['cat']=$cat;
		$x['ycat']=$ycat;
		while (count($searchHistory)>=30) {
			$keys = array_keys($searchHistory);
			$key = array_shift($keys);
			unset($searchHistory[$key]);
		}
		$searchHistory[$skey]=$x;
		// save the updates
		$ytOptions['searchHistory']=$searchHistory;
		//$ytOptions['searchHistory']=array();// do once for testing
		update_option('kpg_YouTube_poster_options', $ytOptions);
	}
	$searchResults=kpg_YouTube_poster_search_YouTube($ytkey,$search,$cat,$ycat);
	// remove the elements that already have YouTube posts
	$searchResults=kpg_checkTubes($searchResults);
	// returns an array of all the things we need to know about a YouTube video
	kpg_displayTubes($searchResults,$kg_date,$kg_post,$kg_publish);
}


function kpg_displayTubes($searchResults,$kg_date,$kg_post,$kg_publish) {
    // get the 
	// show the form
?>

<div class="wrap">
<script type="text/javascript">
	// <!--
	function checkOnOff() {
		var allbox=document.DOIT4.checkall;
		var checks=document.getElementsByName("kg_addme[]");
		var j=0;
		for (j=0;j<checks.length;j++) {
			if (document.DOIT4.checkall.checked==true) {
				checks[j].checked=true;
			} else {
				checks[j].checked=false;
			}
		}
		return false;
	}
	// -->
</script>
<h2>YouTube-Poster Select Videos to Post </h2>
	
<form method="post" action="" name="DOIT4" >
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="kg_sub2,kpg_ytkey" />

<?php wp_nonce_field('update-options'); ?>
<p>Select Videos  &nbsp;&nbsp;(check all: <input type="checkbox" name="checkall" value="" onclick="checkOnOff();" />)</p>
<table>
<tr>
<?php
	$j=0;
	$cols=0;
	foreach ($searchResults as $entry) {?>

    <td>
		<input type="checkbox" name="kg_addme[]" value="<?php echo $j; ?>" />
		<input type="hidden" name="kg_posts[]" value="<?php echo $entry['post']; ?>" />
		<input type="hidden" name="kg_excerpts[]" value="<?php echo urlencode($entry['excerpt']); ?>" />
	</td>
    <td>
	<?php echo $entry['excerpt']; ?>	
	</td>
	<?PHP
	  $cols++;
	  if ($cols>2) {
		$cols=0;
	  ?></tr><tr><?php
	  } ?>
<?php
	$j++;
	}
	//,$kg_date,$kg_post,$kg_publish
?>
</tr>
</table>
<fieldset style="border:medium blue solid;padding:4px;width:320px"><legend>Post/Page Options</legend>
<fieldset style="border:thin black solid;padding:4px;"><legend>Date of Post</legend>

<input name="kg_date" type="radio" value="now" <?PHP if ($kg_date=='now'||$kg_date=='') echo 'checked="1"' ?> />Use current date and time as post date<br/>
<input name="kg_date" type="radio" value="post-date" <?PHP if ($kg_date=='post-date') echo 'checked="1"' ?> />Use the video upload date as the post date
</fieldset>
<fieldset style="border:thin black solid;padding:4px;"><legend>Post or Page</legend>
<input name="kg_post" type="radio" value="page" <?PHP if ($kg_post=='page'||$kg_post=='') echo 'checked="1"' ?>/>Publish tubes as a page<br/>
<input name="kg_post" type="radio" value="post" <?PHP if ($kg_post=='post') echo 'checked="1"' ?> />Publish tubes as a post
</fieldset>
<fieldset style="border:thin black solid;padding:4px;"><legend>Publish or Draft</legend>
<input name="kg_publish" type="radio" value="draft"  <?PHP if ($kg_publish=='draft'||$kg_publish=='') echo 'checked="1"' ?>/>Draft<br/>
<input name="kg_publish" type="radio" value="publish" <?PHP if ($kg_publish=='publish') echo 'checked="1"' ?>  />Publish now
</fieldset>

</fieldset>

<p class="submit">
<input type="submit" name="kg_sub2" class="button-primary" value="Submit posts" />
</p>
</form>
<br/>
<hr/>
</div>
<?PHP

}
// check wordpress for YouTube videos already existing as posts.
function kpg_checkTubes($searchResults) {
	// I will be using the time parameter to check and see if a post has been published
	foreach ($searchResults as $entry) {
		// $entry has the post
		$id=$entry['id'];
		$murl=$entry['murl'];
		if (kpg_check_post($murl)) {
			// remove from the array
			//echo "not found</br>";
			unset($searchResults[$id]);
		}
	
	}
	return $searchResults;
}
function kpg_check_post($murl) {
	global $wpdb; // useful db functions
	$murl=mysql_real_escape_string($murl);
	$sql="SELECT count(*) as CNT FROM ".$wpdb->posts." WHERE instr(POST_CONTENT,'$murl')>0";
	$row=$wpdb->get_row($sql);
	if ($row) {	
	   $CNT=$row->CNT;
	   if ($CNT>0) return true;
	} else {
		echo "<br/>error selecting row in check post $sql<br/>";
	}
	return false;
}
function kpg_YouTube_poster_search_YouTube($ytkey,$ssearch,$cat,$ycat) {
	// this goes out to YouTube and finds the videos.
	$s=str_replace('  ',' ',trim($ssearch));
	$s=urlencode($s);	//blues+(harmonica+OR+harp)+(lesson+OR+instructional)+-halo+-pan
	$r='http://gdata.youtube.com/feeds/api/videos?q='.$s.'&max-results=50&category='.$ycat.'&orderby=published&key='.$ytkey;
	
	/*
		Make these an option setting?
		# relevance – Entries are ordered by their relevance to a search query. 
		This is the default setting for video search results feeds.
		# published – Entries are returned in reverse chronological order. 
		This is the default value for video feeds other than search results feeds.
		# viewCount – Entries are ordered from most views to least views.
		# rating – Entries are ordered from highest rating to lowest rating.
	
	*/
	$rssfile=kpg_ytp_getafile($r);
	// just for now let's print to see what we need
	//$rssfile=str_replace('><',">\r\n<",$rssfile);
	$rssfile=str_replace('media:',"media_",$rssfile);
	$rssfile=str_replace('yt:',"yt_",$rssfile);
	//print_r($rssfile);
	$a=simplexml_load_string($rssfile); 
	$pcnt=0;
	// we have an rss file
	$ansa=array();

	foreach ($a->entry as $entry) {
		$ent=array();
		// get the general info data
		//$link=$entry->link;
		$authorname=$entry->author->name;
		$authoruri=$entry->author->uri;
		//<published>2009-11-01T22:25:23.000Z</published>
		$published=$entry->published;
		$pdate=strtotime($published);
		$ddate=date('Y-m-d H:i:s',$pdate);
		// media_group has the specific data
		$media=$entry->media_group;
		// next the stuff from the actual media_content to include on the page
		$murl=$media->media_content['url'];
		$link=$media->media_player['url'];
		$type=$media->media_content['type'];
		$medium=$media->media_content['medium'];
		$isDefault=$media->media_content['isDefault'];
		$expression=$media->media_content['expression'];
		$duration=$media->media_content['duration'];
		$yt_format=$media->media_content['yt_format'];
		// now the other params
		$keywords=$media->media_keywords;
		$media_description=$media->media_description; // long description
		$media_title=$media->media_title; // short description
		$media_thumbnail=$media->media_thumbnail['url'];
		// now we need the YouTube id in order to keep this unique on the db
		$ss=explode('/v/',$murl);
		$ss=explode('?',$ss[1]);
		$id=$ss[0];
		$content="<p>$media_description</p><p style=\"align:center;\"><object width=\"425\" height=\"344\"><param name=\"movie\" value=\"".$murl."\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowScriptAccess\" value=\"always\"></param><embed src=\"".$murl."\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" allowScriptAccess=\"always\" width=\"425\" height=\"344\"></embed></object></p>
		<p>Author: <a href=\"".$authoruri."\">$authorname</a><br/>
		Duration: $duration<br/>
		Published: $ddate<br/>
		<a href=\"".$link."\">$media_title</a></p>";
		$excerpt="<p><a href=\"".$link."\" target=\"_blank\"><img src=\"".$media_thumbnail."\"/><br/>".$media_title.'</a></p>';
		// format a sample page
		// we want to save all of the things we need in the post if we want it
		
		// serialize the post instead and store it
		// only things uses are the ddate, the excerpt and the post. The rest is just fuel for the post insert.
		$post = array( 
			'post_category' => array($cat), //Add some categories. an array()???
			'post_content' => $content, //The full text of the post.
			'post_date' => $ddate, //[ Y-m-d H:i:s ] //The time post was made.
			'post_excerpt' => $excerpt, //For all your post excerpt needs.
			//'post_name' =>$media_title, // The name (slug) for your post changed from $id
			'post_status' =>'publish', //Set the status of the new post. 
			'post_title' => htmlentities($media_title), //The title of your post.
			'post_type' => 'post' //Sometimes you might want to post a page.
		);  
		// post can be too large so 
		$post=serialize($post);
		$post=base64_encode(gzdeflate($post)); // use gzdecode and base64_decode to undo it
        
		$ent['post']=$post;
		$ent['excerpt']=$excerpt;
		$ent['id']=$id;
		$ent['murl']=$murl;
		// now add to the answer
		$ansa[$id]=$ent;
	}

	return $ansa;	
}
function kpg_ytp_getafile($f) {
   // uses fopen or curl depending on "allow_url_fopen = On" being avaialble
   //first test to see if the ini option allow_url_fopen is on or off
	if (ini_get('allow_url_fopen')) {
		// returns the string value of a file using 
		$rssfile=file($f);
		$rssfile=implode("\n",$rssfile); // in case it is now one long string
		return $rssfile;
	}
	// try using curl instead to see if it works
    $ch = curl_init($f);
	// Set cURL options
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	$ansa = curl_exec($ch);
	curl_close($ch);
	return $ansa;
}


?>