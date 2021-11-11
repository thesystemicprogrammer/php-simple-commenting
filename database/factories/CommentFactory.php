<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {

        return [
            'uri' => '/home/blog',
            'page_hash' => hash('sha1', '/home/blog'),
            'name' => $this->faker->name(),
            'email_hash' => 'sha256:::' . hash('sha256', $this->faker->safeEmail()),
            'comment' => $this->faker->realText(),
            'approval_status' => Comment::APPROVAL_STATUS['open']
        ];
    }
}
