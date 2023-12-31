<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;
use Database\MySQLWrapper;
use Helpers\Settings;

// このコマンドを実行すると、システムはデータベース全体を削除してデータベース全体をクリアします。オプションの引数を提供して、ワイプの前にバックアップを作成することもできます。

// バックアップを作成するには、MySQL のダンプ機能を使用できます。mysqldump ユーティリティは、MySQL データベースのバックアップを作成するために使用されます。これは、別のサーバまたは同じサーバでデータベースを復元するために使用できる SQL ステートメントを含むファイルを生成するコマンドラインツールです。

// データベースダンプを作成するには、mysqldump -u username -p dbname > backup.sql、データベースダンプから復元するには、mysql -u username -p dbname < backup.sql を使用してください。

class DBWipe extends AbstractCommand
{
  // コマンド名を設定
  protected static ?string $alias = 'db-wipe';

  //  引数を割り当て
  public static function getArguments(): array
  {
    return [
      (new Argument('backup'))->setDescription('Backup Database.')->setRequired(false)->setAllowAsShort(true)
    ];
  }

  public function execute(): int
  {
    $backup = $this->getArgumentValue('backup');
    if ($backup) {
      $this->backupDB();
    }

    $this->dropTables();

    return 0;
  }

  private function backupDB()
  {
    $mysqli = new MySQLWrapper();
    $dbName = $mysqli->getDatabaseName();
    $mysqli->close();

    $filename = sprintf(
      '%s_%s_%s.sql',
      date('Y-m-d'),
      time(),
      $dbName
    );

    $backupFile = sprintf("%s/../../Database/Backups/%s", __DIR__, $filename);

    // セキュリティ上の問題がないか知りたい。
    // TODO: 例外処理
    $username = Settings::env("DATABASE_USER");
    $password = Settings::env("DATABASE_USER_PASSWORD");
    $sqlRestore = "mysqldump -u $username -p$password $dbName > $backupFile";
    exec($sqlRestore);
    $this->log("Completed backup $dbName .");
  }

  private function dropTables()
  {
    $mysqli = new MySQLWrapper();

    $sqlShowTables = 'SHOW TABLES;';

    $resultShowTables = $mysqli->query($sqlShowTables);

    $databaseName = $mysqli->getDatabaseName();

    if ($resultShowTables->num_rows > 0) {
      while ($row = $resultShowTables->fetch_assoc()) {
        $tableName = $row['Tables_in_' . $databaseName];
        // echo $tableName . PHP_EOL;
        $sqlDropTable = "DROP TABLE $tableName";
        $mysqli->query($sqlDropTable);
        $this->log("$tableName table deleted.");
      }
    } else {
      $this->log("Table does not exist in $databaseName .");
    }

    $mysqli->close();
  }
}
