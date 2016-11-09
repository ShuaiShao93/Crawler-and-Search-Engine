<?php
ini_set('memory_limit', '1000M');
$osuggest = isset($_REQUEST['suggest']) ? $_REQUEST['suggest'] : false;
$results = false;

if ($osuggest):
  include 'PorterStemmer.php';

  $stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

  require_once('Apache/Solr/Service.php');

  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/pagerank/');

  $additionalParameters = array(
      'indent' => 'true',
      'wt' => 'json'
  );
  $words = split_str($osuggest);
  $last = strtolower(array_pop($words));
  try
  {
    $results = $solr->suggest($last, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }

  $suggestions = array();
  $stem_list = array();
  foreach ($results->suggest->suggest as $key => $s) {
    foreach ($s->suggestions as $key => $value) {
        $str = trim(implode(" ", $words)." ".$value->term);
        if (in_array($str, $stopwords))
          continue;
        $base = PorterStemmer::Stem($value->term);
        if (in_array($base, $stem_list)){
          continue;
        }
        else {
          array_push($stem_list, $base);
        }
        array_push($suggestions, $str);
        if (count($suggestions) == 5)
          break;
    }
    if (count($suggestions) == 5)
      break;
  }
  echo json_encode($suggestions);

else:

include 'SpellCorrector.php';

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$oquery = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

if ($oquery)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/pagerank/');


  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $oquery = stripslashes($oquery);
  }
  if ($_REQUEST['prtype'] == "internal_ranking")
    $additionalParameters = array(
      'q.op' => 'AND',
      'indent' => 'true',
      'wt' => 'json'
    );
  else
    $additionalParameters = array(
      'q.op' => 'AND',
      'sort' => 'pageRankFile desc',
      'indent' => 'true',
      'wt' => 'json'
    );

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($oquery, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>SS Solr Client</title>
    <link rel="stylesheet" href="jquery-ui.css">
    <script   src="jquery-2.1.4.js"></script>
    <script   src="jquery-ui.js"></script>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($oquery, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="radio" name="prtype" value="internal_ranking" <?php if (!isset($_REQUEST['prtype']) || $_REQUEST['prtype'] == "internal_ranking") echo "checked"; ?>>Internal Ranking</input>
      <input type="radio" name="prtype" value="page_rank" <?php if (isset($_REQUEST['prtype']) && $_REQUEST['prtype'] == "page_rank") echo "checked"; ?>>Page Rank</input>
      <input type="submit"/>
    </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);

  $words = split_str($oquery);
  $cquery = "";
  $flag = false;
  foreach ($words as $key => $value) {
    $correct = SpellCorrector::correct($value);
    if ($correct != strtolower($value)){
      $flag = true;
    }
    $cquery .= $correct." ";
  }
  $cquery = trim($cquery);
  if ($flag) {echo "<div><p><span style='font-size:18px;'>Did you mean: <a href=\"hw4.php?q=".urlencode($cquery)."&prtype=".$_REQUEST['prtype']."\" style='font-weight:bold;'>$cquery</a></span></div><hr />";} 
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
    $title = $doc->getField('title')? $doc->getField('title')['value'] : "N/A";
    $author = $doc->getField('author')? $doc->getField('author')['value'] : "N/A";
    $date_created = $doc->getField('creation_date')? $doc->getField('creation_date')['value'] : "N/A";
    $size = $doc->getField('stream_size')? $doc->getField('stream_size')['value'] / 1000 .' KB' : "N/A";
    $url = str_replace('|', '/', substr($doc->getField('id')['value'], strpos($doc->getField('id')['value'], 'http')));
    if (substr($url, -5) == ".html") {
      $url = substr($url, 0, -5);
    }
    $res = array(
      'title' => $title,
      'author' => $author,
      'date created' => $date_created,
      'size' => $size
    );
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php
    // iterate document fields / values
    foreach ($res as $field => $value)
    {
?>
          <tr>
            <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <th>link</th>
            <td>
              <a href="<?php echo $url; ?>"><?php echo $url; ?></a>
            </td>
          </tr>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
<script type="text/javascript">
$(function(){
  $('#q').autocomplete({
    source: function(request, response){
      $.get('hw4.php', {suggest: request.term}, function(data) {
          response(data);
      }, 'json');
    }
  });
});
</script>
<?php
endif;

function split_str($str) {
  $words = explode(" ", preg_replace('/\s(?=\s)/', '', trim($str)));
  return $words;
}
?>