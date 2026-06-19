<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Exceptions\GenerationFailedException;

it('includes the underlying cause in the message (happy path)', function () {
    $exception = GenerationFailedException::allCandidatesFailed('reasoning', new RuntimeException('Class "Laravel\Ai\AnonymousAgent" not found'));

    expect($exception->getMessage())
        ->toContain('role [reasoning]')
        ->toContain('Laravel\Ai\AnonymousAgent')
        ->and($exception->getPrevious())->toBeInstanceOf(RuntimeException::class);
});

it('reads cleanly without a cause (edge path)', function () {
    expect(GenerationFailedException::allCandidatesFailed('reasoning')->getMessage())
        ->toBe('All model candidates for role [reasoning] failed to generate a response.');
});
