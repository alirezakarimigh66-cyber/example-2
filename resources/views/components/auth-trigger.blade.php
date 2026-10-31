@props(['mode' => 'login'])
<a href="{{ route('home', ['auth' => $mode]) }}" data-auth-open="{{ $mode }}" {{ $attributes->merge(['class' => 'auth-trigger']) }}>{{ $slot }}</a>
