@if (session('status'))
    <div class="alert alert-success mb-4">
        <span>{{ session('status') }}</span>
    </div>
@endif