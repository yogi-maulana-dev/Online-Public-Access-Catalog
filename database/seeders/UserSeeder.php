<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Super Admin', 'admin@press.test', RoleName::SuperAdmin, 'Prof. Dr.', 'University Press'],
            ['Publisher Admin', 'publisher@press.test', RoleName::PublisherAdmin, null, 'University Press'],
            ['Editor Naskah', 'editor@press.test', RoleName::Editor, 'M.A.', 'Dewan Editorial'],
            ['Reviewer Ahli', 'reviewer@press.test', RoleName::Reviewer, 'Dr.', 'Mitra Bestari'],
            ['Sri Rahmawati', 'author@press.test', RoleName::Author, 'Prof. Dr., M.Kom.', 'Teknik Informatika'],
            ['Mahasiswa Pembaca', 'reader@press.test', RoleName::Reader, null, 'Universitas'],
        ];

        foreach ($accounts as [$name, $email, $role, $title, $affiliation]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'title' => $title,
                    'affiliation' => $affiliation,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$role->value]);
        }

        // Link the Author demo account to an existing author profile so they own books.
        $authorUser = User::where('email', 'author@press.test')->first();
        $authorProfile = Author::where('name', 'Sri Rahmawati')->first();
        if ($authorUser && $authorProfile) {
            $authorProfile->update(['user_id' => $authorUser->id, 'email' => $authorUser->email]);
        }
    }
}
