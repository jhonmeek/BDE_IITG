<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\password;

class CreateAdminUser extends Command
{
    protected $signature = 'bde:create-admin
        {email : Adresse email du compte}
        {--name=Administrateur BDE : Nom affiche}
        {--password= : Mot de passe (demande interactivement si absent)}';

    protected $description = 'Cree ou met a jour un compte super administrateur BDE';

    public function handle(): int
    {
        $plainPassword = $this->option('password') ?: password('Mot de passe du super admin');

        $validator = Validator::make(
            ['email' => $this->argument('email'), 'password' => $plainPassword],
            [
                'email' => ['required', 'email'],
                'password' => ['required', Password::min(12)->letters()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Role::findOrCreate('super_admin', 'web');

        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            [
                'name' => $this->option('name'),
                'password' => $plainPassword,
                'is_active' => true,
            ],
        );

        $user->syncRoles(['super_admin']);

        $this->info("Compte super admin pret : {$user->email}");

        return self::SUCCESS;
    }
}
