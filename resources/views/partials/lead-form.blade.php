@php
    $initialStep = $errors->hasAny(['category_id', 'city_id', 'details', 'timeline', 'budget'])
        ? 1
        : ($errors->hasAny(['name', 'email', 'phone', 'postal_code']) ? 2 : ($errors->any() ? 3 : 1));
@endphp

<form class="lead-form" data-lead-stepper data-initial-step="{{ $initialStep }}" method="post" action="{{ route('leads.store') }}">
    @csrf
    <div class="lead-progress">
        <p class="lead-progress__count" data-step-count hidden aria-live="polite">Step {{ $initialStep }} of 3</p>
        <ol aria-label="Quote request steps">
            <li data-progress-step="1" @if ($initialStep === 1) aria-current="step" @endif><span>1</span><span>Project</span></li>
            <li data-progress-step="2" @if ($initialStep === 2) aria-current="step" @endif><span>2</span><span>Contact</span></li>
            <li data-progress-step="3" @if ($initialStep === 3) aria-current="step" @endif><span>3</span><span>Review</span></li>
        </ol>
    </div>

    @if ($errors->any())
        <div class="lead-error-summary" role="alert">
            <strong>Some details need another look.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="lead-step" data-lead-step="1" aria-labelledby="lead-step-1-title">
        <h2 id="lead-step-1-title" data-step-title tabindex="-1">About your project</h2>
        <p class="lead-step__intro">Choose a service and location, then tell us a little about the work.</p>
        <div class="form-grid">
            <div class="field">
                <label for="lead-category">Service</label>
                <select id="lead-category" name="category_id" required>
                    <option value="">Choose a service</option>
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}" @selected((string) old('category_id', $selectedCategory ?? ($category->id ?? '')) === (string) $categoryOption->id)>{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="lead-city">Project city</label>
                <select id="lead-city" name="city_id" required>
                    <option value="">Choose a city</option>
                    @foreach ($cities as $cityOption)
                        <option value="{{ $cityOption->id }}" @selected((string) old('city_id', $selectedCity ?? ($city->id ?? '')) === (string) $cityOption->id)>{{ $cityOption->name }}, {{ $cityOption->country->code }}</option>
                    @endforeach
                </select>
                @error('city_id')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="field">
            <label for="lead-details">What needs doing?</label>
            <textarea id="lead-details" name="details" minlength="20" maxlength="5000" placeholder="A few details will help providers understand your project." required>{{ old('details') }}</textarea>
            @error('details')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="lead-timeline">When would you like the work done?</label>
                <select id="lead-timeline" name="timeline">
                    <option value="">Choose a timeline</option>
                    <option value="asap" @selected(old('timeline') === 'asap')>As soon as possible</option>
                    <option value="within_2_weeks" @selected(old('timeline') === 'within_2_weeks')>Within 2 weeks</option>
                    <option value="within_1_month" @selected(old('timeline') === 'within_1_month')>Within a month</option>
                    <option value="flexible" @selected(old('timeline') === 'flexible')>I'm flexible</option>
                </select>
                @error('timeline')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="lead-budget">Approximate budget <span class="field__optional">Optional</span></label>
                <input id="lead-budget" name="budget" value="{{ old('budget') }}" maxlength="60" placeholder="For example, £500–£1,000 or $1,000–$2,000">
                @error('budget')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="lead-step__actions lead-stepper-controls">
            <span></span>
            <button class="button" type="button" data-step-next>Continue to contact details</button>
        </div>
    </section>

    <section class="lead-step" data-lead-step="2" aria-labelledby="lead-step-2-title">
        <h2 id="lead-step-2-title" data-step-title tabindex="-1">Your contact details</h2>
        <p class="lead-step__intro">Providers need these details to respond to your request.</p>
        <div class="form-grid">
            <div class="field">
                <label for="lead-name">Your name</label>
                <input id="lead-name" name="name" value="{{ old('name') }}" maxlength="120" autocomplete="name" required>
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="lead-email">Email address</label>
                <input id="lead-email" type="email" name="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" required>
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="lead-phone">Phone number</label>
                <input id="lead-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="24" autocomplete="tel" required>
                @error('phone')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="lead-postcode">Postcode / ZIP code</label>
                <input id="lead-postcode" name="postal_code" value="{{ old('postal_code') }}" maxlength="16" autocomplete="postal-code" required>
                @error('postal_code')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="lead-step__actions lead-stepper-controls">
            <button class="button light" type="button" data-step-back>Back</button>
            <button class="button" type="button" data-step-next>Review your request</button>
        </div>
    </section>

    <section class="lead-step" data-lead-step="3" aria-labelledby="lead-step-3-title">
        <h2 id="lead-step-3-title" data-step-title tabindex="-1">Review your request</h2>
        <p class="lead-step__intro">Check your details before sending. You can go back to make changes.</p>
        <dl class="lead-review" data-lead-review>
            <div><dt>Service</dt><dd data-review-value="category_id">Not selected</dd></div>
            <div><dt>Project city</dt><dd data-review-value="city_id">Not selected</dd></div>
            <div><dt>Project details</dt><dd data-review-value="details">Not provided</dd></div>
            <div><dt>Timeline</dt><dd data-review-value="timeline">Not provided</dd></div>
            <div><dt>Budget</dt><dd data-review-value="budget">Not provided</dd></div>
            <div><dt>Your name</dt><dd data-review-value="name">Not provided</dd></div>
            <div><dt>Email</dt><dd data-review-value="email">Not provided</dd></div>
            <div><dt>Phone</dt><dd data-review-value="phone">Not provided</dd></div>
            <div><dt>Postcode / ZIP code</dt><dd data-review-value="postal_code">Not provided</dd></div>
        </dl>
        <p class="lead-privacy-note">Your contact details are only shared with matched providers so they can respond.</p>
        <div class="field lead-consent">
            <label><input type="checkbox" name="consent" value="1" @checked(old('consent')) required> I agree that {{ $siteName }} may share my request and contact details with up to five matching service providers so they can respond. I have read the <a href="{{ route('landing.page', 'privacy') }}">Privacy Policy</a>.</label>
            @error('consent')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="field lead-consent">
            <label><input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent'))> Send me occasional home improvement tips and updates. I can unsubscribe at any time.</label>
        </div>
        <div class="lead-step__actions lead-stepper-controls">
            <button class="button light" type="button" data-step-back>Back</button>
            <button class="button" type="submit">Send my free request</button>
        </div>
        <button class="button lead-submit-fallback" type="submit">Send my free request</button>
    </section>

    <div style="position:absolute;left:-10000px" aria-hidden="true">
        <label for="website">Leave this field empty</label>
        <input id="website" name="website" tabindex="-1" autocomplete="off">
    </div>
    <noscript><p class="notice">All sections are shown above. Complete each one before sending your request.</p></noscript>
</form>
