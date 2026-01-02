<!-- Dark Mode Toggle -->
<div class="dark-mode-toggle-wrapper text-center mt-3">
    <label class="switch">
        <input type="checkbox" id="darkModeToggle" @if (request()->cookie('dark_mode') === 'true') checked @endif>
        <span class="slider round"></span>
    </label>
</div>
