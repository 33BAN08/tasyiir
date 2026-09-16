<?php

namespace App\View;

class View
{
    public function __construct(protected string $name, protected array $data = [])
    {
    }

    public function with(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function render(): string
    {
        $data = $this->data;
        extract($data);

        $__sections = [];
        $__section_stack = [];

        $sourcePath = BladeLite::resolveSourcePath($this->name);
        if (!is_file($sourcePath)) {
            throw new \RuntimeException("View [{$this->name}] not found at {$sourcePath}");
        }
        $raw = file_get_contents($sourcePath);

        if (preg_match('/@extends\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $raw, $m)) {
            $layoutName = $m[1];
            $childRaw = preg_replace('/@extends\([^)]*\)\s*/', '', $raw, 1);
            $childCompiled = BladeLite::compileRawToCache($childRaw, $this->name);

            ob_start();
            include $childCompiled;
            ob_end_clean(); // stray top-level output (outside @section) is discarded, sections are captured

            $layoutCompiled = BladeLite::compile($layoutName);
            ob_start();
            include $layoutCompiled;
            return ob_get_clean();
        }

        $compiled = BladeLite::compile($this->name);
        ob_start();
        include $compiled;
        return ob_get_clean();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
