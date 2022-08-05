<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkMorphImageTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.morph-image.images'), function (Blueprint $table) {
            $table->uuid('id');
            $table->nullableUuidMorphs('host');
            $table->nullableUuidMorphs('morph');
            $table->string('filename')->nullable();
            $table->string('serial')->nullable();
            $table->string('identifier')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->longText('data')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_visible')->default(1);
            $table->boolean('is_enabled')->default(1);

            $table->timestampsTz();
            $table->softDeletes();

            $table->primary('id');
            $table->index('serial');
            $table->index('identifier');
            $table->index(['type', 'size']);
            $table->index('is_visible');
            $table->index('is_enabled');
        });
        if (!config('wk-morph-image.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.morph-image.images_lang'), function (Blueprint $table) {
                $table->uuid('id');
                $table->nullableUuidMorphs('morph');
                $table->uuid('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->text('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');

                $table->primary('id');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.morph-image.images_lang'));
        Schema::dropIfExists(config('wk-core.table.morph-image.images'));
    }
}
