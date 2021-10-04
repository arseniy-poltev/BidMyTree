<?php
namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

/**
 * Class UserTransformer.
 *
 * @package namespace App\Transformers;
 */
class UserTransformer extends TransformerAbstract
{
    private $fileSystem;

    public function __construct()
    {
        $this->fileSystem = resolve('Illuminate\Contracts\Filesystem\Factory');
    }

    /**
     * Transform the User entity.
     *
     * @param \App\Models\User $model
     *
     * @return array
     */
    public function transform(User $model)
    {
        return [
            'slug' => $model->slug(),
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'email' => $model->email,
            'avatar' => $model->avatar ? $this->fileSystem->url($model->avatar) : null,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
