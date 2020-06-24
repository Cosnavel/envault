<?php

namespace App\Http\Livewire;

use App\AuthRequest;
use App\Notifications\AuthRequestedNotification;
use App\Rules\ValidAuthAttempt;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class Auth extends Component
{
    /**
     * @var string
     */
    public $email = '';

    /**
     * @var \App\AuthRequest|null
     */
    public $request;

    /**
     * @var string
     */
    public $token = '';

    /**
     * @var \App\User|null
     */
    public $user;

    /**
     * @return void
     */
    public function confirm()
    {
        $this->validate([
            'token' => ['required', new ValidAuthAttempt($this->request, $this->user)],
        ]);

        $this->request->delete();

        auth()->login($this->user);

        $this->user->last_login_at = carbon();
        $this->user->save();

        $this->emit('auth.confirmed');

        event(new \App\Events\Users\SignedInEvent($this->user));

        redirect()->route('home');
    }

    /**
     * @param bool $resend
     * @return void
     */
    public function processRequest($resend = false)
    {
        $token = Str::random(16);

        $this->request = $this->user->auth_requests()->create([
            'token' => Hash::make($token),
        ]);

        $this->user->notify(new AuthRequestedNotification($token));

        $this->emit('auth.request.sent');

        if ($resend) {
            $this->emit('auth.request.resent');
        }
    }

    /**
     * @return void
     */
    public function request()
    {
        $this->validate([
            'email' => ['required', 'email', 'exists:users'],
        ]);

        $this->user = User::where('email', $this->email)->firstOrFail();

        $this->processRequest();

        $this->emit('auth.requested');
    }

    /**
     * @return void
     */
    public function mount()
    {
        $this->email = request()->input('email') ?? null;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('auth');
    }
}
