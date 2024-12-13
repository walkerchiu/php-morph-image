<?php

namespace WalkerChiu\MorphImage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\MorphImage\Models\Entities\Image;
use WalkerChiu\MorphImage\Models\Entities\ImageLang;

class ImageTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\MorphImage\MorphImageServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Image.
     *
     * For WalkerChiu\MorphImage\Models\Entities\MorphImage
     * 
     * @return void
     */
    public function testMorphImage()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-image.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-image.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-image.soft_delete', 1);

        // Give
        $db_morph_1 = factory(Image::class)->create();
        $db_morph_2 = factory(Image::class)->create();
        $db_morph_3 = factory(Image::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Image::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_morph_2->delete();
            $records = Image::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Image::withTrashed()
                    ->find($db_morph_2->id)
                    ->restore();
            $record_2 = Image::find($db_morph_2->id);
            $records = Image::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $record_2->lang();
            // Then
            $this->assertEquals($class, ImageLang::class);

        // Scope query on enabled records
            // When
            $records = Image::ofEnabled()
                            ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Image::ofDisabled()
                            ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
