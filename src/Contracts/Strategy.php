<?php

namespace Blaspsoft\Onym\Contracts;

interface Strategy
{
    /**
     * Generate a filename using the strategy.
     *
     * @param string|null $filename The base filename
     * @param string|null $extension The file extension
     * @param array $options Strategy-specific options
     * @return string The generated filename with extension
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string;

    /**
     * Get the strategy name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get default options for the strategy.
     *
     * @return array
     */
    public function getDefaultOptions(): array;

    /**
     * Validate options for the strategy.
     *
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function validateOptions(array $options): void;
}