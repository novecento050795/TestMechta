<?php
namespace vBulletin\Search;
class Search {
  public static function doSearch(): void {
    if ($_REQUEST['searchid']){
      $_REQUEST['do'] = 'showresults';
    } elseif (!empty($_REQUEST['q'])) {
      $_REQUEST['do'] = 'process';
      $_REQUEST['query'] = &$_REQUEST['q'];
    }
    $db = new \PDO('mysql:dbname=vbforum;host=127.0.0.1', 'forum', '123456');
    if ($_REQUEST['do'] == 'process') {
      $sth = $db->prepare('SELECT * FROM vb_post WHERE text like ?');
      $sth->execute(array($_REQUEST['query']));
      $result = $sth->fetchAll();
      self::renderSearchResult($result);
      $file = fopen('/var/www/search_log.txt', 'a+');
      fwrite($file, $_REQUEST['query'] . "\n");
    } elseif ($_REQUEST['do'] == 'showresults') {
      $sth = $db->prepare('SELECT * FROM vb_searchresult WHERE searchid = ?');
      $sth->execute(array($_REQUEST['searchid']));
      $result = $sth->fetchAll();
      self::renderSearchResult($result);
    }
    else {
      echo "<h2>Search in forum</h2><form><input name='q'></form>";
    }
  }
  public static function renderSearchResult($result) {
    global $render;

    foreach($result as $row){
      if ($row['forumid'] != 5){
        $render->render_searh_result($row);
      }
    }
  }
}

// 1. 