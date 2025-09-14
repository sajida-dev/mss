<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPassword;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordService
{
    /**
     * Reset a user's password (admin/superadmin only)
     * 
     * @param int $userId
     * @param string|null $newPassword If null, generates a random password
     * @return array Returns ['success' => true, 'new_password' => 'password']
     */
    public static function resetUserPassword(int $userId, ?string $newPassword = null): array
    {
        $user = User::findOrFail($userId);

        // Generate new password if not provided
        if (!$newPassword) {
            $newPassword = Str::random(12);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Update the encrypted password in user_passwords table
        UserPassword::updateOrCreate(
            ['user_id' => $user->id],
            ['password_encrypted' => Crypt::encryptString($newPassword)]
        );

        return [
            'success' => true,
            'new_password' => $newPassword,
            'user_name' => $user->name
        ];
    }

    /**
     * Get decrypted password for a user (admin/superadmin only)
     * 
     * @param int $userId
     * @return array Returns ['success' => true, 'password' => 'decrypted_password']
     */
    public static function getUserPassword(int $userId): array
    {
        $user = User::findOrFail($userId);
        $userPassword = UserPassword::where('user_id', $userId)->first();

        if (!$userPassword) {
            return [
                'success' => false,
                'error' => 'Password not found'
            ];
        }

        try {
            $decrypted = Crypt::decryptString($userPassword->password_encrypted);
            return [
                'success' => true,
                'password' => $decrypted
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Decryption failed'
            ];
        }
    }

    /**
     * Update user password (for user's own password change)
     * 
     * @param User $user
     * @param string $newPassword
     * @return bool
     */
    public static function updateUserPassword(User $user, string $newPassword): bool
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        UserPassword::updateOrCreate(
            ['user_id' => $user->id],
            ['password_encrypted' => Crypt::encryptString($newPassword)]
        );

        return true;
    }

    /**
     * Create user with password (for new user creation)
     * 
     * @param array $userData
     * @param string $password
     * @return User
     */
    public static function createUserWithPassword(array $userData, string $password): User
    {
        $user = User::create([
            ...$userData,
            'password' => Hash::make($password),
        ]);

        UserPassword::create([
            'user_id' => $user->id,
            'password_encrypted' => Crypt::encryptString($password),
        ]);

        return $user;
    }
}
