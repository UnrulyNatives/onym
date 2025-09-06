<?php

return [
   
    /*
    |--------------------------------------------------------------------------
    | Onym Default Filename
    |--------------------------------------------------------------------------
    |
    | Configure the default filename to use when none is provided.
    |
    */
    'default_filename' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Onym Default Extension
    |--------------------------------------------------------------------------
    |
    | Configure the default extension to use when none is provided.
    */
    'default_extension' => 'txt',

    /*
    |--------------------------------------------------------------------------
    | Onym Generation Strategy
    |--------------------------------------------------------------------------
    |
    | Configure the default strategy for filename generation.
    | Available strategies: 'random', 'uuid', 'timestamp', 'date',
    | 'numbered', 'slug', 'hash'
    |
    */
    'strategy' => 'random',

    /*
    |--------------------------------------------------------------------------
    | Storage Path for Collision Detection
    |--------------------------------------------------------------------------
    |
    | Configure the storage path for collision detection. When set, the
    | unique() method will check for existing files in this directory.
    | Set to null to disable collision detection.
    |
    */
    'storage_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Maximum Unique Generation Attempts
    |--------------------------------------------------------------------------
    |
    | Configure the maximum number of attempts to generate a unique filename
    | before falling back to UUID with timestamp suffix.
    |
    */
    'max_unique_attempts' => 10,

    /*
    |--------------------------------------------------------------------------
    | Onym Generation Options
    |--------------------------------------------------------------------------
    |
    | Configure the default options for each strategy.
    | These options are used to generate the new filename.
    |
    */
    'options' => [

        /*
        |--------------------------------------------------------------------------
        | Random Strategy Options
        |--------------------------------------------------------------------------
        |
        | - length: Length of the random string (1-255)
        | - use_filename: Include original filename with random string
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'random' => [
            'length' => 16,
            'use_filename' => false,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | UUID Strategy Options
        |--------------------------------------------------------------------------
        |
        | - use_filename: Include original filename with UUID
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'uuid' => [
            'use_filename' => false,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | Timestamp Strategy Options
        |--------------------------------------------------------------------------
        |
        | - format: PHP DateTime format string
        | - prepend_timestamp: Put timestamp before filename instead of after
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'timestamp' => [
            'format' => 'Y-m-d_H-i-s',
            'prepend_timestamp' => false,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | Date Strategy Options
        |--------------------------------------------------------------------------
        |
        | - format: PHP DateTime format string
        | - prepend_date: Put date before filename instead of after
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'date' => [
            'format' => 'Y-m-d',
            'prepend_date' => false,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | Numbered Strategy Options
        |--------------------------------------------------------------------------
        |
        | - number: Starting number
        | - separator: Separator between filename and number
        | - pad_length: Zero-pad numbers to this length (0 = no padding)
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'numbered' => [
            'number' => 1,
            'separator' => '_',
            'pad_length' => 0,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | Slug Strategy Options
        |--------------------------------------------------------------------------
        |
        | - separator: Character to use as separator in slug
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'slug' => [
            'separator' => '-',
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | Hash Strategy Options
        |--------------------------------------------------------------------------
        |
        | - algorithm: Hash algorithm (md5, sha1, sha256, etc.)
        | - length: Truncate hash to this length (null = full hash)
        | - use_filename: Include original filename with hash
        | - include_timestamp: Add timestamp to hash input for uniqueness
        | - prefix: String to prepend to the filename
        | - suffix: String to append before the extension
        |
        */
        'hash' => [
            'algorithm' => 'md5',
            'length' => null,
            'use_filename' => false,
            'include_timestamp' => false,
            'prefix' => '',
            'suffix' => '',
        ],
    ],
];