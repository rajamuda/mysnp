<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    'locales' => [
        'en' => 'EN',
        // 'zh-CN' => '中文',
        // 'es' => 'ES',
        'id' => 'ID',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => env('APP_LOG', 'single'),

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Directory information
    |--------------------------------------------------------------------------
    |
    | Provide directory address for jobs, tools, and sequence
    |
    */
    'rootDir' => '/home/rajamuda/htdocs/mysnp',

    'toolsDir' => [
        'bowtie2' => '/home/rajamuda/htdocs/mysnp/resources/tools/mapper/bowtie2',
        'bwa' => '/home/rajamuda/htdocs/mysnp/resources/tools/mapper/bwa',

        'samtools' => '/home/rajamuda/htdocs/mysnp/resources/tools/caller/samtools',
        'bcftools' => '/home/rajamuda/htdocs/mysnp/resources/tools/caller/bcftools',
        'vcfutils' => '/home/rajamuda/htdocs/mysnp/resources/tools/caller/bcftools/misc',
        'vcflib' => '/home/rajamuda/htdocs/mysnp/resources/tools/vcflib/bin',

        'gatk' => '/home/rajamuda/htdocs/mysnp/resources/tools/caller/gatk',

        'snpeff' => '/home/rajamuda/htdocs/mysnp/resources/tools/annotate/snpEff',

        'snphylo' => '/home/rajamuda/htdocs/mysnp/resources/tools/snphylo',
    ],

    'toolsAlias' => [
        'bt2' => 'Bowtie2',
        'bwa' => 'BWA',
        'novo' => 'Novoalign',

        'sam' => 'BCFtools/Samtools',
        'gatk' => 'GATK',
        'picard' => 'Picard',
    ],

    'jobsDir' => '/home/rajamuda/htdocs/mysnp/resources/jobs',
    'phyloDir' => '/home/rajamuda/htdocs/mysnp/resources/phylo-tree',
    'sequenceDir' => '/home/rajamuda/htdocs/mysnp/resources/sequence',
    'dbSnpDir' => '/home/rajamuda/htdocs/mysnp/resources/sequence/dbsnp',


    /*
    |--------------------------------------------------------------------------
    | SNP Identification Tools default parameter
    |--------------------------------------------------------------------------
    |
    | Provide information about parameter for each tools used and given default
    | value for each parameter to ease identification process
    |
    */
    'defaultParams' => [
        'bowtie2' => [
            '-N'        => '0', // int
            '-L'        => '22', // int
            '-i'        => 'S,1,1.15', // func 
            '--n-ceil'  => 'L,0,0.15', // func
            '--dpad'    => '15', // int
            '--gbar'    => '4', // int
            '--ignore-quals' => false, // bool
            '--nofw' => false, // bool
            '--norc' => false, // bool
            '--local' => false, // bool
            '--ma'      => '0', // int
            '--mp'      => '6', //int
            '--np'      => '1', // int
            '--rdg'     => '5,3', // int1,int2
            '--rfg'     => '5,3', // int1,int2
            '--score-min' => 'L,-0.6,-0.6', // func
            '-D'        => '15', // int
            '-R'        => '2', // int
            '-I'        => '0', // int
            '-X'        => '500', // int
            '--no-mixed' => false, // bool
            '--no-discordant' => false, // bool
            '--no-dovetail' => false, // bool
            '--no-contain' => false, // bool
            '--no-overlap' => false, // bool
        ],

        'samtools' => [
            '-A' => false, // bool
            '-B' => false, // bool
            '-R' => false, // bool
            '-C' => '0', // int
            '-M' => '60', // int
            '-q' => '0', // int
            '-Q' => '13', // int
            '-I' => true, // bool
            '-e' => '20', // int
            '-F' => '0.002', // float
            '-h' => '100', // int
            '-L' => '250', // int
            '-m' => '1', // int
            '-o' => '40', // int
            '-P' => 'all', // string              
        ],

        'vcfutils' => [
            '-Q' => '10', // int
            '-d' => '2', // int
            '-D' => '10000000', // int
            '-a' => '2', // int
            '-w' => '3', // int
            '-W' => '10', // int              
        ],

        'bwa' => [
            'aln' => [
                '-n' => '0.04', // float
                '-o' => '1', // int
                '-e' => '-1', // int
                '-d' => '16', // int
                '-i' => '5', // int
                '-l' => 'inf', // int (25-35), may be infinite
                '-k' => '2', // int
                '-t' => '1', // int
                '-M' => '3', // int
                '-O' => '11', // int
                '-E' => '4', // int
                // '-q' => '0',
            ],
            'samse' => [
                // '-n' => '3',
            ],
            'sampe' => [
                '-a' => '500', // int
                '-o' => '100000', // int
                // '-n' => '3',
                // '-N' => '10',
                // '-P' => false,
            ],
        ],

        'gatk' => [
            'snp_only' => true,
            '-contamination' => '0.0', // double
            '-hets' => '0.001', // double
            '-indelHeterozygosity' => '0.000125', // 1.25E-4 double
            '--maxReadsInRegionPerSample' => '10000', // int
            '--min_base_quality_score' => '10', // int
            '--minReadsPerAlignmentStart' => '10', // int
            '-ploidy' => '2', // int
            '-stand_call_conf' => '30.0', // double
            '-stand_emit_conf' => '30.0', // double
        ],

        'picard' => [
            'MIN_AB' => '0.0', // double
            'MIN_DP' => '0', // int
            'MIN_GQ' => '0', // int
            'MAX_FS' => '1.7976931348623157E308', // double
            'MIN_QD' => '0.0', // double
        ],

    ],


    // bool, int, float/double, string, func
    'defaultParamsType' => [
        'bowtie2' => [
            '-N'        => 'int',
            '-L'        => 'int',
            '-i'        => 'func', 
            '--n-ceil'  => 'func',
            '--dpad'    => 'int',
            '--gbar'    => 'int',
            '--ignore-quals' => 'bool',
            '--nofw' => 'bool',
            '--norc' => 'bool',
            '--local' => 'bool',
            '--ma'      => 'int',
            '--mp'      => 'int',
            '--np'      => 'int',
            '--rdg'     => 'int', // <int1,int2>
            '--rfg'     => 'int', // <int1,int2>
            '--score-min' => 'func',
            '-D'        => 'int',
            '-R'        => 'int',
            '-I'        => 'int',
            '-X'        => 'int',
            '--no-mixed' => 'bool',
            '--no-discordant' => 'bool',
            '--no-dovetail' => 'bool',
            '--no-contain' => 'bool',
            '--no-overlap' => 'bool',
        ],

        'samtools' => [
            '-A' => 'bool',
            '-B' => 'bool',
            '-R' => 'bool',
            '-C' => 'int',
            '-M' => 'int',
            '-q' => 'int',
            '-Q' => 'int',
            '-I' => 'bool',
            '-e' => 'int',
            '-F' => 'float',
            '-h' => 'int',
            '-L' => 'int',
            '-m' => 'int',
            '-o' => 'int',
            '-P' => 'string',              
        ],

        'vcfutils' => [
            '-Q' => 'int',
            '-d' => 'int',
            '-D' => 'int',
            '-a' => 'int',
            '-w' => 'int',
            '-W' => 'int',              
        ],

        'bwa' => [
            'aln' => [
                '-n' => 'float',
                '-o' => 'int',
                '-e' => 'int',
                '-d' => 'int',
                '-i' => 'int',
                '-l' => 'int', // (25-35), or inf
                '-k' => 'int',
                '-t' => 'int',
                '-M' => 'int',
                '-O' => 'int',
                '-E' => 'int',
                // '-q' => '0',
            ],
            'samse' => [
                // '-n' => '3',
            ],
            'sampe' => [
                '-a' => 'int',
                '-o' => 'int',
                // '-n' => '3',
                // '-N' => '10',
                // '-P' => false,
            ],
        ],

        'gatk' => [
            'snp_only' => 'bool',
            '-contamination' => 'float',
            '-hets' => 'float',
            '-indelHeterozygosity' => 'float',
            '--maxReadsInRegionPerSample' => 'int',
            '--min_base_quality_score' => 'int',
            '--minReadsPerAlignmentStart' => 'int',
            '-ploidy' => 'int',
            '-stand_call_conf' => 'float',
            '-stand_emit_conf' => 'float',
        ],

        'picard' => [
            'MIN_AB' => 'float',
            'MIN_DP' => 'int',
            'MIN_GQ' => 'int',
            'MAX_FS' => 'string', //float
            'MIN_QD' => 'float',
        ],

    ],

    'process' => [
        // 'indexing', // opsional
        'preparing',
        'mapping',
        'sorting',
        'preprocessing',
        'calling',
        'filtering',
        'annotation',
        'storing_to_db',
    ],

    'vcfInfo' => [
        'DP' => 'Raw read depth',
        'VDB' => 'Variant distance bias (bigger is better)',
        'RPB' => 'Read posititon bias (bigger is better)',
        'MQB' => 'Mapping quality bias (bigger is better)',
        'BQB' => 'Base quality bias (bigger is better)',
        'MQSB' => 'Mapping quality vs Strand bias (bigger is better)',
        'SGB' => 'Segregation based metrics',
        'MQ0F' => 'Fraction of MQ0 reads (smaller is better)',
        'PL' => 'Phred-scaled genotype likelihoods',
        'GT' => 'Genotype',
        'ICB' => 'Inbreeding coeficient binomial (bigger is better)',
        'HOB' => 'Bias in the number of HOMs (bigger is better)',
        'AC' => 'Allele count in genotype for each ALT allele',
        'AN' => 'Total number of alleles in called genotypes',
        'DP4' => 'Number of high-quality ref-forward, ref-reverse, alt-forward, and alt-reverse bases',
        'MQ' => 'Average mapping quality',
        'AA' => 'Ancestral Allele',
        'AF' => 'Allele frequency for each ALT allele',
    ],

];
