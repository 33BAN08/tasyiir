<?php

namespace App\View;

/**
 * PlanZeen Phase 1 — a small, self-contained subset of the Blade templating
 * engine. It supports exactly the directives this project's views use:
 *
 *   @extends, @section/@endsection, @yield, @include, @php/@endphp,
 *   @if/@elseif/@else/@endif, @foreach/@endforeach,
 *   {{ }} / {!! !!} echoes, {{-- --}} comments,
 *   and anonymous Blade-style components: <x-name attr="v" :prop="$expr">slot</x-name>
 *
 * This exists only because Composer/Packagist is unreachable from this
 * sandbox (see NOTES.md). The directive syntax intentionally mirrors real
 * Laravel Blade so these .blade.php files need little to no change once
 * the project is moved to a real Laravel + Livewire install in Phase 2.
 */
class BladeLite
{
    protected static string $viewsPath;
    protected static string $cachePath;

    public static function boot(string $viewsPath, string $cachePath): void
    {
        static::$viewsPath = rtrim($viewsPath, '/');
        static::$cachePath = rtrim($cachePath, '/');
        if (!is_dir(static::$cachePath)) {
            mkdir(static::$cachePath, 0775, true);
        }
    }

    public static function resolveSourcePath(string $viewName): string
    {
        $relative = str_replace('.', '/', $viewName) . '.blade.php';
        return static::$viewsPath . '/' . $relative;
    }

    /** Compile a named view (from disk) into a cached PHP file, returning its path. */
    public static function compile(string $viewName): string
    {
        $source = static::resolveSourcePath($viewName);
        if (!is_file($source)) {
            throw new \RuntimeException("View [{$viewName}] not found at {$source}");
        }

        $cacheFile = static::$cachePath . '/' . str_replace('/', '_', $viewName) . '.php';

        if (!is_file($cacheFile) || filemtime($cacheFile) < filemtime($source)) {
            $compiled = static::compileString(file_get_contents($source));
            file_put_contents($cacheFile, $compiled);
        }

        return $cacheFile;
    }

    /** Compile raw (already-loaded) template text into a cached PHP file. Used for @extends children. */
    public static function compileRawToCache(string $raw, string $keyName): string
    {
        $cacheFile = static::$cachePath . '/' . str_replace('/', '_', $keyName) . '__child.php';
        $compiled = static::compileString($raw);
        file_put_contents($cacheFile, $compiled);
        return $cacheFile;
    }

    /**
     * Render a component (<x-name ...>) in an isolated variable scope.
     * $props already contains resolved PHP values; $slot is pre-rendered HTML.
     */
    public static function renderComponent(string $name, array $props, string $slot = ''): string
    {
        $props['slot'] = $slot;
        $path = static::compile('components.' . $name);
        return static::runIsolated($path, $props);
    }

    /** Render an @include, sharing the parent's variables. */
    public static function renderInclude(string $name, array $vars = []): string
    {
        $path = static::compile($name);
        return static::runIsolated($path, $vars);
    }

    protected static function runIsolated(string $compiledPath, array $vars): string
    {
        $__renderer = function () use ($compiledPath, $vars) {
            extract($vars);
            ob_start();
            include $compiledPath;
            return ob_get_clean();
        };

        return $__renderer();
    }

    // ------------------------------------------------------------------
    // Compilation pipeline
    // ------------------------------------------------------------------

    public static function compileString(string $content): string
    {
        // 0. Normalise "@directive (" -> "@directive(" so authors can use either style.
        $content = preg_replace('/@(if|elseif|foreach|forelse|include|yield|section)\s+\(/', '@$1(', $content);

        // 1. Strip {{-- comments --}}
        $content = preg_replace('/\{\{--.*?--\}\}/s', '', $content);

        // 2. Expand <x-component /> and <x-component>...</x-component> tags.
        $content = static::expandComponents($content);

        // 3. Control structures with parenthesised arguments.
        $content = static::compileParenDirective($content, 'if', fn($a) => "<?php if({$a}): ?>");
        $content = static::compileParenDirective($content, 'elseif', fn($a) => "<?php elseif({$a}): ?>");
        $content = str_replace('@else', '<?php else: ?>', $content);
        $content = str_replace('@endif', '<?php endif; ?>', $content);

        $content = static::compileParenDirective($content, 'foreach', fn($a) => "<?php foreach({$a}): ?>");
        $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);

        // @forelse(...) ... @empty ... @endforelse
        $content = static::compileParenDirective(
            $content,
            'forelse',
            fn($a) => "<?php \$__forelse_stack[] = true; foreach({$a}): \$__forelse_stack[array_key_last(\$__forelse_stack)] = false; ?>"
        );
        $content = str_replace('@empty', '<?php endforeach; if (end($__forelse_stack)): ?>', $content);
        $content = str_replace('@endforelse', '<?php endif; array_pop($__forelse_stack); ?>', $content);

        // 4. @include('view.name') — shares scope via get_defined_vars().
        $content = static::compileParenDirective(
            $content,
            'include',
            fn($a) => "<?php echo \\App\\View\\BladeLite::renderInclude({$a}, get_defined_vars()); ?>"
        );

