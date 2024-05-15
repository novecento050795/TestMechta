<?php
namespace vBulletin\Search;

use vBulletin\DB\DB;
use vBulletin\Log\Logger;

class SearchPostService
{
  private $db;

  public function __construct()
  {
    $this->db = new DB('vb_post');
  }

  public function getSearchResultById(int $searchId): array
  {
    return $this->renderSearchResult(
      $this->db
        ->table('vb_searchresult')
        ->where('text', '=', $searchId)
        ->get()
    );
  }

  public function searchByText(string $text): array
  {
    $posts = $this->db
      ->where('text', 'like', $text)
      ->get();

    Logger::log($text);
    return $this->renderSearchResult($posts);
  }

  private function renderSearchResult(array $data): array
  {
    // todo some logic
    foreach($data as $row){
      if ($row['forumid'] != 5){
        SearchResultRenderer::render($row);
      }
    }
    return $data;
  }
}

class SearchResultRenderer
{
  public static function render($row): void
  {
    // todo some logic
  }
}

?>

<?php
namespace vBulletin\DB;

class DB
{
  private $db;
  private $table;
  private $filters;
  private $select = '*';

  public function __contruct(string $table) 
  {
    $this->db = new \PDO(
      "mysql:dbname=" . config('db') . "; host=" . config('db-host'), // Псевдокод db: vbforum db-host: 127.0.0.1
      config('db-user'), // Псевдокод db-user: forum
      config('db-password') // Псевдокод db-password: 123456
    );
    $this->table = $table;
  }

  public function table(string $table): DB
  {
    $this->table = $table;
    return $this;
  }

  public function select(array $columns): DB
  {
    $this->select = implode(', ', $columns);
    return $this;
  }

  public function where(string $column, string $operation, string $value): DB
  {
    $filter = "$column $operation $value";
    $this->filters = $this->filters 
      ? ($this->filters . "and $filter") 
      : "WHERE $filter";
    return $this;
  }

  public function get(): array
  {
    return $this->db
      ->prepare("SELECT $this->select FROM $this->table $this->filters")
      ->fetchAll();
  }

  // todo Больше функций для выборки через связи и orWhere/whereNull/whereNotNull/итд
}

?>

<?php
namespace vBulletin\Log;

class Logger
{

  public static function log(string $data): void
  {
    $file = fopen(config('log-path'), 'a+'); // log-path: /var/www/search_log.txt песвдокод
    fwrite($file, $data . "\n");
  }
}
?>

<?php
// usage Example
// Итоги:
// 1. Привел наименования методов/классов/переменных к общему виду
// 2. Разделил функциональную ответственность по классам БД/логирование/поиск/рендер
// 3. Обезопасил код от неожиданных входных данных
// 4. Вынес полезные функции в сервисы для переиспользования
// 5. Вынес данные бд и путь к логам в конфиги для настроек под разные окружения

use vBulletin\Search\SearchPostService;

$searcher = new SearchPostService();

$searchId = $_REQUEST['searchid'];
$query = $_REQUEST['q'];

$data = $searcher->getSearchResultById($searchId);
$data = $searcher->searchByText($query);


