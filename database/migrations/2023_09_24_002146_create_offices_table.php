<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->index()->constrained("users")->onDelete("cascade");
            $table->decimal("lat", 11, 8);
            $table->decimal("lng", 11, 8);
            $table->foreignId('featured_image_id')->index()->nullable()->constrained('images')->onDelete('set null');
            $table->tinyInteger("approval_status")->default(1);
            $table->boolean("hidden")->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('office_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId("office_id")->index()->constrained("offices")->onDelete("cascade");
            $table->string("title");
            $table->text("description");
            $table->text("address_line1");
            $table->text("address_line2")->nullable();
            $table->integer("price_per_day");
            $table->integer("monthly_discount")->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offices');
    }
}