        // 5. @yield('name') / @yield('name', 'default')
        $content = static::compileParenDirective($content, 'yield', function ($a) {
            $parts = static::splitTopLevelArgs($a);
            $key = $parts[0];
            $default = $parts[1] ?? "''";
            return "<?= (\$__sections[{$key}] ?? {$default}) ?>";
        });

        // 6. @section('name') ... @endsection   OR inline: @section('name', 'value')
        $content = static::compileParenDirective(
            $content,
            'section',
            function ($a) {
                $parts = static::splitTopLevelArgs($a);
                if (count($parts) >= 2) {
                    return "<?php \$__sections[{$parts[0]}] = {$parts[1]}; ?>";
                }
                return "<?php \$__section_stack[] = {$a}; ob_start(); ?>";
            }
        );
        $content = str_replace(
            '@endsection',
            '<?php $__sections[array_pop($__section_stack)] = ob_get_clean(); ?>',
            $content
        );

        // 7. @php ... @endphp
        $content = str_replace('@php', '<?php', $content);
        $content = str_replace('@endphp', '?>', $content);

        // 8. Raw + escaped echoes.
        $content = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $content);
        $content = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?= e($1) ?>', $content);

        return $content;
    }

    /**
     * Find every "@directive(...)" occurrence (matching nested parens correctly)
     * and replace it using $replacer($argsString).
     */
    protected static function compileParenDirective(string $content, string $directive, callable $replacer): string
    {
        $needle = '@' . $directive . '(';
        $result = '';
        $offset = 0;

        while (($pos = strpos($content, $needle, $offset)) !== false) {
            $result .= substr($content, $offset, $pos - $offset);
            $argStart = $pos + strlen($needle);
            $depth = 1;
            $i = $argStart;
            $len = strlen($content);
            while ($i < $len && $depth > 0) {
                if ($content[$i] === '(') {
                    $depth++;
                } elseif ($content[$i] === ')') {
                    $depth--;
                }
                $i++;
            }
            $args = substr($content, $argStart, $i - $argStart - 1);
            $result .= $replacer($args);
            $offset = $i;
        }

        $result .= substr($content, $offset);
        return $result;
    }

    /** Split "arg1, arg2, ..." on top-level commas only (ignores commas inside (), [], strings). */
    protected static function splitTopLevelArgs(string $args): array
    {
        $parts = [];
        $depth = 0;
        $current = '';
        $inString = null; // ' or "
        $len = strlen($args);

        for ($i = 0; $i < $len; $i++) {
            $ch = $args[$i];

            if ($inString !== null) {
                $current .= $ch;
                if ($ch === $inString && ($i === 0 || $args[$i - 1] !== '\\')) {
                    $inString = null;
                }
                continue;
            }

            if ($ch === '\'' || $ch === '"') {
                $inString = $ch;
                $current .= $ch;
                continue;
            }

            if ($ch === '(' || $ch === '[' || $ch === '{') {
                $depth++;
            } elseif ($ch === ')' || $ch === ']' || $ch === '}') {
                $depth--;
            }

            if ($ch === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $ch;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    /** Expand <x-name ...>...</x-name> and <x-name ... /> tags into renderComponent() calls. */
    protected static function expandComponents(string $content): string
    {
        for ($pass = 0; $pass < 12; $pass++) {
            $before = $content;

            // Self-closing: <x-name attr="v" ... />
            $content = preg_replace_callback(
                '/<x-([a-zA-Z0-9_.-]+)((?:\s+[:\w-]+="[^"]*")*)\s*\/>/s',
                function ($m) {
                    $propsPhp = static::attributesToPhpArray($m[2]);
                    return "<?php echo \\App\\View\\BladeLite::renderComponent('{$m[1]}', {$propsPhp}, ''); ?>";
                },
                $content
            );

            // Paired: <x-name attr="v" ...>slot</x-name>
            $content = preg_replace_callback(
                '/<x-([a-zA-Z0-9_.-]+)((?:\s+[:\w-]+="[^"]*")*)\s*>(.*?)<\/x-\1>/s',
                function ($m) {
                    $propsPhp = static::attributesToPhpArray($m[2]);
                    $slotSource = $m[3];
                    return "<?php ob_start(); ?>{$slotSource}<?php \$__slot = ob_get_clean(); echo \\App\\View\\BladeLite::renderComponent('{$m[1]}', {$propsPhp}, \$__slot); ?>";
                },
                $content
            );

            if ($content === $before) {
                break;
            }
        }

        return $content;
    }

    /** Parse a component tag's raw attribute string into a PHP array literal (as source text). */
    protected static function attributesToPhpArray(string $attrString): string
    {
        $pairs = [];
        if (preg_match_all('/([:\w-]+)\s*=\s*"([^"]*)"/s', $attrString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $rawName = $match[1];
                $rawValue = $match[2];

                if (str_starts_with($rawName, ':')) {
                    $key = str_replace('-', '_', substr($rawName, 1));
                    // Raw PHP expression — used as-is.
                    $pairs[] = var_export($key, true) . ' => (' . $rawValue . ')';
                } else {
                    $key = str_replace('-', '_', $rawName);
                    $pairs[] = var_export($key, true) . ' => ' . var_export($rawValue, true);
                }
            }
        }

        return '[' . implode(', ', $pairs) . ']';
    }
}
