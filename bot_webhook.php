<?php
//website url for calling telegram api methods, add api methods to end of this string to perform those functions
$methods_url = 'https://api.telegram.org/bot1106142975:AAHNqv11YBW2N9UkmCmBQ9CVCkX1b-x4Am4/';

//get the post request send from the bot and load it to variable
$php_header_input = file_get_contents('php://input');
//decode the post request to json format
$json_array = json_decode($php_header_input, true);
//separating each arrays of the json to its own variables for each access
$json_array_message = $json_array['message'];
$json_array_message_from = $json_array_message['from'];
$json_array_message_chat = $json_array_message['chat'];
$entities = $json_array_message['entities'];

//extracting all the relevant information from the entire json string for easy access
$message_id = $json_array_message['message_id'];
$message_date = $json_array_message['date'];
$message_text = $json_array_message['text'];

$f_id = $json_array_message_from['id'];
$f_fname = $json_array_message_from['first_name'];
$f_lname = $json_array_message_from['last_name'];
$f_username = $json_array_message_from['username'];

$chat_id = $json_array_message_chat['id'];
$chat_fname = $json_array_message_chat['first_name'];
$chat_lname = $json_array_message_chat['last_name'];
$chat_username = $json_array_message_chat['username'];
$chat_type = $json_array_message_chat['type'];

$entities_type = $entities[0]['type'];

//sends the message to the bot that calls this php files
function sendMessage($message){
	global $methods_url;
    global $chat_id;
	$url = file_get_contents($methods_url.'sendmessage?chat_id='.$chat_id.'&text='.$message);
}

//sends the photo message to the bot that calls this php file
function sendPhoto($photo_url, $caption){
	global $methods_url;
    global $chat_id;
	$url = file_get_contents($methods_url.'sendPhoto?chat_id='.$chat_id.'&photo='.$photo_url.'&caption='.$caption);
}

//display all the variable values for debugging purpose
function displayAllDetails(){
	global $message_id;
	global $message_date;
	global $message_text;

	global $f_id;
	global $f_fname;
	global $f_lname;
	global $f_username;

	global $chat_id;
	global $chat_fname;
	global $chat_lname;
	global $chat_username;
	global $chat_type;
	
	global $entities_type;
	
	sendMessage('message_id : '.$message_id);
	sendMessage('message_date : '.$message_date);
	sendMessage('message_text : '.$message_text);
	
	sendMessage('f_id : '.$f_id);
	sendMessage('f_fname : '.$f_fname);
	sendMessage('f_lname : '.$f_lname);
	sendMessage('f_username : '.$f_username);
	
	sendMessage('chat_id : '.$chat_id);
	sendMessage('chat_fname : '.$chat_fname);
	sendMessage('chat_lname : '.$chat_lname);
	sendMessage('chat_username : '.$chat_username);
	sendMessage('chat_type : '.$chat_type);
	
	sendMessage('entities_type : '.$entities_type);
}

//extract corona details from website
function extractCoronaDetails(){
	include('simple_html_dom.php');
	$health_website = 'https://www.health.govt.nz/our-work/diseases-and-conditions/covid-19-novel-coronavirus';
	$html = file_get_html($health_website);
	$list = $html->find('div[class="col-sm-8 col-xs-12"]',0);
	$case_numbers = $list->find('p',0);
	$replaced = urlencode(str_replace('&nbsp;',' ',$case_numbers->plaintext));
	sendmessage($replaced.'%0A%0ASource: '.$health_website);
}

//extract all movies from yts
function displayTopMoviesFromYts(){
	include('simple_html_dom.php');
	$piratebay_website = 'https://yts.mx/trending-movies';
	$html = file_get_html($piratebay_website);
	$movies = $html->find('div[class="browse-movie-wrap col-xs-10 col-sm-4 col-md-5 col-lg-4"]');
	foreach($movies as $movie){
		$movie_link = $movie->find('a',1)->href;
		$img_url = $movie->find('img[class="img-responsive"]',1)->src;
		$movie_year = $movie->find('div[class="browse-movie-year"]',0)->plaintext;
		$movie_rating = $movie->find('h4',0)->plaintext;
		$movie_title = $movie->find('a',1)->plaintext;
		
		$caption = 'Title: '.$movie_title.'%0AYear: '.$movie_year.'%0ARating: '.$movie_rating.'%0ALink: '.$movie_link;
		
		sendPhoto($img_url, $caption);
	}
}

//search for a particualr keyword in yts movies
function searchFromYts($movie_name){
	include('simple_html_dom.php');
	$piratebay_website = 'https://yts.mx/browse-movies/pirates/all/all/0/latest/0/all';
	$html = file_get_html('$piratebay_website');
	$movies = $html->find('div[class="browse-movie-wrap col-xs-10 col-sm-4 col-md-5 col-lg-4"]');
	sendMessage($movie_name);
}

//process command to get each arguments
$arguments = explode(' ',$message_text);

//switch between each bot command
switch($arguments[0]){
	case '/start':
		sendMessage("Hi, I can get you relevant information that is relevant to modern times and keeps updating as times changes. To list the set of commands type '/'.");
	break;
	case '/corona':
		extractCoronaDetails();
	break;
	case '/movies':
		displayTopMoviesFromYts();
	break;
	case '/searchmovie':
		global $arguments;
		$args = '';
		for($i=1;$i<count($arguments);$i++){
			$args = $args.' '.$arguments[$i];
		}
		searchFromYts('pirates');
	break;
	case '/debug':
		displayAllDetails();
	break;
	default:
		sendMessage('Command Not Recognised');
	break;
}

?>