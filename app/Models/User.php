<?php

namespace App\Models;

use App\Jobs\sendEmailPasswordReset;
use App\Mail\emailPasswordReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\updatePasswordNotification;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Laravel\Sanctum\HasApiTokens;
use PHPUnit\TextUI\XmlConfiguration\Group;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use PhpParser\Node\Expr\New_;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['email', 'id_hospital', 'name', 'cpf', 'image', 'phone', 'news-email', 'department'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */


    public function getJWTCustomClaims()
    {
        return [];
    }
    public function sendPasswordResetNotification($token)
    {
        $email = $this->email;
        $encrypted = Crypt::encryptString($email);

        $url = 'https://teste-senne.mageda.com.br/reset-password?token=' . $token . '&key=' . $encrypted;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function sendPasswordLink($token)
    {
        $email = $this->email;
        $encrypted = Crypt::encryptString($email);

        $url = 'https://teste-senne.mageda.com.br/reset-password?token=' . $token . '&key=' . $encrypted;

        $this->notify(new updatePasswordNotification($url));
    }

    /* Função para adicionar a URL do site automaticamente na imagem após puxar do banco
    URL determinada no .env */
    public function getImageAttribute($value)
    {
        if ($value) {
            return config('app.url') . 'uploads/' . $value;
        }
    }


    public function hospitalsUser()
    {
        return $this->hasMany(UsersHospitals::class, 'id_user');
    }

    public function hospitals()
    {
        return $this->hasMany(UsersHospitals::class, 'id');
    }





    public function group()
    {
        return $this->hasMany(Groups::class, 'id');
    }


    public function logsUser()
    {
        return $this->hasMany(UserLog::class, 'id_user');
    }


    public function user_permissions()
    {
        return $this->hasMany(UserPermissoes::class, 'id');
    }




    public function permission_user($id_user, $id_permissao)
    {
        return UserPermissoes::where('id_user',  $id_user)->where('id_permissao', $id_permissao)->first();
    }


    /* Verificar e validar tipo do usuário */
    public function authorizeRoles($roles)
    {
        if (is_array($roles)) {
            return $this->hasAnyRole($roles);
        }

        return $this->hasRole($roles);
    }

    /* Atribuir USUÁRIO a uma ROLE. (Usuários são pertencentes a uma role - belongsTo) */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /* Verificação de múltiplas roles */
    public function hasAnyRole($roles)
    {
        return null !== $this->role()->whereIn('id', $roles)->first();
    }

    /* Verificação role única */
    public function hasRole($role)
    {
        return null !== $this->role()->where('id', $role)->first();
    }
}
