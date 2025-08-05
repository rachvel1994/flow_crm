<?php


use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


if (!function_exists('diffForHumans')) {
    function diffForHumans($date): ?string
    {
        return Carbon::parse($date)->diffForHumans();
    }
}

if (!function_exists('getImageUrl')) {
    function getImageUrl($url = null): ?string
    {
        return empty($url) ? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) : asset('storage/' . $url);
    }
}

if (!function_exists('dateWithoutTime')) {
    function dateWithoutTime($date): ?string
    {
        return Carbon::parse($date)->format('d-m-Y');
    }
}

if (!function_exists('money')) {
    function money($price): string
    {
        return number_format($price, 2, ',', '.') . ' ₾';
    }
}

if (!function_exists('moneyWithoutSymbol')) {
    function moneyWithoutSymbol($price): string
    {
        return number_format($price, 2, ',', '.');
    }
}


if (!function_exists('send_sms')) {
    function send_sms(string $mobile, string $template, array $params = []): void
    {
        $mobile = mobile_format($mobile);

        foreach ($params as $key => $value) {
            $template = str_replace(":$key", $value, $template);
        }

        $response = Http::timeout(30)
            ->retry(1, 200)
            ->get('http://212.72.155.180:2375/api/sendmsg.php', [
                'username' => 'T309MaUKb3irC8w',
                'password' => 'Ry3L0n__4a5EQk96Hj45',
                'num' => $mobile,
                'msg' => $template,
                'utf' => 1,
            ]);
    }
}

if (!function_exists('mobile_format')) {
    function mobile_format($mobile): string
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);

        if (str_starts_with($mobile, '+995')) {
            $mobile = substr($mobile, 1);
        } elseif (!str_starts_with($mobile, '995')) {
            $mobile = '995' . $mobile;
        }

        return $mobile;
    }
}

if (!function_exists('generateSecurePassword')) {

    function generateSecurePassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $all = $upper . $lower . $digits . $special;

        $password = substr(str_shuffle($upper), 0, 2) .
            substr(str_shuffle($lower), 0, 4) .
            substr(str_shuffle($digits), 0, 2) .
            substr(str_shuffle($special), 0, 2);

        $remaining = $length - strlen($password);
        $password .= substr(str_shuffle($all), 0, $remaining);

        return str_shuffle($password);
    }
}


if (!function_exists('single_pay')) {
    function single_pay($id): void
    {
        $user = auth()->user();
        if (!$user) return;

        $parcel = Parcel::query()
            ->where('id', $id)
            ->where('is_payed', false)
            ->first();

        if (!$parcel) {
            session()->flash('error', 'ამ ამანათისთვის გასასტუმრებელი თანხა არ მოიძებნა.');
            return;
        }

        if ($parcel->price <= 0) {
            return;
        }


        $amount = $parcel->price - $parcel->amount_payed;

        if ($user->balance >= $amount) {
            $parcel->update([
                'amount_payed' => $parcel->price,
                'is_payed' => true,
            ]);

            $user->update([
                'balance' => $user->balance - $amount,
                'debt' => $user->debt - $amount,
            ]);
            $shopOrderId = (string)Str::uuid();

            Transaction::query()->create([
                'amount' => $parcel->price,
                'bank_type' => 'local',
                'status' => 'completed',
                'user_id' => $user->id,
                'shop_order_id' => $shopOrderId,
            ]);

            session()->flash('success', 'გადახდა წარმატებით შესრულდა.');
        } else {
            session()->flash('error', 'ბალანსი არასაკმარისია. საჭირო თანხა: ' . number_format($amount, 2, ',', '.') . ' ₾');
        }
    }
}

if (!function_exists('multi_pay')) {
    function multi_pay(array $ids): void
    {
        $user = auth()->user();
        if (!$user) return;

        $parcels = Parcel::query()
            ->whereIn('id', $ids)
            ->where('is_payed', false)
            ->get();

        if ($parcels->isEmpty()) {
            session()->flash('info', 'გასასტუმრებელი ამანათები ვერ მოიძებნა.');
            return;
        }

        $total = $parcels->sum(fn($parcel) => $parcel->price - $parcel->amount_payed);

        if ($user->balance < $total) {
            session()->flash('error', 'ბალანსი არასაკმარისია. საჭირო თანხა: ' . number_format($total, 2, ',', '.') . ' ₾');
            return;
        }

        if ($total <= 0) {
            return;
        }

        foreach ($parcels as $parcel) {
            $parcel->update([
                'amount_payed' => $parcel->price,
                'is_payed' => true,
            ]);

            $shopOrderId = (string)Str::uuid();

            Transaction::query()->create([
                'amount' => $parcel->price,
                'bank_type' => 'local',
                'status' => 'completed',
                'user_id' => $user->id,
                'shop_order_id' => $shopOrderId,
            ]);
        }

        $user->update([
            'balance' => $user->balance - $total,
            'debt' => $user->debt - $total,
        ]);

        session()->flash('success', 'გადახდები წარმატებით შესრულდა.');
    }
}

