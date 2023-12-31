<?php

namespace Commands;

// すべてのコマンドが持っているメソッドを定義するコマンドインターフェース。
interface Command
{
  // インスタンス化せずにデータにアクセスできるように、静的関数を使用します。
  public static function getAlias(): string;

  /** 
   * このコマンドにおいて使用される引数の配列を返す。
   * @return Argment[]
   */
  public static function getArguments(): array;

  // helpを表示する。
  public static function getHelp(): string;
  // コマンドに対する値が必須
  // php console {program} {program_value} program_valueが必須
  public static function isCommandValueRequired(): bool;

  /** @return bool | string - 値が存在する場合は、値の文字列かパラメータが存在する場合はtrueを返します。引数が設定されていない場合はfalseを返します。 */
  public function getArgumentValue(string $arg): bool | string;

  // コマンドの実行
  public function execute(): int;
}
