<?php

namespace App\Repositories\AuthRepositories;

use App\DTO\Auth\ForgotPasswordDTO;
use App\Models\ForgotPasswordMail;
use App\Models\User;
use App\Repositories\Interfaces;
use Illuminate\Database\Eloquent\Model;

class ForgotPasswordRepository implements Interfaces\ForgotPasswordInterface
{
    protected $forgotPasswordModel;

    public function __construct(ForgotPasswordMail $forgotPasswordMail)
    {
        $this->forgotPasswordModel = $forgotPasswordMail;
    }

   public function create(User $user, ForgotPasswordDTO $forgotPasswordDTO): Model
   {
       $forgotPasswordMail = $user->forgotPasswordMail()->create([
           "code" => $forgotPasswordDTO->code,
           "remember_token" => $forgotPasswordDTO->rememberToken,
           "expired_at" => $forgotPasswordDTO->expiredAt
       ]);

       return $forgotPasswordMail;
   }

   public function findRecoveryCodeByUser(User $user)
   {
       return $user->forgotPasswordMail()->latest("id")->first();
   }

    public function controlUserCode(User $user): bool
   {
       $mailControl = $user->forgotPasswordMail()->exists();

       return $mailControl;
   }

   public function deleteUserCode(User $user)
   {
       $user->forgotPasswordMail()->delete();
   }
}
