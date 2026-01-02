<!-- Language Switcher -->
<div class="language-switcher-wrapper text-end p-3">
    @if (app()->getLocale() == 'en')
        <a href="{{ route('language.change', 'ar') }}" class="language-switcher-btn">العـربـيـة</a>
    @else
        <a href="{{ route('language.change', 'en') }}" class="language-switcher-btn">English</a>
    @endif
</div>
