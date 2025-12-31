<!-- Language Switcher -->
<div class="language-switcher-wrapper text-end p-3">
    @if (app()->getLocale() == 'en')
        <a href="{{ route('language.change', 'ar') }}" class="language-switcher-btn">العـربـيـة</a>
    @else
        <a href="{{ route('language.change', 'en') }}" class="language-switcher-btn">English</a>
    @endif
</div>

<!-- Dark Mode Toggle -->
<div class="dark-mode-toggle-wrapper text-center mt-3">
    <label class="switch">
        <input type="checkbox" id="darkModeToggle" @if (request()->cookie('dark_mode') === 'true') checked @endif>
        <span class="slider round"></span>
    </label>
</div>
