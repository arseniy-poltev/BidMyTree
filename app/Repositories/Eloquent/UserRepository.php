<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Presenters\UserPresenter;
use App\Criterias\OnlyOwnUserCriteria;
use App\Repositories\Eloquent\BaseRepoWithSlugs;
use App\Contracts\Repository\UserRepositoryContract;

class UserRepository extends BaseRepoWithSlugs implements UserRepositoryContract
{
    private $auth;

    protected $skipPresenter = true;

    public function boot()
    {
        $this->auth = resolve('Illuminate\Contracts\Auth\Factory');

        $this->pushCriteria(OnlyOwnUserCriteria::class);
    }

    public function model()
    {
        return User::class;
    }

    public function presenter()
    {
        return UserPresenter::class;
    }

    public function currentUser()
    {
        return $this->find($this->auth->user()->id);
    }

    public function setCurrentAvatar($fileUrl)
    {
        return $this->update(['avatar' => $fileUrl], $this->auth->user()->id);
    }

    public function getCurrentAvatarFile()
    {
        return $this->skipPresenter()->find($this->auth->user()->id)->avatar;
    }

    public function removeCurrentAvatar()
    {
        return $this->update(['avatar' => null], $this->auth->user()->id);
    }
}
