<?php

namespace App\Console\Commands;

use App\Models\Gebruiker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdministrator extends Command
{
    protected $signature = 'sportcentrum:beheerder';

    protected $description = 'Maak veilig een beheerder aan met een zelfgekozen wachtwoord';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Voer dit commando interactief uit om je wachtwoord veilig in te voeren.');

            return self::FAILURE;
        }
        $data = ['naam' => $this->ask('Naam'), 'email' => $this->ask('E-mailadres'), 'password' => $this->secret('Wachtwoord (minimaal 12 tekens)'), 'password_confirmation' => $this->secret('Herhaal wachtwoord')];
        $validator = Validator::make($data, ['naam' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:gebruikers,email'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        Gebruiker::create(['voornaam' => $data['naam'], 'achternaam' => '', 'telefoonnummer' => '', 'email' => $data['email'], 'password' => $data['password'], 'rol' => 'beheerder', 'geactiveerd_op' => now()]);
        $this->info('Beheerder aangemaakt. Je kunt nu inloggen op de website.');

        return self::SUCCESS;
    }
}
