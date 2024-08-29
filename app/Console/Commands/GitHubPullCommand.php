<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;

abstract class GitHubPullCommand extends Command
{
    protected $signature = 'github:pull {repo} {--branch=main}';
    protected $description = 'Pull code from a GitHub repository and add it to the project';

    protected function pullFromGitHub(string $repo, string $branch = 'main'): void
    {
        $tempDir = storage_path('app/temp_repo');

        // Clone the repository
        $this->info("Cloning repository: $repo");
        $process = new Process(['git', 'clone', '--branch', $branch, '--depth', '1', $repo, $tempDir]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Failed to clone repository: ' . $process->getErrorOutput());
            return;
        }

        // Copy files to the project
        $this->info('Copying files to the project...');
        $this->copyFiles($tempDir);

        // Clean up
        File::deleteDirectory($tempDir);
        $this->info('Temporary files cleaned up.');

        $this->info('Operation completed successfully.');
    }

    abstract protected function copyFiles(string $sourceDir): void;
}
