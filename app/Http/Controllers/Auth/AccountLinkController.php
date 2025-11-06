<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\Account\AccountLink;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\RedirectsUsers;

class AccountLinkController extends Controller
{
    use RedirectsUsers;

    /**
     * Where to redirect users after accepting the link.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Display the account link acceptance page.
     *
     * @param  string  $key
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse
     */
    public function show($key)
    {
        $accountLink = AccountLink::where('invitation_key', $key)
            ->firstOrFail();

        // Check if the current user matches the invited user
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('status', trans('settings.account_link_login_required'));
        }

        if (Auth::id() !== $accountLink->user_id) {
            return redirect()->route('dashboard')
                ->withErrors(trans('settings.account_link_wrong_user'));
        }

        return view('settings.users.accept-link')
            ->with('accountLink', $accountLink)
            ->with('key', $key);
    }

    /**
     * Accept the account link invitation.
     *
     * @param  Request  $request
     * @param  string  $key
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $key)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $accountLink = AccountLink::where('invitation_key', $key)
            ->firstOrFail();

        // Verify the user is the one invited
        if (Auth::id() !== $accountLink->user_id) {
            return redirect()->route('dashboard')
                ->withErrors(trans('settings.account_link_wrong_user'));
        }

        // Security check: verify the inviter's email
        if ($request->input('email_security') != $accountLink->invitedBy->email) {
            return redirect()->back()
                ->withErrors(trans('settings.users_error_email_not_similar'))
                ->withInput();
        }

        $user = Auth::user();
        $oldAccountId = $user->account_id;

        // Update the user's account to the new account
        $user->account_id = $accountLink->account_id;
        $user->invited_by_user_id = $accountLink->invited_by_user_id;
        $user->save();

        // Delete the account link invitation
        $accountLink->delete();

        // TODO: Optionally handle the old account (delete if no users left, etc.)

        return redirect($this->redirectPath())
            ->with('status', trans('settings.account_link_accepted_success'));
    }

    /**
     * Decline the account link invitation.
     *
     * @param  string  $key
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($key)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $accountLink = AccountLink::where('invitation_key', $key)
            ->firstOrFail();

        // Verify the user is the one invited
        if (Auth::id() !== $accountLink->user_id) {
            return redirect()->route('dashboard')
                ->withErrors(trans('settings.account_link_wrong_user'));
        }

        $accountLink->delete();

        return redirect()->route('dashboard')
            ->with('status', trans('settings.account_link_declined'));
    }
}
