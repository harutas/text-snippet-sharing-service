<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;
use Exception;

// コードを生成します。新しいコマンドを生成し、新しいマイグレーションを生成する 2 つの機能が利用できます。
class CodeGeneration extends AbstractCommand
{
  // 使用するコマンド名を設定します
  protected static ?string $alias = 'code-gen';
  protected static bool $requiredCommandValue = true;

  // 引数を割り当てます
  public static function getArguments(): array
  {
    return [
      (new Argument('name'))->setDescription('Name of the file that is to be generated.')->setRequired(false),
    ];
  }

  public function execute(): int
  {
    $codeGenType = $this->getCommandValue();
    $this->log('Generating code for.......' . $codeGenType);

    if ($codeGenType === 'migration') {
      $migrationName = $this->getArgumentValue('name');
      try {
        $this->generateMigrationFile($migrationName);
      } catch (Exception $e) {
        $this->log($e);
      }
    } else if ($codeGenType === 'command') {
      $commandName = $this->getArgumentValue('name');
      try {
        $this->generateCommandFile($commandName);
        $this->registerCommand($commandName);
      } catch (Exception $e) {
        $this->log($e);
      }
    }

    return 0;
  }

  private function generateMigrationFile(string $migrationName): void
  {
    $filename = sprintf(
      '%s_%s_%s.php',
      date('Y-m-d'),
      time(),
      $migrationName
    );

    $migrationContent = $this->getMigrationContent($migrationName);

    // 移行ファイルを保存するパスを指定します
    $path = sprintf("%s/../../Database/Migrations/%s", __DIR__, $filename);

    file_put_contents($path, $migrationContent);
    $this->log("Migration file {$filename} has been generated!");
  }

  private function getMigrationContent(string $migrationName): string
  {
    $className = $this->pascalCase($migrationName);

    return <<<MIGRATION
<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class {$className} implements SchemaMigration
{
    public function up(): array
    {
        // マイグレーションロジックをここに追加してください
        return [];
    }

    public function down(): array
    {
        // ロールバックロジックを追加してください
        return [];
    }
}
MIGRATION;
  }

  private function generateCommandFile($commandName): void
  {
    $filename = sprintf(
      '%s.php',
      $commandName
    );

    $commandContent = $this->getCommandContent($commandName);

    // 移行ファイルを保存するパスを指定します
    $path = sprintf("%s/../../Commands/Programs/%s", __DIR__, $filename);
    if (!file_exists($path)) {
      file_put_contents($path, $commandContent);
      $this->log("Command file {$filename} has been generated!");
    } else {
      throw new Exception("$filename already exists.", 1);
    }
  }

  private function getCommandContent(string $commandName): string
  {
    $className = $this->pascalCase($commandName);

    return <<<COMMAND
<?php

namespace Commands\Programs;
    
use Commands\AbstractCommand;
use Commands\Argument;
    
class {$className} extends AbstractCommand
{
    // TODO: エイリアスを設定してください。
    protected static ?string \$alias = 'INSERT COMMAND HERE';

    // TODO: 引数を設定してください。
    public static function getArguments(): array
    {
        return [];
    }

    // TODO: 実行コードを記述してください。
    public function execute(): int
    {
        return 0;
    }
}
COMMAND;
  }

  public function registerCommand($commandName): void
  {
    $registryFilePath = sprintf("%s/../../Commands/registry.php", __DIR__);
    $existingArray = include $registryFilePath;
    $className = 'Commands\Programs\\' . $commandName;

    if (!array_search($className, $existingArray)) {
      // 新しい要素を追加
      if (class_exists($className)) {
        $existingArray[] = $className;

        // 配列をPHPコードに変換
        $newContent = '<?php return ' . var_export($existingArray, true) . ';';

        // ファイルに書き込み
        file_put_contents($registryFilePath, $newContent);

        $this->log("Command has been registered.");
      }
    } else {
      throw new Exception("$className has already been registered.", 1);
    }
  }

  private function generateSeederFile(string $seederName): void
  {
    $className = $this->addSeederSuffix($seederName, "Seeder");
    $filename = sprintf(
      '%s.php',
      $className
    );

    $seederContent = $this->getSeederContent($className);

    // 移行ファイルを保存するパスを指定します
    $path = sprintf("%s/../../Database/Seeds/%s", __DIR__, $filename);
    if (!file_exists($path)) {
      file_put_contents($path, $seederContent);
      $this->log("Seeder file {$filename} has been generated!");
    } else {
      throw new Exception("$filename already exists.", 1);
    }
  }

  private function getSeederContent(string $seederName): string
  {
    $className = $this->addSeederSuffix($seederName);

    return <<<SEEDER
<?php

namespace Database\Seeds;

use Database\AbstractSeeder;
    
class CarsSeeder extends AbstractSeeder
{

  // TODO: tableName文字列を割り当ててください。
  protected ?string \$tableName = null;

  // TODO: tableColumns配列を割り当ててください。
  protected array \$tableColumns = [];

  public function createRowData(int \$count): array
  {
    // TODO: createRowData()メソッドを実装してください。
    return [];
  }
}  
SEEDER;
  }

  private function addSeederSuffix(string $inputString): string
  {
    if (!$this->endsWith($inputString, "Seeder")) {
      $inputString .= "Seeder";
    }
    return $inputString;
  }

  private function endsWith($haystack, $needle): bool
  {
    $length = strlen($needle);
    if ($length == 0) {
      return true;
    }
    return (substr($haystack, -$length) === $needle);
  }

  private function pascalCase(string $string): string
  {
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
  }
}
