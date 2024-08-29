<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

abstract class BaseGitHubPull extends Command
{
    protected $signature = 'github:pull {repo} {--branch=main}';
    protected $description = 'Pull code from a GitHub repository and add it to the project';

    public function handle()
    {
        $repo = $this->argument('repo');
        $branch = $this->option('branch');
        $tempDir = storage_path('app/temp_git_' . time());

        $this->info("Cloning repository: $repo");
        $this->cloneRepo($repo, $branch, $tempDir);

        $this->info("Copying files to project...");
        $this->copyFiles($tempDir);

        $this->info("Cleaning up...");
        $this->cleanup($tempDir);

        $this->info("Done!");
    }

    private function cloneRepo($repo, $branch, $tempDir)
    {
        $process = new Process(['git', 'clone', '--branch', $branch, '--single-branch', '--depth', '1', $repo, $tempDir]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function copyFiles($tempDir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            $relativePath = substr($item->getPathname(), strlen($tempDir) + 1);
            $destination = base_path($relativePath);

            if (!file_exists(dirname($destination))) {
                mkdir(dirname($destination), 0755, true);
            }

            copy($item, $destination);
            $this->info("Copied: $relativePath");
        }
    }

    private function cleanup($tempDir)
    {
        $process = new Process(['rm', '-rf', $tempDir]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
