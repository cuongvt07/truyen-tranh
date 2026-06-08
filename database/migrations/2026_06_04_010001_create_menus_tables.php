<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mỗi vị trí menu (header / browse / footer / mobile) là 1 bản ghi
        if (!Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table) {
                $table->id();
                $table->string('location')->unique();   // header | browse | footer | mobile
                $table->string('name');                  // tên hiển thị trong admin
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('menu_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('label');                       // nhãn text trực tiếp
                $table->string('label_key')->nullable();       // khóa dịch tùy chọn (messages.nav.home)
                $table->string('url')->default('#');           // path tương đối hoặc URL ngoài
                $table->string('icon')->nullable();            // class FontAwesome: fa fa-home
                $table->string('target')->default('_self');    // _self | _blank
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
                $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
                $table->index(['menu_id', 'parent_id', 'order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
