<x-mail::message>
# Password Changed Successfully

Hello {{ $user->name }},

We wanted to let you know that your account password was **successfully updated**.

**Details:**
- **Account:** {{ $user->email }}
- **Time:** {{ now()->format('F j, Y, g:i a') }}
- **IP Address:** {{ $ip }}

If you did **not** make this change, please reset your password immediately and contact our support team.

<x-mail::button :url="config('app.url') . '/login'">
Go to Login
</x-mail::button>

Stay safe,
**{{ config('app.name') }} Team**
</x-mail::message>
