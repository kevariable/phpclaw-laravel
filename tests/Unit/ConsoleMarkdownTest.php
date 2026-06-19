<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Support\ConsoleMarkdown;

it('renders headings, bold, code, bullets and italics to ANSI (happy path)', function () {
    $out = (new ConsoleMarkdown)->render("## Title\n- an **important** item\nuse `artisan` and *stress* it");

    expect($out)
        ->toContain('Title')->toContain("\e[1m")
        ->toContain('important')->toContain('•')
        ->toContain('artisan')->toContain('stress')
        ->and($out)->not->toContain('## Title')
        ->and($out)->not->toContain('**important**');
});

it('leaves plain text untouched (edge path)', function () {
    expect((new ConsoleMarkdown)->render('just plain text'))->toBe('just plain text');
});
