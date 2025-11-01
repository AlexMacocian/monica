<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Helpers\RequestHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class OIDCController extends Controller
{
    /**
     * Redirect the user to the Authentik authentication page.
     */
    public function redirectToProvider()
    {
        $driver = Socialite::driver('authentik');
        \Log::info('OIDC Redirect scopes', ['scopes' => $driver->getScopes()]);
        
        return $driver
            ->redirect();
    }

    /**
     * Obtain the user information from Authentik.
     */
    public function handleProviderCallback(Request $request)
    {
        \Log::info('OIDC Callback started', ['url' => $request->fullUrl(), 'params' => $request->all()]);
        
        // Add early error handling
        if (!$request->has('code')) {
            \Log::error('OIDC Callback missing authorization code', ['params' => $request->all()]);
            return redirect('/login')->withErrors(['msg' => 'Authorization code missing. Please try again.']);
        }
        
        try {
            $authentikUser = Socialite::driver('authentik')->user();
            \Log::info('OIDC User retrieved', [
                'email' => $authentikUser->email, 
                'id' => $authentikUser->id,
                'name' => $authentikUser->name,
                'raw_user' => $authentikUser->user,
                'all_data' => $authentikUser->getRaw()
            ]);
        } catch (\Exception $e) {
            \Log::error('OIDC Authentication failed', ['error' => $e->getMessage()]);
            return redirect('/login')->withErrors(['msg' => 'Authentication failed. Please try again.']);
        }

        // Check if user already exists by OIDC subject ID
        $user = User::where('oidc_sub', $authentikUser->id)->first();

        if ($user) {
            \Log::info('OIDC User found by subject ID', ['user_id' => $user->id]);
            // Update user info from OIDC provider
            $this->updateUserOIDCInfo($user, $authentikUser);
            Auth::login($user);
            \Log::info('OIDC User logged in successfully', ['user_id' => $user->id]);
            return redirect()->intended('/dashboard');
        }

        // Check if user exists by email (if email is available)
        if (!empty($authentikUser->email)) {
            $existingUser = User::where('email', $authentikUser->email)->first();

            if ($existingUser) {
                \Log::info('OIDC User found by email', ['user_id' => $existingUser->id]);
                // Link existing user to OIDC provider
                $this->updateUserOIDCInfo($existingUser, $authentikUser);
                Auth::login($existingUser);
                \Log::info('OIDC Existing user logged in successfully', ['user_id' => $existingUser->id]);
                return redirect()->intended('/dashboard');
            }
        }

        // Create new user
        \Log::info('OIDC Creating new user', ['email' => $authentikUser->email]);
        try {
            $user = $this->createUser($authentikUser);
            Auth::login($user);
            \Log::info('OIDC New user created and logged in successfully', ['user_id' => $user->id]);
            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            \Log::error('OIDC User creation failed', ['error' => $e->getMessage()]);
            return redirect('/login')->withErrors(['msg' => 'User creation failed. Please try again.']);
        }
    }    /**
     * Create a new user from OIDC provider data.
     */
    private function createUser($authentikUser)
    {
        // Generate a unique username if name is not provided
        $firstName = $authentikUser->user['given_name'] ?? explode(' ', $authentikUser->name)[0] ?? 'User';
        $lastName = $authentikUser->user['family_name'] ?? (explode(' ', $authentikUser->name)[1] ?? '');

        // Require email from OIDC provider
        if (empty($authentikUser->email)) {
            \Log::error('OIDC User missing email', ['user_id' => $authentikUser->id, 'raw_data' => $authentikUser->getRaw()]);
            throw new \Exception('Email not provided by OIDC provider');
        }
        $email = $authentikUser->email;

        // Create account and user using Monica's default method
        // Generate a secure random password for OIDC users (they won't use it)
        $randomPassword = Str::random(32);
        
        $account = \App\Models\Account\Account::createDefault(
            $firstName,
            $lastName,
            $email,
            $randomPassword,
            \App\Helpers\RequestHelper::ip(),
            'en' // Default locale
        );

        // Get the created user
        $user = $account->users()->first();
        
        // Mark email as verified for OIDC users (since they're authenticated via OIDC provider)
        $user->markEmailAsVerified();
        
        // Update user with OIDC info
        $this->updateUserOIDCInfo($user, $authentikUser);
        
        return $user;
    }

    /**
     * Update user with OIDC provider information.
     */
    private function updateUserOIDCInfo(User $user, $authentikUser)
    {
        $user->update([
            'oidc_sub' => $authentikUser->id,
            'oidc_email' => $authentikUser->email,
        ]);
    }
}