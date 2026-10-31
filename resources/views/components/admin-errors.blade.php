@if($errors->any())
    <div class="error-summary w-full" role="alert" tabindex="-1">
        <p>لطفاً موارد زیر را اصلاح کنید:</p>
        <ul class="list-disc list-inside mt-2">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
