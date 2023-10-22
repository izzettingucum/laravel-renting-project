<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string("name");
        });

        Schema::create('offices_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_id")->index()->constrained("offices")->onDelete("cascade");
            $table->foreignId("tag_id")->index()->constrained("tags")->onDelete("cascade");

            $table->unique(["office_id", "tag_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
