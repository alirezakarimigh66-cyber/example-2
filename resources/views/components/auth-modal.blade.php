@php
    $initialMode = old('auth_mode', request('auth')) === 'register' ? 'register' : 'login';
    $shouldOpen = $errors->any() || in_array(request('auth'), ['login', 'register'], true);
@endphp
<dialog id="auth-modal" class="auth-modal" aria-labelledby="auth-title" aria-describedby="auth-description" data-mode="{{ $initialMode }}" @if($shouldOpen) open data-auto-open @endif>
    <div class="modal-content">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="auth-title">حساب دوپامین شما</h2>
                <p id="auth-description" class="muted mt-2">با شماره موبایل و رمز عبور ادامه دهید.</p>
            </div>
            <button type="button" class="close-button" data-auth-close aria-label="بستن پنجره ورود">×</button>
        </div>
        <div class="auth-tabs flex gap-6" role="tablist" aria-label="ورود یا ثبت‌نام">
            @foreach(['login' => 'ورود', 'register' => 'ثبت‌نام'] as $mode => $label)
                <button id="tab-{{ $mode }}" type="button" role="tab" aria-controls="panel-{{ $mode }}" aria-selected="{{ $initialMode === $mode ? 'true' : 'false' }}" tabindex="{{ $initialMode === $mode ? '0' : '-1' }}" data-auth-tab="{{ $mode }}">{{ $label }}</button>
            @endforeach
        </div>
        <div id="auth-errors" class="error-summary" role="alert" tabindex="-1" @if(!$errors->any()) hidden @endif>
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @foreach(['login' => 'ورود به حساب', 'register' => 'ساخت حساب'] as $mode => $submitLabel)
            <section id="panel-{{ $mode }}" role="tabpanel" aria-labelledby="tab-{{ $mode }}" @if($initialMode !== $mode) hidden @endif>
                <form method="POST" action="{{ route($mode.'.store') }}" data-auth-form="{{ $mode }}" class="flex flex-col gap-5">
                    @csrf
                    <input type="hidden" name="auth_mode" value="{{ $mode }}">
                    @if($mode === 'register')
                        <div class="field">
                            <label for="register-name">نام و نام خانوادگی</label>
                            <input id="register-name" name="name" autocomplete="name" required maxlength="255" value="{{ old('name') }}" aria-describedby="register-name-error">
                            <p class="field-error" id="register-name-error"></p>
                        </div>
                    @endif
                    <div class="field">
                        <label for="{{ $mode }}-phone">شماره موبایل</label>
                        <input id="{{ $mode }}-phone" name="phone" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="09123456789" required maxlength="30" pattern="(?:0|\+98|0098|98)9[0-9۰-۹٠-٩]{9}" value="{{ old('phone') }}" aria-describedby="{{ $mode }}-phone-hint {{ $mode }}-phone-error">
                        <p id="{{ $mode }}-phone-hint" class="field-hint">شماره ایران، با 09 یا ‎+98</p>
                        <p class="field-error" id="{{ $mode }}-phone-error"></p>
                    </div>
                    <div class="field">
                        <label for="{{ $mode }}-password">رمز عبور</label>
                        <div class="password-field flex">
                            <input id="{{ $mode }}-password" type="password" name="password" dir="ltr" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" required @if($mode === 'register') minlength="12" @endif maxlength="{{ $mode === 'register' ? 72 : 1024 }}" aria-describedby="{{ $mode }}-password-error {{ $mode }}-password-hint">
                            <button type="button" data-password-toggle="{{ $mode }}-password" aria-controls="{{ $mode }}-password" aria-pressed="false">نمایش</button>
                        </div>
                        <p id="{{ $mode }}-password-hint" class="field-hint">{{ $mode === 'register' ? 'حداقل ۱۲ کاراکتر، شامل حروف بزرگ و کوچک انگلیسی و عدد.' : 'رمز عبور حساب خود را وارد کنید.' }}</p>
                        <p class="field-error" id="{{ $mode }}-password-error"></p>
                    </div>
                    @if($mode === 'register')
                        <div class="field">
                            <label for="register-password_confirmation">تکرار رمز عبور</label>
                            <div class="password-field flex">
                                <input id="register-password_confirmation" type="password" name="password_confirmation" dir="ltr" autocomplete="new-password" required minlength="12" maxlength="72" aria-describedby="register-password_confirmation-error">
                                <button type="button" data-password-toggle="register-password_confirmation" aria-controls="register-password_confirmation" aria-pressed="false">نمایش</button>
                            </div>
                            <p class="field-error" id="register-password_confirmation-error"></p>
                        </div>
                    @else
                        <label class="flex items-center gap-2"><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label>
                    @endif
                    <button type="submit" class="button primary w-full" data-submit-label="{{ $submitLabel }}">{{ $submitLabel }}</button>
                    <p data-auth-progress role="status" aria-live="polite" hidden>در حال بررسی…</p>
                </form>
            </section>
        @endforeach
        <noscript><p class="mt-4">برای تغییر فرم: <a href="{{ route('home', ['auth' => 'login']) }}">ورود</a> / <a href="{{ route('home', ['auth' => 'register']) }}">ثبت‌نام</a>. برای بستن، <a href="{{ route('home') }}">بازگشت به خانه</a>.</p></noscript>
    </div>
</dialog>
