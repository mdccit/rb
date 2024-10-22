<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Comment extends Model
{
  use HasFactory;
  // use HasUuids;

  protected $fillable = ['post_id', 'content', 'user_id'];

  /**
   * Connect the relevant database
   *
   */
  public static function connect($connection = null)
  {
    $connection = $connection ?: config('database.default');
    return (new static)->setConnection($connection);
  }

  public function post()
  {
    return $this->belongsTo(Post::class, 'post_id');
  }

  public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
