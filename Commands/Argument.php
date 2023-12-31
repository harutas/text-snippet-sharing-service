<?php

namespace Commands;

// コマンドが使用できる引数を定義するビルダークラス。コマンドは必要に応じてすべてのオプション引数を作成する必要があります。ビルダーを使用すると、引数をさらにカスタマイズできるようになります。たとえば、値が必要か、引数の短縮形が許可されているかどうかなどです。
class Argument
{
  // コマンドの引数名 --rollbackや--init
  private string $argument;
  private string $description = "";
  // この引数が必須であるか
  private bool $required = true;
  // 短縮系を許容するか
  private bool $allowAsShort = false;
  public function __construct(string $argument)
  {
    $this->argument = $argument;
  }

  public function getArgument(): string
  {
    return $this->argument;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $description): Argument
  {
    $this->description = $description;
    return $this;
  }

  public function isRequired(): bool
  {
    return $this->required;
  }

  public function setRequired(bool $required): Argument
  {
    $this->required = $required;
    return $this;
  }

  public function isShortAllowed(): bool
  {
    return $this->allowAsShort;
  }

  public function setAllowAsShort(bool $allowAsShort): Argument
  {
    $this->allowAsShort = $allowAsShort;
    return $this;
  }
}
