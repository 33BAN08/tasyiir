<?php

namespace Tests\Concerns;

use Illuminate\Support\Env;

/**
 * routes/web.php registers a different set of routes per edition, and that
 * happens while the application boots — before a test body can call config().
 * The edition therefore has to be in the environment before parent::setUp().
 *
 * Env keeps one repository for the whole PHP process and its immutable writer
 * refuses to replace a variable that is already set, so a value loaded from
 * .env by an earlier test class would otherwise stick for every class after
 * it. Dropping the repository makes the next read pick up what we put in
 * $_ENV / $_SERVER / putenv here.
 */
trait RunsInMode
{
    protected ?string $previousMode = null;

    protected function runInMode(string $mode): void
    {
        $this->previousMode = getenv('TASYIIR_MODE') === false ? null : (string) getenv('TASYIIR_MODE');

        $this->putMode($mode);
    }

    protected function restoreMode(): void
    {
        if ($this->previousMode === null) {
            putenv('TASYIIR_MODE');
            unset($_ENV['TASYIIR_MODE'], $_SERVER['TASYIIR_MODE']);
            $this->forgetEnvRepository();

            return;
        }

        $this->putMode($this->previousMode);
    }

    protected function putMode(string $mode): void
    {
        putenv("TASYIIR_MODE={$mode}");
        $_ENV['TASYIIR_MODE'] = $mode;
        $_SERVER['TASYIIR_MODE'] = $mode;

        $this->forgetEnvRepository();
    }

    protected function forgetEnvRepository(): void
    {
        $repository = new \ReflectionProperty(Env::class, 'repository');
        $repository->setAccessible(true);
        $repository->setValue(null, null);
    }
}
