<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('generates a tool class (happy path)', function () {
    $path = app_path('Phpclaw/Tools/WeatherTool.php');
    File::delete($path);

    $this->artisan('make:phpclaw-tool', ['name' => 'WeatherTool'])->assertSuccessful();

    expect(File::exists($path))->toBeTrue()
        ->and(File::get($path))->toContain('implements Tool');

    File::delete($path);
});
