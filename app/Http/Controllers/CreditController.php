<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\ProviderProfile;
use App\Models\StripePayment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class CreditController extends Controller
{
    public function index(Request $request): View
    {
        $provider = $request->user()->providerProfile;
        abort_unless($provider->status === 'approved', 403);

        return view('provider.credits.index', [
            'provider' => $provider,
            'packs' => config('marketplace.credit_packs'),
            'currencySymbol' => $provider->serviceAreas()->with('country')->first()?->country->currency_symbol ?? '£',
            'currencyCode' => $provider->serviceAreas()->with('country')->first()?->country->currency ?? 'GBP',
            'transactions' => $provider->transactions()->latest()->paginate(15),
            'title' => 'Credits and billing | '.config('marketplace.name'),
            'description' => 'Manage credits for unlocking matched home service leads.',
            'indexable' => false,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $provider = $request->user()->providerProfile;
        abort_unless($provider->status === 'approved', 403);
        abort_unless(config('services.stripe.secret'), 503, 'Stripe payments are not configured.');

        $data = $request->validate(['pack' => ['required', 'string', 'in:starter,growth,scale']]);
        $pack = config('marketplace.credit_packs.'.$data['pack']);
        $country = $provider->serviceAreas()->with('country')->first()?->country;
        $currency = strtolower($country?->currency ?? 'GBP');
        $client = new StripeClient(config('services.stripe.secret'));
        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $request->user()->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $pack['price_cents'],
                    'product_data' => [
                        'name' => $pack['credits'].' '.config('marketplace.name').' credits',
                        'description' => 'Credits for unlocking matched service leads.',
                    ],
                ],
            ]],
            'metadata' => [
                'provider_profile_id' => (string) $provider->id,
                'credits' => (string) $pack['credits'],
            ],
            'success_url' => route('provider.credits.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('provider.credits.index'),
        ]);

        StripePayment::create([
            'provider_profile_id' => $provider->id,
            'checkout_session_id' => $session->id,
            'amount_cents' => $pack['price_cents'],
            'currency' => strtoupper($currency),
            'credits' => $pack['credits'],
            'status' => 'pending',
        ]);

        return redirect()->away($session->url);
    }

    public function success(): View
    {
        return view('provider.credits.success', [
            'title' => 'Payment processing | '.config('marketplace.name'),
            'description' => 'Your credit purchase is being confirmed.',
            'indexable' => false,
        ]);
    }

    public function webhook(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        abort_unless($secret, 503, 'Stripe webhook verification is not configured.');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret,
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            Log::warning('Rejected Stripe webhook with invalid payload or signature.');

            return response('Invalid webhook signature.', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object;
        DB::transaction(function () use ($event, $session) {
            $payment = StripePayment::query()
                ->where('checkout_session_id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'paid') {
                return;
            }

            abort_unless(
                $session->payment_status === 'paid'
                && (int) $session->amount_total === $payment->amount_cents
                && strtoupper((string) $session->currency) === $payment->currency
                && (int) $session->metadata->provider_profile_id === $payment->provider_profile_id
                && (int) $session->metadata->credits === $payment->credits,
                422,
                'Stripe payment details did not match the stored checkout.',
            );

            $provider = ProviderProfile::query()->lockForUpdate()->findOrFail($payment->provider_profile_id);
            $provider->increment('credit_balance', $payment->credits);
            $balance = (int) $provider->fresh()->credit_balance;
            CreditTransaction::create([
                'provider_profile_id' => $provider->id,
                'amount' => $payment->credits,
                'balance_after' => $balance,
                'type' => 'purchase',
                'description' => 'Purchased '.$payment->credits.' credits',
                'reference' => 'stripe-session:'.$payment->checkout_session_id,
            ]);
            $payment->update(['status' => 'paid', 'stripe_event_id' => $event->id]);
        });

        return response()->json(['received' => true]);
    }
}