if (!function_exists('initiatePayment')) {
    /**
     * @param float $amount
     * @param string $bank
     * @return void
     * @throws ConnectionException
     */
    function initiatePayment(float $amount, string $bank): void
    {
        if ($bank == 'bog') {
            bog_pay($amount, $bank);
        }
    }
}

if (!function_exists('bog_pay')) {
    /**
     * @throws ConnectionException
     */
    function bog_pay($amount, $bank)
    {
        $shopOrderId = (string)Str::uuid();

        // Step 1: Get access token
        $authResponse = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode('74831:hc7m229y7Glp'),
            ])
            ->post('https://oauth2.bog.ge/auth/realms/bog/protocol/openid-connect/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$authResponse->ok()) {
            Log::error('BOG Auth failed', [
                'status' => $authResponse->status(),
                'body' => $authResponse->body(),
            ]);
            return null;
        }

        $token = $authResponse->json('access_token');

        $payload = [
            'callback_url' => route('payment.handleBogCallback', ['shop_order_id' => $shopOrderId]),
            'external_order_id' => $shopOrderId,
            'purchase_units' => [
                'currency' => 'GEL',
                'total_amount' => $amount,
                'basket' => [[
                    'unit_price' => $amount,
                    'quantity' => 1,
                    'product_id' => $shopOrderId,
                ]],
            ],
            'locale' => 'ka',
            'shop_order_id' => $shopOrderId,
            'capture_method' => 'AUTOMATIC',
            'redirect_urls' => [
                'fail' => route('profile'),
                'success' => route('profile'),
            ],
        ];


        $response = Http::withToken($token)
            ->post('https://api.bog.ge/payments/v1/ecommerce/orders', $payload);

        if ($response->failed() || !$response->json('id')) {
            Log::error('BOG Payment creation failed', [
                'status' => $response->status(),
                'request' => $payload,
                'body' => $response->body(),
            ]);
            return null;
        }

        // Save transaction
        Transaction::create([
            'transaction_id' => $response['id'],
            'bank_type' => $bank,
            'user_id' => auth()->id(),
            'amount' => $amount,
            'status' => 'created',
            'shop_order_id' => $shopOrderId,
        ]);

        $redirectUrl = $response->json('_links.redirect.href');

        if (!$redirectUrl) {
            Log::error('Missing redirect URL in BOG response.', ['response' => $response->json()]);
            return null;
        }

        return redirect()->away($redirectUrl);
    }
}

if (!function_exists('priority_array')) {
    function priority_array(?string $priority = null): array|null
    {
        $priorities = [
            'low' => [
                'label' => 'დაბალი',
                'color' => 'gray',
            ],
            'medium' => [
                'label' => 'საშუალო',
                'color' => 'blue',
            ],
            'high' => [
                'label' => 'მაღალი',
                'color' => 'orange',
            ],
            'emergency' => [
                'label' => 'გადაუდებელი',
                'color' => 'red',
            ],
        ];

        return $priority !== null
            ? ($priorities[$priority] ?? null)
            : $priorities;
    }
}

if (!function_exists('socials')) {
    function socials(?string $social = null): mixed
    {
        $socials = [
            'Facebook' => 'Facebook',
            'Messenger' => 'Messenger',
            'Instagram' => 'Instagram',
            'WhatsApp' => 'WhatsApp',
            'Viber' => 'Viber',
            'TikTok' => 'TikTok',
            'YouTube' => 'YouTube',
            'Telegram' => 'Telegram',
            'LinkedIn' => 'LinkedIn',
            'Twitter' => 'Twitter (X)',
            'Snapchat' => 'Snapchat',
            'Pinterest' => 'Pinterest',
            'Skype' => 'Skype',
            'Threads' => 'Threads',
        ];

        return $social !== null
            ? ($socials[$social] ?? null)
            : $socials;
    }
}

if (!function_exists('languages')) {
    function languages(?string $key = null): mixed
    {
        $languageMap = [
            'georgian' => 'ქართული',
            'russian' => 'რუსული',
            'english' => 'ინგლისური',
            'german' => 'გერმანული',
            'turkish' => 'თურქული',
            'armenian' => 'სომხური',
            'chinese' => 'ჩინური',
        ];

        if ($key !== null) {
            return $languageMap[$key] ?? null;
        }

        return $languageMap;
    }
}
